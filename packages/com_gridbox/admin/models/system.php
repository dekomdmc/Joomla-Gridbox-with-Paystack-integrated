<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.path');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
use Joomla\Registry\Registry;

class gridboxModelSystem extends JModelList
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'title', 'theme', 'order_list'
            );
        }
        parent::__construct($config);
    }

    public function applySettings()
    {
        $db = JFactory::getDbo();
        $app = JFactory::getApplication();
        $obj = new stdClass();
        $obj->title = $app->input->get('title', '', 'string');
        $obj->theme = $app->input->get('theme', 0, 'int');
        $obj->id = $app->input->get('id', 0, 'int');
        $obj->page_options = $app->input->get('options', '{}', 'string');
        $db->updateObject('#__gridbox_system_pages', $obj, 'id');
    }

    public function setGridboxFilters()
    {
        $app = JFactory::getApplication();
        $ordering = $app->getUserStateFromRequest($this->context . '.ordercol', 'filter_order', null);
        $direction = $app->getUserStateFromRequest($this->context . '.orderdirn', 'filter_order_Dir', null);
        gridboxHelper::setGridboxFilters($ordering, $direction, $this->context);
    }

    public function getGridboxFilters()
    {
        $array = gridboxHelper::getGridboxFilters($this->context);
        if (!empty($array)) {
            foreach ($array as $obj) {
                $name = str_replace($this->context.'.', '', $obj->name);
                $this->setState($name, $obj->value);
            }
        }
    }

    public function setFilters()
    {
        $this->setGridboxFilters();
        $this::populateState();
    }
    
    protected function getListQuery()
    {
        $this->getGridboxFilters();
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->where('`order_list` = 0')
            ->from('#__gridbox_system_pages');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        if (!empty($items)) {
            $query = $db->getQuery(true)
                ->select('MAX(order_list) as max, COUNT(id) as count')
                ->where('`order_list` <> 0')
                ->from('#__gridbox_system_pages');
            $db->setQuery($query);
            $obj = $db->loadObject();
            if ($obj->count == 0) {
                $obj->max = 0;
            }
            foreach ($items as $value) {
                $value->order_list = ++$obj->max;
                $db->updateObject('#__gridbox_system_pages', $value, 'id');
            }
        }
        $query = $db->getQuery(true);
        $this->checkThemes();
        $query->select('id, title, theme, type, order_list, page_options')
            ->from('#__gridbox_system_pages');
        if (!gridboxHelper::checkSystemApp('preloader')) {
            $query->where('type <> '.$db->quote('preloader'));
        }
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('title LIKE ' . $search);
        }
        $orderCol = $this->state->get('list.ordering', 'id');
        $orderDirn = $this->state->get('list.direction', 'desc');
        if ($orderCol == 'order_list') {
            $orderDirn = 'ASC';
        }
        $query->order($db->escape($orderCol . ' ' . $orderDirn));
        
        return $query;
    }

    protected function checkThemes()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, theme')
            ->from('#__gridbox_system_pages');
        $db->setQuery($query);
        $pages = $db->loadObjectList();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__template_styles')
            ->where('`client_id` = 0')
            ->where('`template` = ' .$db->quote('gridbox'))
            ->where('`home` = 1');
        $db->setQuery($query);
        $default = $db->loadResult();
        if (!$default) {
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__template_styles')
                ->where('`client_id` = 0')
                ->where('`template` = ' .$db->quote('gridbox'));
            $db->setQuery($query);
            $default = $db->loadResult();
        }
        foreach ($pages as $page) {
            $query = $db->getQuery(true);
            $query->select('id')
                ->from('#__template_styles')
                ->where('`id` = ' .$db->Quote($page->theme));
            $db->setQuery($query);
            $id = $db->loadResult();
            if ($id != $page->theme) {
                $page->theme = $default;
                $db->updateObject('#__gridbox_system_pages', $page, 'id');
            }
        }
    }
    
    public function getThemes()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title')
            ->from('#__template_styles')
            ->where('`template` = ' .$db->Quote('gridbox'));
        $db->setQuery($query);
        
        return $db->loadObjectList();
    }
    
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        
        return parent::getStoreId($id);
    }
    
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);
        parent::populateState('id', 'desc');
    }
}