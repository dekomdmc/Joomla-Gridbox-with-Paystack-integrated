<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class gridboxControllergridbox extends JControllerForm
{
    public function getModel($name = 'gridbox', $prefix = 'gridboxModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

    public function isSafari()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        
        return stripos($agent, 'Safari') !== false && stripos($agent, 'Chrome') === false;
    }

    public function compressAdaptiveImage($ind = '')
    {
        $input = JFactory::getApplication()->input;
        $image = $input->get('image', '', 'string');
        $dir = JPATH_ROOT.$image;
        $ext = strtolower(JFile::getExt($dir));
        $endExt = $ext;
        $imageCreate = $this->imageCreate($ext);
        $gd_info = gd_info();
        $dev = $input->get('dev_mode', '', 'string');
        if (!$gd_info['WebP Support'] && !empty($dev)) {
            print_r('Your PHP image library GD was compiled without WebP Support');exit;
        }
        if (gridboxHelper::$website->adaptive_images == 1 && !empty($ind) && $gd_info['WebP Support']
            && gridboxHelper::$website->adaptive_images_webp == 1 && $ext != 'webp' && !self::isSafari()) {
            $name = JFile::getName($image);
            $name = JFile::stripExt($name);
            $image = str_replace($name.'.'.$ext, $name.'.webp', $image);
            $endExt = 'webp';
        } else if (gridboxHelper::$website->compress_images == 1 && empty($ind) && $gd_info['WebP Support']
            && gridboxHelper::$website->compress_images_webp == 1 && $ext != 'webp' && !self::isSafari()) {
            $name = JFile::getName($image);
            $name = JFile::stripExt($name);
            $image = str_replace($name.'.'.$ext, $name.'.webp', $image);
            $endExt = 'webp';
        }
        $imageSave = $this->imageSave($endExt);
        $size = gridboxHelper::$website->images_max_size * 1;
        if (!empty($ind)) {
            $size = gridboxHelper::$breakpoints->{$ind} * 1;
        }
        $quality = gridboxHelper::$website->images_quality * 1;
        $path = JPATH_ROOT.'/'.IMAGE_PATH.'/compressed';
        $url = JUri::root().IMAGE_PATH.'/compressed';
        if (!empty($ind)) {
            $path .= '/'.$ind;
            $url .= '/'.$ind;
            $quality = gridboxHelper::$website->adaptive_quality * 1;
        }
        $array = explode('/', $image);
        $name = array_pop($array);
        $n = count($array);
        if (!JFolder::exists($path)) {
            JFolder::create($path);
        }
        for ($i = 2; $i < $n; $i++) {
            $path .= '/'.$array[$i];
            $url .= '/'.$array[$i];
            if (!JFolder::exists($path)) {
                JFolder::create($path);
            }
        }
        $path .= '/'.$name;
        $url .= '/'.$name;
        $exists = JFile::exists($path);
        $origFlag = JFile::exists($dir);
        if ($origFlag && !$exists && !$im = $imageCreate($dir)) {
            $path = $dir;
            $url = JUri::root(true).$image;
        } else if ($origFlag && !$exists) {
            $width = imagesx($im);
            $height = imagesy($im);
            if ($width <= $size && $height <= $size) {
                $w = $width;
                $h = $height;
            } else {
                $ratio = $width / $height;
                if ($width > $height) {
                    $w = $size;
                    $h = $size / $ratio;
                } else {
                    $h = $size;
                    $w = $size * $ratio;
                }
            }
            $out = imagecreatetruecolor($w, $h);
            if ($ext == 'png') {
                imagealphablending($out, false);
                imagesavealpha($out, true);
                $transparent = imagecolorallocatealpha($out, 255, 255, 255, 127);
                imagefilledrectangle($out, 0, 0, $w, $h, $transparent);
            }
            imagecopyresampled($out, $im, 0, 0, 0, 0, $w, $h, $width, $height);
            if ($endExt == 'png') {
                $quality = 9 - round($quality / 11.111111111111);
            }
            $imageSave($out, $path, $quality);
            imagedestroy($out);
            imagedestroy($im);
        }
        if ($origFlag) {
            header('Location: '.$url);
        }
        exit;
    }

    public function compressImagelaptop()
    {
        $this->compressAdaptiveImage('laptop');
    }

    public function compressImagetb()
    {
        $this->compressAdaptiveImage('tablet');
    }

    public function compressImagetbpt()
    {
        $this->compressAdaptiveImage('tablet-portrait');
    }

    public function compressImagesm()
    {
        $this->compressAdaptiveImage('phone');
    }

    public function compressImagesmpt()
    {
        $this->compressAdaptiveImage('phone-portrait');
    }

    public function compressImage()
    {
        self::compressAdaptiveImage();
    }

    public function imageSave($type) {
        switch ($type) {
            case 'png':
                $imageSave = 'imagepng';
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
            case 'webp':
                $imageCreate = 'imagecreatefromwebp';
                break;
            default:
                $imageCreate = 'imagecreatefromjpeg';
        }
        return $imageCreate;
    }

    public function login()
    {
        $input = JFactory::getApplication()->input;
        $login = $input->get('ba_login', '', 'string');
        $password = $input->get('ba_password', '', 'string');
        $credentials = array('username' => $login, 'password' => $password);
        $msg = '';
        if (!JFactory::getApplication()->login($credentials)) {
            $msg = JText::_('LOGIN_ERROR');
        }
        echo $msg;
        exit;
    }

    public function createPage()
    {
        gridboxHelper::checkUserEditLevel('core.create');
        $model = $this->getModel();
        $id = $model->createPage();
        echo $id;
        exit;
    }

    public function getSession()
    {
        $session = JFactory::getSession();
        echo new JResponseJson($session->getState());
        exit;
    }

    public function save($key = NULL, $urlVar = NULL)
    {
        
    }
}