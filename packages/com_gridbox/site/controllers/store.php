<?php

/**
 * @package   Gridbox
 * @author    Balbooa http://www.balbooa.com/
 * @copyright Copyright @ Balbooa
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class gridboxControllerStore extends JControllerForm
{
    public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, array('ignore_request' => false));
    }

    public function register()
    {
        $response = new stdClass();
        if (gridboxHelper::$store->checkout->guest) {
            $app = JFactory::getApplication();
            $array = $this->input->post->getArray(array());
            JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_users/models');
            $model = JModelLegacy::getInstance('Registration', 'UsersModel');
            $data = (array) $model->getData();
            foreach ($array as $key => $value) {
                $data[$key] = $value;
            }
            $data['email'] = JStringPunycode::emailToPunycode($data['email1']);
            $data['password'] = $data['password1'];
            $user = new JUser;
            $response->status = $user->bind($data);
            if ($response->status) {
                $response->status = $user->save();
            }
            if ($response->status) {
                $credentials = array();
                $credentials['username'] = $data['username'];
                $credentials['password'] = $data['password1'];
                $options = array();
                $options['remember'] = false;
                $app->login($credentials, $options);
            } else {
                $response->message = $user->getError();
            }
        } else {
            $response->status = false;
        }
        $str = json_encode($response);
        echo $str;
        exit();
    }

    public function login()
    {
        $app = JFactory::getApplication();
        $response = new stdClass();
        $response->message = JText::_('LOGIN_ERROR');
        $options = array();
        $options['remember'] = $this->input->get('remember', 0, 'int') == 1;
        $credentials = array();
        $credentials['username'] = $this->input->get('username', '', 'username');
        $credentials['password'] = $this->input->get('password', '', 'raw');
        $response->status = $app->login($credentials, $options);
        if ($response->status && $options['remember']) {
            $app->setUserState('rememberLogin', true);
        }
        $str = json_encode($response);
        echo $str;
        exit();
    }

    public function saveUserProfile()
    {
        $id = JFactory::getUser()->id;
        if (!empty($id)) {
            $data = $this->input->post->getArray(array());
            $data['id'] = $id;
            jimport('joomla.application.component.model');
            JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_users/models');
            $model = JModelLegacy::getInstance('Profile', 'UsersModel');
            $model->save($data);
        }
        exit;
    }

    public function logout()
    {
        JFactory::getApplication()->logout();
        header('Location: ' . JUri::root());
        exit();
    }

    public function getTaxCountries()
    {
        $countries = gridboxHelper::getTaxCountries();
        $str = json_encode($countries);
        print_r($str);
        exit;
    }

    public function getProductsList()
    {
        $id = $this->input->get('id', 0, 'int');
        $model = $this->getModel();
        $data = $model->getProductsList($id);
        $str = json_encode($data);
        echo $str;
        exit;
    }

    public function getAppStoreFields()
    {
        $id = $this->input->get('id', 0, 'int');
        $type = $this->input->get('type', '', 'string');
        $model = $this->getModel();
        $data = $model->getAppStoreFields($id, $type);
        $str = json_encode($data);
        echo $str;
        exit;
    }

    public function getLiveSearchData()
    {
        $search = $this->input->get('search', '', 'string');
        $model = $this->getModel();
        $out = $model->getLiveSearchData($search);
        echo $out;
        exit;
    }

    public function setCartShipping()
    {
        $input = JFactory::getApplication()->input;
        $id = $this->input->get('id', 0, 'int');
        $cart = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $obj = $model->setCartShipping($id, $cart);
        exit;
    }

    public function setCartPayment()
    {
        $input = JFactory::getApplication()->input;
        $id = $this->input->get('id', 0, 'int');
        $cart = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $obj = $model->setCartPayment($id, $cart);
        exit;
    }

    public function setCustomerInfo()
    {
        $input = JFactory::getApplication()->input;
        $id = $this->input->get('id', 0, 'int');
        $value = $this->input->get('value', '', 'string');
        $cart = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $obj = $model->setCustomerInfo($id, $value, $cart);
        exit;
    }

    public function getWishlistAuthenticationMessage()
    {
        $obj = new stdClass();
        $obj->status = false;
        $obj->message = JText::_('PLEASE_SIGN_IN_TO_MOVE_WISHLIST');

        return $obj;
    }

    public function addPostToWishlist()
    {
        $id = $this->input->get('id', 0, 'int');
        $wishlist = gridboxHelper::getWishlistId();
        if (gridboxHelper::$store->wishlist->login && JFactory::getUser()->id == 0) {
            $obj = $this->getWishlistAuthenticationMessage();
        } else {
            $model = $this->getModel();
            $obj = $model->addPostToWishlist($id, $wishlist);
        }
        $str = json_encode($obj);
        echo $str;
        exit;
    }

    public function addPostToCart()
    {
        $input = JFactory::getApplication()->input;
        $id = $this->input->get('id', 0, 'int');
        $cart = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $obj = $model->addPostToCart($id, $cart);
        $str = json_encode($obj);
        echo $str;
        exit;
    }

    public function deleteStoreBadge()
    {
        gridboxHelper::checkUserEditLevel();
        $id = $this->input->get('id', 0, 'int');
        $model = $this->getModel();
        $model->deleteStoreBadge($id);
        echo "{}";
        exit;
    }

    public function updateStoreBadge()
    {
        gridboxHelper::checkUserEditLevel();
        $badge = new stdClass();
        $badge->id = $this->input->get('id', 0, 'int');
        $badge->title = $this->input->get('title', '', 'string');
        $badge->color = $this->input->get('color', '#1da6f4', 'string');
        $model = $this->getModel();
        $model->updateStoreBadge($badge);
        echo "{}";
        exit;
    }

    public function getStoreBadge()
    {
        $model = $this->getModel();
        $badges = $model->getStoreBadge();
        $str = json_encode($badges);
        echo $str;
        exit;
    }

    public function addProductBadge()
    {
        $model = $this->getModel();
        $obj = $model->addProductBadge();
        $str = json_encode($obj);
        echo $str;
        exit;
    }

    public function pagseguroCallback()
    {
        $transactionCode = $this->input->get("transactionCode", '', 'string');
        $id = $this->input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->pagseguroCallback($id, $transactionCode);
        echo gridboxHelper::getStoreSystemUrl('thank-you-page');
        exit;
    }

    public function robokassaCallback()
    {
        $inv_id = $this->input->get("InvId", 0, 'int');
        $model = $this->getModel();
        $model->robokassaCallback($inv_id);
        exit;
    }

    public function liqpayCallback()
    {
        $data = $this->input->get('data', '', 'string');
        $signature = $this->input->get('signature', '', 'string');
        $model = $this->getModel();
        $model->liqpayCallback($data, $signature);
        exit;
    }

    public function dotpayCallback()
    {
        $data = $this->input->post->getArray(array());
        $model = $this->getModel();
        $model->dotpayCallback($data);
        echo "OK";
        exit;
    }

    public function payfastCallback()
    {
        header('HTTP/1.0 200 OK');
        flush();
        $data = $this->input->post->getArray(array());
        $model = $this->getModel();
        $model->payfastCallback($data);
        exit;
    }

    public function klarnaCallback()
    {
        $input = JFactory::getApplication()->input;
        $order_id = $input->get('order_id', '', 'string');
        $model = $this->getModel();
        $model->klarnaCallback($order_id);
        exit;
    }

    public function submitLiqpay()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitLiqpay($id);
    }

    public function submitDotpay()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitDotpay($id);
    }

    public function submitPayfast()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitPayfast($id);
    }

    public function mollieCallback()
    {
        $id = $this->input->get('id', '', 'string');
        $model = $this->getModel();
        $model->mollieCallback($id);
        exit;
    }

    public function submitMollie()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitMollie($id);
    }

    public function submitRobokassa()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitRobokassa($id);
    }

    public function submitPayupl()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitPayupl($id);
    }

    public function submitPaystack()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitPaystack($id);
    }

    public function submitPagseguro()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitPagseguro($id);
    }

    public function submitKlarna()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitKlarna($id);
    }

    public function submitYandexKassa()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitYandexKassa($id);
    }

    public function submit2checkout()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submit2checkout($id);
    }

    public function stripeCharges()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $payment_id = $input->get('id', 0, 'int');
        $model = $this->getModel();
        $model->stripeCharges($id, $payment_id);
    }

    public function updateOrder()
    {
        $id = $this->input->cookie->get('gridbox_store_order', 0, 'int');
        $params = $this->input->get('params', '{}', 'string');
        $model = $this->getModel();
        $model->updateOrder($id, $params);
        exit;
    }

    public function payAuthorize()
    {
        $id = $this->input->cookie->get('gridbox_store_order', 0, 'int');
        $cardNumber = $this->input->get('cardNumber', '', 'string');
        $expirationDate = $this->input->get('expirationDate', '', 'string');
        $cardCode = $this->input->get('cardCode', '', 'string');
        $cardNumber = str_replace(' ', '', $cardNumber);
        $expArray = explode('/', $expirationDate);
        $expirationDate = $expArray[1] . '-' . $expArray[0];
        $model = $this->getModel();
        $model->payAuthorize($id, $cardNumber, $expirationDate, $cardCode);
    }

    public function createOrder()
    {
        if (gridboxHelper::$store->checkout->login && !gridboxHelper::$store->checkout->guest && empty(JFactory::getUser()->id)) {
            $obj = new stdClass();
            $obj->denied = true;
            $str = json_encode($obj);
            print_r($str);
            exit;
        }
        $input = JFactory::getApplication()->input;
        $post = $input->post->getArray(array());
        $id = $input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $order = $model->createOrder($post, $id);
        $order->url = gridboxHelper::getStoreSystemUrl('thank-you-page');
        $str = json_encode($order);
        print_r($str);
        exit;
    }

    public function getPaymentOptions()
    {
        $input = JFactory::getApplication()->input;
        $payment = $input->get('payment', 0, 'int');
        $model = $this->getModel();
        $obj = $model->getPaymentOptions($payment);
        $str = json_encode($obj);
        print_r($str);
        exit;
    }

    public function removeProductFromCart()
    {
        $input = JFactory::getApplication()->input;
        $model = $this->getModel();
        $product_id = $input->get('id', 0, 'int');
        $model->removeProductFromCart($product_id);
        exit;
    }

    public function applyPromoCode()
    {
        $input = JFactory::getApplication()->input;
        $model = $this->getModel();
        $promo = $input->get('promo', '', 'string');
        $id = $input->cookie->get('gridbox_store_cart', 0, 'int');
        $out = $model->applyPromoCode($promo, $id);
        echo $out;
        exit;
    }

    public function getStoreCart()
    {
        $input = JFactory::getApplication()->input;
        $view = $input->get('view', '', 'string');
        $id = $input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $out = $model->getStoreCartHTML($view, $id);
        echo $out;
        exit;
    }

    public function clearWishlist()
    {
        $id = gridboxHelper::getWishlistId();
        $model = $this->getModel();
        $model->clearWishlist($id);
        $this->getWishlist();
    }

    public function removeProductFromWishlist()
    {
        $id = gridboxHelper::getWishlistId();
        $product_id = $this->input->get('id', 0, 'int');
        $model = $this->getModel();
        $model->clearWishlist($id, $product_id);
        $this->getWishlist();
    }

    public function removeExtraOptionCart()
    {
        $id = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $product_id = $this->input->get('id', 0, 'int');
        $key = $this->input->get('key', '', 'string');
        $field_id = $this->input->get('field_id', 0, 'int');
        $model = $this->getModel();
        $model->removeExtraOptionCart($id, $product_id, $key, $field_id);
        exit;
    }

    public function removeExtraOptionWishlist()
    {
        $id = gridboxHelper::getWishlistId();
        $product_id = $this->input->get('id', 0, 'int');
        $key = $this->input->get('key', '', 'string');
        $field_id = $this->input->get('field_id', 0, 'int');
        $model = $this->getModel();
        $model->removeExtraOptionWishlist($id, $product_id, $key, $field_id);
        exit;
    }

    public function moveProductFromWishlist()
    {
        $id = gridboxHelper::getWishlistId();
        $product_id = $this->input->get('id', 0, 'int');
        $cart_id = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $model->moveProductFromWishlist($id, $product_id, $cart_id);
        $this->getWishlist();
    }

    public function moveProductsFromWishlist()
    {
        $id = gridboxHelper::getWishlistId();
        $cart_id = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $products = gridboxHelper::getStoreWishlistProducts($id);
        $model = $this->getModel();
        foreach ($products as $key => $product) {
            $model->moveProductFromWishlist($id, $product->id, $cart_id);
        }
        $this->getWishlist();
    }

    public function getWishlist()
    {
        $view = $this->input->get('view', '', 'string');
        $id = gridboxHelper::getWishlistId();
        $model = $this->getModel();
        $out = $model->getWishlistHTML($view, $id);
        echo $out;
        exit;
    }

    public function addProductToWishlist()
    {
        $id = $this->input->get('id', 0, 'int');
        $variation = $this->input->get('variation', '', 'string');
        $wishlist = gridboxHelper::getWishlistId();
        $extra_options = $this->input->get('extra_options', '', 'string');
        if (gridboxHelper::$store->wishlist->login && JFactory::getUser()->id == 0) {
            $obj = $this->getWishlistAuthenticationMessage();
        } else {
            $model = $this->getModel();
            $obj = $model->addProductToWishlist($id, $wishlist, $variation, $extra_options);
        }
        $str = json_encode($obj);
        echo $str;
        exit;
    }

    public function addProductToCart()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $variation = $input->get('variation', '', 'string');
        $quantity = $input->get('quantity', 0, 'int');
        $cart = $input->cookie->get('gridbox_store_cart', 0, 'int');
        $extra_options = $input->get('extra_options', '', 'string');
        $model = $this->getModel();
        $model->addProductToCart($id, $cart, $quantity, $variation, $extra_options);
        exit;
    }

    public function setCartCountry()
    {
        $id = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $country = $this->input->get('country', '', 'string');
        $region = $this->input->get('region', '', 'string');
        $model = $this->getModel();
        $model->setCartCountry($id, $country, $region);
        exit;
    }

    public function updateProductQuantity()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $quantity = $input->get('quantity', 0, 'int');
        $cart = $input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $model->updateProductQuantity($id, $cart, $quantity);
        exit;
    }

    public function uploadDigitalFile()
    {
        $file = isset($_FILES['file']) ? $_FILES['file'] : array();
        $id = $this->input->post->get('id', 0, 'int');
        $model = $this->getModel();
        $obj = $model->uploadDigitalFile($file, $id);
        $str = json_encode($obj);
        echo $str;
        exit();
    }

    public function downloadDigitalFile()
    {
        $token = $this->input->get('file', '', 'string');
        $model = $this->getModel();
        $model->downloadDigitalFile($token);
    }
}
