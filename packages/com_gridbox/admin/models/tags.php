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

class gridboxModelTags extends JModelList
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'title', 'published', 'state', 'hits', 'order_list'
            );
        }
        parent::__construct($config);
    }

    public function updateTags()
    {
        $input = JFactory::getApplication()->input;
        $obj = new stdClass();
        $obj->title = $input->get('category_title', '', 'string');
        $obj->alias = $input->get('category_alias', '', 'string');
        $obj->id = $input->get('category-id', 0, 'int');
        $db = JFactory::getDbo();
        if (empty($obj->alias)) {
            $obj->alias = $obj->title;
        }
        $obj->alias = gridboxHelper::getAlias($obj->alias, '#__gridbox_tags', $obj->id);
        $obj->access = $input->get('category_access', '', 'string');
        $obj->language = $input->get('category_language', '', 'string');
        $obj->description = $input->get('category_description', '', 'raw');
        $obj->image = $input->get('category_intro_image', '', 'string');
        $obj->meta_title = $input->get('category_meta_title', '', 'string');
        $obj->meta_description = $input->get('category_meta_description', '', 'raw');
        $obj->meta_keywords = $input->get('category_meta_keywords', '', 'string');
        $obj->robots = $input->get('category_robots', '', 'string');
        $obj->share_image = $input->get('category_share_image', '', 'string');
        $obj->share_title = $input->get('category_share_title', '', 'string');
        $obj->share_description = $input->get('category_share_description', '', 'string');
        $obj->sitemap_include = $input->get('category_sitemap_include', 0, 'int');
        $obj->changefreq = $input->get('category_changefreq', 'monthly', 'string');
        $obj->priority = $input->get('category_priority', '0.5', 'string');
        $db->updateObject('#__gridbox_tags', $obj, 'id');
    }

    public function addTag($title)
    {
        $db = JFactory::getDbo();
        $obj = new stdClass();
        $obj->id = 0;
        $obj->title = $title;
        $obj->alias = gridboxHelper::getAlias($title, '#__gridbox_tags');
        $db->insertObject('#__gridbox_tags', $obj);
    }

    public function duplicate($pks)
    {
        $db = JFactory::getDbo();
        foreach ($pks as $pk) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_tags')
                ->where('id = '.$pk);
            $db->setQuery($query);
            $obj = $db->loadObject();
            $obj->id = 0;
            $obj->hits = 0;
            $obj->published = 0;
            $obj->title = $this->getNewTitle($obj->title);
            $obj->alias = gridboxHelper::getAlias($obj->alias, '#__gridbox_tags');
            $db->insertObject('#__gridbox_tags', $obj);
        }
    }

    public function getNewTitle($title)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from('#__gridbox_tags')
            ->where('`title` = ' .$db->Quote($title));
        $db->setQuery($query);
        $id = $db->loadResult();
        if (!empty($id)) {
            $title = JString::increment($title);
            $title = $this->getNewTitle($title);
        }
        
        return $title;
    }

    public function publish($cid, $value)
    {
        $db = JFactory::getDbo();
        foreach ($cid as $id) {
            $obj = new stdClass();
            $obj->id = $id * 1;
            $obj->published = $value * 1;
            $db->updateObject('#__gridbox_tags', $obj, 'id');
        }
    }

    public function delete($cid)
    {
        $db = JFactory::getDbo();
        foreach ($cid as $id) {
            $query = $db->getQuery(true)
                ->delete('#__gridbox_tags')
                ->where('id = '.$id);
            $db->setQuery($query)
                ->execute();
            $query = $db->getQuery(true)
                ->delete('#__gridbox_tags_map')
                ->where('tag_id = '.$id);
            $db->setQuery($query)
                ->execute();
        }
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
        $layout = $app->input->get('layout', '');
        if ($layout != 'apps' && $layout != 'modal') {
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_tags')
                ->where('`order_list` = 0');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            if (!empty($items)) {
                $query = $db->getQuery(true)
                    ->select('MAX(order_list) as max, COUNT(id) as count')
                    ->from('#__gridbox_tags')
                    ->where('`order_list` <> 0');
                $db->setQuery($query);
                $obj = $db->loadObject();
                if ($obj->count == 0) {
                    $obj->max = 0;
                }
                foreach ($items as $value) {
                    $value->order_list = ++$obj->max;
                    $db->updateObject('#__gridbox_tags', $value, 'id');
                }
            }
        }
        $query = $db->getQuery(true);
        if ($layout == 'apps') {
            $query->select('title, id')
                ->from('#__gridbox_app')
                ->where('type <> '.$db->quote('system_apps'))
                ->where('type <> '.$db->quote('single'))
                ->order($db->escape('order_list DESC'));
            $search = $this->getState('filter.search');
            if (!empty($search)) {
                $search = $db->quote('%' . $db->escape($search, true) . '%', false);
                $query->where('title LIKE ' . $search);
            }

            return $query;
        }  else if ($layout == 'modal') {
            $id = $app->input->get('id', 0, 'int');
            $query->select('DISTINCT t.title, t.id')
                ->from('#__gridbox_tags AS t')
                ->leftJoin('`#__gridbox_tags_map` AS tm ON t.id = tm.tag_id')
                ->leftJoin('`#__gridbox_pages` AS p ON p.id = tm.page_id')
                ->where('p.app_id = '.$id)
                ->order($db->escape('t.order_list DESC'));
            $search = $this->getState('filter.search');
            if (!empty($search)) {
                $search = $db->quote('%' . $db->escape($search, true) . '%', false);
                $query->where('t.title LIKE ' . $search);
            }
            $db->setQuery($query);

            return $query;
        }
        $query->select('*')
            ->from('#__gridbox_tags');
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('title LIKE ' . $search);
        }
        $published = $this->getState('filter.state');
        if (is_numeric($published)) {
            $query->where('published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(published IN (0, 1))');
        }
        $access = $this->getState('filter.access');
        if (!empty($access)) {
            $query->where('access = '.$db->quote($access));
        }
        $language = $this->getState('filter.language');
        if (!empty($language)) {
            $query->where('language = '.$db->quote($language));
        }
        $orderCol = $this->state->get('list.ordering', 'id');
        $orderDirn = $this->state->get('list.direction', 'desc');
        if ($orderCol == 'order_list') {
            $orderDirn = 'ASC';
        }
        $query->order($db->escape($orderCol . ' ' . $orderDirn));
        
        return $query;
    }

    public function getItems()
    {
        $store = $this->getStoreId();
        $app = JFactory::getApplication();
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }
        $query = $this->_getListQuery();
        try
        {
            $items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
        }
        catch (RuntimeException $e)
        {
            $this->setError($e->getMessage());
            return false;
        }
        $db = JFactory::getDbo();
        foreach ($items as $tag) {
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__gridbox_tags_map')
                ->where('tag_id = '.$tag->id);
            $db->setQuery($query);
            $tag->count = $db->loadResult();
        }
        $this->cache[$store] = $items;

        return $this->cache[$store];
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
        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'access_filter', '', 'string');
        $this->setState('filter.access', $access);
        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'language_filter', '', 'string');
        $this->setState('filter.language', $language);
        parent::populateState('id', 'desc');
    }
}