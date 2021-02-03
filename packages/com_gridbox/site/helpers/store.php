<?php

/**
 * @package   Gridbox
 * @author    Balbooa http://www.balbooa.com/
 * @copyright Copyright @ Balbooa
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

class store
{
    private $store;
    private $pdf;
    private $tags;
    private $email;
    private $config;

    public function __construct()
    {
        $this->store = $this->checkSettings();
    }

    public function getSettings()
    {
        return $this->store;
    }

    protected function checkSettings()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_api')
            ->where('service = ' . $db->quote('store'));
        $db->setQuery($query);
        $obj = $db->loadObject();
        $store = json_decode($obj->key);
        $path = JPATH_ROOT . '/administrator/components/com_gridbox/assets/json/store.json';
        $update = false;
        if (!isset($store->currency)) {
            $str = JFile::read($path);
            $store = json_decode($str);
            $dir = JPATH_ROOT . '/administrator/components/com_gridbox/views/layouts/store-options/';
            $store->notification->body = JFile::read($dir . 'new-order-notification.html');
            $store->stock->body = JFile::read($dir . 'out-of-stock-notification.html');
            $store->confirmation->body = JFile::read($dir . 'order-confirmation.html');
            $store->completed->body = JFile::read($dir . 'order-completed.html');
            $update = true;
        }
        if (!isset($store->completed)) {
            $str = JFile::read($path);
            $object = json_decode($str);
            $dir = JPATH_ROOT . '/administrator/components/com_gridbox/views/layouts/store-options/';
            $store->completed = $object->completed;
            $store->completed->body = JFile::read($dir . 'order-completed.html');
            $update = true;
        }
        if (empty($store->completed->body)) {
            $dir = JPATH_ROOT . '/administrator/components/com_gridbox/views/layouts/store-options/';
            $store->completed->body = JFile::read($dir . 'order-completed.html');
            $update = true;
        }
        if (!isset($store->tax->mode)) {
            $tax = new stdClass();
            $tax->mode = 'excl';
            $tax->rates = array();
            if (!empty($store->tax->amount)) {
                $rate = new stdClass();
                $rate->title = JText::_('TAX');
                $rate->rate = $store->tax->amount;
                $rate->categories = array();
                $rate->country = '';
                $rate->regions = array();
                $rate->shipping = $store->tax->shipping;
                $tax->rates[] = $rate;
            }
            $store->tax = $tax;
            $update = true;
        }
        if (!isset($store->checkout)) {
            $str = JFile::read($path);
            $object = json_decode($str);
            $store->checkout = $object->checkout;
            $store->wishlist = $object->wishlist;
            $update = true;
        }
        if (!isset($store->units)) {
            $str = JFile::read($path);
            $object = json_decode($str);
            $store->units = $object->units;
            $update = true;
        }
        foreach ($store->tax->rates as $rate) {
            if (!isset($rate->country_id) && !empty($rate->country)) {
                $update = true;
                $country = new stdClass();
                $country->title = $rate->country;
                $db->insertObject('#__gridbox_countries', $country);
                $rate->country_id = $db->insertid();
                unset($rate->country);
                foreach ($rate->regions as $region) {
                    $state = new stdClass();
                    $state->title = $region->title;
                    $state->country_id = $rate->country_id;
                    $db->insertObject('#__gridbox_country_states', $state);
                    $region->state_id = $db->insertid();
                    unset($region->title);
                }
            }
        }

        if ($update) {
            $obj->key = json_encode($store);
            $db->updateObject('#__gridbox_api', $obj, 'id');
        }

        return $store;
    }

    public function checkShippingOptions($items)
    {
        $db = JFactory::getDbo();
        $options = null;
        foreach ($items as $item) {
            if (empty($item->options)) {
                if (!$options) {
                    $path = JPATH_ROOT . '/administrator/components/com_gridbox/assets/json/shipping-options.json';
                    $str = JFile::read($path);
                    $options = json_decode($str);
                }
                $options->flat->price = $item->price;
                $options->flat->enabled = $item->free !== '' ? true : false;
                $options->flat->free = $item->free;
                $item->options = json_encode($options);
                $db->updateObject('#__gridbox_store_shipping', $item, 'id');
            }
        }
    }

    public function checkAppType($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('type')
            ->from('#__gridbox_app')
            ->where('id = ' . $id);
        $db->setQuery($query);
        $type = $db->loadResult();

        return $type == 'products';
    }

    protected function getOrder($id, $deep = false)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders')
            ->where('id = ' . $id);
        $db->setQuery($query);
        $order = $db->loadObject();
        if ($deep) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_order_customer_info')
                ->where('order_id = ' . $id);
            $db->setQuery($query);
            $order->info = $db->loadObjectList();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_orders_discount')
                ->where('order_id = ' . $id);
            $db->setQuery($query);
            $order->promo = $db->loadObject();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_orders_shipping')
                ->where('order_id = ' . $id);
            $db->setQuery($query);
            $order->shipping = $db->loadObject();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_order_products')
                ->where('order_id = ' . $id);
            $db->setQuery($query);
            $order->products = $db->loadObjectList();
            foreach ($order->products as $product) {
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_store_order_product_variations')
                    ->where('product_id = ' . $product->id);
                $db->setQuery($query);
                $product->variations = $db->loadObjectList();
            }
        }

        return $order;
    }

    protected function getPayment($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders_payment')
            ->where('order_id = ' . $id);
        $db->setQuery($query);
        $payment = $db->loadObject();

        return $payment;
    }

    public function setOrder($id)
    {
        $payment = $this->getPayment($id);
        $input = JFactory::getApplication()->input;
        $empty = array('liqpay', 'mollie', 'payfast', 'dotpay', 'pagseguro');
        if (!$payment || $payment->type == 'offline') {
            $this->approveOrder($id);
        } else if (in_array($payment->type, $empty)) {
            $this->approveOrder(0, null, false);
        } else if ($payment->type == 'robokassa') {
            $inv_id = $input->get("InvId", 0, 'int');
            $approveId = (!empty($inv_id) && $inv_id == $id) ? $id : 0;
            $update = (!empty($inv_id) && $inv_id == $id) ? true : false;
            $this->approveOrder($approveId, null, $update);
        } else if ($payment->type == 'paypal') {
            $order = $this->getOrder($id);
            $params = json_decode($order->params);
            if ($params->status == 'COMPLETED' && floor($params->purchase_units[0]->amount->value) == floor($order->total)) {
                $this->approveOrder($id);
            }
        } else if ($payment->type == 'authorize') {
            $order = $this->getOrder($id);
            $params = json_decode($order->params);
            if (!empty($params->transactionResponse->transId)) {
                $this->approveOrder($id);
            }
        } else if ($payment->type == 'cloudpayments') {
            $order = $this->getOrder($id);
            $params = json_decode($order->params);
            if ($params->amount == $order->total) {
                $this->approveOrder($id);
            }
        } else if ($payment->type == 'payupl') {
            $order = $this->getOrder($id);
            $payupl = gridboxHelper::getStorePayment('payupl');
            $payupl->params = json_decode($payupl->settings);
            $json = gridboxHelper::authorizePayupl($payupl->params);
            $params = json_decode($order->params);
            $url = 'https://secure' . ($payupl->params->environment == 'sandbox' ? '.snd' : '') . '.payu.com';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . "/api/v2_1/orders/" . $params->id);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer " . $json->access_token
            ));
            $response = curl_exec($ch);
            curl_close($ch);
            $json = json_decode($response);
            $price = $this->preparePrice($order->total, '', '.', 2);
            if ($json->orders[0]->status == 'COMPLETED' && $json->orders[0]->totalAmount == $price * 100) {
                $this->approveOrder($id, $response);
            }
        } else if ($payment->type == 'klarna') {
            $order = $this->getOrder($id);
            $klarna = gridboxHelper::getStorePayment('klarna');
            $klarna->params = json_decode($klarna->settings);
            $params = json_decode($order->params);
            $order_id = $_GET['order_id'];
            $headers = array('Content-Type: application/json');
            $curl = curl_init('https://api.playground.klarna.com//checkout/v3/orders/' . $order_id);
            curl_setopt($curl, CURLOPT_USERPWD, $klarna->params->username . ':' . $klarna->params->password);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 80);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $body = curl_exec($curl);
            $response = json_decode($body);
            if ($response->status == 'checkout_complete') {
                unset($response->html_snippet);
                $body = json_encode($body);
                $this->approveOrder($id, $body);
            } else {
                $this->approveOrder(0, null, false);
            }
        } else if ($payment->type == 'yandex-kassa') {
            $order = $this->getOrder($id);
            $yandex = gridboxHelper::getStorePayment('yandex-kassa');
            $yandex->params = json_decode($yandex->settings);
            $params = json_decode($order->params);
            $encodedAuth = base64_encode($yandex->params->shop_id . ':' . $yandex->params->secret_key);
            $headers = array('Content-Type: application/json', 'Authorization: Basic ' . $encodedAuth);
            $curl = curl_init('https://payment.yandex.net/api/v3/payments/' . $params->id);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 80);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $body = curl_exec($curl);
            $response = json_decode($body);
            if (isset($response->status) && $response->status == 'succeeded') {
                $this->approveOrder($id, $body);
            }
        } else if ($payment->type == 'twocheckout') {
            $post = $input->post->getArray(array());
            $price = $this->preparePrice($order->total, '', '.', 2);
            $price2 = $this->preparePrice($post['total'], '', '.', 2);
            if ($post['credit_card_processed'] == 'Y' && $price == $price2) {
                $this->approveOrder($id);
            }
        } else if ($payment->type == 'stripe') {
            $order = $this->getOrder($id);
            $stripe = gridboxHelper::getStorePayment('stripe');
            $params = json_decode($order->params);
            $ua = array(
                'bindings_version' => '7.17.0', 'lang' => 'php',
                'lang_version' => phpversion(), 'publisher' => 'stripe', 'uname' => php_uname()
            );
            $headers = array(
                'X-Stripe-Client-User-Agent: ' . json_encode($ua),
                'User-Agent: Stripe/v1 PhpBindings/7.17.0',
                'Authorization: Bearer ' . $stripe->params->secret_key
            );
            $url = 'https://api.stripe.com/v1/events?type=checkout.session.completed&created[gte]=' . (time() - 2 * 60 * 60);
            $curl = curl_init();
            $options = array();
            $options[CURLOPT_HTTPGET] = 1;
            $options[CURLOPT_URL] = $url;
            $options[CURLOPT_CONNECTTIMEOUT] = 30;
            $options[CURLOPT_TIMEOUT] = 80;
            $options[CURLOPT_RETURNTRANSFER] = true;
            $options[CURLOPT_HTTPHEADER] = $headers;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            curl_setopt_array($curl, $options);
            $body = curl_exec($curl);
            $json = json_decode($body);
            $object = null;
            foreach ($json->data as $data) {
                if ($data->data->object->id == $params->id) {
                    $object = $data;
                    break;
                }
            }
            if ($object) {
                $str = json_encode($object);
                $this->approveOrder($id, $str);
            }
        } else if ($payment->type == "paystack") {
            $order = $this->getOrder($id);
            $paystack = gridboxHelper::getStorePayment('paystack');
            $initUrl = "https://api.paystack.co/transaction/initialize";
            $curl = curl_init();
            $options = array();

            $headers = array(
                'Content-type: application/json',
                'Authorization: Bearer ' . $paystack->params->secret_key
            );

            $options[CURLOPT_HTTPGET] = 1;
            $options[CURLOPT_URL] = $initUrl;
            $options[CURLOPT_CONNECTTIMEOUT] = 30;
            $options[CURLOPT_TIMEOUT] = 80;
            $options[CURLOPT_RETURNTRANSFER] = true;
            $options[CURLOPT_HTTPHEADER] = $headers;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            curl_setopt_array($curl, $options);
        }
    }

    public function approveOrder($id, $params = null, $update = true, $cookie = true, $redirect = true)
    {
        if ($update) {
            $this->updateOrder($id, $params);
        }
        if ($cookie) {
            $time = time() - 604800;
            gridboxHelper::setcookie('gridbox_store_order', 0, $time);
            gridboxHelper::setcookie('gridbox_store_cart', 0, $time);
        }
        $payment = $this->getPayment($id);
        $order = $this->getOrder($id, true);
        $digital = true;
        foreach ($order->products as $product) {
            if ($product->product_type != 'digital') {
                $digital = false;
                break;
            }
        }
        if ($digital && $payment && $payment->type != 'offline') {
            $this->updateStatus($id, 'completed');
        }
        if ($redirect) {
            $url = gridboxHelper::getStoreSystemUrl('thank-you-page');
            header('Location: ' . $url);
        }
        exit;
    }

    public function updateOrder($id, $params = null)
    {
        $db = JFactory::getDbo();
        $str = (string)$id;
        $len = strlen($str);
        $number = '#00000000';
        $i = $len >= 8 ? 1 : 9 - $len;
        $obj = new stdClass();
        $obj->id = $id;
        $obj->date = JDate::getInstance()->format('Y-m-d H:i:s');
        $obj->order_number = substr($number, 0, $i) . $str;
        $obj->published = 1;
        if ($params) {
            $obj->params = $params;
        }
        $db->updateObject('#__gridbox_store_orders', $obj, 'id');
        $this->prepareEmails($id);
        $this->sendNotificationEmail();
        $this->sendStoreEmail('confirmation');
        $this->clear();
    }

    protected function sendNotificationEmail()
    {
        if (!empty($this->store->notification->admins)) {
            $recipients = $this->store->notification->admins;
            $sender = array($this->config->get('mailfrom'), $this->config->get('fromname'));
            $this->sendEmail($sender, $this->store->notification->subject, $recipients, $this->store->notification->body);
        }
    }

    protected function sendStockEmail()
    {
        if (!empty($this->store->stock->admins) && !empty($this->outStock)) {
            $recipients = $this->store->stock->admins;
            $sender = array($this->config->get('mailfrom'), $this->config->get('fromname'));
            foreach ($this->outStock as $product) {
                $this->tags->{'[Product Title]'} = $product->title;
                $this->tags->{'[Product SKU]'} = $product->sku;
                $this->tags->{'[Product Quantity]'} = $product->stock;
                $this->sendEmail($sender, $this->store->stock->subject, $recipients, $this->store->stock->body);
            }
        }
    }

    protected function sendStoreEmail($key)
    {
        if (!empty($this->store->{$key}->email)) {
            $sender = array($this->store->{$key}->email, $this->store->{$key}->name);
            $recipients = array($this->email);
            $this->sendEmail($sender, $this->store->{$key}->subject, $recipients, $this->store->{$key}->body);
        }
    }

    protected function prepareEmails($id)
    {
        JFactory::getLanguage()->load('com_gridbox');
        $this->config = JFactory::getConfig();
        $this->outStock = array();
        $order = $this->getOrder($id, true);
        $db = JFactory::getDbo();
        $products = $order->products;
        $title = array();
        $sku = array();
        $quantity = array();
        foreach ($products as $product) {
            if ($product->product_type == 'digital' && $order->status == 'completed') {
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_store_order_license')
                    ->where('product_id = ' . $product->id);
                $db->setQuery($query);
                $license = $db->loadObject();
                if ($license->expires == 'new') {
                    $query = $db->getQuery(true)
                        ->select('d.digital_file')
                        ->from('#__gridbox_store_order_products AS op')
                        ->where('op.id = ' . $product->id)
                        ->leftJoin('#__gridbox_store_product_data AS d ON d.product_id = op.product_id');
                    $db->setQuery($query);
                    $digital_file = $db->loadResult();
                    $digital = !empty($digital_file) ? json_decode($digital_file) : new stdClass();
                    if (empty($digital->expires->value)) {
                        $license->expires = '';
                    } else {
                        $expires = array('h' => 'hour', 'd' => 'day', 'm' => 'month', 'y' => 'year');
                        $time = strtotime('+' . $digital->expires->value . ' ' . $expires[$digital->expires->format]);
                        $license->expires = date("Y-m-d H:i:s", $time);
                    }
                    $db->updateObject('#__gridbox_store_order_license', $license, 'id');
                }
            }
            $query = $db->getQuery(true)
                ->select('variations, stock, sku')
                ->from('#__gridbox_store_product_data')
                ->where('product_id = ' . $product->product_id);
            $db->setQuery($query);
            $result = $db->loadObject();
            if (!empty($product->variation) && $result) {
                $variations = json_decode($result->variations);
                if (isset($variations->{$product->variation})) {
                    $result->stock = $variations->{$product->variation}->stock;
                    $result->sku = $variations->{$product->variation}->sku;
                } else {
                    continue;
                }
            } else if (!$result) {
                continue;
            }
            $title[] = $product->title;
            $sku[] = $product->sku;
            $quantity[] = $product->quantity;
            if ($result->stock !== '' && $result->stock * 1 <= $this->store->stock->quantity * 1) {
                $product->stock = $result->stock;
                $product->sku = $result->sku;
                $this->outStock[] = $product;
            }
        }
        $shipping = $order->shipping;
        $discount = $order->promo;
        $information = $order->info;
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders_payment')
            ->where('order_id = ' . $id);
        $db->setQuery($query);
        $payment = $db->loadObject();
        $tags = new stdClass();
        $tags->id = $id;
        $tags->{'[Product Title]'} = implode(', ', $title);
        $tags->{'[Product SKU]'} = implode(', ', $sku);
        $tags->{'[Product Quantity]'} = implode(', ', $quantity);
        $tags->{'[Store Name]'} = $this->store->general->store_name;
        $tags->{'[Store Legal Business Name]'} = $this->store->general->business_name;
        $tags->{'[Store Phone]'} = $this->store->general->phone;
        $tags->{'[Store Email]'} = $this->store->general->email;
        $general = $this->store->general;
        $address = array();
        if (!empty($general->country)) {
            $address[] = $general->country;
        }
        if (!empty($general->region)) {
            $address[] = $general->region;
        }
        if (!empty($general->city)) {
            $address[] = $general->city;
        }
        if (!empty($general->street)) {
            $address[] = $general->street;
        }
        if (!empty($general->zip_code)) {
            $address[] = $general->zip_code;
        }
        $tags->{'[Store Address]'} = implode(', ', $address);
        $tags->{'[Order Number]'} = $order->order_number;
        $tags->{'[Order Date]'} = JDate::getInstance($order->date)->format(gridboxHelper::$website->date_format);
        include JPATH_ROOT . '/components/com_gridbox/views/layout/patterns/store/email-store-order-details.php';
        $tags->{'[Order Details]'} = $out;
        foreach ($information as $info) {
            $tags->{'[Customer ID=' . $info->customer_id . ']'} = $info->value;
            if ($info->type == 'email') {
                $this->email = $info->value;
            } else if ($info->type == 'country') {
                $object = json_decode($info->value);
                $values = array();
                if (!empty($object->region)) {
                    $values[] = $object->region;
                }
                $values[] = $object->country;
                $value = implode(', ', $values);
                $tags->{'[Customer ID=' . $info->customer_id . ']'} = $value;
            }
        }
        $this->tags = $tags;
    }

    protected function sendEmail($sender, $subject, $recipients, $body)
    {
        $tags = $this->tags;
        $mailer = JFactory::getMailer();
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $subject = $this->replaceStoreDataTags($tags, $subject);
        $body = $this->replaceStoreDataTags($tags, $body);
        $subject = $this->checkInvoice($subject, $tags->id, $mailer);
        $body = $this->checkInvoice($body, $tags->id, $mailer);
        $mailer->setSender($sender);
        $mailer->setSubject($subject);
        $mailer->addRecipient($recipients);
        $mailer->setBody($body);
        $mailer->Send();
    }

    protected function checkInvoice($text, $id, $mailer)
    {
        if (strpos($text, '[Invoice: Attached]') !== false) {
            $text = str_replace('[Invoice: Attached]', '', $text);
            $this->createPdf($id);
            if (!empty($this->pdf)) {
                $mailer->addAttachment(array($this->pdf));
            }
        }

        return $text;
    }

    protected function createPdf($id)
    {
        if (empty($this->pdf)) {
            $config = JFactory::getConfig();
            $path = $config->get('tmp_path') . '/';
            $order = $this->getOrder($id, true);
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
            include JPATH_ROOT . '/components/com_gridbox/libraries/php/tfpdf/pdf.php';
            $pdf = new pdf('Portrait', 'mm', 'A4');
            $pdf->store = $this->store;
            $this->pdf = $pdf->create($order, $this->store->general, 'F', $path);
        }
    }

    protected function clear()
    {
        if (!empty($this->pdf)) {
            unlink($this->pdf);
        }
    }

    public function updateStatus($id, $status, $comment = null)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('status')
            ->from('#__gridbox_store_orders')
            ->where('id = ' . $id);
        $db->setQuery($query);
        $old = $db->loadResult();
        $query = $db->getQuery(true)
            ->update('#__gridbox_store_orders')
            ->set('status = ' . $db->quote($status))
            ->where('id = ' . $id);
        $db->setQuery($query)
            ->execute();
        if ($status == 'completed' && $old != 'completed') {
            $this->updateOrderUsed($id);
            $this->prepareEmails($id);
            $this->sendStockEmail();
            $this->sendStoreEmail('completed');
        } else if ($old == 'completed' && $status != 'completed') {
            $this->updateOrderUsed($id, '-');
        }
        if ($comment != null) {
            $obj = new stdClass();
            $obj->date = JDate::getInstance()->format('Y-m-d H:i:s');
            $obj->status = $status;
            $obj->comment = $comment;
            $obj->order_id = $id;
            $obj->user_id = JFactory::getUser()->id;
            $db->insertObject('#__gridbox_store_orders_status_history', $obj);
        }
        $this->clear();
    }

    protected function updateOrderUsed($id, $action = '+')
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('promo_id')
            ->from('#__gridbox_store_orders_discount')
            ->where('order_id = ' . $id);
        $db->setQuery($query);
        $promo_id = $db->loadResult();
        if (!empty($promo_id)) {
            $query = $db->getQuery(true)
                ->update('#__gridbox_store_promo_codes')
                ->set('`used` = `used` ' . $action . ' 1')
                ->where('`id` = ' . $promo_id);
            $db->setQuery($query)
                ->execute();
        }
        $query = $db->getQuery(true)
            ->select('d.id, d.stock, o.variation, d.variations, o.quantity')
            ->from('#__gridbox_store_order_products AS o')
            ->where('o.order_id = ' . $id)
            ->leftJoin('#__gridbox_store_product_data AS d ON o.product_id = d.product_id');
        $db->setQuery($query);
        $products = $db->loadObjectList();
        foreach ($products as $product) {
            $variations = json_decode($product->variations);
            $q = $product->quantity * ($action == '+' ? -1 : 1);
            if (empty($product->variation) && $product->stock !== '') {
                $product->stock = $product->stock * 1 + $q;
            } else if (
                !empty($product->variation) && isset($variations->{$product->variation})
                && $variations->{$product->variation}->stock !== ''
            ) {
                $variation = $variations->{$product->variation};
                $variation->stock = $variation->stock * 1  + $q;
            } else {
                continue;
            }
            $obj = new stdClass();
            $obj->id = $product->id;
            $obj->variations = json_encode($variations);
            $obj->stock = $product->stock;
            $db->updateObject('#__gridbox_store_product_data', $obj, 'id');
        }
    }

    protected function replaceStoreDataTags($tags, $text)
    {
        foreach ($tags as $tag => $value) {
            if ($tag == 'id') {
                continue;
            }
            $text = str_replace($tag, $value, $text);
        }
        $text = preg_replace('/\[Customer ID=\d+\]/', '', $text);

        return $text;
    }

    protected function preparePrice($price, $thousand, $separator, $decimals)
    {
        $price = round($price * 1, $decimals);
        $price = number_format($price, $decimals, $separator, $thousand);

        return $price;
    }
}
