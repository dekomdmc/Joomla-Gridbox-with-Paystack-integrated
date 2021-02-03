<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class gridboxViewappslist extends JViewLegacy
{
    protected $items;
    protected $state;
    protected $about;
    protected $apps;

    public function display($tpl = null) 
    {
        $this->items = $this->get('Items');
        $this->apps = gridboxHelper::getApps();
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->about = gridboxHelper::aboutUs();
        $doc = JFactory::getDocument();
        $doc->addStyleSheet('components/com_gridbox/assets/css/ba-admin.css?'.$this->about->version);
                
        parent::display($tpl);
    }

    public function createAppObject($type, $icon, $title, $system = false)
    {
        $obj = new stdClass();
        $obj->type = $type;
        $obj->icon = 'zmdi zmdi-'.$icon;
        $obj->title = $title;
        $obj->system = $system;

        return $obj;
    }

    public function getAppsList()
    {
        $list = array();
        $list[] = $this->createAppObject('single', 'file', JText::_('PAGES'));
        $list[] = $this->createAppObject('blog', 'format-color-text', JText::_('BLOG'));
        $list[] = $this->createAppObject('products', 'shopping-cart', JText::_('STORE'));
        $list[] = $this->createAppObject('comments', 'comment-more', JText::_('COMMENTS'), true);
        $list[] = $this->createAppObject('reviews', 'ticket-star', JText::_('REVIEWS'), true);
        $list[] = $this->createAppObject('blank', 'crop-free', 'Zero App');
        $list[] = $this->createAppObject('portfolio', 'camera', 'Portfolio');
        $list[] = $this->createAppObject('hotel-rooms', 'hotel', 'Hotel Rooms');
        $list[] = $this->createAppObject('photo-editor', 'camera-alt', JText::_('PHOTO_EDITOR'), true);
        $list[] = $this->createAppObject('code-editor', 'code-setting', JText::_('CODE_EDITOR'), true);
        $list[] = $this->createAppObject('performance', 'time-restore-setting', JText::_('PERFORMANCE'), true);
        $list[] = $this->createAppObject('preloader', 'spinner', JText::_('PRELOADER'), true);
        $list[] = $this->createAppObject('canonical', 'link', JText::_('CANONICAL'), true);
        $list[] = $this->createAppObject('sitemap', 'device-hub', 'XML '.JText::_('SITEMAP'), true);

        return $list;
    }

    public function preferences()
    {
        $url = 'index.php?option=com_config&amp;view=component&amp;component=com_gridbox&amp;path=';
        
        return $url;
    }
}