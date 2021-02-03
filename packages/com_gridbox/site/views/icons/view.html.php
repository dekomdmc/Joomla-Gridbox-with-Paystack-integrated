<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

class gridboxViewIcons extends JViewLegacy
{
    public $items;

    public function display($tpl = null)
    {
        if (!JFactory::getUser()->authorise('core.edit', 'com_gridbox')) {
            JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return;
        }
        $this->items = $this->get('Item');
        $app = JFactory::getApplication();
        $input = JFactory::getApplication()->input;
        $doc = JFactory::getDocument();
        $doc->setTitle('Gridbox Editor');
        $doc->addScript(JUri::root(true) . '/media/jui/js/jquery.min.js');
        $doc->addScript(JUri::root(true) . '/media/jui/js/bootstrap.min.js');
        $doc->addScript(JURI::root() . 'components/com_gridbox/assets/js/ba-icons.js');
        $doc->addStyleSheet(JURI::root() . 'components/com_gridbox/assets/css/ba-style-editor.css');
        
        parent::display($tpl);
    }
}