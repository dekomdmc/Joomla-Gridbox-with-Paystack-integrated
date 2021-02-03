<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

class gridboxModelAppslist extends JModelList
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'title', 'state'
            );
        }
        parent::__construct($config);
    }

    public function addSystemApp($type)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(id)')
            ->from('#__gridbox_app')
            ->where('type = '.$db->quote('system_apps'))
            ->where('title = '.$db->quote($type));
        $db->setQuery($query);
        $count = $db->loadResult();
        $flag = $count == 0;
        if ($flag) {
            $obj = new stdClass();
            $obj->title = $type;
            $obj->type = 'system_apps';
            $db->insertObject('#__gridbox_app', $obj);
        }
        var_dump($flag);exit;
    }

    public function getSystemApps()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title, type')
            ->from('#__gridbox_app')
            ->where('type = '.$db->quote('system_apps'))
            ->order('id ASC');
        $db->setQuery($query);
        $system = $db->loadObjectList();

        return $system;
    }

    public function getItems()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title, type')
            ->from('#__gridbox_app')
            ->order('id ASC');
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $query->where('type <> '.$db->quote('system_apps'));
            $search = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('title LIKE ' . $search);
        }
        $db->setQuery($query);
        $items = $db->loadObjectList();
        if (!empty($search)) {
            $search = $this->getState('filter.search');
            $system = $this->getSystemApps();
            foreach ($system as $app) {
                $title = strtoupper($app->title);
                $title = str_replace('-', '_', $title);
                $title = JText::_($title);
                $title = mb_strtolower($title);
                $search = mb_strtolower($search);
                if (strpos($title, $search) !== false) {
                    $items[] = $app;
                }
            }
        }

        return $items;
    }

    public function setFilters()
    {
        $this::populateState();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        return parent::getStoreId($id);
    }
    
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
        parent::populateState('id', 'desc');
    }
    
}