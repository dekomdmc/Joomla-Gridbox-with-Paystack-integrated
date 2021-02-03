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

class JFormFieldCreate extends JFormFieldList
{
    protected $type = 'create';
    
    protected function getInput()
    {
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $doc = JFactory::getDocument();
        $doc->addStyleDeclaration("\niframe[src*='option=com_gridbox'] {\n\theight: 545px;\n}");
        if ($this->value === 0 || $this->value === '0') {
            $title = JText::_('PAGES');
        } else {
            $query = $db->getQuery(true)
                ->select($db->quoteName('title'))
                ->from($db->quoteName('#__gridbox_app'))
                ->where($db->quoteName('id').' = '.(int)$this->value);
            $db->setQuery($query);
            $title = $db->loadResult();
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        }
        $iframe = '<iframe src="index.php?option=com_gridbox&view=pages&layout=apps&edit_type=create&tmpl=component"></iframe>';
        $html = array();
        $html[] = '<span class="input-append">';
        $html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' .$title. '" placeholder="'
            .JText::_('APP').'" disabled="disabled" size="35" />';
        $html[] = '<a href="#gridbox-app-modal" class="btn" role="button"  data-toggle="modal">'
            . '<span class="icon-file"></span> '
            . JText::_('JSELECT') . '</a>';
        $html[] = '</span>';
        $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . $this->value . '" />';
        $html[] = '<div id="gridbox-app-modal" class="modal hide fade" style="width: 740px; height: 545px;'
            .' margin-left: -370px; overflow: hidden;"><div class="modal-body">'.$iframe.'</div></div>';
        
        return implode("\n", $html);
    }
}