<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');


class gridboxControllerPage extends JControllerForm
{
    public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

    public function getItemsFilterCount()
    {
        $input = JFactory::getApplication()->input;
        $app = $input->get('app', 0, 'int');
        $data = $input->get('data', '', 'raw');
        $object = json_decode($data);
        $count = gridboxHelper::getItemsFilterCount($app, $object);
        print_r($count);
        exit;
    }

	public function getRecentPosts()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $input->set('view', 'gridbox');
        $id = $input->get('id', 0, 'int');
        $sorting = $input->get('sorting');
        $limit = $input->get('limit', 0, 'int');
        $maximum = $input->get('maximum', 0, 'int');
        $category = $input->get('category', '', 'string');
        $featured = $input->get('featured', false, 'bool');
        $pagination = $input->get('pagination', '', 'string');
        $start = $input->get('page', 1, 'int');
        $not = $input->get('not', '', 'string');
        $start--;
        gridboxHelper::$editItem = null;
        $obj = new stdClass();
		$obj->pagination = gridboxHelper::getRecentPostsPagination($id, $limit, $category, $featured, $start, $pagination);
        $start *= $limit;
        $obj->posts = gridboxHelper::getRecentPosts($id, $sorting, $limit, $maximum, $category, $featured, $start, $not);        
        $str = json_encode($obj);
        echo $str;exit;
    }
}