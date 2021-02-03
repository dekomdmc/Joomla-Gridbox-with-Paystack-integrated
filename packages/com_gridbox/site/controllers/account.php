<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class gridboxControllerAccount extends JControllerForm
{
    public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

    public function getOrder()
    {
        $id = $this->input->get('id', 0, 'int');
        $model = $this->getModel();
        $order = $model->getOrder($id);
        $order->date = JHtml::date($order->date, gridboxHelper::$dateFormat);
        $str = json_encode($order);
        echo $str;exit();
    }

    public function saveCustomerInfo()
    {
        $data = $this->input->post->getArray(array());
        $model = $this->getModel();
        $order = $model->saveCustomerInfo($data);
        exit();
    }
}