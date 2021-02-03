<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

class gridboxViewSystem extends JViewLegacy
{
    protected $item;
    
    public function display($tpl = null)
    {
        $this->item = $this->get('Item');
        $this->item->html = gridboxHelper::checkModules($this->item->html, $this->item->items);
        $this->prepareDocument();
        parent::display($tpl);
    }

    public function prepareDocument()
    {
        $doc = JFactory::getDocument();
        $doc->addScript(JUri::root(true).'/media/jui/js/jquery.min.js');
        $doc->addScript(JUri::root(true).'/media/jui/js/bootstrap.min.js');
        $time = $this->item->saved_time;
        if (!empty($time)) {
            $time = '?'.$time;
        }
        $doc->addStyleSheet(JUri::root().'templates/gridbox/css/storage/'.$this->item->type.'.css'.$time);
        $doc->setTitle($this->item->title);
        gridboxHelper::checkMoreScripts($this->item->html);
    }
}