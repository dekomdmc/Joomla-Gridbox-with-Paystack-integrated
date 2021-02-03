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

class gridboxModelOrders extends JModelList
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'state', 'publish_up', 'publish_down'
            );
        }
        parent::__construct($config);
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

    public function getOrder($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders')
            ->where('id = '.$id);
        $db->setQuery($query);
        $order = $db->loadObject();
        if ($order->unread == 1) {
            $query = $db->getQuery(true)
                ->update('#__gridbox_store_orders')
                ->set('unread = 0')
                ->where('id = '.$id);
            $db->setQuery($query)
                ->execute();
        }
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
            if (!empty($product->variation)) {
                $data = $variations->{$product->variation};
            } else {
                $data = new stdClass();
                $data->price = $obj->price;
                $data->sale_price = $obj->sale_price;
                $data->sku = $obj->sku;
                $data->stock = $obj->stock;
            }
            $data->id = $obj->product_id;
            $data->dimensions = !empty($obj->dimensions) ? json_decode($obj->dimensions) : new stdClass();
            $data->title = $obj->title;
            $data->image = $obj->image;
            $data->prices = new stdClass();
            $data->prices->price = gridboxHelper::preparePrice($data->price);
            $data->product_type = $obj->product_type;
            if (!empty($data->sale_price)) {
                $data->prices->sale = gridboxHelper::preparePrice($data->sale_price);
            }
            $data->categories = gridboxHelper::getProductCategoryId($obj->product_id);
            $data->variations = array();
            $product->extra = gridboxHelper::getProductExtraOptions($obj->extra_options);
            $product->extra_options = !empty($product->extra_options) ? json_decode($product->extra_options) : new stdClass();
            if (isset($product->extra_options->price)) {
                $product->price -= $product->extra_options->price * $product->quantity;
            }
            if ($product->sale_price && isset($product->extra_options->price)) {
                $product->sale_price -= $product->extra_options->price * $product->quantity;
            }
            $data->extra_options = new stdClass();
            $data->extra_options->count = $data->extra_options->price = 0;
            $data->extra_options->items = new stdClass();
            if (isset($product->extra_options->items)) {
                foreach ($product->extra_options->items as $ind => $item) {
                    if (!isset($product->extra->{$ind})) {
                        continue;
                    }
                    $count = 0;
                    $extra = new stdClass();
                    $extra->title = $product->extra->{$ind}->title;
                    $extra->required = $product->extra->{$ind}->required == '1';
                    $extra->values = new stdClass();
                    foreach ($item->values as $key => $value) {
                        if (!isset($product->extra->{$ind}->items->{$key})) {
                            continue;
                        }
                        $value->value = $product->extra->{$ind}->items->{$key}->title;
                        $value->price = $product->extra->{$ind}->items->{$key}->price;
                        $count++;
                        if ($value->price != '') {
                            $data->extra_options->price += $value->price * 1;
                        }
                        $extra->values->{$key} = $value;
                    }
                    if ($count == 0) {
                        continue;
                    }
                    $data->extra_options->count += $count;
                    $data->extra_options->items->{$ind} = $extra;
                }
            }
            $data->extra = $product->extra;
            $data->variation = $product->variation;
            if (!empty($data->variation)) {
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_store_product_variations_map')
                    ->where('product_id = '.$obj->product_id);
                $db->setQuery($query);
                $map = $db->loadObjectList();
                $images = new stdClass();
                foreach ($map as $variation) {
                    $images->{$variation->option_key} = json_decode($variation->images);
                }
                $info = array();
                $array = explode('+', $product->variation);
                foreach ($array as $var) {
                    if (isset($fieldsData[$var])) {
                        $info[] = '<span>'.$fieldsData[$var]->title.' '.$fieldsData[$var]->value.'</span>';
                        $data->variations[] = $fieldsData[$var];
                    }
                    if (!empty($images->{$var})) {
                        $data->image = $images->{$var}[0];
                    }
                }
                $data->info = implode('/', $info);
            }
            $product->data = $data;
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

    public function updateOrder($cart)
    {
        $db = JFactory::getDbo();
        $order = new stdClass();
        $order->id = $cart->order_id;
        $order->subtotal = $cart->subtotal;
        $order->tax = $cart->tax;
        $order->total = $cart->total;
        $order->currency_symbol = gridboxHelper::$store->currency->symbol;
        $order->currency_position = gridboxHelper::$store->currency->position;
        $db->updateObject('#__gridbox_store_orders', $order, 'id');
        if ($cart->validPromo) {
            $discount = new stdClass();
            $discount->order_id = $order->id;
            $discount->promo_id = $cart->promo->id;
            $discount->title = $cart->promo->title;
            $discount->code = $cart->promo->code;
            $discount->unit = $cart->promo->unit;
            $discount->discount = $cart->promo->discount;
            $discount->value = $cart->discount;
            $order->discount = $discount;
            if (isset($cart->promo->db_id)) {
                $discount->id = $cart->promo->db_id;
                $db->updateObject('#__gridbox_store_orders_discount', $discount, 'id');
            } else {
                $this->deleteTable($db, '#__gridbox_store_orders_discount', 'order_id = '.$order->id);
                $db->insertObject('#__gridbox_store_orders_discount', $discount);
            }
        }
        if ($cart->shipping) {
            $shipping = new stdClass();
            $shipping->order_id = $order->id;
            $params = json_decode($cart->shipping->options);
            $shipping->type = $params->type;
            $shipping->title = $cart->shipping->title;
            $shipping->price = $cart->shipping->price;
            $shipping->tax = $cart->shipping->tax;
            $shipping->shipping_id = $cart->shipping->id;
            if (isset($cart->shipping->db_id)) {
                $shipping->id = $cart->shipping->db_id;
                $db->updateObject('#__gridbox_store_orders_shipping', $shipping, 'id');
            } else {
                $this->deleteTable($db, '#__gridbox_store_orders_shipping', 'order_id = '.$order->id);
                $db->insertObject('#__gridbox_store_orders_shipping', $shipping);
            }
        }
        $pids = array();
        $vids = array();
        foreach ($cart->products as $obj) {
            $extraPrice = isset($obj->extra_options->price) ? $obj->extra_options->price * $obj->quantity : 0;
            $product = new stdClass();
            $product->order_id = $order->id;
            $product->title = $obj->title;
            $product->image = $obj->image;
            $product->product_id = $obj->id;
            $product->variation = $obj->variation;
            $product->quantity = $obj->quantity;
            $product->price = $obj->price * $obj->quantity + $extraPrice;
            $product->sale_price = $obj->sale_price != '' ? $obj->sale_price * $obj->quantity + $extraPrice : '';
            $product->sku = $obj->sku;
            $product->tax = $obj->tax ? $obj->tax->amount : '';
            $product->tax_title = $obj->tax ? $obj->tax->title : '';
            $product->tax_rate = $obj->tax ? $obj->tax->rate : '';
            $product->net_price = $obj->net_price;
            $product->extra_options = json_encode($obj->extra_options);
            $product->product_type = $obj->product_type;
            if (isset($obj->db_id)) {
                $product->id = $obj->db_id;
                $db->updateObject('#__gridbox_store_order_products', $product, 'id');
            } else {
                $db->insertObject('#__gridbox_store_order_products', $product);
                $product->id = $db->insertid();
                if ($product->product_type == 'digital') {
                    $product->product_token = hash('md5', date("Y-m-d H:i:s"));
                    $db->updateObject('#__gridbox_store_order_products', $product, 'id');
                    $digital = !empty($obj->data->digital_file) ? json_decode($obj->data->digital_file) : new stdClass();
                    $license = new stdClass();
                    $license->product_id = $product->id;
                    $license->order_id = $order->id;
                    $license->limit = isset($digital->max) ? $digital->max : '';
                    $license->expires = 'new';
                    $db->insertObject('#__gridbox_store_order_license', $license);
                }
            }
            $pids[] = $product->id;
            foreach ($obj->variations as $object) {
                $variation = new stdClass();
                $variation->product_id = $product->id;
                $variation->order_id = $order->id;
                $variation->title = $object->title;
                $variation->value = $object->value;
                $variation->color = $object->color;
                $variation->image = $object->image;
                $variation->type = $object->type;
                if (isset($object->id)) {
                    $variation->id = $obj->id;
                    $db->updateObject('#__gridbox_store_order_product_variations', $variation, 'id');
                } else {
                    $db->insertObject('#__gridbox_store_order_product_variations', $variation);
                    $variation->id = $db->insertid();
                }
                $vids[] = $variation->id;
            }
        }
        $str = implode(', ', $pids);
        $this->deleteTable($db, '#__gridbox_store_order_products', 'id NOT IN ('.$str.') AND order_id = '.$order->id);
        $this->deleteTable($db, '#__gridbox_store_order_license', 'product_id NOT IN ('.$str.') AND order_id = '.$order->id);
        if (!empty($vids)) {
            $str = implode(', ', $vids);
            $this->deleteTable($db, '#__gridbox_store_order_product_variations', 'id NOT IN ('.$str.') AND order_id = '.$order->id);
        }
        foreach ($cart->info as $key => $value) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_order_customer_info')
                ->where('id = '.$key);
            $db->setQuery($query);
            $customer = $db->loadObject();
            $customer->value = $value;
            if ($customer->type == 'country' && !empty($customer->value)) {
                $customer->value = $this->setCountryValue($customer->value);
            }
            $db->updateObject('#__gridbox_store_order_customer_info', $customer, 'id');
        }
    }

    public function createOrder($cart)
    {
        $db = JFactory::getDbo();
        $order = new stdClass();
        $order->cart_id = 0;
        $order->user_id = 0;
        $order->subtotal = $cart->subtotal;
        $order->tax = $cart->tax;
        $order->tax_mode = gridboxHelper::$store->tax->mode;
        $order->total = $cart->total;
        $order->published = 1;
        $order->currency_symbol = gridboxHelper::$store->currency->symbol;
        $order->currency_position = gridboxHelper::$store->currency->position;
        $db->insertObject('#__gridbox_store_orders', $order);
        $order->id = $db->insertid();
        if ($cart->validPromo) {
            $discount = new stdClass();
            $discount->order_id = $order->id;
            $discount->promo_id = $cart->promo->id;
            $discount->title = $cart->promo->title;
            $discount->code = $cart->promo->code;
            $discount->unit = $cart->promo->unit;
            $discount->discount = $cart->promo->discount;
            $discount->value = $cart->discount;
            $order->discount = $discount;
            $db->insertObject('#__gridbox_store_orders_discount', $discount);
        }
        if ($cart->shipping) {
            $shipping = new stdClass();
            $params = json_decode($cart->shipping->options);
            $shipping->type = $params->type;
            $shipping->order_id = $order->id;
            $shipping->title = $cart->shipping->title;
            $shipping->price = $cart->shipping->price;
            $shipping->tax = '';
            if ($cart->shipping->tax) {
                $shipping->tax = $cart->shipping->tax->amount;
                $shipping->tax_title = $cart->shipping->tax->title;
                $shipping->tax_rate = $cart->shipping->tax->rate;
            }
            $shipping->shipping_id = $cart->shipping->id;
            $db->insertObject('#__gridbox_store_orders_shipping', $shipping);
        }
        $payment = new stdClass();
        $payment->order_id = $order->id;
        $payment->title = '';
        $payment->type = 'admin';
        $payment->payment_id = 0;
        $db->insertObject('#__gridbox_store_orders_payment', $payment);
        foreach ($cart->products as $obj) {
            $extraPrice = isset($obj->extra_options->price) ? $obj->extra_options->price * $obj->quantity : 0;
            $product = new stdClass();
            $product->order_id = $order->id;
            $product->title = $obj->title;
            $product->image = $obj->image;
            $product->product_id = $obj->id;
            $product->variation = $obj->variation;
            $product->quantity = $obj->quantity;
            $product->price = $obj->price * $obj->quantity + $extraPrice;
            $product->sale_price = $obj->sale_price != '' ? $obj->sale_price * $obj->quantity + $extraPrice : '';
            $product->sku = $obj->sku;
            $product->tax = $obj->tax ? $obj->tax->amount : '';
            $product->tax_title = $obj->tax ? $obj->tax->title : '';
            $product->tax_rate = $obj->tax ? $obj->tax->rate : '';
            $product->net_price = $obj->net_price;
            $product->extra_options = json_encode($obj->extra_options);
            $product->product_type = $obj->product_type;
            $db->insertObject('#__gridbox_store_order_products', $product);
            $product->id = $db->insertid();
            if ($product->product_type == 'digital') {
                $product->product_token = hash('md5', date("Y-m-d H:i:s"));
                $db->updateObject('#__gridbox_store_order_products', $product, 'id');
                $digital = !empty($obj->data->digital_file) ? json_decode($obj->data->digital_file) : new stdClass();
                $license = new stdClass();
                $license->product_id = $product->id;
                $license->order_id = $order->id;
                $license->limit = isset($digital->max) ? $digital->max : '';
                $license->expires = 'new';
                $db->insertObject('#__gridbox_store_order_license', $license);
            }
            foreach ($obj->variations as $object) {
                $variation = new stdClass();
                $variation->product_id = $product->id;
                $variation->order_id = $order->id;
                $variation->title = $object->title;
                $variation->value = $object->value;
                $variation->color = $object->color;
                $variation->image = $object->image;
                $variation->type = $object->type;
                $db->insertObject('#__gridbox_store_order_product_variations', $variation);
            }
        }
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_customer_info')
            ->order('order_list ASC');
        $db->setQuery($query);
        $info = $db->loadObjectList();
        foreach ($info as $obj) {
            $customer = new stdClass();
            $customer->order_id = $order->id;
            $customer->customer_id = $obj->id;
            $customer->title = $obj->title;
            $customer->type = $obj->type;
            $customer->value = isset($cart->info->{$obj->id}) ? $cart->info->{$obj->id} : '';
            $customer->options = $obj->options;
            $customer->invoice = $obj->invoice;
            $customer->order_list = $obj->order_list;
            if ($obj->type == 'country' && !empty($customer->value)) {
                $customer->value = $this->setCountryValue($customer->value);
            }
            $db->insertObject('#__gridbox_store_order_customer_info', $customer);
        }
        gridboxHelper::$storeHelper->updateOrder($order->id);
    }

    public function setCountryValue($value)
    {
        $db = JFactory::getDbo();
        $obj = json_decode($value);
        $query = $db->getQuery(true)
            ->select('title')
            ->from('#__gridbox_countries')
            ->where('id = '.$obj->country);
        $db->setQuery($query);
        $obj->country = $db->loadResult();
        if (!empty($obj->region)) {
            $query = $db->getQuery(true)
                ->select('title')
                ->from('#__gridbox_country_states')
                ->where('id = '.$obj->region);
            $db->setQuery($query);
            $obj->region = $db->loadResult();
        }

        return json_encode($obj);
    }

    public function getStatus($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, status, user_id, date')
            ->from('#__gridbox_store_orders')
            ->where('id = '.$id);
        $db->setQuery($query);
        $obj = $db->loadObject();
        $query = $db->getQuery(true)
            ->select('h.*, u.username')
            ->from('#__gridbox_store_orders_status_history AS h')
            ->where('h.order_id = '.$id)
            ->leftJoin('#__users AS u on u.id = h.user_id')
            ->order('h.id DESC');
        $db->setQuery($query);
        $obj->history = $db->loadObjectList();
        foreach ($obj->history as $record) {
            $record->date = JDate::getInstance($record->date)->format('M d, Y, H:i');
        }
        $query = $db->getQuery(true)
            ->select('type')
            ->from('#__gridbox_store_orders_payment')
            ->where('order_id = '.$id);
        $db->setQuery($query);
        $obj->payment = $db->loadResult();
        $query = $db->getQuery(true)
            ->select('username')
            ->from('#__users')
            ->where('id = '.$obj->user_id);
        $db->setQuery($query);
        $obj->username = $db->loadResult();
        $obj->date = JDate::getInstance($obj->date)->format('M d, Y, H:i');
        

        return $obj;
    }

    public function updateStatus($id, $status, $comment)
    {
        gridboxHelper::$storeHelper->updateStatus($id, $status, $comment);
    }

    public function delete($cid)
    {
        $db = JFactory::getDbo();
        foreach ($cid as $id) {
            $this->deleteTable($db, '#__gridbox_store_orders', 'id = '.$id);
            $this->deleteTable($db, '#__gridbox_store_orders_status_history', 'order_id = '.$id);
            $this->deleteTable($db, '#__gridbox_store_orders_discount', 'order_id = '.$id);
            $this->deleteTable($db, '#__gridbox_store_orders_shipping', 'order_id = '.$id);
            $this->deleteTable($db, '#__gridbox_store_orders_payment', 'order_id = '.$id);
            $this->deleteTable($db, '#__gridbox_store_order_customer_info', 'order_id = '.$id);
            $this->deleteTable($db, '#__gridbox_store_order_products', 'order_id = '.$id);
            $this->deleteTable($db, '#__gridbox_store_order_license', 'order_id = '.$id);
            $this->deleteTable($db, '#__gridbox_store_order_product_variations', 'order_id = '.$id);
        }
    }

    public function deleteTable($db, $table, $where)
    {
        $query = $db->getQuery(true)
            ->delete($table)
            ->where($where);
        $db->setQuery($query)
            ->execute();
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

    public function getShipping()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_shipping')
            ->where('published = 1')
            ->order('order_list ASC');
        $db->setQuery($query);
        $shipping = $db->loadObjectList();

        return $shipping;
    }

    public function getPromo()
    {
        $db = JFactory::getDbo();
        $date = JDate::getInstance()->format('Y-m-d H:i:s');
        $date = $db->quote($date);
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('COUNT(p.id)')
            ->from('#__gridbox_store_promo_codes AS p')
            ->where('p.published = 1')
            ->where('(p.publish_down = '.$nullDate.' OR p.publish_down >= '.$date.')')
            ->where('(p.publish_up = '.$nullDate.' OR p.publish_up <= '.$date.')')
            ->where('(p.limit = 0 OR p.used < pc.limit)')
            ->leftJoin('#__gridbox_store_promo_codes AS pc ON pc.id = p.id');
        $db->setQuery($query);
        $count = $db->loadResult();

        return $count;
    }

    public function getCustomerInfo()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_customer_info')
            ->order('order_list ASC');
        $db->setQuery($query);
        $info = $db->loadObjectList();
        foreach ($info as $value) {
            $value->settings = json_decode($value->options);
        }

        return $info;
    }

    public function getStatuses()
    {
        $data = gridboxHelper::getStatuses();

        return $data;
    }
    
    protected function getListQuery()
    {
        $this->getGridboxFilters();
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('o.*, c.value AS customer_name, ci.value AS email')
            ->from('#__gridbox_store_orders AS o')
            ->where('o.published = 1')
            ->where('c.customer_id = 1')
            ->where('ci.type = '.$db->quote('email'))
            ->leftJoin('#__gridbox_store_order_customer_info AS c ON c.order_id = o.id')
            ->leftJoin('#__gridbox_store_order_customer_info AS ci ON ci.order_id = o.id');
        $status = $this->getState('filter.state');
        if (!empty($status)) {
            $query->where('o.status = '.$db->quote($status));
        }
        $publish_up = $this->getState('filter.publish_up');
        if (!empty($publish_up)) {
            $publish_up = $publish_up.' 00:00:01';
            $query->where('o.date > '.$db->quote($publish_up));
        }
        $publish_down = $this->getState('filter.publish_down');
        if (!empty($publish_down)) {
            $publish_down = $publish_down.' 23:59:59';
            $query->where('o.date < '.$db->quote($publish_down));
        }
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%'.$db->escape($search, true).'%', false);
            $query->where('(o.order_number LIKE '.$search.' OR c.value LIKE '.$search.' OR ci.value LIKE '.$search.')');
        }
        $orderCol = $this->state->get('list.ordering', 'date');
        $orderDirn = $this->state->get('list.direction', 'desc');
        $query->order('o.'.$orderCol.' '.$orderDirn.', o.id DESC');
        
        return $query;
    }
    
    protected function getStoreId($id = '')
    {
        $id .= ':'.$this->getState('filter.search');
        $id .= ':'.$this->getState('filter.state');
        
        return parent::getStoreId($id);
    }
    
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);
        $publish_up = $this->getUserStateFromRequest($this->context.'.filter.publish_up', 'publish_up', '', 'string');
        $publish_down = $this->getUserStateFromRequest($this->context.'.filter.publish_down', 'publish_down', '', 'string');
        $this->setState('filter.publish_up', $publish_up);
        $this->setState('filter.publish_down', $publish_down);
        parent::populateState('id', 'desc');
    }
}