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

class gridboxModelApps extends JModelList
{
    public $appType;

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'title', 'published', 'theme', 'state', 'page_category', 'created', 'hits', 'order_list', 'author'
            );
        }
        $this->appType = $this->getType();
        $this->context = strtolower('com_gridbox.'.$this->getName().'.'.$this->appType);
        parent::__construct($config);
    }

    public function getType()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('type')
            ->from('#__gridbox_app')
            ->where('id = '.$id);
        $db->setQuery($query);
        $type = $db->loadResult();

        return $type;
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

    public function restore($id, $category)
    {
        $obj = json_decode($category);
        gridboxHelper::movePageFields($id, $obj->app_id);
        $obj->page_category = $obj->id;
        $obj->id = $id;
        $obj->order_list = 0;
        $obj->root_order_list = 0;
        JFactory::getDbo()->updateObject('#__gridbox_pages', $obj, 'id');
    }

    public function getAuthors()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('a.title, a.id, a.avatar, u.username')
            ->from('`#__gridbox_authors` AS a')
            ->leftJoin('`#__users` AS u ON '.$db->quoteName('u.id').' = '.$db->quoteName('a.user_id'));
        $db->setQuery($query);
        $authors = $db->loadObjectList();

        return $authors;
    }
    
    protected function getListQuery()
    {
        $this->getGridboxFilters();
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $layout = $app->input->get('layout', '');
        $id = $app->input->get('id', '');
        $input = JFactory::getApplication()->input;
        $category = $input->getVar('category', 0, 'get', 'int');
        if ($layout != 'modal' && !empty($category)) {
            $query = $db->getQuery(true)
                ->select('DISTINCT p.order_list')
                ->from('#__gridbox_pages AS p')
                ->where('p.app_id = '.$id)
                ->where('p.page_category <> '.$db->Quote('trashed'))
                ->where('(p.published IN (0, 1))')
                ->leftJoin('`#__gridbox_pages` AS m ON m.order_list = p.order_list')
                ->where('m.app_id = '.$id)
                ->where('m.page_category <> '.$db->Quote('trashed'))
                ->where('(m.published IN (0, 1))')
                ->where('m.id <> p.id');
            $db->setQuery($query);
            $order_list = $db->loadObjectList();
            $array = array();
            foreach ($order_list as $value) {
                $array[] = $value->order_list;
            }
            $sql = implode(',', $array);
            if (!empty($sql)) {
                $sql = ' OR `order_list` in ('.$sql.')';
            }
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_pages')
                ->where('`app_id` = '.$id)
                ->where('`order_list` = 0'.$sql)
                ->where('`page_category` <> '.$db->quote('trashed'))
                ->where('`page_category` = '.$db->quote($category))
                ->where('(published IN (0, 1))');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            if (!empty($items)) {
                $query = $db->getQuery(true)
                    ->select('MAX(order_list) as max, COUNT(id) as count')
                    ->from('#__gridbox_pages')
                    ->where('`app_id` = '.$id)
                    ->where('`order_list` <> 0')
                    ->where('`page_category` <>'.$db->quote('trashed'))
                    ->where('`page_category` = '.$db->quote($category))
                    ->where('(published IN (0, 1))');
                $db->setQuery($query);
                $obj = $db->loadObject();
                if ($obj->count == 0) {
                    $obj->max = 0;
                }
                foreach ($items as $value) {
                    $value->order_list = ++$obj->max;
                    $db->updateObject('#__gridbox_pages', $value, 'id');
                }
            }
        } else if ($layout != 'modal') {
            $query = $db->getQuery(true)
                ->select('DISTINCT p.root_order_list')
                ->from('#__gridbox_pages AS p')
                ->where('p.app_id = '.$id)
                ->where('p.page_category <> '.$db->Quote('trashed'))
                ->where('(p.published IN (0, 1))')
                ->leftJoin('`#__gridbox_pages` AS m ON m.root_order_list = p.root_order_list')
                ->where('m.app_id = '.$id)
                ->where('m.page_category <> '.$db->Quote('trashed'))
                ->where('(m.published IN (0, 1))')
                ->where('m.id <> p.id');
            $db->setQuery($query);
            $root_order_list = $db->loadObjectList();
            $array = array();
            foreach ($root_order_list as $value) {
                $array[] = $value->root_order_list;
            }
            $sql = implode(',', $array);
            if (!empty($sql)) {
                $sql = ' OR `root_order_list` in ('.$sql.')';
            }
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_pages')
                ->where('`app_id` = '.$id)
                ->where('`root_order_list` = 0'.$sql)
                ->where('`page_category` <> '.$db->Quote('trashed'))
                ->where('(published IN (0, 1))');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            if (!empty($items)) {
                $query = $db->getQuery(true)
                    ->select('MAX(root_order_list) as max, COUNT(id) as count')
                    ->from('#__gridbox_pages')
                    ->where('`app_id` = '.$id)
                    ->where('`root_order_list` <> 0')
                    ->where('`page_category` <>'.$db->Quote('trashed'))
                    ->where('(published IN (0, 1))');
                $db->setQuery($query);
                $obj = $db->loadObject();
                if ($obj->count == 0) {
                    $obj->max = 0;
                }
                foreach ($items as $value) {
                    $value->root_order_list = ++$obj->max;
                    $db->updateObject('#__gridbox_pages', $value, 'id');
                }
            }
        }
        $query = $db->getQuery(true);
        if ($layout == 'modal') {
            $query->select('title, id')
                ->from('#__gridbox_app')
                ->where('type <> '.$db->quote('system_apps'))
                ->where('type <> '.$db->quote('single'))
                ->order($db->escape('id ASC'));
            $search = $this->getState($this->context.'filter.search');
            if (!empty($search)) {
                $search = $db->quote('%' . $db->escape($search, true) . '%', false);
                $query->where('title LIKE ' . $search);
            }

            return $query;
        }
        $this->checkThemes();
        $query->select('DISTINCT p.id, p.title, p.theme, p.published, p.meta_title, p.meta_description, p.featured,
            p.meta_keywords, p.intro_image, p.page_alias, p.page_category, p.end_publishing, p.root_order_list, p.robots,
            p.share_image, p.share_title, p.share_description, sitemap_include, changefreq, priority,
            p.page_access, p.intro_text, p.created, p.language, p.app_id, p.hits, p.order_list, p.class_suffix')
            ->from('`#__gridbox_pages` AS p')
            ->where('p.app_id = '.$id)
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->leftJoin('`#__gridbox_authors_map` AS m ON '.$db->quoteName('m.page_id').' = '.$db->quoteName('p.id'));
        if (!empty($category)) {
            $this->setState('filter.search', '');
            $query->where('p.page_category = '.$db->quote($category));
        }
        if ($this->appType == 'products') {
            $query->leftJoin('#__gridbox_store_product_data AS spd ON p.id = spd.product_id')
                ->select('spd.price, spd.sku, spd.stock');
        }
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%'.$db->escape($search, true) . '%', false);
            $query->where('p.title LIKE ' . $search);
        }
        $published = $this->getState('filter.state');
        if ($app->input->get('layout') == 'modal') {
            $published = 1;
        }
        if (is_numeric($published)) {
            $query->where('p.published = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('p.published IN (0, 1)');
        }
        $theme = $this->getState('filter.theme');
        if (!empty($theme)) {
            $query->where('p.theme = '.(int)$theme);
        }
        $author = $this->getState('filter.author');
        if (!empty($author)) {
            $query->where('m.author_id = '.(int)$author);
        }
        $access = $this->getState('filter.access');
        if (!empty($access)) {
            $query->where('p.page_access = '.$db->quote($access));
        }
        $language = $this->getState('filter.language');
        if (!empty($language)) {
            $query->where('p.language = '.$db->quote($language));
        }
        $orderCol = $this->state->get('list.ordering', 'id');
        $orderDirn = $this->state->get('list.direction', 'desc');
        if ($orderCol == 'order_list') {
            $orderDirn = 'ASC';
        }
        if ($orderCol == 'order_list' && empty($category)) {
            $orderCol = 'root_order_list';
        }
        if ($orderCol == 'ordering') {
            $orderCol = 'title ' . $orderDirn . ', ordering';
        }
        if ($orderCol == 'author') {
            $orderCol = 'm.author_id';
        } else {
            $orderCol = 'p.'.$orderCol;
        }
        $query->order($db->escape($orderCol.' '.$orderDirn));
        
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
        try {
            if ($app->input->get('layout') == 'modal') {
                $items = $this->_getList($query, 0, 0);
            } else {
                $items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
            }            
        } catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }
        $db = JFactory::getDbo();
        foreach ($items as $key => $item) {
            $query = $db->getQuery(true)
                ->select('a.id, a.avatar, a.title')
                ->from('#__gridbox_authors_map AS m')
                ->where('m.page_id = '.$item->id)
                ->leftJoin('#__gridbox_authors AS a ON a.id = m.author_id')
                ->order('m.id ASC');
            $db->setQuery($query);
            $items[$key]->author = $db->loadObjectList();
        }
        $this->cache[$store] = $items;

        return $this->cache[$store];
    }
    
    protected function checkThemes()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, theme');
        $query->from('#__gridbox_pages');
        $db->setQuery($query);
        $pages = $db->loadObjectList();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from('#__template_styles')
            ->where('`client_id` = 0')
            ->where('`template` = ' .$db->Quote('gridbox'))
            ->where('`home` = 1');
        $db->setQuery($query);
        $default = $db->loadResult();
        foreach ($pages as $page) {
            $query = $db->getQuery(true);
            $query->select('id')
                ->from('#__template_styles')
                ->where('`id` = ' .$db->Quote($page->theme));
            $db->setQuery($query);
            $id = $db->loadResult();
            if ($id != $page->theme) {
                $table = JTable::getInstance('pages', 'gridboxTable');
                $table->load($page->id);
                $table->bind(array('theme' => $default));
                $table->store();
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
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        //print_r($this->context.'.filter.search');exit;
        $this->setState('filter.search', $search);
        $published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);
        $theme = $this->getUserStateFromRequest($this->context.'.filter.theme', 'theme_filter', '', 'string');
        $this->setState('filter.theme', $theme);
        $author = $this->getUserStateFromRequest($this->context.'.filter.author', 'author_filter', '', 'string');
        $this->setState('filter.author', $author);
        $access = $this->getUserStateFromRequest($this->context.'.filter.access', 'access_filter', '', 'string');
        $this->setState('filter.access', $access);
        $language = $this->getUserStateFromRequest($this->context.'.filter.language', 'language_filter', '', 'string');
        $this->setState('filter.language', $language);
        parent::populateState('id', 'desc');
    }
}