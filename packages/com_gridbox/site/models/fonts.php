<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;
jimport('joomla.filesystem.file');
jimport('joomla.http.http');
jimport('joomla.filesystem.folder');

class gridboxModelFonts extends JModelItem
{
    public function getTable($type = 'Fonts', $prefix = 'gridboxTable', $config = array()) 
    {
        return JTable::getInstance($type, $prefix, $config);
    }
    
    public function getItem($id = null)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('font, styles, id, custom_src')
            ->from('`#__gridbox_fonts`')
            ->order($db->quoteName('font') . ' ASC');
        $db->setQuery($query);
        $item = $db->loadObjectList();
        
        return $item;
    }

    public function getGoogleFonts()
    {
        $file = JPATH_COMPONENT.'/libraries/google-fonts/font.json';
        $obj = JFile::read($file);
        $obj = json_decode($obj);

        return $obj;
    }
    
    public function delete()
    {
        $input = JFactory::getApplication()->input;
        $pks = $input->get('font_id', array(), 'array');
        $db = JFactory::getDbo();
        foreach ($pks as $id) {
            $query = $db->getQuery(true)
                ->select('custom_src, font')
                ->from('#__gridbox_fonts')
                ->where('id = '.$db->quote($id));
            $db->setQuery($query);
            $obj = $db->loadObject();
            if (!empty($obj->custom_src) && $obj->custom_src != 'web-safe-fonts') {
                $dir = JPATH_ROOT. '/templates/gridbox/library/fonts/';
                if (JFile::exists($dir.$obj->custom_src)) {
                    JFile::delete($dir.$obj->custom_src);
                }
                $folder = str_replace('+', '-', $obj->font);
                $files = JFolder::files($dir.$folder);
                if (count($files) == 0) {
                    JFolder::delete($dir.$folder);
                }
            }
            $db->setQuery('DELETE FROM `#__gridbox_fonts` WHERE `id` = '.$db->quote($id));
            $db->execute();
        }
    }

    public function refreshList()
    {
        $file = JPATH_COMPONENT.'/libraries/google-fonts/font.json';
        $url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyBNJxvxv5f7Xp-I0ZkmCO-Y5JyggF5AHbg';
        $http = new JHttp();
        $obj = $http->get($url);
        JFile::write($file, $obj->body);
        $fonts = JFile::read($file);
        $fonts = json_decode($fonts);
        $str = gridboxHelper::createFontString($fonts);

        return $str;
    }

    public function addFont($custom_src = '')
    {
        $input = JFactory::getApplication()->input;
        $font = $input->get('font_family', '', 'string');
        $style = $input->get('font_style', '', 'string');
        $webSafeFonts = $input->get('web_safe_fonts', '', 'string');
        if (empty($style)) {
            $style = 400;
        }
        if ($webSafeFonts == 'web-safe-fonts') {
            $custom_src = $webSafeFonts;
        }
        $font = trim($font);
        $font = str_replace(' ', '+', $font);
        if ($this->checkFont($font, $style)) {
            $table = $this->getTable();
            $array = array('font' => $font, 'styles' => $style, 'custom_src' => $custom_src);
            $table->bind($array);
            if ($table->store()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    public function checkFont($font, $style)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from('#__gridbox_fonts')
            ->where('`font` = ' .$db->quote($font))
            ->where('`styles` = ' .$db->quote($style));
        $db->setQuery($query);
        $id = $db->loadResult();
        if (empty($id)) {
            return true;
        } else {
            return false;
        }
    }
}
