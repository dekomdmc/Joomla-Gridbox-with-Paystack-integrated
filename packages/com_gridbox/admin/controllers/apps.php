<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class gridboxControllerApps extends JControllerAdmin
{
    public function getModel($name = 'gridbox', $prefix = 'gridboxModel', $config = array()) 
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }

    public function setFeatured()
    {
        gridboxHelper::checkUserEditLevel();
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $featured = $input->get('featured', 0, 'int');
        $model = $this->getModel();
        $model->setFeatured($id, $featured);
        exit();
    }

    public function moveTo()
    {
        gridboxHelper::checkUserEditLevel();
        $model = $this->getModel('category', 'gridboxModel');
        $input = JFactory::getApplication()->input;
        $data = $input->get('category_id', '', 'string');
        $obj = json_decode($data);
        $cid = $input->get('cid', array(), 'array()');
        foreach ($cid as $id) {
            $model->pageMoveTo($obj->id, $id, $obj->app_id);
        }
        gridboxHelper::ajaxReload('SUCCESS_MOVED');
    }

    public function pageMoveTo()
    {
        gridboxHelper::checkUserEditLevel();
        $model = $this->getModel('category', 'gridboxModel');
        $input = JFactory::getApplication()->input;
        $data = $input->get('category_id', '', 'string');
        $obj = json_decode($data);
        $id = $input->get('context-item', 0, 'int');
        $model->pageMoveTo($obj->id, $id, $obj->app_id);
        gridboxHelper::ajaxReload('SUCCESS_MOVED');
    }

    public function orderCategories()
    {
        gridboxHelper::checkUserEditLevel();
        $model = $this->getModel('category');
        $model->orderCategories();
        exit();
    }

    public function updateCategory()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('category-id', '', 'string');
        $user = JFactory::getUser();
        if ($user->authorise('core.edit.category.'.$id, 'com_gridbox')) {
            $model = $this->getModel('category', 'gridboxModel');
            $model->updateCategory();
            gridboxHelper::ajaxReload('JLIB_APPLICATION_SAVE_SUCCESS');
        } else {
            gridboxHelper::ajaxReload('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED');
        }
    }

    public function deleteCategory()
    {
        $input = JFactory::getApplication()->input;
        $data = $input->get('context-item', '', 'string');
        $obj = json_decode($data);
        $model = $this->getModel('category', 'gridboxModel');
        if ($model->checkDeletePermissions($obj)) {
            $model->removeCategory();
            gridboxHelper::ajaxReload('COM_GRIDBOX_N_ITEMS_DELETED');
        } else {
            gridboxHelper::ajaxReload('JERROR_CORE_DELETE_NOT_PERMITTED');
        }
    }

    public function categoryMoveTo()
    {
        gridboxHelper::checkUserEditLevel();
        $model = $this->getModel('category', 'gridboxModel');
        $model->moveTo();
        gridboxHelper::ajaxReload('SUCCESS_MOVED');
    }

    public function categoryDuplicate()
    {
        gridboxHelper::checkUserEditLevel();
        $input = JFactory::getApplication()->input;
        $id = $input->get('context-item', 0, 'int');
        $model = $this->getModel('category', 'gridboxModel');
        $gridboxModel = $this->getModel();
        $model->duplicate($id, $gridboxModel);
        gridboxHelper::ajaxReload('GRIDBOX_DUPLICATED');
    }

    public function applySettings()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('blog', 0, 'int');
        $user = JFactory::getUser();
        if ($user->authorise('core.edit.app.'.$id, 'com_gridbox')) {
            $model = $this->getModel();
            $model->applySettings();
            gridboxHelper::ajaxReload('JLIB_APPLICATION_SAVE_SUCCESS');
        } else {
            gridboxHelper::ajaxReload('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED');
        }
    }

    public function addCategory()
    {
        $input = JFactory::getApplication()->input;
        $parent = $input->get('parent_id', 0, 'int');
        $blog = $input->get('blog', 0, 'int');
        $obj = new stdClass();
        if (!empty($parent)) {
            $id = $parent;
            $type = 'category';
        } else {
            $id = $blog;
            $type = 'app';
        }
        if (gridboxHelper::assetsCheckPermission($id, $type, 'core.create', '')) {
            $model = $this->getModel('category', 'gridboxModel');
            $title = $input->get('category_name', '', 'string');
            $order = $input->get('category_order_list', 0, 'int');
            $obj->id = $model->createCat($title, $blog, $parent, $order);
            $obj->msg = JText::_('ITEM_CREATED');
        } else {
            $obj->id = 0;
            $obj->msg = JText::_('JERROR_CORE_CREATE_NOT_PERMITTED');
        }
        $str = json_encode($obj);
        echo $str;
        exit();
    }

    public function publish()
    {
        $cid = JFactory::getApplication()->input->get('cid', array(), 'array');
        $task = $this->getTask();
        if ($task != 'unpublish') {
            $text = $this->text_prefix . '_N_ITEMS_PUBLISHED';
        } else {
            $text = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
        }
        foreach ($cid as $pk) {
            $assets = new gridboxAssetsHelper($pk, 'page');
            $flag = $assets->checkPermission('core.edit.state');
            if (!$flag) {
                break;
            }
        }
        if ($flag) {
            parent::publish();
        } else {
            $text = 'JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED';
        }
        gridboxHelper::ajaxReload($text);
    }

    public function addTrash()
    {
        $pks = $this->input->getVar('cid', array(), 'post', 'array');
        foreach ($pks as $pk) {
            $assets = new gridboxAssetsHelper($pk, 'page');
            $flag = $assets->checkPermission('core.delete');
            if (!$flag) {
                break;
            }
        }
        if ($flag) {
            $model = $this->getModel();
            $model->trash($pks);
            gridboxHelper::ajaxReload($this->text_prefix . '_N_ITEMS_TRASHED');
        } else {
            gridboxHelper::ajaxReload('JERROR_CORE_DELETE_NOT_PERMITTED');
        }
    }

    public function contextTrash()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('context-item', 0, 'int');
        $assets = new gridboxAssetsHelper($id, 'page');
        $flag = $assets->checkPermission('core.delete');
        if ($flag) {
            $array = array($id);
            $model = $this->getModel();
            $model->trash($array);
            gridboxHelper::ajaxReload($this->text_prefix . '_N_ITEMS_TRASHED');
        } else {
            gridboxHelper::ajaxReload('JERROR_CORE_DELETE_NOT_PERMITTED');
        }
    }
    
    public function contextDuplicate()
    {
        gridboxHelper::checkUserEditLevel();
        $input = JFactory::getApplication()->input;
        $id = $input->get('context-item', 0, 'int');
        $array = array();
        $array[] = $id;
        $model = $this->getModel();
        $model->duplicate($array);
        gridboxHelper::ajaxReload('GRIDBOX_DUPLICATED');
    }
    
    public function duplicate()
    {
        gridboxHelper::checkUserEditLevel();
        $pks = $this->input->getVar('cid', array(), 'post', 'array');
        $blog = $this->input->get('blog', 0, 'int');
        $model = $this->getModel();
        $model->duplicate($pks);
        gridboxHelper::ajaxReload('gridbox_DUPLICATED');
    }

    public function getTags()
    {
        gridboxHelper::checkUserEditLevel();
        $tags = gridboxHelper::getTags();
        $json = json_encode($tags);
        echo $json;
        exit;
    }
}