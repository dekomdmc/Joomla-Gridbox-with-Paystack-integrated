<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldPage extends JFormFieldList
{
    protected $type = 'page';
    
    protected function getInput()
    {
    	$app = JFactory::getApplication();
        $id = $app->input->get('id');
        $db = JFactory::getDbo();
        $link = '';
        if (!empty($id)) {
            $query = $db->getQuery(true)
                ->select('link')
                ->from('#__menu')
                ->where('id = '.$id);
            $db->setQuery($query);
            $link = $db->loadResult();
        }
        $appTitle = '';
        $appId = '';
        $hide = 'jQuery("#'.$this->id.'_id").closest(".control-group").hide();';
        $iframe = '';
        if (!empty($link)) {
            $array = array();
            parse_str($link, $array);
            if (isset($array['id']) && !empty($array['id'])) {
                $pageId = $array['id'];
                $query = $db->getQuery(true)
                	->select('p.app_id')
                	->from('#__gridbox_pages AS p')
                	->where('p.id = '.$pageId)
                	->select('a.title')
                	->leftJoin('`#__gridbox_app` AS a'
                        . ' ON '
                        . $db->quoteName('a.id')
                        . ' = ' 
                        . $db->quoteName('p.app_id')
                    );
                $db->setQuery($query);
                $obj = $db->loadObject();
                if (is_object($obj)) {
                    $appTitle = $obj->title;
                    $appId = $obj->app_id;
                    if ($obj->app_id == 0) {
                        $appTitle = JText::_('PAGES');
                    }
                }
            }
        }
        if (!empty($appTitle)) {
            $hide = '';
            $iframe = '<iframe src="index.php?option=com_gridbox&view=pages&layout=modal&tmpl=component&id=';
            $iframe .= $appId.'"></iframe>';
        }
        $doc = JFactory::getDocument();
        $doc->addStyleDeclaration("\niframe[src*='option=com_gridbox'] {\n\theight: 545px;\n}");
        $html = array();
        $db	= JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__gridbox_pages'))
            ->where($db->quoteName('id') . ' = ' . (int) $this->value);
        $db->setQuery($query);
        $title = $db->loadResult();
        if (empty($title)) {
            $title = JText::_('SELECT_PAGE');
        }
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        if ($this->value == 0) {
			$value = '';
		} else {
            $value = $this->value;
        }
        $html = array();
        $html[] = '<span class="input-append">';
		$html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' .$title. '" disabled="disabled" size="35" />';
		$html[] = '<a href="#gridbox-page-modal" class="btn" role="button"  data-toggle="modal">'
			. '<span class="icon-file"></span> '
			. JText::_('JSELECT') . '</a>';
        $html[] = '</span>';
        $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . $value . '" />';
        $html[] = '<div id="gridbox-page-modal" class="modal hide fade" style="width: 740px; height: 545px;'
            .' margin-left: -370px; overflow: hidden;"><div class="modal-body">'.$iframe.'</div></div>';
        include JPATH_ROOT.'/administrator/components/com_gridbox/views/layouts/select-page.php';
        $html[] = $out;
        $script = 'jQuery(document).ready(function(){
            jQuery("#'.$this->id.'_id").closest(".control-group").before(jQuery("#select-app"));
            jQuery("#select-app").css("display", "");
            '.$hide.'
        });';
        $doc->addScriptDeclaration($script);

        return implode("\n", $html);
    }
}