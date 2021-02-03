<?php

/**
 * @package   Gridbox
 * @author    Balbooa http://www.balbooa.com/
 * @copyright Copyright @ Balbooa
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;
jimport('joomla.filesystem.file');
jimport('joomla.http.http');
jimport('joomla.filesystem.folder');

class gridboxModelStore extends JModelItem
{
    public function getTable($type = 'Fonts', $prefix = 'gridboxTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getProductsList($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('p.id, p.title, p.intro_image AS image, d.price')
            ->from('#__gridbox_pages AS p')
            ->where('p.id <> ' . $id)
            ->where('a.type = ' . $db->quote('products'))
            ->leftJoin('#__gridbox_app AS a ON a.id = p.app_id')
            ->leftJoin('#__gridbox_store_product_data AS d ON d.product_id = p.id')
            ->order('p.id ASC');
        $db->setQuery($query);
        $data = new stdClass();
        $data->currency = gridboxHelper::$store->currency;
        $data->list = $db->loadObjectList();
        $t = $data->currency->thousand;
        $s = $data->currency->separator;
        $d = $data->currency->decimals;
        foreach ($data->list as $value) {
            $value->image = (!empty($value->image) && strpos($value->image, 'balbooa.com') === false ? JUri::root() : '') . $value->image;
            $value->price = gridboxHelper::preparePrice($value->price, $t, $s, $d);
        }

        return $data;
    }

    public function getAppStoreFields($id, $type)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('DISTINCT pf.field_key, pf.title')
            ->from('#__gridbox_store_product_variations_map AS vm')
            ->where('(pf.field_type = ' . $db->quote('image') . ' OR pf.field_type = ' . $db->quote('color') . ')')
            ->leftJoin('#__gridbox_pages AS p ON p.id = vm.product_id')
            ->leftJoin('#__gridbox_store_products_fields AS pf ON pf.id = vm.field_id')
            ->order('vm.order_group ASC, pf.title ASC');
        if ($type != 'store-search-result') {
            $query->where('p.app_id = ' . $id);
        }
        $db->setQuery($query);
        $fields = $db->loadObjectList();
        $data = array('badge' => JText::_('PRODUCT_BADGE'), 'wishlist' => JText::_('WISHLIST'));
        foreach ($fields as $field) {
            $data[$field->field_key] = $field->title;
        }
        $data['price'] = JText::_('PRICE');
        $data['cart'] = JText::_('ADD_TO_CART');

        return $data;
    }

    public function getLiveSearchQuery($search)
    {
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $levels = $user->getAuthorisedViewLevels();
        $groups = implode(',', $levels);
        $lang = JFactory::getLanguage()->getTag();
        $wheres = array();
        $wheres[] = 'p.title LIKE ' . $db->quote('%' . $db->escape($search, true) . '%', false);
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->from('#__gridbox_pages AS p')
            ->where('(' . implode(' OR ', $wheres) . ')')
            ->where('p.page_category <> ' . $db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= ' . $db->quote($date))
            ->where('(p.end_publishing = ' . $nullDate . ' OR p.end_publishing >= ' . $db->quote($date) . ')')
            ->where('p.language in (' . $db->quote($lang) . ',' . $db->quote('*') . ')')
            ->where('p.page_access in (' . $groups . ')')
            ->where('a.type =' . $db->quote('products'))
            ->leftJoin('#__gridbox_app AS a ON p.app_id = a.id')
            ->order('p.created desc');

        return $query;
    }

    public function getLiveSearchData($search)
    {
        $db = JFactory::getDbo();
        $data = new stdClass();
        $query = $this->getLiveSearchQuery($search)
            ->select('p.id, p.title, p.intro_image, p.page_category, p.app_id');
        $db->setQuery($query, 0, 10);
        $data->pages = $db->loadObjectList();
        $query = $this->getLiveSearchQuery($search)
            ->select('COUNT(p.id)');
        $db->setQuery($query);
        $data->count = $db->loadResult();
        foreach ($data->pages as $page) {
            $product = gridboxHelper::getProductData($page->id);
            $page->prices = gridboxHelper::prepareProductPrices($page->id, $product->price, $product->sale_price);
            $page->link = gridboxHelper::getGridboxPageLinks($page->id, 'product', $page->app_id, $page->page_category);
        }
        $currency = gridboxHelper::$store->currency;
        include JPATH_ROOT . '/components/com_gridbox/views/layout/patterns/store/live-search-results.php';

        return $out;
    }

    public function addPostToWishlist($id, $wishlist_id)
    {
        $variations = gridboxHelper::getProductVariationsMap($id);
        $data = gridboxHelper::getProductData($id);
        $variation = '';
        foreach ($data->variations as $key => $value) {
            if (isset($value->default) && $value->default) {
                $variation = $key;
                break;
            }
        }
        $extraFlag = false;
        $options = new stdClass();
        foreach ($data->extra_options as $field_id => $extra) {
            $required = $extra->required * 1 == 1;
            foreach ($extra->items as $item) {
                if ($required && $item->default) {
                    $required = false;
                }
                if ($item->default) {
                    $options->{$item->key} = new stdClass();
                    $options->{$item->key}->price = $item->price;
                    $options->{$item->key}->field_id = $field_id;
                }
            }
            if ($required) {
                $extraFlag = $required;
            }
        }
        $response = new stdClass();
        $response->status = false;
        if ((empty($variations) || !empty($variation)) && !$extraFlag) {
            $extra_options = json_encode($options);
            $response->status = true;
            $response->data = $this->addProductToWishlist($id, $wishlist_id, $variation, $extra_options);
        }

        return $response;
    }

    public function addPostToCart($id, $cart_id)
    {
        $variations = gridboxHelper::getProductVariationsMap($id);
        $data = gridboxHelper::getProductData($id);
        $variation = '';
        foreach ($data->variations as $key => $value) {
            if (isset($value->default) && $value->default) {
                $variation = $key;
                break;
            }
        }
        $extraFlag = false;
        $options = new stdClass();
        foreach ($data->extra_options as $field_id => $extra) {
            $required = $extra->required * 1 == 1;
            foreach ($extra->items as $item) {
                if ($required && $item->default) {
                    $required = false;
                }
                if ($item->default) {
                    $options->{$item->key} = new stdClass();
                    $options->{$item->key}->price = $item->price;
                    $options->{$item->key}->field_id = $field_id;
                }
            }
            if ($required) {
                $extraFlag = $required;
            }
        }
        $response = new stdClass();
        $response->status = false;
        if ((empty($variations) || !empty($variation)) && !$extraFlag) {
            $extra_options = json_encode($options);
            $response->status = true;
            $this->addProductToCart($id, $cart_id, 1, $variation, $extra_options);
        }

        return $response;
    }

    public function deleteStoreBadge($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__gridbox_store_badges')
            ->where('id = ' . $id);
        $db->setQuery($query)
            ->execute();
    }

    public function updateStoreBadge($badge)
    {
        $db = JFactory::getDbo();
        $db->updateObject('#__gridbox_store_badges', $badge, 'id');
    }

    public function getStoreBadge()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_badges');
        $db->setQuery($query);
        $badges = $db->loadObjectList();

        return $badges;
    }

    public function addProductBadge()
    {
        $db = JFactory::getDbo();
        $badge = new stdClass();
        $badge->color = '#1da6f4';
        $badge->title = JText::_('PRODUCT_BADGE');
        $db->insertObject('#__gridbox_store_badges', $badge);
        $badge->id = $db->insertid();

        return $badge;
    }

    public function payfastCallback($data)
    {
        $payfast = gridboxHelper::getStorePayment('payfast');
        $payfast->params = json_decode($payfast->settings);
        if ($data['payment_status'] == 'COMPLETE') {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_store_orders')
                ->where('params = ' . $db->quote($data['m_payment_id']));
            $db->setQuery($query);
            $id = $db->loadResult();
            if ($id) {
                $json = json_encode($data);
                gridboxHelper::$storeHelper->approveOrder($id, $json, true, false, false);
            }
        }
        exit();
    }

    public function pagseguroCallback($id, $transactionCode)
    {
        if (!empty($id) && !empty($transactionCode)) {
            gridboxHelper::$storeHelper->approveOrder($id, $transactionCode, true, false, false);
        }
    }

    public function robokassaCallback($inv_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__gridbox_store_orders')
            ->where('id = ' . $inv_id)
            ->where('published = 0');
        $db->setQuery($query);
        $id = $db->loadResult();
        if ($id) {
            gridboxHelper::$storeHelper->approveOrder($id, null, true, false, false);
        }
        exit();
    }

    public function dotpayCallback($data)
    {
        if (isset($data['operation_status']) && $data['operation_status'] == 'completed') {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_store_orders')
                ->where('params = ' . $db->quote($data['control']));
            $db->setQuery($query);
            $id = $db->loadResult();
            if ($id) {
                gridboxHelper::$storeHelper->approveOrder($id, null, true, false, false);
            }
        }
    }

    public function liqpayCallback($data, $signature)
    {
        $liqpay = gridboxHelper::getStorePayment('liqpay');
        $liqpay->params = json_decode($liqpay->settings);
        $str = $liqpay->params->private_key . $data . $liqpay->params->private_key;
        $sign = base64_encode(sha1($str, 1));
        if ($sign == $signature) {
            $json = base64_decode($data);
            $obj = json_decode($json);
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_store_orders')
                ->where('params = ' . $obj->order_id);
            $db->setQuery($query);
            $id = $db->loadResult();
            if ($id) {
                gridboxHelper::$storeHelper->approveOrder($id, $json, true, false, false);
            }
        }
        exit();
    }

    public function klarnaCallback($order_id)
    {
        $klarna = gridboxHelper::getStorePayment('klarna');
        $klarna->params = json_decode($klarna->settings);
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
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_store_orders')
                ->where('params = ' . $order_id);
            $db->setQuery($query);
            $id = $db->loadResult();
            if ($id) {
                gridboxHelper::$storeHelper->approveOrder($id, $body, true, false, false);
            }
        }
    }

    public function mollieCallback($id)
    {
        $mollie = gridboxHelper::getStorePayment('mollie');
        $mollie->params = json_decode($mollie->settings);
        $headers = array('Authorization: Bearer ' . $mollie->params->api_key, 'Content-Type: application/json');
        $curl = curl_init();
        $options = array();
        $options[CURLOPT_URL] = 'https://api.mollie.com/v2/payments/' . $id;
        $options[CURLOPT_CONNECTTIMEOUT] = 30;
        $options[CURLOPT_TIMEOUT] = 80;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_SSL_VERIFYPEER] = false;
        curl_setopt_array($curl, $options);
        $body = curl_exec($curl);
        $response = json_decode($body);
        if (!empty($response->paidAt) && empty($response->_links->refunds) && empty($response->_links->chargebacks)) {
            $orderId = $response->metadata->order_id;
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_store_orders')
                ->where('params = ' . $orderId);
            $db->setQuery($query);
            $id = $db->loadResult();
            if ($id) {
                gridboxHelper::$storeHelper->approveOrder($id, $body, true, false, false);
            }
        }
        exit();
    }

    public function submitRobokassa($id)
    {
        $order = $this->getOrder($id);
        $robokassa = gridboxHelper::getStorePayment('robokassa');
        $robokassa->params = json_decode($robokassa->settings);
        $title = array();
        foreach ($order->products as $product) {
            $title[] = $product->title;
        }
        if ($order->shipping) {
            $title[] = $order->shipping->title;
        }
        $name = implode(', ', $title);
        $price = gridboxHelper::preparePrice($order->total, '', '.', 2);

        $inv_id = $id;
        $code = gridboxHelper::$store->currency->code;
        $allowedCurrency = array('USD', 'EUR', 'KZT');
        $OutSumCurrency = in_array($code, $allowedCurrency);
        $cache = $robokassa->params->merchant_id . ":" . $price . ":" . $inv_id . ":";
        if ($OutSumCurrency) {
            $cache .= $code . ":";
        }
        $cache .= $robokassa->params->merchant_password;
        $signature = md5($cache);
?>
        <form action="https://auth.robokassa.ru/Merchant/Index.aspx" method="POST" id="payment-form">
            <input type=hidden name=MerchantLogin value="<?php echo $robokassa->params->merchant_id; ?>">
            <input type=hidden name=OutSum value="<?php echo $price; ?>">
            <input type=hidden name=InvId value="<?php echo $inv_id; ?>">
            <input type=hidden name=Description value="<?php echo $name; ?>">
            <input type=hidden name=SignatureValue value="<?php echo $signature; ?>">
            <?php
            if ($OutSumCurrency) {
            ?>
                <input type=hidden name=OutSumCurrency value="<?php echo $code; ?>">
            <?php
            }
            ?>
        </form>
        <script type="text/javascript">
            document.getElementById('payment-form').submit();
        </script>
        <?php
        exit;
    }

    public function submitMollie($id)
    {
        $order = $this->getOrder($id);
        $mollie = gridboxHelper::getStorePayment('mollie');
        $mollie->params = json_decode($mollie->settings);
        $title = array();
        foreach ($order->products as $product) {
            $title[] = $product->title;
        }
        if ($order->shipping) {
            $title[] = $order->shipping->title;
        }
        $name = implode(', ', $title);
        $orderId = time();
        $price = gridboxHelper::preparePrice($order->total, '', '.', 2);
        $array = array(
            "amount" => array("currency" => gridboxHelper::$store->currency->code, "value" => $price),
            "description" => $name,
            "redirectUrl" => gridboxHelper::getStoreSystemUrl('thank-you-page'),
            "webhookUrl" => JUri::root() . "index.php?option=com_gridbox&task=store.mollieCallback",
            "metadata" => array("order_id" => $orderId)
        );
        $headers = array('Authorization: Bearer ' . $mollie->params->api_key, 'Content-Type: application/json');
        $curl = curl_init();
        $options = array();
        $options[CURLOPT_POST] = 1;
        $options[CURLOPT_POSTFIELDS] = json_encode($array);
        $options[CURLOPT_URL] = 'https://api.mollie.com/v2/payments';
        $options[CURLOPT_CONNECTTIMEOUT] = 30;
        $options[CURLOPT_TIMEOUT] = 80;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_SSL_VERIFYPEER] = false;
        curl_setopt_array($curl, $options);
        $body = curl_exec($curl);
        $response = json_decode($body);
        $this->updateOrder($id, $orderId);
        if (isset($response->_links) && isset($response->_links->checkout)) {
            header('Location: ' . $response->_links->checkout->href, true, 303);
        } else {
        ?>
            <script type="text/javascript">
                localStorage.setItem('gridbox_payment_error', '<?php echo addslashes($response->detail); ?>');
                window.location.href = '<?php echo $array["redirectUrl"] ?>';
            </script>
            <?php
        }
        exit();
    }

    public function submitPaystack($id)
    {

        $paystack = gridboxHelper::getStorePayment('paystack');
        $order = $this->getOrder($id);
        $price = gridboxHelper::preparePrice($order->total, '', '.', 2);

        if (isset($_GET['reference'])) {
            $headers = array(
                'Authorization: Bearer ' . $paystack->params->api_key
            );

            $curl = curl_init();
            $options = array();

            $options[CURLOPT_HTTPGET] = 1;
            $options[CURLOPT_URL] = "https://api.paystack.co/transaction/verify/" . $_GET['reference'];
            $options[CURLOPT_CONNECTTIMEOUT] = 30;
            $options[CURLOPT_TIMEOUT] = 80;
            $options[CURLOPT_HTTPHEADER] = $headers;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_RETURNTRANSFER] = true;

            curl_setopt_array($curl, $options);
            $body = curl_exec($curl);
            $json = json_decode($body);
            if ($json->status == 1 && $json->data->amount == $price * 100) {
                $this->updateOrder($id, $order->id);
            ?>
                <script>
                    window.location.href = "/";
                </script>
        <?php
            }
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select("*")
            ->from('#__gridbox_store_order_customer_info')
            ->where("title = 'Email'")
            ->where('order_id = ' . $order->id);
        $db->setQuery($query);
        $customerInfo = $db->loadObjectList();

        $initUrl = "https://api.paystack.co/transaction/initialize";
        $curl = curl_init();
        $options = array();
        $headers = array(
            'Content-type: application/json',
            'Authorization: Bearer ' . $paystack->params->api_key
        );

        $options[CURLOPT_HTTPGET] = 1;
        $options[CURLOPT_URL] = $initUrl;
        $options[CURLOPT_CONNECTTIMEOUT] = 30;
        $options[CURLOPT_TIMEOUT] = 80;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_SSL_VERIFYPEER] = false;
        $options[CURLOPT_POSTFIELDS] = json_encode([
            "email" => $customerInfo[0]->value,
            "amount" => $price * 100
        ]);
        curl_setopt_array($curl, $options);
        $body = curl_exec($curl);
        $json = json_decode($body);
        // print_r($json);
        // return false;
        ?>
        <script type="text/javascript">
            window.location.href = "<?= $json->data->authorization_url ?>";
        </script>
    <?php
    }

    public function submitPayfast($id)
    {
        $order = $this->getOrder($id);
        $payfast = gridboxHelper::getStorePayment('payfast');
        $payfast->params = json_decode($payfast->settings);
        $title = array();
        foreach ($order->products as $product) {
            $title[] = $product->title;
        }
        if ($order->shipping) {
            $title[] = $order->shipping->title;
        }
        $name = implode(', ', $title);
        $name = str_replace('"', '', $name);
        $url = ($payfast->params->environment == 'sandbox' ? 'https://sandbox.' : 'https://www.') . 'payfast.co.za/eng/process';
        $price = gridboxHelper::preparePrice($order->total, '', '.', 2);
        $m_payment_id = time();
        $this->updateOrder($id, $m_payment_id);
    ?>
        <form id="payment-form" method="POST" action="<?php echo $url; ?>" accept-charset="utf-8">
            <input type="hidden" name="merchant_id" value="<?php echo $payfast->params->merchant_id; ?>">
            <input type="hidden" name="merchant_key" value="<?php echo $payfast->params->merchant_key; ?>">
            <input type="hidden" name="return_url" value="<?php echo gridboxHelper::getStoreSystemUrl('thank-you-page'); ?>">
            <input type="hidden" name="notify_url" value="<?php echo JUri::root() . "index.php?option=com_gridbox&task=store.payfastCallback"; ?>">
            <input type="hidden" name="m_payment_id" value="<?php echo $m_payment_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $price; ?>">
            <input type="hidden" name="item_name" value="<?php echo $name; ?>">
        </form>
        <script type="text/javascript">
            document.getElementById("payment-form").submit();
        </script>
    <?php
        exit;
    }

    public function submitDotpay($id)
    {
        $order = $this->getOrder($id);
        $dotpay = gridboxHelper::getStorePayment('dotpay');
        $dotpay->params = json_decode($dotpay->settings);
        $title = array();
        foreach ($order->products as $product) {
            $title[] = $product->title;
        }
        if ($order->shipping) {
            $title[] = $order->shipping->title;
        }
        $name = implode(', ', $title);
        $name = str_replace('"', '', $name);
        $price = gridboxHelper::preparePrice($order->total, '', '.', 2);
        $array = array(
            "api_version" => "dev",
            "amount" => $price,
            "currency" => gridboxHelper::$store->currency->code,
            "description" => $name,
            "url" => gridboxHelper::getStoreSystemUrl('thank-you-page'),
            "type" => "0",
            "buttontext" => JText::_('RETURN_TO_SHOP'),
            "urlc" => JUri::root() . "index.php?option=com_gridbox&task=store.dotpayCallback",
            "control" => hash('md5', date("Y-m-d H:i:s")),
            "ignore_last_payment_channel" => 1
        );
        $chkStr = $dotpay->params->pin . $array['api_version'] . $dotpay->params->account_id . $array['amount'] .
            $array['currency'] . $array['description'] . $array['control'] . $array['url'] . $array['type'] .
            $array['buttontext'] . $array['urlc'] . $array['ignore_last_payment_channel'];
        $chk = hash('sha256', $chkStr);
        $url = 'https://ssl.dotpay.pl/' . ($dotpay->params->environment == 'sandbox' ? 'test_payment/' : 't2/');
        $this->updateOrder($id, $array['control']);
    ?>
        <form id="payment-form" method="POST" action="<?php echo $url ?>" accept-charset="utf-8">
            <input type="hidden" name="id" value="<?php echo $dotpay->params->account_id; ?>" />
            <?php
            foreach ($array as $key => $value) {
            ?>
                <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
            <?php
            }
            ?>
            <input type="hidden" name="chk" value="<?php echo $chk; ?>" />
        </form>
        <script type="text/javascript">
            document.getElementById("payment-form").submit();
        </script>
    <?php
        exit;
    }

    public function submitLiqpay($id)
    {
        $order = $this->getOrder($id);
        $liqpay = gridboxHelper::getStorePayment('liqpay');
        $liqpay->params = json_decode($liqpay->settings);
        $title = array();
        foreach ($order->products as $product) {
            $title[] = $product->title;
        }
        if ($order->shipping) {
            $title[] = $order->shipping->title;
        }
        $name = implode(', ', $title);
        $name = str_replace('"', '', $name);
        $orderId = time();
        $params = array(
            'action' => 'pay', 'amount' => $order->total, 'currency' => gridboxHelper::$store->currency->code,
            'description' => $name, 'server_url' => JUri::root() . "index.php?option=com_gridbox&task=store.liqpayCallback",
            'result_url' => gridboxHelper::getStoreSystemUrl('thank-you-page'), 'version' => '3',
            'public_key' => $liqpay->params->public_key, 'order_id' => $orderId
        );
        $data = base64_encode(json_encode($params));
        $str = $liqpay->params->private_key . $data . $liqpay->params->private_key;
        $signature = base64_encode(sha1($str, 1));
        $this->updateOrder($id, $orderId);
    ?>
        <form id="payment-form" method="POST" action="https://www.liqpay.ua/api/3/checkout" accept-charset="utf-8">
            <input type="hidden" name="data" value="<?php echo $data; ?>" />
            <input type="hidden" name="signature" value="<?php echo $signature; ?>" />
        </form>
        <script type="text/javascript">
            document.getElementById("payment-form").submit();
        </script>
        <?php
        exit;
    }

    public function submitPagseguro($id)
    {
        $order = $this->getOrder($id);
        $pagseguro = gridboxHelper::getStorePayment('pagseguro');
        $pagseguro->params = json_decode($pagseguro->settings);
        $url = 'https://ws.';
        if ($pagseguro->params->environment == 'sandbox') {
            $url .= 'sandbox.';
        }
        $url .= 'pagseguro.uol.com.br/v2/checkout';
        $array = array('email' => $pagseguro->params->email, 'token' => $pagseguro->params->token);
        $url .= '?' . http_build_query($array);
        $title = array();
        foreach ($order->products as $product) {
            $title[] = $product->title;
        }
        $name = implode(', ', $title);
        $total = $order->total - ($order->shipping ? $order->shipping->price : 0);
        $price = gridboxHelper::preparePrice($total, '', '.', 2);
        $content = "currency=BRL&itemId1=" . $id . "&itemDescription1=" . $name . "&itemAmount1=" . $price . "&itemQuantity1=1";
        if ($order->shipping) {
            $price = gridboxHelper::preparePrice($order->shipping->price, '', '.', 2);
            $content .= '&itemShippingCost1=' . $price;
        }
        $content .= '&shippingAddressRequired=true';
        //$content .= "&reference=".md5(time());
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded; charset=utf-8"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $xml = curl_exec($ch);
        curl_close($ch);
        $object = simplexml_load_string($xml);
        $response = new stdClass();
        $response->code = isset($object->code) ? $object->code : null;
        $response->status = $response->code ? true : false;
        $response->error = isset($object->error) ? $object->error->message : null;
        $str = json_encode($response);
        echo $str;
        exit;
    }

    public function submitKlarna($id)
    {
        $order = $this->getOrder($id);
        $klarna = gridboxHelper::getStorePayment('klarna');
        $klarna->params = json_decode($klarna->settings);
        $url = 'https://api.';
        if ($klarna->params->region == 'america') {
            $url .= 'na.';
        } else if ($klarna->params->region == 'oceania') {
            $url .= 'oc.';
        }
        if ($klarna->params->environment == 'sandbox') {
            $url .= 'playground.';
        }
        $url .= 'klarna.com/';
        $title = array();
        foreach ($order->products as $product) {
            $title[] = $product->title;
        }
        if ($order->shipping) {
            $title[] = $order->shipping->title;
        }
        $name = implode(', ', $title);
        $price = gridboxHelper::preparePrice($order->total, '', '.', 2);
        $locale = JFactory::getLanguage()->getTag();
        $confirmation = gridboxHelper::getStoreSystemUrl('thank-you-page');
        $checkout = gridboxHelper::getStoreSystemUrl('checkout');
        $terms = !empty($klarna->params->terms) ? $klarna->params->terms : JUri::root();
        $array = array(
            "purchase_country" => $klarna->params->country,
            "purchase_currency" => gridboxHelper::$store->currency->code,
            "locale" => $locale,
            "order_amount" => $price * 100,
            "order_tax_amount" => 0,
            "order_lines" => array(
                array(
                    "name" => $name,
                    "quantity" => 1,
                    "unit_price" => $price * 100,
                    "tax_rate" => 0,
                    "total_amount" => $price * 100,
                    "total_tax_amount" => 0
                )
            ),
            "merchant_urls" => array(
                "terms" => $terms,
                "checkout" => $checkout . "?order_id={checkout.order.id}",
                "confirmation" => $confirmation . "?order_id={checkout.order.id}",
                "push" => JUri::root() . "index.php?option=com_gridbox&task=store.klarnaCallback?order_id={checkout.order.id}"
            )
        );
        $headers = array('Content-Type: application/json');
        $curl = curl_init('https://api.playground.klarna.com/checkout/v3/orders');
        curl_setopt($curl, CURLOPT_USERPWD, $klarna->params->username . ':' . $klarna->params->password);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($array));
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 80);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($curl);
        exit;
    }

    public function submitYandexKassa($id)
    {
        $order = $this->getOrder($id);
        $yandex = gridboxHelper::getStorePayment('yandex-kassa');
        $yandex->params = json_decode($yandex->settings);
        $title = array();
        foreach ($order->products as $product) {
            $title[] = $product->title;
        }
        if ($order->shipping) {
            $title[] = $order->shipping->title;
        }
        $name = implode(', ', $title);
        $orderId = uniqid('', true);
        $price = gridboxHelper::preparePrice($order->total, '', '.', 2);
        $array = array(
            'amount' => array(
                'value' => $price,
                'currency' => gridboxHelper::$store->currency->code,
            ),
            'confirmation' => array(
                'type' => 'redirect',
                'return_url' => gridboxHelper::getStoreSystemUrl('thank-you-page'),
            ),
            'capture' => true,
            'description' => $name,
        );
        $encodedAuth = base64_encode($yandex->params->shop_id . ':' . $yandex->params->secret_key);
        $headers = array('Idempotence-Key: ' . $orderId, 'Content-Type: application/json', 'Authorization: Basic ' . $encodedAuth);
        $curl = curl_init('https://payment.yandex.net/api/v3/payments');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($array));
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 80);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($curl);
        $response = json_decode($body);
        $this->updateOrder($id, $body);
        if (isset($response->confirmation)) {
            header('Location: ' . $response->confirmation->confirmation_url);
        } else {
        ?>
            <script type="text/javascript">
                localStorage.setItem('gridbox_payment_error', '<?php echo addslashes($response->description); ?>');
                window.location.href = '<?php echo JUri::root() . "index.php/" . $alias; ?>';
            </script>
        <?php
        }
        exit();
    }

    public function submitPayupl($id)
    {
        $order = $this->getOrder($id);
        $payupl = gridboxHelper::getStorePayment('payupl');
        $payupl->params = json_decode($payupl->settings);
        $title = array();
        foreach ($order->products as $product) {
            $title[] = $product->title;
        }
        if ($order->shipping) {
            $title[] = $order->shipping->title;
        }
        $name = implode(', ', $title);
        $name = str_replace('"', '', $name);
        $url = 'https://secure' . ($payupl->params->environment == 'sandbox' ? '.snd' : '') . '.payu.com';
        $price = gridboxHelper::preparePrice($order->total, '', '.', '2');
        $json = gridboxHelper::authorizePayupl($payupl->params);
        if (isset($json->error)) {
            print_r($json->error_description);
            exit;
        }
        $fields = new stdClass();
        $fields->continueUrl = gridboxHelper::getStoreSystemUrl('thank-you-page');
        $fields->customerIp = $_SERVER['REMOTE_ADDR'];
        $fields->merchantPosId = $payupl->params->pos_id;
        $fields->description = gridboxHelper::$store->general->store_name;
        $fields->currencyCode = gridboxHelper::$store->currency->code;
        $fields->totalAmount = $price * 100;
        $product = new stdClass();
        $product->name = $name;
        $product->unitPrice = $price * 100;
        $product->quantity = 1;
        $fields->products = array($product);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . "/api/v2_1/orders/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $json->access_token
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($response);
        $this->updateOrder($id, '{"id":"' . $json->orderId . '"}');
        header('Location: ' . $json->redirectUri);
        exit;
    }

    public function submit2checkout($id)
    {
        $order = $this->getOrder($id);
        $checkout = gridboxHelper::getStorePayment('twocheckout');
        $checkout->params = json_decode($checkout->settings);
        if ($checkout->params->environment == 'sandbox') {
            $url = 'https://sandbox.2checkout.com/checkout/purchase';
        } else {
            $url = 'https://www.2checkout.com/checkout/purchase';
        }
        $title = array();
        foreach ($order->products as $product) {
            $title[] = $product->title;
        }
        if ($order->shipping) {
            $title[] = $order->shipping->title;
        }
        $price = gridboxHelper::preparePrice($order->total, '', '.', 2);
        ?>
        <form id="payment-form" action="<?php echo $url; ?>" method="post">
            <input type="hidden" name="sid" value="<?php echo $checkout->params->account_number; ?>">
            <input type="hidden" name="mode" value="2CO">
            <input type="hidden" name="pay_method" value="PPI">
            <input type="hidden" name="x_receipt_link_url" value="<?php echo gridboxHelper::getStoreSystemUrl('thank-you-page'); ?>">
            <input type="hidden" name="li_1_name" value="<?php echo implode(', ', $title); ?>">
            <input type="hidden" name="li_1_price" value="<?php echo $price; ?>">
            <input type="hidden" name="li_1_type" value="product">
            <input type="hidden" name="li_1_quantity" value="1">
        </form>
        <script type="text/javascript">
            document.getElementById('payment-form').submit();
        </script>
<?php
        exit;
    }

    public function stripeCharges($id, $payment_id)
    {
        $order = $this->getOrder($id);
        $stripe = $this->getPayment($payment_id);
        $stripe->params = json_decode($stripe->settings);
        $array = array(
            'payment_method_types' => array('card'),
            'line_items' => array(),
            'success_url' => gridboxHelper::getStoreSystemUrl('thank-you-page'),
            'cancel_url' => JUri::root()
        );
        $title = array();
        foreach ($order->products as $product) {
            $title[] = $product->title;
        }
        if ($order->shipping) {
            $title[] = $order->shipping->title;
        }
        $price = round($order->total, 2);
        $line_item = array();
        $line_item['name'] = implode(', ', $title);
        $line_item['amount'] = $price * 100;
        $line_item['quantity'] = 1;
        $line_item['currency'] = gridboxHelper::$store->currency->code;
        $array['line_items'][] = $line_item;
        $ua = array(
            'bindings_version' => '7.17.0', 'lang' => 'php',
            'lang_version' => phpversion(), 'publisher' => 'stripe', 'uname' => php_uname()
        );
        $headers = array(
            'X-Stripe-Client-User-Agent: ' . json_encode($ua),
            'User-Agent: Stripe/v1 PhpBindings/7.17.0',
            'Authorization: Bearer ' . $stripe->params->secret_key
        );
        $curl = curl_init();
        $options = array();
        $options[CURLOPT_POST] = 1;
        $options[CURLOPT_POSTFIELDS] = $this->encode($array);
        $options[CURLOPT_URL] = 'https://api.stripe.com/v1/checkout/sessions';
        $options[CURLOPT_CONNECTTIMEOUT] = 30;
        $options[CURLOPT_TIMEOUT] = 80;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_SSL_VERIFYPEER] = false;
        curl_setopt_array($curl, $options);
        $body = curl_exec($curl);
        // print_r($body);
        exit;
    }

    public function encode($arr, $prefix = null)
    {
        if (!is_array($arr)) {
            return $arr;
        }
        $r = array();
        foreach ($arr as $k => $v) {
            if (is_null($v)) {
                continue;
            }
            if ($prefix && $k && !is_int($k)) {
                $k = $prefix . "[" . $k . "]";
            } else if ($prefix) {
                $k = $prefix . "[]";
            }
            if (is_array($v)) {
                $r[] = $this->encode($v, $k, true);
            } else {
                $r[] = urlencode($k) . "=" . urlencode($v);
            }
        }

        return implode("&", $r);
    }

    public function getPaymentOptions($id)
    {
        if (!empty($id)) {
            $obj = $this->getPayment($id);
        } else {
            $obj = new stdClass();
            $obj->type = 'offline';
            $obj->settings = '{}';
        }

        return $obj;
    }

    public function payAuthorize($id, $cardNumber, $expirationDate, $cardCode)
    {
        $order = $this->getOrder($id);
        $authorize = gridboxHelper::getStorePayment('authorize');
        $authorize->params = json_decode($authorize->settings);

        $obj = new stdClass();
        $obj->createTransactionRequest = new stdClass();
        $obj->createTransactionRequest->merchantAuthentication = new stdClass();
        $obj->createTransactionRequest->merchantAuthentication->name = $authorize->params->login_id;
        $obj->createTransactionRequest->merchantAuthentication->transactionKey = $authorize->params->transaction_key;
        $obj->createTransactionRequest->clientId = 'sdk-php-2.0.0-ALPHA';
        $obj->createTransactionRequest->refId = 'ref' . time();
        $obj->createTransactionRequest->transactionRequest = new stdClass();
        $obj->createTransactionRequest->transactionRequest->transactionType = 'authCaptureTransaction';
        $obj->createTransactionRequest->transactionRequest->amount = $order->total;
        $obj->createTransactionRequest->transactionRequest->payment = new stdClass();
        $obj->createTransactionRequest->transactionRequest->payment->creditCard = new stdClass();
        $obj->createTransactionRequest->transactionRequest->payment->creditCard->cardNumber = $cardNumber;
        $obj->createTransactionRequest->transactionRequest->payment->creditCard->expirationDate = $expirationDate;
        $obj->createTransactionRequest->transactionRequest->payment->creditCard->cardCode = $cardCode;
        $xmlRequest = json_encode($obj);
        $url =  ($authorize->params->environment == 'sandbox' ? 'https://apitest' : 'https://api2') . '.authorize.net/xml/v1/request.api';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 45);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/json"));
        $text = curl_exec($curl);
        curl_close($curl);
        $response = json_decode(substr($text, 3), true);
        $str = json_encode($response);
        // print_r($str);
        exit;
    }

    public function updateOrder($id, $params)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->update('#__gridbox_store_orders')
            ->set('params = ' . $db->quote($params))
            ->where('id = ' . $id);
        $db->setQuery($query)
            ->execute();
    }

    public function getOrder($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders')
            ->where('id = ' . $id);
        $db->setQuery($query);
        $order = $db->loadObject();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_order_products')
            ->where('order_id = ' . $id);
        $db->setQuery($query);
        $order->products = $db->loadObjectList();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders_shipping')
            ->where('order_id = ' . $id);
        $db->setQuery($query);
        $order->shipping = $db->loadObject();

        return $order;
    }

    public function setCartShipping($id, $cart_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders_shipping')
            ->where('cart_id = ' . $cart_id);
        $db->setQuery($query);
        $shipping = $db->loadObject();
        if (!$shipping) {
            $shipping = new stdClass();
            $shipping->cart_id = $cart_id;
            $shipping->order_id = 0;
            $shipping->shipping_id = $id;
            $shipping->title = $shipping->price = $shipping->tax = '';
            $db->insertObject('#__gridbox_store_orders_shipping', $shipping);
        } else {
            $shipping->shipping_id = $id;
            $db->updateObject('#__gridbox_store_orders_shipping', $shipping, 'id');
        }
    }

    public function setCartPayment($id, $cart_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_orders_payment')
            ->where('cart_id = ' . $cart_id);
        $db->setQuery($query);
        $payment = $db->loadObject();
        if (!$payment) {
            $payment = new stdClass();
            $payment->cart_id = $cart_id;
            $payment->order_id = 0;
            $payment->payment_id = $id;
            $payment->title = $payment->type = '';
            $db->insertObject('#__gridbox_store_orders_payment', $payment);
        } else {
            $payment->payment_id = $id;
            $db->updateObject('#__gridbox_store_orders_payment', $payment, 'id');
        }
    }

    public function createOrder($data, $id)
    {
        $db = JFactory::getDbo();
        $cart = gridboxHelper::getStoreCart($id);
        $total = $cart->total + (gridboxHelper::$store->tax->mode == 'excl' ? $cart->tax : 0);
        if (!empty($data['shipping'])) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_shipping')
                ->where('id = ' . $data['shipping']);
            $db->setQuery($query);
            $obj = $db->loadObject();
            $tax = gridboxHelper::getStoreShippingTax($cart);
            $obj = gridboxHelper::getStoreShippingItem($obj, $total, $tax, $cart->total);
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_orders_shipping')
                ->where('cart_id = ' . $cart->id);
            $db->setQuery($query);
            $shipping = $db->loadObject();
            $shipping = $shipping ? $shipping : new stdClass();
            $shipping->type = $obj->params->type;
            $shipping->title = $obj->title;
            $shipping->price = $obj->price;
            $shipping->tax = $obj->tax;
            $shipping->shipping_id = $data['shipping'];
            $shipping->tax_title = $tax ? $tax->title : '';
            $shipping->tax_rate = $tax ? $tax->rate : '';
            $total = $obj->total;
        } else {
            $query = $db->getQuery(true)
                ->delete('#__gridbox_store_orders_shipping')
                ->where('cart_id = ' . $cart->id);
            $db->setQuery($query)
                ->execute();
        }
        $order = new stdClass();
        $order->cart_id = $id;
        $order->user_id = JFactory::getUser()->id;
        $order->subtotal = $cart->subtotal;
        $order->tax = $cart->tax;
        $order->tax_mode = gridboxHelper::$store->tax->mode;
        $order->total = $total;
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
        if (!empty($data['shipping'])) {
            $shipping->order_id = $order->id;
            $order->shipping = $shipping;
            if (!isset($shipping->id)) {
                $db->insertObject('#__gridbox_store_orders_shipping', $shipping);
            } else {
                $db->updateObject('#__gridbox_store_orders_shipping', $shipping, 'id');
            }
        }
        if (!empty($data['payment'])) {
            $obj = $this->getPayment($data['payment'] * 1);
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_orders_payment')
                ->where('cart_id = ' . $cart->id);
            $db->setQuery($query);
            $payment = $db->loadObject();
            $payment = $payment ? $payment : new stdClass();
            $payment->order_id = $order->id;
            $payment->title = $obj->title;
            $payment->type = $obj->type;
            $payment->payment_id = $obj->id;
            if (!isset($payment->id)) {
                $db->insertObject('#__gridbox_store_orders_payment', $payment);
            } else {
                $db->updateObject('#__gridbox_store_orders_payment', $payment, 'id');
            }
        }
        $order->products = array();
        foreach ($cart->products as $obj) {
            $product = new stdClass();
            $product->order_id = $order->id;
            $product->title = $obj->title;
            $product->image = !empty($obj->images) ? $obj->images[0] : $obj->intro_image;
            $product->product_id = $obj->product_id;
            $product->variation = $obj->variation;
            $product->quantity = $obj->quantity;
            $product->price = $obj->data->price;
            $product->sale_price = $obj->data->sale_price;
            $product->sku = $obj->data->sku;
            $product->tax = $obj->tax ? $obj->tax->amount : '';
            $product->tax_title = $obj->tax ? $obj->tax->title : '';
            $product->tax_rate = $obj->tax ? $obj->tax->rate : '';
            $product->net_price = $obj->net_price;
            $product->extra_options = json_encode($obj->extra_options);
            $product->product_type = isset($obj->data->product_type) ? $obj->data->product_type : '';
            $order->products[] = $product;
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
                $variation->type = $object->field_type;
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
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_order_customer_info')
                ->where('customer_id = ' . $obj->id)
                ->where('cart_id = ' . $cart->id);
            $db->setQuery($query);
            $customer = $db->loadObject();
            $customer = $customer ? $customer : new stdClass();
            $customer->order_id = $order->id;
            $customer->customer_id = $obj->id;
            $customer->title = $obj->title;
            $customer->type = $obj->type;
            $customer->value = isset($data[$obj->id]) ? $data[$obj->id] : '';
            $customer->options = $obj->options;
            $customer->invoice = $obj->invoice;
            $customer->order_list = $obj->order_list;
            if ($obj->type == 'country' && !empty($customer->value)) {
                $value = json_decode($customer->value);
                $query = $db->getQuery(true)
                    ->select('title')
                    ->from('#__gridbox_countries')
                    ->where('id = ' . $value->country);
                $db->setQuery($query);
                $value->country = $db->loadResult();
                if (!empty($value->region)) {
                    $query = $db->getQuery(true)
                        ->select('title')
                        ->from('#__gridbox_country_states')
                        ->where('id = ' . $value->region);
                    $db->setQuery($query);
                    $value->region = $db->loadResult();
                }
                $customer->value = json_encode($value);
            }
            if (!isset($customer->id)) {
                $db->insertObject('#__gridbox_store_order_customer_info', $customer);
            } else {
                $db->updateObject('#__gridbox_store_order_customer_info', $customer, 'id');
            }
        }
        $time = time() + 604800;
        gridboxHelper::setcookie('gridbox_store_order', $order->id, $time);

        return $order;
    }

    public function setCustomerInfo($id, $value, $cart_id)
    {
        $db = JFactory::getDbo();
        $user_id = JFactory::getUser()->id;
        if (!empty($user_id)) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_user_info')
                ->where('customer_id = ' . $id)
                ->where('user_id = ' . $user_id);
        } else {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_order_customer_info')
                ->where('customer_id = ' . $id)
                ->where('cart_id = ' . $cart_id);
        }
        $db->setQuery($query);
        $customer = $db->loadObject();
        if (!empty($user_id) && !$customer) {
            $customer = new stdClass();
            $customer->user_id = $user_id;
            $customer->customer_id = $id;
            $customer->value = $value;
            $db->insertObject('#__gridbox_store_user_info', $customer);
        } else if (!empty($user_id)) {
            $customer->value = $value;
            $db->updateObject('#__gridbox_store_user_info', $customer, 'id');
        } else if (!$customer) {
            $customer = new stdClass();
            $customer->order_id = 0;
            $customer->cart_id = $cart_id;
            $customer->customer_id = $id;
            $customer->title = $customer->type = '';
            $customer->value = $value;
            $customer->options = '';
            $db->insertObject('#__gridbox_store_order_customer_info', $customer);
        } else {
            $customer->value = $value;
            $db->updateObject('#__gridbox_store_order_customer_info', $customer, 'id');
        }
    }

    protected function getPayment($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_payment_methods')
            ->where('id = ' . $id);
        $db->setQuery($query);
        $obj = $db->loadObject();

        return $obj;
    }

    public function getStoreCartHTML($view = '', $id)
    {
        $cart = gridboxHelper::getStoreCart($id);
        $currency = gridboxHelper::$store->currency;
        $promoCodes = gridboxHelper::getPublishedPromoCode();
        if ($view == 'gridbox') {
            gridboxHelper::prepareCartForEditor($cart);
        }
        $cart->empty = count($cart->products) == 0;
        include JPATH_ROOT . '/components/com_gridbox/views/layout/patterns/store/cart.php';

        return $out;
    }

    public function getWishlistHTML($view = '', $id)
    {
        $wishlist = gridboxHelper::getStoreWishlist($id, true);
        $currency = gridboxHelper::$store->currency;
        if ($view == 'gridbox') {
            $this->prepareWishlistForEditor($wishlist);
        }
        $wishlist->empty = count($wishlist->products) == 0;
        include JPATH_ROOT . '/components/com_gridbox/views/layout/patterns/store/wishlist.php';

        return $out;
    }

    public function prepareWishlistForEditor($wishlist)
    {
        $currency = gridboxHelper::$store->currency;
        $product = new stdClass();
        $product->id = 0;
        $product->title = 'Product';
        $product->intro_image = 'components/com_gridbox/assets/images/thumb-square.png';
        $product->quantity = 1;
        $product->images = array();
        $product->data = new stdClass();
        $product->data->price = 36.99;
        $product->data->stock = 1;
        $product->data->sale_price = '';
        $product->prices = new stdClass();
        $product->prices->sale_price = '';
        $product->prices->regular = gridboxHelper::preparePrice(36.99, $currency->thousand, $currency->separator, $currency->decimals);
        $product->variations = array();
        $product->extra_options = new stdClass();
        $product->extra_options->items = new stdClass();
        $product->extra_options->count = 0;
        $product->link = JUri::root();
        $wishlist->products = array($product);
    }

    public function applyPromoCode($code, $id)
    {
        $db = JFactory::getDbo();
        if ($code != '') {
            $query = gridboxHelper::getPromoCodeQuery()
                ->select('p.id, p.unit, p.discount, p.applies_to, p.disable_sales')
                ->where('p.code = ' . $db->quote($code));
            $db->setQuery($query);
            $promo = $db->loadObject();
        } else {
            $promo = new stdClass();
            $promo->id = 0;
        }
        $result = $code != '' ? 'invalid' : 'valid';
        if ($code != '' && !empty($promo->id)) {
            $products = gridboxHelper::getStoreCartProducts($id);
            foreach ($products as $product) {
                $valid = gridboxHelper::checkPromoCode($promo, $product);
                if ($valid) {
                    $result = 'valid';
                    break;
                }
            }
        }
        if ($result == 'valid') {
            $cart = new stdClass();
            $cart->id = $id;
            $cart->promo_id = $promo->id;
            $db->updateObject('#__gridbox_store_cart', $cart, 'id');
        }

        return $result;
    }

    public function updateProductQuantity($id, $cart_id, $quantity)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_cart_products')
            ->where('id = ' . $id);
        $db->setQuery($query);
        $product = $db->loadObject();
        $data = $this->getProductData($product->product_id, $product->variation);
        $cart = gridboxHelper::getStoreCartObject($cart_id);
        $product = $this->getProduct($cart->id, $data->product_id, $product->variation, $product->extra_options);
        $product->quantity = 0;
        $this->setProductQuantity($product, $data, $quantity);
        gridboxHelper::updateStoreCart($cart);
    }

    public function addProductToCart($id, $cart_id, $quantity, $variation = '', $extra_options = '{}')
    {
        $data = $this->getProductData($id, $variation);
        if ($data->stock !== '0') {
            $cart = gridboxHelper::getStoreCartObject($cart_id);
            $product = $this->getProduct($cart->id, $data->product_id, $variation, $extra_options);
            $this->setProductQuantity($product, $data, $quantity);
        }
        gridboxHelper::updateStoreCart($cart);
    }

    public function setCartCountry($id, $country, $region)
    {
        $cart = gridboxHelper::getStoreCartObject($id);
        $cart->country = $country;
        $cart->region = $region;
        gridboxHelper::updateStoreCart($cart);
    }

    public function moveProductFromWishlist($id, $product_id, $cart_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('wp.*')
            ->from('#__gridbox_store_wishlist_products AS wp')
            ->where('wp.id = ' . $product_id);
        $db->setQuery($query);
        $product = $db->loadObject();
        $this->addProductToCart($product->product_id, $cart_id, 1, $product->variation, $product->extra_options);
        $query = $db->getQuery(true)
            ->delete('#__gridbox_store_wishlist_products')
            ->where('id = ' . $product_id);
        $db->setQuery($query)
            ->execute();
    }

    public function addProductToWishlist($id, $wishlist_id, $variation = '', $extra_options = '{}')
    {
        $data = $this->getProductData($id, $variation);
        $wishlist = gridboxHelper::updateStoreWishlist($wishlist_id);
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_wishlist_products')
            ->where('wishlist_id = ' . $wishlist->id)
            ->where('product_id = ' . $id)
            ->where('variation = ' . $db->quote($variation))
            ->where('extra_options = ' . $db->quote($extra_options));
        $db->setQuery($query);
        $product = $db->loadObject();
        if (!$product) {
            $product = new stdClass();
            $product->wishlist_id = $wishlist->id;
            $product->product_id = $id;
            $product->variation = $variation;
            $product->extra_options = $extra_options;
            $db->insertObject('#__gridbox_store_wishlist_products', $product);
            $product->id = $db->insertid();
        }

        return $data;
    }

    public function clearWishlist($wishlist_id, $product_id = 0)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__gridbox_store_wishlist_products')
            ->where('wishlist_id = ' . $wishlist_id);
        if (!empty($product_id)) {
            $query->where('id = ' . $product_id);
        }
        $db->setQuery($query)
            ->execute();
    }

    public function setProductQuantity($product, $data, $quantity)
    {
        $db = JFactory::getDbo();
        $product->quantity += $quantity;
        if (isset($data->product_type) && $data->product_type == 'digital' && $product->quantity > 1) {
            $product->quantity = 1;
        }
        if ($data->stock !== '' && $product->quantity > $data->stock) {
            $product->quantity = $data->stock * 1;
        }
        $db->updateObject('#__gridbox_store_cart_products', $product, 'id');
    }

    public function getProductData($id, $variation = '')
    {
        $db = JFactory::getDbo();
        $data = gridboxHelper::getProductData($id);
        $data->images = array();
        if (!empty($variation)) {
            $map = gridboxHelper::getProductVariationsMap($data->product_id);
            $images = new stdClass();
            foreach ($map as $value) {
                $images->{$value->option_key} = json_decode($value->images);
            }
            $vars = explode('+', $variation);
            foreach ($vars as $value) {
                if (!empty($images->{$value})) {
                    $data->images = $images->{$value};
                }
            }
            foreach ($data->variations->{$variation} as $key => $value) {
                $data->{$key} = $value;
            }
        }

        return $data;
    }

    public function getProduct($cart_id, $product_id, $variation, $extra_options = '{}')
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_cart_products')
            ->where('cart_id = ' . $cart_id)
            ->where('variation = ' . $db->quote($variation))
            ->where('extra_options = ' . $db->quote($extra_options))
            ->where('product_id = ' . $product_id);
        $db->setQuery($query);
        $product = $db->loadObject();
        if (!$product) {
            $product = new stdClass();
            $product->cart_id = $cart_id;
            $product->product_id = $product_id;
            $product->variation = $variation;
            $product->extra_options = $extra_options;
            $product->quantity = 0;
            $db->insertObject('#__gridbox_store_cart_products', $product);
            $product->id = $db->insertid();
        }

        return $product;
    }

    public function removeExtraOptionCart($cart_id, $product_id, $key, $field_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_cart_products')
            ->where('cart_id = ' . $cart_id)
            ->where('id = ' . $product_id);
        $db->setQuery($query);
        $product = $db->loadObject();
        $extra = json_decode($product->extra_options);
        if (isset($extra->{$key})) {
            unset($extra->{$key});
        }
        $product->extra_options = json_encode($extra);
        $db->updateObject('#__gridbox_store_cart_products', $product, 'id');
    }

    public function removeExtraOptionWishlist($wishlist_id, $product_id, $key, $field_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_wishlist_products')
            ->where('wishlist_id = ' . $wishlist_id)
            ->where('id = ' . $product_id);
        $db->setQuery($query);
        $product = $db->loadObject();
        $extra = json_decode($product->extra_options);
        if (isset($extra->{$key})) {
            unset($extra->{$key});
        }
        $product->extra_options = json_encode($extra);
        $db->updateObject('#__gridbox_store_wishlist_products', $product, 'id');
    }

    public function removeProductFromCart($product_id)
    {
        gridboxHelper::removeProductFromCart($product_id);
    }

    public function uploadDigitalFile($file, $id)
    {
        $obj = new stdClass();
        if (isset($file['error']) && $file['error'] == 0) {
            $ext = strtolower(JFile::getExt($file['name']));
            $dir = gridboxHelper::getDigitalFolder($id);
            if (!JFolder::exists($dir)) {
                JFolder::create($dir);
            }
            $name = str_replace('.' . $ext, '', $file['name']);
            $filename = gridboxHelper::replace($name);
            $filename = JFile::makeSafe($filename);
            $name = str_replace('-', '', $filename);
            $name = str_replace('.', '', $name);
            if ($name == '') {
                $filename = date("Y-m-d-H-i-s") . '.' . $ext;
            }
            $i = 2;
            $name = $filename;
            while (JFile::exists($dir . $name . '.' . $ext)) {
                $name = $filename . '-' . ($i++);
            }
            $filename = $name . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $dir . $filename);
            $obj = new stdClass();
            $obj->name = $file['name'];
            $obj->filename = $filename;
        } else {
            $obj->error = 'ba-alert';
            $obj->msg = JText::_('NOT_ALLOWED_FILE_SIZE');
        }

        return $obj;
    }

    public function downloadDigitalFile($token)
    {
        $db = JFactory::getDbo();
        $date = $db->quote(date("Y-m-d H:i:s"));
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('d.product_id, d.digital_file, op.id, o.status, o.user_id')
            ->from('#__gridbox_store_order_products AS op')
            ->where('op.product_token = ' . $db->quote($token))
            ->where('p.page_category <> ' . $db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= ' . $date)
            ->where('(p.end_publishing = ' . $nullDate . ' OR p.end_publishing >= ' . $date . ')')
            ->leftJoin('#__gridbox_store_product_data AS d ON d.product_id = op.product_id')
            ->leftJoin("#__gridbox_store_orders AS o ON op.order_id = o.id")
            ->leftJoin("#__gridbox_pages AS p ON d.product_id = p.id");
        $db->setQuery($query);
        $product = $db->loadObject();
        $user_id = JFactory::getUser()->id;
        if ($product->status == 'completed' && !empty($product->digital_file)/* && $user_id == $product->user_id*/) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_order_license')
                ->where('product_id = ' . $product->id);
            $db->setQuery($query);
            $license = $db->loadObject();
            $digital = json_decode($product->digital_file);
            $folder = gridboxHelper::getDigitalFolder($product->product_id);
            $file = $folder . $digital->file->filename;
            $expired = false;
            $limit = $license->limit == '' || $license->downloads < $license->limit;
            if (!empty($license->expires)) {
                $expired = $date > $license->expires;
            }
            if (JFile::exists($file) && !$expired && $limit) {
                $query = $db->getQuery(true)
                    ->update('#__gridbox_store_order_license')
                    ->set('downloads = ' . ($license->downloads * 1 + 1))
                    ->where('id = ' . $license->id);
                $db->setQuery($query)
                    ->execute();
                if (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $digital->file->name . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                readfile($file);
            }
        }
        return JError::raiseError(404, JText::_('DOWNLOAD_FILE_NOT_AVAILABLE'));
        exit;
    }

    public function getItem($id = null)
    {
    }
}
