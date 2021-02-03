<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

class gridboxViewBlog extends JViewLegacy
{
    protected $item;
    protected $category;
    
    public function display($tpl = null)
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0 , 'int');
        $tag = $input->get('tag', 0 , 'int');
        $this->item = $this->get('Item');
        if (empty($this->item)) {
            return JError::raiseError(404, JText::_('NOT_FOUND'));
        }
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        if (!in_array($this->item->access, $groups)) {
            JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return;
        }
        $app = $input->get('app');
        $itemId = $input->get('Itemid');
        $menus = JFactory::getApplication()->getMenu('site');
        $attributes = array('link');
        $link = 'index.php?option=com_gridbox&view=blog&app='.$app.'&id='.$id;
        $values = array($link);
        $menuItems = $menus->getItems($attributes, $values);
        $menuFlag = gridboxHelper::checkMenuItems($menuItems, $itemId);
        if (!empty($menuItems) && !empty($itemId) && $menuFlag && empty($tag)) {
            $link = JRoute::_('index.php?Itemid='.$menuItems[0]->id);
            header('Location: '.$link);
            exit;
        }
        $this->setBreadcrumb();
        $this->item->params = gridboxHelper::checkModules($this->item->app_layout, $this->item->app_items);
        $this->category = $this->get('Category');
        $this->prepareDocument();
        parent::display($tpl);
    }

    public function setBreadcrumb()
    {
        $app = JFactory::getApplication();
        $pathway = $app->getPathway();
        $input = JFactory::getApplication()->input;
        $id = $input->get('id');
        if ($id > 0) {
            $array = gridboxHelper::getCategoryBreadcrumb($id);
            $path = array_reverse($array);
            foreach ($path as $key => $value) {
                $pathway->addItem($value['title'], $value['link']);
            }
        }
    }

    public function prepareDocument()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0);
        $doc = JFactory::getDocument();
        $time = $this->item->saved_time;
        if (!empty($time)) {
            $time = '?'.$time;
        }
        $doc->addStyleSheet(JUri::root().'components/com_gridbox/assets/css/storage/app-'.$this->item->id.'.css'.$time);
        gridboxHelper::checkMoreScripts($this->item->params);
        $app = JFactory::getApplication();
        $menus = $app->getMenu();
        $menu = $menus->getActive();
        if (!empty($this->category)) {
            $title = $this->category->meta_title;
            $desc = $this->category->meta_description;
            $keywords = $this->category->meta_keywords;
            $robots = $this->category->robots;
            if (empty($title)) {
                $title = $this->category->title;
            }
        } else {
            $title = $this->item->meta_title;
            $desc = $this->item->meta_description;
            $keywords = $this->item->meta_keywords;
            $robots = $this->item->robots;
            if (empty($title)) {
                $title = $this->item->title;
            }
        }
        if (isset($menu) && $menu->query['option'] == 'com_gridbox' && $menu->query['view'] == 'blog'
                && $menu->query['app'] == $this->item->id && $menu->query['id'] == $id) {
            $params  = $menus->getParams($menu->id);
            $menu_title = $params->get('page_title');
            $menu_description = $params->get('menu-meta_description');
            $menu_keywords = $params->get('menu-meta_keywords');
            if (!empty($menu_title)) {
                $title = $menu_title;
            }
            if (!empty($menu_description)) {
                $desc = $menu_description;
            }
            if (!empty($menu_keywords)) {
                $keywords = $menu_keywords;
            }
            if ($menu_robots = $params->get('robots')) {
                $robots = $menu_robots;
            }
        }
        $tag = $input->get('tag', '', 'string');
        $author = $input->get('author', '', 'string');
        if (!empty($tag) || !empty($author)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('title, meta_title, meta_description, meta_keywords, robots');
            if (!empty($tag)) {
                $query->from('#__gridbox_tags')
                    ->where('`id` = '.$tag * 1);
            } else {
                $query->from('#__gridbox_authors')
                    ->where('`id` = '.$author * 1);
            }
            $db->setQuery($query);
            $obj = $db->loadObject();
            $title = $obj->meta_title;
            $desc = $obj->meta_description;
            $keywords = $obj->meta_keywords;
            $robots = $obj->robots;
            if (empty($title)) {
                $title = $obj->title;
            }
        }
        if ($app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } else if ($app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }
        $doc->setTitle($title);
        $doc->setDescription($desc);
        $doc->setMetaData('keywords', $keywords);
        if (empty($robots)) {
            $config = JFactory::getConfig();
            $robots = $config->get('robots');
        }
        if ($robots) {
            $doc->setMetadata('robots', $robots);
        }
    }
}