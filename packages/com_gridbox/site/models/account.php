<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

class gridboxModelAccount extends JModelItem
{
    public function getTable($type = 'pages', $prefix = 'gridboxTable', $config = array()) 
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getItem()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'string');
        $order = $this->getOrder($id);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_customer_info')
            ->order('order_list ASC');
        $db->setQuery($query);
        $info = $db->loadObjectList();
        $obj = new stdClass();
        $empty = new stdClass();
        $empty->title = '';
        $empty->items = array();
        $array = array();
        $object = null;
        foreach ($order->info as $value) {
            $value->empty = true;
            $obj->{$value->customer_id} = $value;
        }
        foreach ($info as $value) {
            if (!$object || $value->type == 'headline') {
                $array[] = new stdClass();
                $object = end($array);
                $object->title = $value->type == 'headline' && $value->invoice == 1 ? $value->title : '';
                $object->items = array();
            }
            if ($value->type != 'headline' && $value->type != 'acceptance' && isset($obj->{$value->id})) {
                $obj->{$value->id}->empty = false;
                $obj->{$value->id}->invoice = $value->invoice;
                if ($obj->{$value->id}->value === '' || $obj->{$value->id}->invoice == 0) {
                    continue;
                }
                $object->items[] = $obj->{$value->id};
            }
        }
        foreach ($obj as $value) {
            if ($value->empty && $value->type != 'headline' && $value->type != 'acceptance') {
                if ($value->value === '' || $value->invoice == 0) {
                    continue;
                }
                $empty->items[] = $value;
            }
        }
        $array[] = $empty;
        $order->contact_info = $array;

        return $order;
    }

    public function getTime()
    {
        $item = gridboxHelper::getSystemParamsByType('checkout');

        return $item->saved_time;
    }

    public function saveCustomerInfo($data)
    {
        $db = JFactory::getDbo();
        $user_id = JFactory::getUser()->id;
        foreach ($data as $customer_id => $value) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_user_info')
                ->where('customer_id = '.$customer_id)
                ->where('user_id = '.$user_id);
            $db->setQuery($query);
            $customer = $db->loadObject();
            if (!$customer) {
                $customer = new stdClass();
                $customer->user_id = $user_id;
                $customer->customer_id = $customer_id;
                $customer->value = $value;
                $db->insertObject('#__gridbox_store_user_info', $customer);
            } else {
                $customer->value = $value;
                $db->updateObject('#__gridbox_store_user_info', $customer, 'id');
            }
        }
    }

    public function getData()
    {
        $data = new stdClass();
        $data->orders = $this->getOrders();
        $data->digital = new stdClass();
        $data->digital->products = array();
        $data->digital->limit = 0;
        $data->digital->expires = 0;
        $db = JFactory::getDbo();
        $date = $db->quote(date("Y-m-d H:i:s"));
        $nullDate = $db->quote($db->getNullDate());
        foreach ($data->orders as $order) {
            if ($order->status != 'completed') {
                continue;
            }
            foreach ($order->products as $product) {
                if ($product->product_type == 'digital') {
                    $query = $db->getQuery(true)
                        ->select('l.*')
                        ->from('#__gridbox_store_order_license AS l')
                        ->where('l.product_id = '.$product->id)
                        ->where('p.page_category <> '.$db->quote('trashed'))
                        ->where('p.published = 1')
                        ->where('p.created <= '.$date)
                        ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$date.')')
                        ->leftJoin('#__gridbox_store_order_products AS op ON op.id = l.product_id')
                        ->leftJoin("#__gridbox_pages AS p ON op.product_id = p.id");
                    $db->setQuery($query);
                    $license = $db->loadObject();
                    $expired = false;
                    $limit = $license && ($license->limit == '' || $license->downloads < $license->limit);
                    if (!empty($license->expires)) {
                        $expired = $date > $license->expires;
                    }
                    if (!$expired && $limit) {
                        $product->license = $license;
                        $data->digital->products[] = $product;
                        $data->digital->limit += !empty($license->limit) ? 1 : 0;
                        $data->digital->expires += !empty($license->expires) ? 1 : 0;
                    }
                }
            }
        }

        return $data;
    }

    public function getOrders()
    {
        $id = JFactory::getUser()->id;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders')
            ->where('published = 1')
            ->where('user_id = '.$id)
            ->order('date desc');
        $db->setQuery($query);
        $orders = $db->loadObjectList();
        foreach ($orders as $order) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_order_products')
                ->where('order_id = '.$order->id);
            $db->setQuery($query);
            $order->products = $db->loadObjectList();
        }

        return $orders;
    }

    public function getOrder($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders')
            ->where('id = '.$id);
        $db->setQuery($query);
        $order = $db->loadObject();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders_discount')
            ->where('order_id = '.$id);
        $db->setQuery($query);
        $order->promo = $db->loadObject();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders_shipping')
            ->where('order_id = '.$id);
        $db->setQuery($query);
        $order->shipping = $db->loadObject();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders_payment')
            ->where('order_id = '.$id);
        $db->setQuery($query);
        $order->payment = $db->loadObject();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_order_products')
            ->where('order_id = '.$id);
        $db->setQuery($query);
        $order->products = $db->loadObjectList();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_products_fields');
        $db->setQuery($query);
        $fields = $db->loadObjectList();
        $fieldsData = array();
        foreach ($fields as $field) {
            $options = json_decode($field->options);
            foreach ($options as $option) {
                $option->value = $option->title;
                $option->title = $field->title;
                $option->type = $field->field_type;
                $fieldsData[$option->key] = $option;
            }
        }
        foreach ($order->products as $product) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_order_product_variations')
                ->where('product_id = '.$product->id);
            $db->setQuery($query);
            $product->variations = $db->loadObjectList();
            $info = array();
            foreach ($product->variations as $variation) {
                $info[] = '<span>'.$variation->title.' '.$variation->value.'</span>';
            }
            $product->info = implode('/', $info);
            $query = $db->getQuery(true)
                ->select('p.title, p.intro_image AS image, d.*')
                ->from('#__gridbox_pages AS p')
                ->where('d.product_id = '.$product->product_id)
                ->where('p.page_category <> '.$db->quote('trashed'))
                ->leftJoin('#__gridbox_store_product_data AS d ON d.product_id = p.id');
            $db->setQuery($query);
            $obj = $db->loadObject();
            if (!$obj) {
                continue;
            }
            $variations = json_decode($obj->variations);
            if (!empty($product->variation) && !isset($variations->{$product->variation})) {
                continue;
            }
            $product->extra_options = !empty($product->extra_options) ? json_decode($product->extra_options) : new stdClass();
        }
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_order_customer_info')
            ->where('order_id = '.$id)
            ->order('order_list ASC, id ASC');
        $db->setQuery($query);
        $order->info = $db->loadObjectList();
        
        return $order;
    }

    public function getCustomerInfoGroup($title)
    {
        $group = new stdClass();
        $group->title = $title;
        $group->items = array();

        return $group;
    }

    public function getCustomerInfo()
    {
        $info = gridboxHelper::getCustomerInfo();
        $groups = array();
        $group = null;
        foreach ($info as $key => $obj) {
            if ($obj->type == 'headline') {
                $groups[] = $group = $this->getCustomerInfoGroup($obj->title);
            } else if (!$group) {
                $groups[] = $group = $this->getCustomerInfoGroup('');
            }
            if ($obj->type != 'headline' && $obj->type != 'acceptance') {
                $group->items[] = $obj->id;
            }
        }

        return $groups;
    }

    public function getStatuses()
    {
        $data = new stdClass();
        $data->undefined = new stdClass();
        $data->undefined->title = 'Undefined';
        $data->undefined->color = '#f10000';
        foreach (gridboxHelper::$store->statuses as $status) {
            $data->{$status->key} = $status;
        }

        return $data;
    }
}