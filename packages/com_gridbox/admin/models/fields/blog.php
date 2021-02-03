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

class JFormFieldBlog extends JFormFieldList
{
    protected $type = 'blog';
    
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
            if (isset($array['app']) && !empty($array['app'])) {
                $appId = $array['app'];
                $query = $db->getQuery(true)
                    ->select('title')
                    ->from('#__gridbox_app')
                    ->where('id = '.$array['app']);
                $db->setQuery($query);
                $appTitle = $db->loadResult();
            }
        }
        if (!empty($appTitle)) {
            $hide = '';
            $iframe = '<iframe src="index.php?option=com_gridbox&view=category&layout=modal&tmpl=component&id=';
            $iframe .= $appId.'"></iframe>';
        }
        $doc = JFactory::getDocument();
        $doc->addStyleDeclaration("\niframe[src*='option=com_gridbox'] {\n\theight: 545px;\n}");
        $query = $db->getQuery(true)
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__gridbox_categories'))
            ->where($db->quoteName('id') . ' = ' . (int) $this->value);
        $db->setQuery($query);
        $title = $db->loadResult();
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        if ($this->value == 0) {
			$title = JText::_('ROOT');
		}
        $html = array();
        $html[] = '<span class="input-append">';
		$html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' .$title. '" placeholder="'
            .JText::_('CATEGORY').'" disabled="disabled" size="35" />';
		$html[] = '<a href="#gridbox-category-modal" class="btn" role="button"  data-toggle="modal">'
			. '<span class="icon-file"></span> '
			. JText::_('JSELECT') . '</a>';
        $html[] = '</span>';
        $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . $this->value . '" />';
        $html[] = '<div id="gridbox-category-modal" class="modal hide fade" style="width: 740px; height: 545px;'
            .' margin-left: -370px; overflow: hidden;"><div class="modal-body">'.$iframe.'</div></div>';
        include JPATH_ROOT.'/administrator/components/com_gridbox/views/layouts/select-category.php';
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