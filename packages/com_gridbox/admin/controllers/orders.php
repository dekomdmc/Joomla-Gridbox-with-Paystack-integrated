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

class gridboxControllerOrders extends JControllerAdmin
{
    public function getModel($name = 'orders', $prefix = 'gridboxModel', $config = array())
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));

        return $model;
    }

    public function getStatistic()
    {
        $input = JFactory::getApplication()->input;
        $date = $input->get('date', '', 'string');
        $type = $input->get('type', '', 'string');
        $data = gridboxHelper::getShopStatistic($date, $type);
        $str = json_encode($data);
        echo $str;
        exit;
    }

    public function getOrder()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $model = $this->getModel();
        $order = $model->getOrder($id);
        $str = json_encode($order);
        print_r($str);exit;
    }

    public function updateOrder()
    {
        $input = JFactory::getApplication()->input;
        $data = $input->get('data', '', 'string');
        $object = json_decode($data);
        $model = $this->getModel();
        $model->updateOrder($object);
        print_r('{}');exit;
    }

    public function createOrder()
    {
        $input = JFactory::getApplication()->input;
        $data = $input->get('data', '', 'string');
        $object = json_decode($data);
        $model = $this->getModel();
        $model->createOrder($object);
        print_r('{}');exit;
    }

    public function updateStatus()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $status = $input->get('status', '', 'string');
        $comment = $input->get('comment', '', 'string');
        $model = $this->getModel();
        $model->updateStatus($id, $status, $comment);
        echo '{}';exit;
    }

    public function getStatus()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $model = $this->getModel();
        $obj = $model->getStatus($id);
        $str = json_encode($obj);
        echo $str;exit;
    }

    public function contextDelete()
    {
        gridboxHelper::checkUserEditLevel();
        $pks = array($this->input->get('context-item', 0, 'int'));
        $model = $this->getModel();
        $model->delete($pks);
        gridboxHelper::ajaxReload('COM_GRIDBOX_N_ITEMS_DELETED');
    }

    public function delete()
    {
        gridboxHelper::checkUserEditLevel();
        $pks = $this->input->getVar('cid', array(), 'post', 'array');
        $model = $this->getModel();
        $model->delete($pks);
        gridboxHelper::ajaxReload('COM_GRIDBOX_N_ITEMS_DELETED');
    }
}