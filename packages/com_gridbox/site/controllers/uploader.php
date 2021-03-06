<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class gridboxControllerUploader extends JControllerForm
{

    public function checkFileExists()
    {
        gridboxHelper::checkUserEditLevel();
        $content = file_get_contents('php://input');
        $obj = json_decode($content);
        $name = $obj->title;
        $file = gridboxHelper::replace($name);
        $file = JFile::makeSafe($file.'.'.$obj->ext);
        $name = str_replace('-', '', $file);
        $name = str_replace($obj->ext, '', $name);
        $name = str_replace('.', '', $name);
        if ($name == '') {
            $file = date("Y-m-d-H-i-s").'.'.$obj->ext;
        }
        $obj->path = str_replace($obj->name, '', $obj->path).$file;
        echo JFile::exists(JPATH_ROOT.'/'.IMAGE_PATH.$obj->path);exit;
    }

    public function savePhotoEditorImage()
    {
        gridboxHelper::checkUserEditLevel();
        $content = file_get_contents('php://input');
        $obj = json_decode($content);
        if (isset($obj->title)) {
            $name = $obj->title;
            $file = gridboxHelper::replace($name);
            $file = JFile::makeSafe($file.'.'.$obj->ext);
            $name = str_replace('-', '', $file);
            $name = str_replace($obj->ext, '', $name);
            $name = str_replace('.', '', $name);
            if ($name == '') {
                $file = date("Y-m-d-H-i-s").'.'.$obj->ext;
            }
            $obj->path = str_replace($obj->name, '', $obj->path).$file;
        }
        $data = explode(',', $obj->image);
        $method = $obj->method;
        $str = $method($data[1]);
        if ($obj->ext == 'png') {
            $imageSave = $this->imageSave($obj->ext);
            $imageCreate = $this->imageCreate($obj->ext);
            $img = imagecreatefromstring($str);
            $width = imagesx($img);
            $height = imagesy($img);
            $out = imagecreatetruecolor($width, $height);
            imagealphablending($out, false);
            imagesavealpha($out, true);
            $transparent = imagecolorallocatealpha($out, 255, 255, 255, 127);
            imagefilledrectangle($out, 0, 0, $width, $height, $transparent);          
            imagecopyresampled($out, $img, 0, 0, 0, 0, $width, $height, $width, $height);
            $imageSave($out, JPATH_ROOT.'/'.IMAGE_PATH.$obj->path, 9);
        } else {
            JFile::write(JPATH_ROOT.'/'.IMAGE_PATH.$obj->path, $str);
        }
        echo JPATH_ROOT.'/'.IMAGE_PATH.$obj->path;
        exit();
    }
    
    public function moveTo()
    {
        gridboxHelper::checkUserEditLevel();
        $input = JFactory::getApplication()->input;
        $ba_image = $input->get('ba_image', '', 'string');
        $ba_folder = $input->get('ba_folder', '', 'string');
        $file = JPATH_ROOT.'/'.IMAGE_PATH.$ba_image;
        $target = JPATH_ROOT.'/'.IMAGE_PATH.$ba_folder;
        if (JFolder::exists($file)) {
            $name = explode('/', $file);
            $name = end($name);
            JFolder::move($file, $target.'/'.$name);
        } else if (JFile::exists($file)) {
            $name = JFile::getName($file);
            JFile::move($file, $target.'/'.$name);
        }        
        echo new JResponseJson(true, JText::_('SUCCESS_MOVED'));
        jexit();
    }

    public function moveTarget()
    {
        gridboxHelper::checkUserEditLevel();
        $input = JFactory::getApplication()->input;
        $ba_target = $input->get('ba_target', '', 'string');
        $ba_path = $input->get('ba_path', '', 'string');
        $target = JPATH_ROOT.'/'.IMAGE_PATH.$ba_target;
        $path = JPATH_ROOT.'/'.IMAGE_PATH.$ba_path;
        if ($ba_path == JPATH_ROOT.'/'.IMAGE_PATH) {
            $path = JPATH_ROOT.'/'.IMAGE_PATH;
        }
        $flag = $input->get('ba_flag', '', 'string');
        if ((bool)$flag == false) {
            if (!empty($target)) {
                if (JFolder::exists($target)) {
                    $name = explode('/', $target);
                    $name = end($name);
                    JFolder::move($target, $path.'/'.$name);
                } else if (JFile::exists($target)) {
                    $name = JFile::getName($target);
                    JFile::move($target, $path.'/'.$name);
                }
            }
        } else {
            $target = explode(';', $target);
            foreach ($target as $key => $item) {
                if (!empty($item)) {
                    if (JFolder::exists($item)) {
                        $name = explode('/', $item);
                        $name = end($name);
                        JFolder::move($item, $path.'/'.$name);
                    } else if (JFile::exists($item)) {
                        $name = JFile::getName($item);
                        JFile::move($item, $path.'/'.$name);
                    }
                }
            }
        }
        echo new JResponseJson($path.'/'.$name, JText::_('SUCCESS_MOVED'));
        jexit();
    }

    public function renameTarget()
    {
        gridboxHelper::checkUserEditLevel();
        $input = JFactory::getApplication()->input;
        $ba_target = $input->get('ba_target', '', 'string');
        $target = JPATH_ROOT.'/'.IMAGE_PATH.$ba_target;
        $name = $input->get('ba_name', '', 'string');
        $name = str_replace(' ', '-', $name);
        $dir = explode('/', $target);
        $n = count($dir) - 1;
        unset($dir[$n]);
        $dir = implode('/', $dir);
        if (!empty($target)) {
            if (JFolder::exists($target)) {
                JFolder::move($target, $dir.'/'.$name);
            } else if (JFile::exists($target)) {
                $ext = JFile::getExt($target);
                $name .= '.'.$ext;
                JFile::move($target, $dir.'/'.$name);
            }
        }
        echo new JResponseJson($dir.'/'.$name, JText::_('SUCCESS_RENAME'));
        jexit();
    }

    public function deleteTarget()
    {
        gridboxHelper::checkUserEditLevel();
        $input = JFactory::getApplication()->input;
        $ba_target = $input->get('ba_target', '', 'string');
        $target = JPATH_ROOT.'/'.IMAGE_PATH.$ba_target;
        $result = JText::_('SUCCESS_DELETE');
        $flag = true;
        if (!empty($target)) {
            if (JFolder::exists($target)) {
                if(!JFolder::delete($target)) {
                    $result = JText::_('DELETE_FOLDER_ERROR');
                    $flag = false;
                }
            } else if (JFile::exists($target)) {
                if(!JFile::delete($target)) {
                    $result = JText::_('DELETE_FILE_ERROR');
                    $flag = false;
                }
            }
        }
        echo new JResponseJson($flag, $result);
        jexit();
    }

    public function addFolder()
    {
        gridboxHelper::checkUserEditLevel();
        $location = $this->getDir();
        $dir = $location[0];
        $input = JFactory::getApplication()->input;
        $nfolder = $input->get('new-folder', '', 'string');
        $nfolder = str_replace(' ', '-', $nfolder);
        if (JFolder::create($dir.'/'.$nfolder)) {
            $result = JText::_('FOLDER_IS_CREATED');
        } else {
            $result = JText::_('FOLDER_IS_NOT_CREATED');
        }
        echo '<input type="hidden" id="ba-message-data" value="'.$result.'">';
        ?>
            <script type="text/javascript">
                var msg = document.getElementById("ba-message-data").value;
                window.parent.postMessage(msg, "*");
            </script>

        <?php
        exit;
    }
    
    public function getDir()
    {
        gridboxHelper::checkUserEditLevel();
        $redirect = 'index.php?option=com_gridbox&view=uploader&tmpl=component';
        $dir = JPATH_ROOT.'/'.IMAGE_PATH;
        $input = JFactory::getApplication()->input;
        $folder = $input->get('current-dir', '', 'string');
        $dir .= $folder;
        $redirect .= '&folder='.$dir;
        $array = array($dir, $redirect);

        return $array;
        
    }
    
    public function delete()
    {
        gridboxHelper::checkUserEditLevel();
        $location = $this->getDir();
        $dir = $location[0];
        $redirect = $location[1];
        $input = JFactory::getApplication()->input;
        $items = $input->get('ba-rm', '', 'array');
        $result = JText::_('SUCCESS_DELETE');
        foreach ($items as $item) {
            if ($item != '') {
                if (JFolder::exists($dir. '/' .$item)) {
                    if(!JFolder::delete($dir. '/' .$item)) {
                        $result = JText::_('DELETE_FOLDER_ERROR');
                    }
                }
                if (JFile::exists($dir. '/' .$item)) {
                    if(!JFile::delete($dir. '/' .$item)){
                        $result = JText::_('DELETE_FILE_ERROR');
                    }
                }
            }
        }
        echo '<input type="hidden" id="ba-message-data" value="'.$result.'">';
        ?>
            <script type="text/javascript">
                var msg = document.getElementById("ba-message-data").value;
                window.parent.postMessage(msg, "*");
            </script>

        <?php
        exit;
    }

    public function uploadAjax()
    {
        gridboxHelper::checkUserEditLevel();
        $input = JFactory::getApplication()->input;
        $folder = $input->get('folder', '', 'string');
        $file = $input->get('file', '', 'raw');
        $ext = strtolower(JFile::getExt($file));
        $name = str_replace('.'.$ext, '', $file);
        $file = gridboxHelper::replace($name);
        $file = JFile::makeSafe($file.'.'.$ext);
        $name = str_replace('-', '', $file);
        $name = str_replace($ext, '', $name);
        $name = str_replace('.', '', $name);
        if ($name == '') {
            $file = date("Y-m-d-H-i-s").'.'.$ext;
        }
        $dir = JPATH_ROOT.'/'.IMAGE_PATH.$folder;
        $url = JUri::root().IMAGE_PATH.$folder;
        $model = $this->getModel();
        $types = $model->getFiletypes();
        if (in_array($ext, $types)) {
            file_put_contents($dir.'/'.$file, file_get_contents('php://input'));
            $image = new stdClass;
            $image->name = $file;
            $image->path = $folder.'/'.$file;
            $image->size = filesize($dir.'/'.$file);
            $image->ext = $ext;
            $image->url = $url.'/'.$file;
            echo json_encode($image);
        }
        exit;
    }

    public function formUpload()
    {
        gridboxHelper::checkUserEditLevel();
        $input = JFactory::getApplication()->input;
        $items = $input->files->get('files', '', 'array');
        $input = JFactory::getApplication()->input;
        $folder = $input->get('current_folder', '', 'string');
        $dir = JPATH_ROOT.'/'.IMAGE_PATH.$folder;
        $contentLength = (int) $_SERVER['CONTENT_LENGTH'];
        $mediaHelper = new JHelperMedia;
        $uploadMaxFileSize = $mediaHelper->toBytes(ini_get('upload_max_filesize'));
        $url = JUri::root().IMAGE_PATH.$folder;
        $model = $this->getModel();
        $images = array();
        $types = $model->getFiletypes();
        foreach($items as $item) {
            $flag = true;
            if (($item['error'] == 1) || ($uploadMaxFileSize > 0 && $item['size'] > $uploadMaxFileSize)) {
                $flag = false;
            }
            $ext = strtolower(JFile::getExt($item['name']));
            if (in_array($ext, $types) && $flag) {
                $name = str_replace('.'.$ext, '', $item['name']);
                $file = gridboxHelper::replace($name);
                $file = JFile::makeSafe($file.'.'.$ext);
                $name = str_replace('-', '', $file);
                $name = str_replace($ext, '', $name);
                $name = str_replace('.', '', $name);
                if ($name == '') {
                    $file = date("Y-m-d-H-i-s").'.'.$ext;
                }
                JFile::upload($item['tmp_name'], $dir.$file);
                $image = new stdClass;
                $image->name = $file;
                $image->ext = $ext;
                $image->path = $folder.'/'.$file;
                $image->size = filesize($dir.$file);
                $image->url = $url.'/'.$file;
                $images[] = $image;
            }
        }
        $images = json_encode($images);
?>
    <script type="text/javascript">
        var images = <?php echo $images; ?>;
        window.parent.uploadCallback(images);
    </script>
<?php
    exit();
    }

    public function showImage()
    {
        gridboxHelper::checkUserEditLevel();
        $input = JFactory::getApplication()->input;
        $image = $input->get('image', '', 'string');
        $dir = JPATH_ROOT.'/'.IMAGE_PATH.$image;
        $ext = strtolower(JFile::getExt($dir));
        $imageCreate = $this->imageCreate($ext);
        $imageSave = $this->imageSave($ext);
        Header("Content-type: image/".$ext);
        if (!function_exists($imageCreate) || !$im = $imageCreate($dir)) {
            $f = fopen($dir, "r");
            fpassthru($f);
        } else {
            $width = imagesx($im);
            $height = imagesy($im);
            $ratio = $width / $height;
            if ($width > $height) {
                $w = 100;
                $h = 100 / $ratio;
            } else {
                $h = 100;
                $w = 100 * $ratio;
            }
            $out = imagecreatetruecolor($w, $h);
            if ($ext == 'png' || $ext == 'webp') {
                imagealphablending($out, false);
                imagesavealpha($out, true);
                $transparent = imagecolorallocatealpha($out, 255, 255, 255, 127);
                imagefilledrectangle($out, 0, 0, $w, $h, $transparent);
            }
            imagecopyresampled($out, $im, 0, 0, 0, 0, $w, $h, $width, $height);
            $imageSave($out);
            imagedestroy($im);
            imagedestroy($out);
        }
        exit;
    }

    public function imageSave($type) {
        switch ($type) {
            case 'png':
                $imageSave = 'imagepng';
                break;
            case 'gif':
                $imageSave = 'imagegif';
                break;
            case 'webp':
                $imageSave = 'imagewebp';
                break;
            default:
                $imageSave = 'imagejpeg';
        }

        return $imageSave;
    }

    public function imageCreate($type) {
        switch ($type) {
            case 'png':
                $imageCreate = 'imagecreatefrompng';
                break;
            case 'gif':
                $imageCreate = 'imagecreatefromgif';
                break;
            case 'webp':
                $imageCreate = 'imagecreatefromwebp';
                break;
            default:
                $imageCreate = 'imagecreatefromjpeg';
        }
        return $imageCreate;
    }
    
    public function upload()
    {
        gridboxHelper::checkUserEditLevel();
        $location = $this->getDir();
        $dir = $location[0];
        $model = $this->getModel();
        $input = JFactory::getApplication()->input;
        $items = $input->files->get('ba-files', '', 'array');
        $result = JText::_('SUCCESS_UPLOAD');
        $language = JFactory::getLanguage();
        $language->load('com_media', JPATH_ADMINISTRATOR);
        $mediaHelper = new JHelperMedia;
        $postMaxSize = $mediaHelper->toBytes(ini_get('post_max_size'));
        $memoryLimit = $mediaHelper->toBytes(ini_get('memory_limit'));
        $contentLength = (int) $_SERVER['CONTENT_LENGTH'];
        if (($postMaxSize > 0 && $contentLength > $postMaxSize)
            || ($memoryLimit != -1 && $contentLength > $memoryLimit)) {
            $result = $language->_('COM_MEDIA_ERROR_WARNUPLOADTOOLARGE');
        } else {
            $uploadMaxFileSize = $mediaHelper->toBytes(ini_get('upload_max_filesize'));
            foreach($items as $item) {
                $file['name'] = JFile::makeSafe($file['name']);
                $file['name'] = str_replace(' ', '-', $file['name']);
                if (($file['error'] == 1) || ($uploadMaxFileSize > 0 && $file['size'] > $uploadMaxFileSize)) {
                    $result = $language->_('COM_MEDIA_ERROR_WARNFILETOOLARGE');
                    break;
                } else {
                    $ext = strtolower(JFile::getExt($item['name']));
                    $flag = $model->checkExt($ext);
                    if ($flag) {
                        $name = $dir. '/' .$item['name'];
                        if(!JFile::upload( $item['tmp_name'], $name)) {
                            $result = $language->_('COM_MEDIA_ERROR_UNABLE_TO_UPLOAD_FILE');
                            break;
                        }
                    } else {
                        $result = JText::_('INVALID_EXT');
                        break;
                    }
                }                
            }
        }        
        echo '<input type="hidden" id="ba-message-data" value="'.$result.'">';
        ?>
            <script type="text/javascript">
                var msg = document.getElementById("ba-message-data").value;
                window.parent.postMessage(msg, "*");
            </script>

        <?php
        exit;
    }
}