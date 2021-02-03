<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;
jimport('joomla.filter.output');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
use Joomla\Registry\Registry;
include 'mb_compat.php';

abstract class gridboxHelper
{
    public static $fonts;
    public static $up;
    public static $cssRulesFlag;
    public static $breakpoints;
    public static $breakpoint;
    public static $menuBreakpoint;
    public static $website;
    public static $dateFormat;
    public static $customFonts;
    public static $colorVariables;
    public static $presets;
    public static $editItem;
    public static $parentFonts;
    public static $commentUser;
    public static $commentsModerators;
    public static $systemApps;
    public static $reviewsModerators;
    public static $blogPostsInfo;
    public static $blogPostsFields;
    public static $review;
    public static $cacheData;
    public static $store;
    public static $taxRates;
    public static $menuItems;
    public static $storeHelper;

    public static function getWishlistId()
    {
        $input = JFactory::getApplication()->input;
        $user_id = JFactory::getUser()->id;
        if (gridboxHelper::$store->wishlist->login && $user_id != 0) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_store_wishlist')
                ->where('user_id = '.$user_id);
            $db->setQuery($query);
            $id = $db->loadResult();
            $id = empty($id) ? 0 : $id;
        } else {
            $id = $input->cookie->get('gridbox_store_wishlist', 0, 'int');
        }

        return $id;
    }

    public static function getStoreWishlistObject($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_wishlist')
            ->where('id = '.$id);
        $db->setQuery($query);
        $wishlist = $db->loadObject();
        if (!$wishlist) {
            $wishlist = new stdClass();
            $wishlist->id = 0;
        }

        return $wishlist;
    }

    public static function updateStoreWishlist($id)
    {
        $db = JFactory::getDbo();
        $user_id = JFactory::getUser()->id;
        $wishlist = self::getStoreWishlistObject($id);
        if (gridboxHelper::$store->wishlist->login && $user_id != 0) {
            $wishlist->user_id = $user_id;
        }
        if (empty($wishlist->id)) {
            $db->insertObject('#__gridbox_store_wishlist', $wishlist);
            $wishlist->id = $db->insertid();
        }
        if (!gridboxHelper::$store->wishlist->login) {
            $time = time() + 604800 * 4 * 12;
            self::setcookie('gridbox_store_wishlist', $wishlist->id, $time);
        }

        return $wishlist;
    }

    public static function updateStoreCart($cart)
    {
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $cart->user_id = $user->id;
        $db->updateObject('#__gridbox_store_cart', $cart, 'id');
        $time = time() + 604800;
        self::setcookie('gridbox_store_cart', $cart->id, $time);
    }

    public static function getPromoCodeQuery()
    {
        $db = JFactory::getDBO();
        $date = JDate::getInstance()->format('Y-m-d H:i:s');
        $date = $db->quote($date);
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->from('#__gridbox_store_promo_codes AS p')
            ->where('p.published = 1')
            ->where('(p.publish_down = '.$nullDate.' OR p.publish_down >= '.$date.')')
            ->where('(p.publish_up = '.$nullDate.' OR p.publish_up <= '.$date.')')
            ->where('(p.limit = 0 OR p.used < pc.limit)')
            ->leftJoin('#__gridbox_store_promo_codes AS pc ON pc.id = p.id');
        
        return $query;
    }

    public static function checkPromoCode($promo, $product)
    {
        $valid = false;
        if ($promo->applies_to != '*') {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_promo_codes_map')
                ->where('code_id = '.$promo->id)
                ->where('type = '.$db->quote($promo->applies_to));
            $db->setQuery($query);
            $promo->map = $db->loadObjectList();
        }
        if ($promo->applies_to == '*' && $promo->disable_sales == 0) {
            $valid = true;
        } else if ($promo->applies_to == '*' && $promo->disable_sales == 1) {
            $data = self::getProductData($product->product_id);
            $prices = self::prepareProductPrices($data->product_id, $data->price, $data->sale_price);
            $valid = $prices->sale_price !== '' ? false : true;
        } else if ($promo->applies_to == 'product') {
            foreach ($promo->map as $value) {
                if ($product->product_id == $value->item_id && $product->variation == $value->variation && $promo->disable_sales == 0) {
                    $valid = true;
                } else if ($product->product_id == $value->item_id && $product->variation == $value->variation
                    && $promo->disable_sales == 1) {
                    $data = self::getProductData($product->product_id);
                    $prices = self::prepareProductPrices($data->product_id, $data->price, $data->sale_price);
                    $valid = $prices->sale_price !== '' ? false : true;
                }
                if ($valid) {
                    break;
                }
            }
        } else {
            $categories = self::getCategoryId($product->product_id);
            foreach ($promo->map as $value) {
                if (in_array($value->item_id, $categories) && $promo->disable_sales == 0) {
                    $valid = true;
                } else if (in_array($value->item_id, $categories) && $promo->disable_sales == 1) {
                    $data = self::getProductData($product->product_id);
                    $prices = self::prepareProductPrices($data->product_id, $data->price, $data->sale_price);
                    $valid = $prices->sale_price !== '' ? false : true;
                }
                if ($valid) {
                    break;
                }
            }
        }
        
        return $valid;
    }

    public static function getStoreCartObject($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_cart')
            ->where('id = '.$id);
        $db->setQuery($query);
        $cart = $db->loadObject();
        $user_id = JFactory::getUser()->id;
        if (!$cart) {
            $cart = new stdClass();
            $cart->id = 0;
            $cart->promo_id = 0;
            $cart->country = $cart->region = '';
            $db->insertObject('#__gridbox_store_cart', $cart);
            $cart->id = $db->insertid();
            self::updateStoreCart($cart);
        }
        if (!empty($user_id)) {
            $query = $db->getQuery(true)
                ->select('ui.value, ui.id')
                ->from('#__gridbox_store_customer_info AS ci')
                ->where('ci.type = '.$db->quote('country'))
                ->where('ui.user_id = '.$user_id)
                ->leftJoin('#__gridbox_store_user_info AS ui ON ui.customer_id = ci.id');
            $db->setQuery($query);
            $info = $db->loadObject();
            if (!empty($info->value)) {
                $object = json_decode($info->value);
                if (!empty($object->country) && !is_numeric($object->country)) {
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__gridbox_countries')
                        ->where('title = '.$db->quote($object->country));
                    $db->setQuery($query);
                    $country = $db->loadObject();
                    $object->country = $country ? $country->id : 0;
                    if (!empty($object->region)) {
                        $query = $db->getQuery(true)
                            ->select('*')
                            ->from('#__gridbox_country_states')
                            ->where('country_id = '.$object->country)
                            ->where('title = '.$db->quote($object->region));
                        $db->setQuery($query);
                        $region = $db->loadObject();
                        $object->region = $region ? $region->id : 0;
                    }
                    $info->value = json_encode($object);
                    $db->updateObject('#__gridbox_store_user_info', $info, 'id');
                }
                $cart->country = $object->country;
                $cart->region = $object->region;
            }
        }
        if (!empty($cart->country) && !is_numeric($cart->country)) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_countries')
                ->where('title = '.$db->quote($cart->country));
            $db->setQuery($query);
            $country = $db->loadObject();
            $cart->country = $country ? $country->id : 0;
            if (!empty($cart->region)) {
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_country_states')
                    ->where('country_id = '.$cart->country)
                    ->where('title = '.$db->quote($cart->region));
                $db->setQuery($query);
                $region = $db->loadObject();
                $cart->region = $region ? $region->id : 0;
            }
            $db->updateObject('#__gridbox_store_cart', $cart, 'id');
        }

        return $cart;
    }

    public static function getStoreWishlistProducts($id)
    {
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $date = $db->quote(date("Y-m-d H:i:s"));
        $nullDate = $db->quote($db->getNullDate());
        $tag = $db->quote(JFactory::getLanguage()->getTag());
        $query = $db->getQuery(true)
            ->select('wp.*, p.title, p.intro_image, p.app_id, p.page_category')
            ->from('#__gridbox_store_wishlist_products AS wp')
            ->where('wp.wishlist_id = '.$id)
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$date)
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$date.')')
            ->where('p.language in ('.$tag.','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->where('c.published = 1')
            ->where('c.language in ('.$tag.','.$db->quote('*').')')
            ->where('c.access in ('.$groups.')')
            ->leftJoin('#__gridbox_pages as p ON wp.product_id = p.id')
            ->leftJoin('`#__gridbox_categories` AS c ON p.page_category = c.id');
        $db->setQuery($query);
        $products = $db->loadObjectList();
        foreach ($products as $product) {
            $link = self::getGridboxPageLinks($product->product_id, 'product', $product->app_id, $product->page_category);
            $product->link = JRoute::_($link);
            $product->extra_options = !empty($product->extra_options) ? json_decode($product->extra_options) : new stdClass();
        }

        return $products;
    }

    public static function getStoreCartProducts($id)
    {
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $date = $db->quote(date("Y-m-d H:i:s"));
        $nullDate = $db->quote($db->getNullDate());
        $tag = $db->quote(JFactory::getLanguage()->getTag());
        $query = $db->getQuery(true)
            ->select('cp.*, p.title, p.intro_image, p.app_id, p.page_category')
            ->from('#__gridbox_store_cart_products AS cp')
            ->where('cp.cart_id = '.$id)
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$date)
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$date.')')
            ->where('p.language in ('.$tag.','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->where('c.published = 1')
            ->where('c.language in ('.$tag.','.$db->quote('*').')')
            ->where('c.access in ('.$groups.')')
            ->leftJoin('#__gridbox_pages as p ON cp.product_id = p.id')
            ->leftJoin('`#__gridbox_categories` AS c ON p.page_category = c.id');
        $db->setQuery($query);
        $products = $db->loadObjectList();
        foreach ($products as $product) {
            $link = self::getGridboxPageLinks($product->product_id, 'product', $product->app_id, $product->page_category);
            $product->link = JRoute::_($link);
            $product->extra_options = !empty($product->extra_options) ? json_decode($product->extra_options) : new stdClass();
        }

        return $products;
    }

    public static function setOrder()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        if (!empty($id)) {
            self::setBreakpoints();
            self::$storeHelper->setOrder($id);
        }
    }

    public static function authorizePayupl($params)
    {
        $url = 'https://secure'.($params->environment == 'sandbox' ? '.snd' : '').'.payu.com';
        $post = 'grant_type=client_credentials&client_id='.$params->client_id.'&client_secret='.$params->client_secret;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url."/pl/standard/user/oauth/authorize");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/x-www-form-urlencoded"
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($response);

        return $json;
    }

    public static function getStorePayment($type)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_payment_methods')
            ->where('type = '.$db->quote($type));
        $db->setQuery($query);
        $payment = $db->loadObject();
        $payment->params = json_decode($payment->settings);

        return $payment;
    }

    public static function getGridboxMenuItemidByPage($id)
    {
        $itemId = '';
        foreach (self::$menuItems as $item) {
            if (isset($item->query) && isset($item->query['id']) && isset($item->query['view']) &&
                $item->query['view'] == 'page' && $item->query['id'] == $id) {
                $itemId = '&Itemid='.$item->id;
                break;
            }
        }

        return $itemId;
    }

    public static function getGridboxMenuItemidByCategory($app_id, $id)
    {
        $itemId = '';
        foreach (self::$menuItems as $value) {
            if (isset($value->query) && isset($value->query['id']) && isset($value->query['app'])
                && $value->query['view'] == 'blog' && $value->query['app'] == $app_id && $value->query['id'] == $id) {
                $itemId = '&Itemid='.$value->id;
                break;
            }
        }

        return $itemId;
    }

    public static function getGridboxMenuItemidByApp($app_id)
    {
        $itemId = '';
        foreach (self::$menuItems as $value) {
            if (isset($value->query) && isset($value->query['id']) && isset($value->query['app'])
                && $value->query['view'] == 'blog' && $value->query['app'] == $app_id && $value->query['id'] == 0) {
                $itemId = '&Itemid='.$value->id;
                break;
            }
        }

        return $itemId;
    }

    public static function getGridboxMenuItemidByTag($id, $app_id)
    {
        $itemId = '';
        foreach (self::$menuItems as $item) {
            if (isset($item->query) && isset($item->query['tag']) && isset($item->query['app'])
                && $item->query['view'] == 'blog' && $item->query['app'] == $app_id && $item->query['tag'] == $id) {
                $itemId = '&Itemid='.$item->id;
                break;
            }
        }

        return $itemId;
    }

    public static function getGridboxMenuItems()
    {
        if (!self::$menuItems) {
            $menus = JFactory::getApplication()->getMenu('site');
            $component = JComponentHelper::getComponent('com_gridbox');
            $attributes = array('component_id');
            $values = array($component->id);
            self::$menuItems = $menus->getItems($attributes, $values);
        }
    }

    public static function getGridboxPageLinks($id, $type = 'single', $app_id = 0, $category = 0)
    {
        self::getGridboxMenuItems();
        $itemId = self::getGridboxMenuItemidByPage($id);
        if ($type == 'single') {
            $link = 'index.php?option=com_gridbox&view=page&id='.$id;
        } else {
            $link = 'index.php?option=com_gridbox&view=page&blog='.$app_id.'&category='.$category.'&id='.$id;
        }
        if (empty($itemId) && $type != 'single') {
            $itemId = self::getGridboxMenuItemidByCategory($app_id, $category);
            if (empty($itemId)) {
                $catsId = self::getCategoryIdPath($category);
                foreach ($catsId as $catId) {
                    $itemId = self::getGridboxMenuItemidByCategory($app_id, $catId);
                    if (!empty($itemId)) {
                        break;
                    }
                }
            }
            if (empty($itemId)) {
                $itemId = self::getGridboxMenuItemidByApp($app_id);
            }
        }
        if (empty($itemId)) {
            $itemId = '&Itemid=0';
        }
        $link .= $itemId;

        return $link;
    }

    public static function getGridboxCategoryLinks($id, $app_id)
    {
        $link = 'index.php?option=com_gridbox&view=blog&app='.$app_id.'&id='.$id;
        $itemId = '';
        self::getGridboxMenuItems();
        $itemId = self::getGridboxMenuItemidByCategory($app_id, $id);
        if (empty($itemId) && !empty($id)) {
            $catsId = self::getCategoryIdPath($id);
            foreach ($catsId as $catId) {
                $itemId = self::getGridboxMenuItemidByCategory($app_id, $catId);
                if (!empty($itemId)) {
                    break;
                }
            }
        }
        if (empty($itemId)) {
            $itemId = self::getGridboxMenuItemidByApp($app_id);
        }
        if (empty($itemId)) {
            $itemId = '&Itemid=0';
        }
        $link .= $itemId;

        return $link;
    }

    public static function getGridboxTagLinks($id, $app_id)
    {
        $link = 'index.php?option=com_gridbox&view=blog&app='.$app_id.'&id=0&tag='.$id;
        self::getGridboxMenuItems();
        $itemId = self::getGridboxMenuItemidByTag($id, $app_id);
        if (empty($itemId)) {
            $itemId = self::getGridboxMenuItemidByApp($app_id);
        }
        if (empty($itemId)) {
            $itemId = '&Itemid=0';
        }
        $link .= $itemId;

        return $link;
    }

    public static function getGridboxAuthorLinks($id, $app_id)
    {
        self::getGridboxMenuItems();
        $itemId = self::getGridboxMenuItemidByApp($app_id);
        if (empty($itemId)) {
            $itemId = '&Itemid=0';
        }
        $link = 'index.php?option=com_gridbox&view=blog&app='.$app_id.'&id=0&author='.$id.$itemId;

        return $link;
    }

    public static function getStoreWishlist($id)
    {
        $db = JFactory::getDbo();
        $wishlist = self::getStoreWishlistObject($id);
        $wishlist->products = self::getStoreWishlistProducts($id);
        $wishlist->quantity = 0;
        foreach ($wishlist->products as $product) {
            $data = self::getProductData($product->product_id);
            if (!empty($product->variation) && !isset($data->variations->{$product->variation})) {
                self::removeProductFromWishlist($product->id);
                continue;
            }
            $extra_options = new stdClass();
            $extra_options->count = 0;
            $extra_options->price = 0;
            $extra_options->items = new stdClass();
            $removeFlag = false;
            foreach ($product->extra_options as $key => $value) {
                if (!isset($data->extra_options->{$value->field_id}) || !isset($data->extra_options->{$value->field_id}->items->{$key})) {
                    $removeFlag = true;
                    break;
                } else {
                    $obj = $data->extra_options->{$value->field_id};
                    if (!isset($extra_options->items->{$value->field_id})) {
                        $object = new stdClass();
                        $object->title = $obj->title;
                        $object->required = $obj->required == '1';
                        $object->values = new stdClass();
                        $extra_options->items->{$value->field_id} = $object;
                    } else {
                        $object = $extra_options->items->{$value->field_id};
                    }
                    $extra_options->count++;
                    $option = new stdClass();
                    $option->value = $obj->items->{$key}->title;
                    $option->price = $obj->items->{$key}->price;
                    $object->values->{$key} = $option;
                    if (!empty($option->price)) {
                        $extra_options->price += $option->price * 1;
                    }
                }
            }
            if ($removeFlag) {
                self::removeProductFromWishlist($product->id);
                continue;
            }
            $product->extra_options = $extra_options;
            $product->data = !empty($product->variation) ? $data->variations->{$product->variation} : $data;
            $product->data->price += $extra_options->price;
            if ($product->data->sale_price !== '') {
                $product->data->sale_price += $extra_options->price;
            }
            $wishlist->quantity++;
            $product->prices = self::prepareProductPrices($product->product_id, $product->data->price, $product->data->sale_price);
            $price = $product->prices->sale_price !== '' ? $product->prices->sale_price : $product->prices->price;
            $product->variations = array();
            $product->images = array();
            if (!empty($product->variation)) {
                $vars = explode('+', $product->variation);
                $variationsURL = array();
                $variationsMap = self::getProductVariationsMap($product->product_id);
                $images = new stdClass();
                foreach ($variationsMap as $value) {
                    $images->{$value->option_key} = json_decode($value->images);
                }
                foreach ($vars as $value) {
                    $query = $db->getQuery(true)
                        ->select('fd.value, fd.color, fd.image, f.title, f.field_type')
                        ->from('#__gridbox_store_products_fields_data AS fd')
                        ->where('fd.option_key = '.$db->quote($value))
                        ->leftJoin('#__gridbox_store_products_fields AS f ON f.id = fd.field_id');
                    $db->setQuery($query);
                    $variationObj = $db->loadObject();
                    $variationsURL[] = $variationObj->title.'='.$variationObj->value;
                    $product->variations[] = $variationObj;
                    if (!empty($images->{$value})) {
                        $product->images = $images->{$value};
                    }
                }
                $product->variationURL = implode('&', $variationsURL);
            }
        }

        return $wishlist;
    }

    public static function checkProductTaxMap($id, $categories)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('page_category')
            ->from('#__gridbox_pages')
            ->where('id = '.$id);
        $db->setQuery($query);
        $category = $db->loadResult();
        $flag = self::checkProductCategory($category, $categories);

        return $flag;
    }

    public static function getTaxRegion($regions, $region)
    {
        $result = null;
        foreach ($regions as $value) {
            if ($value->state_id == $region) {
                $result = $value;
                break;
            }
        }

        return $result;
    }

    public static function calculateProductTax($id, $price, $cart, $country = true, $region = true, $category = true)
    {
        $obj = null;
        $array = $category ? self::$taxRates->categories : self::$taxRates->empty;
        foreach ($array as $tax) {
            $count = $country ? $tax->country_id == $cart->country : true;
            $cat = $category ? self::checkProductTaxMap($id, $tax->categories) : true;
            $reg = $region ? self::getTaxRegion($tax->regions, $cart->region) : true;
            if ($count && $cat && $reg) {
                $rate = !empty($reg->rate) ? $reg->rate : $tax->rate;
                $obj = new stdClass();
                $obj->key = $tax->key;
                $obj->title = $tax->title;
                $obj->rate = $rate;
                $obj->amount = self::$store->tax->mode == 'excl' ? $price * ($rate / 100) : $price - $price / ($rate / 100 + 1);
                break;
            }
        }
        if (!$obj && $country && $region && $category) {
            $obj = self::calculateProductTax($id, $price, $cart, true, false, true);
        } else if (!$obj && $country && !$region && $category) {
            $obj = self::calculateProductTax($id, $price, $cart, true, true, false);
        } else if (!$obj && $country && $region && !$category) {
            $obj = self::calculateProductTax($id, $price, $cart, true, false, false);
        } else if (!$obj && $country && !$region && !$category) {
            $obj = self::calculateProductTax($id, $price, $cart, false, false, true);
        } else if (!$obj && !$country && !$region && $category) {
            $obj = self::calculateProductTax($id, $price, $cart, false, false, false);
        }

        return $obj;
    }

    public static function getStoreCart($id)
    {
        $db = JFactory::getDbo();
        $cart = self::getStoreCartObject($id);
        $cart->products = self::getStoreCartProducts($id);
        $cart->subtotal = 0;
        $cart->tax = 0;
        $cart->total = 0;
        $cart->discount = 0;
        $cart->taxes = new stdClass();
        $cart->taxes->count = 0;
        if (!empty($cart->promo_id)) {
            $db = JFactory::getDbo();
            $query = self::getPromoCodeQuery()
                ->select('p.id, p.title, p.unit, p.discount, p.applies_to, p.disable_sales, pc.code')
                ->where('p.id = '.$db->quote($cart->promo_id));
            $db->setQuery($query);
            $cart->promo = $db->loadObject();
        } else {
            $cart->promo = NULL;
        }
        $cart->validPromo = false;
        $cart->quantity = 0;
        $cart->net_amount = 0;
        $promoProducts = 0;
        foreach ($cart->products as $product) {
            $product->promo = $cart->promo && self::checkPromoCode($cart->promo, $product);
            if ($product->promo) {
                $promoProducts++;
            }
        }
        foreach ($cart->products as $product) {
            $data = self::getProductData($product->product_id);
            if (!empty($product->variation) && !isset($data->variations->{$product->variation})) {
                self::removeProductFromCart($product->id);
                continue;
            }
            $extra_options = new stdClass();
            $extra_options->count = 0;
            $extra_options->price = 0;
            $extra_options->items = new stdClass();
            $removeFlag = false;
            foreach ($product->extra_options as $key => $value) {
                if (!isset($data->extra_options->{$value->field_id}) || !isset($data->extra_options->{$value->field_id}->items->{$key})) {
                    $removeFlag = true;
                    break;
                } else {
                    $obj = $data->extra_options->{$value->field_id};
                    if (!isset($extra_options->items->{$value->field_id})) {
                        $object = new stdClass();
                        $object->title = $obj->title;
                        $object->required = $obj->required == '1';
                        $object->values = new stdClass();
                        $extra_options->items->{$value->field_id} = $object;
                    } else {
                        $object = $extra_options->items->{$value->field_id};
                    }
                    $extra_options->count++;
                    $option = new stdClass();
                    $option->value = $obj->items->{$key}->title;
                    $option->price = $obj->items->{$key}->price;
                    $object->values->{$key} = $option;
                    if (!empty($option->price)) {
                        $extra_options->price += $option->price * 1;
                    }
                }
            }
            if ($removeFlag) {
                self::removeProductFromCart($product->id);
                continue;
            }
            $product->dimensions = $data->dimensions;
            $product->extra_options = $extra_options;
            $product->data = !empty($product->variation) ? $data->variations->{$product->variation} : $data;
            if ($product->data->stock !== '' && $product->quantity > $product->data->stock) {
                $product->quantity = $product->data->stock * 1;
            }
            if ($product->quantity == 0) {
                self::removeProductFromCart($product->id);
                continue;
            }
            $cart->quantity += $product->quantity * 1;
            $productData = $product->data;
            $productData->price += $extra_options->price;
            if ($productData->sale_price !== '') {
                $productData->sale_price += $extra_options->price;
            }
            $productData->single =  new stdClass();
            $productData->single->price = $productData->price;
            $productData->single->sale = $productData->sale_price;
            $productData->price = $productData->price * $product->quantity;
            if ($productData->sale_price !== '') {
                $productData->sale_price = $productData->sale_price * $product->quantity;
            }
            $product->prices = self::prepareProductPrices($product->product_id, $productData->price, $productData->sale_price);
            $price = $product->prices->sale_price !== '' ? $product->prices->sale_price : $product->prices->price;
            $cart->subtotal += $price;
            $product->tax = self::calculateProductTax($product->product_id, $price, $cart);
            if ($product->promo) {
                $cart->validPromo = true;
                $discount = $cart->promo->unit == '%' ? $price * ($cart->promo->discount / 100) : $cart->promo->discount / $promoProducts;
                $price -= $discount;
                $cart->discount += $discount;
            }
            $product->net_price = $price;
            if ($product->tax) {
                $amount = $product->tax->amount;
                $rate = $product->tax->rate;
                if ($product->promo) {
                    $amount = self::$store->tax->mode == 'excl' ? $price * ($rate / 100) : $price - $price / ($rate / 100 + 1);
                }
                $cart->tax += $amount;
                $product->net_price = self::$store->tax->mode == 'excl' ? $price : $price - $amount;
                if (!isset($cart->taxes->{$product->tax->key})) {
                    $cart->taxes->{$product->tax->key} = new stdClass();
                    $cart->taxes->{$product->tax->key}->title = $product->tax->title;
                    $cart->taxes->{$product->tax->key}->rate = $rate;
                    $cart->taxes->{$product->tax->key}->amount = $amount;
                    $cart->taxes->{$product->tax->key}->net = $product->net_price;
                    $cart->taxes->count++;
                } else {
                    $cart->taxes->{$product->tax->key}->amount += $amount;
                    $cart->taxes->{$product->tax->key}->net += $product->net_price;
                }
            }
            $cart->net_amount += $product->net_price * 1;
            $cart->total += $price;
            $product->variations = array();
            $product->images = array();
            if (!empty($product->variation)) {
                $vars = explode('+', $product->variation);
                $variationsURL = array();
                $variationsMap = self::getProductVariationsMap($product->product_id);
                $images = new stdClass();
                foreach ($variationsMap as $value) {
                    $images->{$value->option_key} = json_decode($value->images);
                }
                foreach ($vars as $value) {
                    $query = $db->getQuery(true)
                        ->select('fd.value, fd.color, fd.image, f.title, f.field_type')
                        ->from('#__gridbox_store_products_fields_data AS fd')
                        ->where('fd.option_key = '.$db->quote($value))
                        ->leftJoin('#__gridbox_store_products_fields AS f ON f.id = fd.field_id');
                    $db->setQuery($query);
                    $variationObj = $db->loadObject();
                    $variationsURL[] = $variationObj->title.'='.$variationObj->value;
                    $product->variations[] = $variationObj;
                    if (!empty($images->{$value})) {
                        $product->images = $images->{$value};
                    }
                }
                $product->variationURL = implode('&', $variationsURL);
            }
        }
        if (!$cart->validPromo && !empty($cart->promo_id)) {
            $obj = new stdClass();
            $obj->id = $id;
            $obj->promo_id = 0;
            $db->updateObject('#__gridbox_store_cart', $obj, 'id');
        }

        return $cart;
    }

    public static function removeProductFromCart($product_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__gridbox_store_cart_products')
            ->where('id = '.$product_id);
        $db->setQuery($query)
            ->execute();
    }

    public static function removeProductFromWishlist($product_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__gridbox_store_wishlist_products')
            ->where('id = '.$product_id);
        $db->setQuery($query)
            ->execute();
    }

    public static function checkIconsLibrary($body)
    {
        $icons = '';
        $link = "\n\t<link href=\"{href}\" rel=\"stylesheet\" type=\"text/css\">";
        $array = array('fa fa-', 'fab fa-', 'fal fa-', 'far fa-', 'fas fa-');
        foreach ($array as $value) {
            if (strpos($body, $value)) {
                $icons .= str_replace('{href}', JUri::root().'templates/gridbox/library/icons/fontawesome/fontawesome.css', $link);
                break;
            }
        }
        if (strpos($body, 'zmdi zmdi-')) {
            $icons .= str_replace('{href}', JUri::root().'templates/gridbox/library/icons/material/material.css', $link);
        }
        if (strpos($body, 'flaticon-')) {
            $icons .= str_replace('{href}', JUri::root().'templates/gridbox/library/icons/outline/flaticon.css', $link);
        }

        return $icons;
    }

    public static function initItems($body)
    {
        $str = JFile::read(JPATH_ROOT.'/components/com_gridbox/assets/js/initItems.json');
        $items = json_decode($str);
        $src = array();
        $keys = new stdClass();
        $str = '';
        $aboutUs = self::aboutUs();
        preg_match_all('/ba-item-[\w-]+/', $body, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if (isset($items->{$match[0]}) && !isset($keys->{$match[0]})) {
                $keys->{$match[0]} = true;
                switch ($match[0]) {
                    case 'ba-item-counter':
                        $src[] = JUri::root().'components/com_gridbox/libraries/counter/counter.js?'.$aboutUs->version;
                        break;
                    case 'ba-item-scroll-to':
                        $src[] = JUri::root().'components/com_gridbox/libraries/smoothScroll/smoothScroll.js?'.$aboutUs->version;
                        break;
                    case 'ba-item-scroll-to-top':
                        $src[] = JUri::root().'components/com_gridbox/libraries/scrolltop/scrolltop.js?'.$aboutUs->version;
                        break;
                    case 'ba-item-countdown':
                        $src[] = JUri::root().'components/com_gridbox/libraries/countdown/countdown.js?'.$aboutUs->version;
                        break;
                    case 'ba-item-weather':
                        $src[] = JUri::root().'components/com_gridbox/libraries/weather/js/weather.js?'.$aboutUs->version;
                        break;
                    case 'ba-item-social':
                        $src[] = JUri::root().'components/com_gridbox/libraries/social/social.js?'.$aboutUs->version;
                        break;
                    case 'ba-item-content-slider':
                    case 'ba-item-slideshow':
                    case 'ba-item-field-slideshow':
                    case 'ba-item-product-slideshow':
                        $src[] = JUri::root().'components/com_gridbox/libraries/slideshow/js/slideshow.js?'.$aboutUs->version;
                        break;
                    case 'ba-item-slideset':
                        $src[] = JUri::root().'components/com_gridbox/libraries/slideset/js/slideset.js?'.$aboutUs->version;
                        break;
                    case 'ba-item-carousel':
                    case 'ba-item-recent-posts-slider':
                    case 'ba-item-related-posts-slider':
                    case 'ba-item-recently-viewed-products':
                        $src[] = JUri::root().'components/com_gridbox/libraries/carousel/js/carousel.js?'.$aboutUs->version;
                        break;
                    case 'ba-item-testimonials':
                        $src[] = JUri::root().'components/com_gridbox/libraries/testimonials/js/testimonials.js?'.$aboutUs->version;
                        break;
                }
                $src[] = JUri::root().'components/com_gridbox/libraries/modules/'.$items->{$match[0]}.'.js?'.$aboutUs->version;
            }
        }
        foreach ($src as $value) {
            $str .= "\n\t<script type=\"text/javascript\" src=\"".$value."\"></script>";
        }
        if (!empty($str)) {
            $initItems = JUri::root().'components/com_gridbox/libraries/modules/initItems.js?'.$aboutUs->version;
            $str = "\n\t<script type=\"text/javascript\" src=\"".$initItems."\"></script>".$str;
        }

        return $str;
    }

    public static function getSystemApps()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('title')
            ->from('#__gridbox_app')
            ->where('type = '.$db->quote('system_apps'))
            ->order('id ASC');
        $db->setQuery($query);
        $system = $db->loadObjectList();
        $object = new stdClass;
        foreach ($system as $obj) {
            $object->{$obj->title} = true;
        }
        self::$systemApps = $object;
    }

    public static function getDesktopFieldFiles($id = 0)
    {
        $app = JFactory::getApplication();
        if (empty($id)) {
            $id = $app->input->get('id', 0, 'int');
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('app_id')
            ->from('#__gridbox_pages')
            ->where('id = '.$id);
        $db->setQuery($query);
        $app_id = $db->loadResult();
        $items = new stdClass();
        if ($app_id) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_fields_desktop_files')
                ->where('page_id = '.$id)
                ->where('app_id = '.$app_id);
            $db->setQuery($query);
            $files = $db->loadObjectList();
            foreach ($files as $file) {
                $items->{$file->id} = $file;
            }
        }

        return $items;
    }

    public static function getDesktopSavedFieldFiles($id = 0)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_fields_desktop_files')
            ->where('page_id = '.$id);
        $db->setQuery($query);
        $files = $db->loadObjectList();
        $items = new stdClass();
        foreach ($files as $file) {
            $items->{$file->id} = $file;
        }

        return $items;
    }

    public static function getSiteMenuModify($array)
    {
        $db = JFactory::getDbo();
        $id = $array['id'];
        $table = '';
        $column = $db->quoteName('saved_time');
        $column .= ', '.$db->quoteName('changefreq');
        $column .= ', '.$db->quoteName('priority');
        $column .= ', '.$db->quoteName('sitemap_include');
        if ($array['option'] == 'com_gridbox' && $array['view'] == 'page') {
            $table = '#__gridbox_pages';
            $column .= ', '.$db->quoteName('created');
        } else if ($array['option'] == 'com_gridbox' && $array['view'] == 'blog') {
            $table = '#__gridbox_app';
            $id = $array['app'];
        }
        if (!empty($table)) {
            $data = self::getSiteComponentModify($table, $column, $id);
        } else {
            $data = new stdClass();
            $data->sitemap_include = 1;
            $data->lastmod = date('Y-m-d');
            $data->changefreq = 'monthly';
            $data->priority = '0.5';
        }

        return $data;
    }

    public static function setLastmod($saved_time)
    {
        if (!empty($saved_time)) {
            $array = explode('-', $saved_time);
            if (count($array) == 6) {
                $saved_time = $array[0].'-'.$array[1].'-'.$array[2].' '.$array[3].':'.$array[4].':'.$array[5];
            }
        }

        return $saved_time;
    }

    public static function getSiteComponentModify($table, $column, $id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select($column)
            ->from($db->quoteName($table))
            ->where('id = '.$id);
        $db->setQuery($query);
        $obj = $db->loadObject();
        $obj->lastmod = self::setLastmod($obj->saved_time);
        $Y = date('Y', strtotime($obj->lastmod));
        if ($Y < 2000 && isset($obj->created)) {
            $obj->lastmod = $obj->created;
        }
        if ($Y < 2000 && !isset($obj->created)) {
            $obj->lastmod = date('Y-m-d');
        } else {
            $obj->lastmod = date('Y-m-d', strtotime($obj->lastmod));
        }

        return $obj;
    }

    public static function getSitemapUrl($url, $data)
    {
        $l = strlen($url) - 1;
        if (self::$website->sitemap_slash && $url[$l] != '/') {
            $url .= '/';
        } else if (!self::$website->sitemap_slash && $url[$l] == '/') {
            $url = substr($url, 0, $l);
        }
        if ($data->sitemap_include == 1) {
            $str = "\t<url>\n";
            $str .= "\t\t<loc>".self::$website->sitemap_domain.$url."</loc>\n";
            $str .= "\t\t<lastmod>".$data->lastmod."</lastmod>\n";
            $str .= "\t\t<changefreq>".$data->changefreq."</changefreq>\n";
            $str .= "\t\t<priority>".$data->priority."</priority>\n";
            $str .= "\t</url>\n";
        } else {
            $str = '';
        }

        return $str;
    }

    public static function checkSitemap()
    {
        if (self::$website->enable_sitemap == 1 && self::$website->sitemap_frequency != 'never') {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_api')
                ->where('service = '.$db->quote('gridbox_sitemap'));
            $db->setQuery($query);
            $obj = $db->loadObject();
            $now = date('Y-m-d H:i:s');
            $date = !empty($obj->key) ? $obj->key : $now;
            $datetime1 = new DateTime($now);
            $datetime2 = new DateTime($date);
            $interval = $datetime1->diff($datetime2);
            if (empty($obj->key) || (self::$website->sitemap_frequency == 'daily' && $interval->days >= 1)
                || (self::$website->sitemap_frequency == 'weekly' && $interval->days >= 7)
                || (self::$website->sitemap_frequency == 'monthly' && $interval->days >= 30)) {
                $obj->key = date('Y-m-d H:i:s');
                $db->updateObject('#__gridbox_api', $obj, 'id');
                self::createSitemap();
            }
        }
    }

    public static function createSitemap()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('m.id, m.link, m.title, m.language')
            ->from('#__menu_types AS mt')
            ->leftJoin('`#__menu` AS m ON mt.menutype = m.menutype')
            ->where('mt.client_id = 0')
            ->where('published = 1')
            ->where('access = 1')
            ->where('m.type = '.$db->quote('component'));
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $langObj = new stdClass();
        if (JLanguageMultilang::isEnabled()) {
            $languages  = JLanguageHelper::getLanguages();
            foreach ($languages as $language) {
                $langObj->{$language->lang_code} = $language->sef;
            }
        }
        $str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $str .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($items as $item) {
            $link = $item->link.'&Itemid='.$item->id;
            $link = 'index.php?Itemid='.$item->id;
            if (JLanguageMultilang::isEnabled() && $item->language != '*' && isset($langObj->{$item->language})) {
                $link .= '&lang='.($langObj->{$item->language});
            }
            $linkString = str_replace('index.php?', '', $item->link);
            parse_str($linkString, $array);
            if (isset($array['option']) && isset($array['id'])) {
                $data = self::getSiteMenuModify($array);
            } else {
                $data = new stdClass();
                $data->sitemap_include = 1;
                $data->lastmod = date('Y-m-d');
                $data->changefreq = 'monthly';
                $data->priority = '0.5';
            }
            $url = JRoute::_($link);
            $str .= self::getSitemapUrl($url, $data);
        }
        self::getGridboxMenuItems();
        $itemId = self::getDefaultMenuItem();
        if (!empty($itemId)) {
            $default = '&Itemid='.$itemId;
        }
        $str .= self::getSitemapGridboxPages(0, 'single', $default, $langObj);
        $query = $db->getQuery(true)
            ->select('id, type, language, saved_time, changefreq, priority, sitemap_include')
            ->from('#__gridbox_app')
            ->where('type <> '.$db->quote('system_apps'))
            ->where('published = 1')
            ->where('access = 1');
        $db->setQuery($query);
        $apps = $db->loadObjectList();
        foreach ($apps as $app) {
            if ($app->type != 'single') {
                $itemId = $itemId = self::getGridboxMenuItemidByApp($app->id);
                $app->lastmod = self::setLastmod($app->saved_time);
                if (date('Y', strtotime($app->lastmod)) < 2000) {
                    $app->lastmod = date('Y-m-d');
                } else {
                    $app->lastmod = date('Y-m-d', strtotime($app->lastmod));
                }
                if (!$itemId) {
                    $link = 'index.php?option=com_gridbox&view=blog&app='.$app->id.'&id=0'.$default;
                    if (JLanguageMultilang::isEnabled() && $app->language != '*' && isset($langObj->{$app->language})) {
                        $link .= '&lang='.($langObj->{$app->language});
                    }
                    $url = JRoute::_($link);
                    $str .= self::getSitemapUrl($url, $app);
                }
                $str .= self::getSitemapGridboxCategories($app->id, $app->lastmod, $default, $langObj);
                $str .= self::getSitemapGridboxTags($app->id, $app->lastmod, $default, $langObj);
                $str .= self::getSitemapGridboxAuthors($app->id, $app->lastmod, $default, $langObj);
            }
            $str .= self::getSitemapGridboxPages($app->id, $app->type, $default, $langObj);
        }
        $str .= '</urlset>';
        JFile::write(JPATH_ROOT.'/sitemap.xml', $str);
        exit;
    }

    public static function getSitemapGridboxAuthors($app_id, $lastmod, $default, $langObj)
    {
        $str = '';
        $db = JFactory::getDbo();
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('DISTINCT t.id, t.changefreq, t.priority, t.sitemap_include')
            ->from('#__gridbox_pages AS p')
            ->where('p.app_id = '.$app_id)
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('p.page_access = 1')
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('t.published = 1')
            ->leftJoin('`#__gridbox_authors_map` AS m ON p.id = m.page_id')
            ->leftJoin('`#__gridbox_authors` AS t ON t.id = m.author_id');
        $db->setQuery($query);
        $authors = $db->loadObjectList();
        foreach ($authors as $author) {
            $link = self::getGridboxAuthorLinks($author->id, $app_id);
            if (strpos($link, '&Itemid=') === false) {
                $link .= $default;
            }
            $url = JRoute::_($link);
            $author->lastmod = $lastmod;
            $str .= self::getSitemapUrl($url, $author);
        }

        return $str;
    }

    public static function getSitemapGridboxTags($app_id, $lastmod, $default, $langObj)
    {
        $str = '';
        $db = JFactory::getDbo();
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('DISTINCT t.id, t.language, t.changefreq, t.priority, t.sitemap_include')
            ->from('#__gridbox_pages AS p')
            ->where('p.app_id = '.$app_id)
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('p.page_access = 1')
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('t.published = 1')
            ->where('t.access = 1')
            ->leftJoin('`#__gridbox_tags_map` AS m ON p.id = m.page_id')
            ->leftJoin('`#__gridbox_tags` AS t ON t.id = m.tag_id');
        $db->setQuery($query);
        $tags = $db->loadObjectList();
        foreach ($tags as $tag) {
            $itemId = self::getGridboxMenuItemidByTag($tag->id, $app_id);
            if (!empty($itemId)) {
                continue;
            }
            $link = self::getGridboxTagLinks($tag->id, $app_id);
            if (strpos($link, '&Itemid=') === false) {
                $link .= $default;
            }
            if (JLanguageMultilang::isEnabled() && $tag->language != '*' && isset($langObj->{$tag->language})) {
                $link .= '&lang='.($langObj->{$tag->language});
            }
            $url = JRoute::_($link);
            $tag->lastmod = $lastmod;
            $str .= self::getSitemapUrl($url, $tag);
        }

        return $str;
    }

    public static function getSitemapGridboxCategories($app_id, $lastmod, $default, $langObj)
    {
        $str = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, language, changefreq, priority, sitemap_include')
            ->from('#__gridbox_categories')
            ->where('app_id = '.$app_id)
            ->where('published = 1')
            ->where('access = 1');
        $db->setQuery($query);
        $categories = $db->loadObjectList();
        foreach ($categories as $category) {
            $itemId = self::getGridboxMenuItemidByCategory($app_id, $category->id);
            if (!empty($itemId)) {
                continue;
            }
            $link = self::getGridboxCategoryLinks($category->id, $app_id);
            if (strpos($link, '&Itemid=') === false) {
                $link .= $default;
            }
            if (JLanguageMultilang::isEnabled() && $category->language != '*' && isset($langObj->{$category->language})) {
                $link .= '&lang='.($langObj->{$category->language});
            }
            $url = JRoute::_($link);
            $category->lastmod = $lastmod;
            $str .= self::getSitemapUrl($url, $category);
        }

        return $str;
    }

    public static function getSitemapGridboxPages($app_id, $type, $default, $langObj)
    {
        $str = '';
        $db = JFactory::getDbo();
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('id, page_category, language, saved_time, created, changefreq, priority, sitemap_include')
            ->from('#__gridbox_pages')
            ->where('app_id = '.$app_id)
            ->where('page_category <> '.$db->quote('trashed'))
            ->where('published = 1')
            ->where('created <= '.$db->quote($date))
            ->where('page_access = 1')
            ->where('(end_publishing = '.$nullDate.' OR end_publishing >= '.$db->quote($date).')');
        $db->setQuery($query);
        $pages = $db->loadObjectList();
        foreach ($pages as $page) {
            $itemId = self::getGridboxMenuItemidByPage($page->id);
            if (!empty($itemId)) {
                continue;
            }
            $link = self::getGridboxPageLinks($page->id, $type, $app_id, $page->page_category);
            if (strpos($link, '&Itemid=') === false) {
                $link .= $default;
            }
            $str .= self::getSitemapGridboxPage($link, $page, $langObj);
        }

        return $str;
    }

    public static function getSitemapGridboxPage($link, $page, $langObj)
    {
        if (JLanguageMultilang::isEnabled() && $page->language != '*' && isset($langObj->{$page->language})) {
            $link .= '&lang='.($langObj->{$page->language});
        }
        $page->lastmod = self::setLastmod($page->saved_time);
        if (date('Y', strtotime($page->lastmod)) < 2000) {
            $page->lastmod = $page->created;
        }
        $page->lastmod = date('Y-m-d', strtotime($page->lastmod));
        $url = JRoute::_($link);
        $str = self::getSitemapUrl($url, $page);

        return $str;
    }

    public static function checkUserEditLevel($action = 'core.edit')
    {
        if (!JFactory::getUser()->authorise($action, 'com_gridbox')) {
            exit;
        }
    }

    public static function getCommentsEditList($user)
    {
        $list = array();
        if ($user->type != 'guest') {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_comments')
                ->where('user_type = '.$db->quote($user->type))
                ->where('user_id = '.$db->quote($user->id));
            $db->setQuery($query);
            $list = $db->loadObjectList();
        }

        return $list;
    }

    public static function getReviewsEditList($user)
    {
        $list = array();
        if ($user->type != 'guest') {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_reviews')
                ->where('user_type = '.$db->quote($user->type))
                ->where('user_id = '.$db->quote($user->id));
            $db->setQuery($query);
            $list = $db->loadObjectList();
        }

        return $list;
    }

    public static function getCommentLikeStatus($id)
    {
        $db = JFactory::getDbo();
        $ip = $_SERVER['REMOTE_ADDR'];
        $query = $db->getQuery(true)
            ->select('status')
            ->from('#__gridbox_comments_likes_map')
            ->where('ip = '.$db->quote($ip))
            ->where('comment_id = '.$id);
        $db->setQuery($query);
        $status = $db->loadResult();

        return $status;
    }

    public static function getReviewLikeStatus($id)
    {
        $db = JFactory::getDbo();
        $ip = $_SERVER['REMOTE_ADDR'];
        $query = $db->getQuery(true)
            ->select('status')
            ->from('#__gridbox_reviews_likes_map')
            ->where('ip = '.$db->quote($ip))
            ->where('comment_id = '.$id);
        $db->setQuery($query);
        $status = $db->loadResult();

        return $status;
    }

    public static function getCommentAttachments($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_comments_attachments')
            ->where('comment_id = '.$id);
        $db->setQuery($query);
        $files = $db->loadObjectList();
        $dir = JUri::root().'components/com_gridbox/assets/uploads/comments/';
        $obj = new stdClass();
        $obj->files = array();
        $obj->images = array();
        foreach ($files as $key => $file) {
            $file->link = $dir.$file->filename;
            if ($file->type == 'file') {
                $obj->files[] = $file;
            } else {
                $obj->images[] = $file;
            }
        }

        return $obj;
    }

    public static function getReviewAttachments($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_reviews_attachments')
            ->where('comment_id = '.$id);
        $db->setQuery($query);
        $files = $db->loadObjectList();
        $dir = JUri::root().'components/com_gridbox/assets/uploads/reviews/';
        $obj = new stdClass();
        $obj->files = array();
        $obj->images = array();
        foreach ($files as $key => $file) {
            $file->link = $dir.$file->filename;
            if ($file->type == 'file') {
                $obj->files[] = $file;
            } else {
                $obj->images[] = $file;
            }
        }

        return $obj;
    }

    public static function getDefaultComment($type)
    {

        $str = '';
        $avatar = JUri::root().'components/com_gridbox/assets/images/default-user.png';
        $message = 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.';
        $status = '';
        $moderators = array();
        $comment = new stdClass();
        $comment->id = 0;
        $attachments = new stdClass();
        $attachments->files = $attachments->images = array();
        $comment->date = '12 '.JText::_('HOURS_AGO');
        $comment->rating = 5;
        $comment->name = 'Name';
        $comment->likes = $comment->dislikes = $comment->parent = 0;
        $comment->status = 'approved';
        include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/'.$type.'/'.$type.'-comment-pattern.php');
        $str .= $out;

        return $str;
    }

    public static function getCommentsCountHTML($id, $view, $sortBy)
    {
        if ($view == 'gridbox') {
            $count = 1;
        } else {
            $count = self::getCommentsCount($id);
        }
        include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/comments-box/comments-box-total-count-pattern.php');

        return $string;
    }

    public static function getReviewsCountHTML($id, $view, $sortBy)
    {
        if ($view == 'gridbox') {
            $count = 1;
            $rating = 5;
            $type = '';
        } else {
            $obj = self::getReviewsCount($id);
            $count = $obj->count;
            $rating = round($obj->rating, 1);
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('a.type')
                ->from('#__gridbox_pages AS p')
                ->where('p.id = '.$id)
                ->leftJoin('#__gridbox_app AS a ON a.id = p.app_id');
            $db->setQuery($query);
            $type = $db->loadResult();
        }
        include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/reviews/reviews-total-count-pattern.php');

        return $string;
    }

    public static function getCommentsCount($id, $parent = 0)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $moderators = self::$commentsModerators;
        $user = self::$commentUser;
        if (empty($user) || !$moderators || $user->type != 'user' || !in_array($user->id, $moderators)) {
            $query->select('id')
                ->from('#__gridbox_comments')
                ->where('page_id = '.$id)
                ->where('status = '.$db->quote('approved'))
                ->where('parent = '.$parent)
                ->order('date desc');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            $count = 0;
            foreach ($items as $item) {
                $count++;
                $count += self::getCommentsCount($id, $item->id);
            }
        } else {
            $query->select('COUNT(id)')
                ->from('#__gridbox_comments')
                ->where('page_id = '.$id)
                ->order('date desc');
            $db->setQuery($query);
            $count = $db->loadResult();
        }

        return $count;
    }

    public static function getReviewsCount($id, $parent = 0)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(id) as count, AVG(rating) as rating')
            ->from('#__gridbox_reviews')
            ->where('page_id = '.$id)
            ->where('parent = '.$parent)
            ->order('date desc');
        $moderators = self::$reviewsModerators;
        $user = self::$commentUser;
        if (empty($user) || $user->type != 'user' || !in_array($user->id, $moderators)) {
            $query->where('status = '.$db->quote('approved'));
        }
        $db->setQuery($query);
        $obj = $db->loadObject();

        return $obj;
    }

    public static function getCommentsUserAvatar($email)
    {
        $avatar = JUri::root().'components/com_gridbox/assets/images/default-user.png';
        if (self::$website->enable_gravatar == 1 && !empty($email)) {
            $hash = md5(strtolower(trim($email)));
            $avatar = "https://www.gravatar.com/avatar/".$hash."?d=".$avatar."&s=50";
        }

        return $avatar;
    }

    public static function getReviewsUserAvatar($email)
    {
        $avatar = JUri::root().'components/com_gridbox/assets/images/default-user.png';
        if (self::$website->reviews_enable_gravatar == 1 && !empty($email)) {
            $hash = md5(strtolower(trim($email)));
            $avatar = "https://www.gravatar.com/avatar/".$hash."?d=".$avatar."&s=50";
        }

        return $avatar;
    }

    public static function getCommentsLoginedUserHTML($obj, $type)
    {
        if (empty($obj->avatar) && $type == 'comments-box') {
            $avatar = self::getCommentsUserAvatar($obj->email);
        } else if (empty($obj->avatar) && $type == 'reviews') {
            $avatar = self::getReviewsUserAvatar($obj->email);
        } else {
            $avatar = $obj->avatar;
        }
        include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/'.$type.'/'.$type.'-logined-user-pattern.php');
        $str = $string;

        return $str;
    }

    public static function getCommentsLogoutedUserHTML($type)
    {
        $avatar = JUri::root().'components/com_gridbox/assets/images/default-user.png';
        include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/'.$type.'/'.$type.'-logouted-user-pattern.php');
        $str = $string;

        return $str;
    }

    public static function setcookie($name, $value, $time)
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $cookie_path = $app->get('cookie_path', '/');
        $cookie_domain = $app->get('cookie_domain');
        $ssl = $app->isSSLConnection();
        if (phpversion() >= '7.3.0') {
            $options = array('expires' => $time, 'path' => $cookie_path, 'domain' => $cookie_domain,
                'secure' => $ssl, 'httponly' => true, 'samesite' => 'Lax');
            setcookie($name, $value, $options);
        } else {
            setcookie($name, $value, $time, $cookie_path, $cookie_domain, $ssl, true);
        }
    }

    public static function setCommentsUser($value)
    {
        $session = JFactory::getSession();
        $session->set('gridbox-comments-user', $value);
    }

    public static function getCommentsUser()
    {
        $session = JFactory::getSession();
        $user = $session->get('gridbox-comments-user', '');

        return $user;
    }

    public static function removeCommentsUser()
    {
        $session = JFactory::getSession();
        $session->clear('gridbox-comments-user');
    }

    public static function setCommentUser()
    {
        $JUser = JFactory::getUser();
        $user = self::getCommentsUser();
        if (!empty($user)) {
            $object = json_decode($user);
        } else {
            $object = new stdClass();
            $object->id = 0;
            $object->type = 'empty';
        }
        if (!empty($user) && $object->type == 'user' && empty($JUser->id)) {
            self::removeCommentsUser();
            $object = new stdClass();
            $object->id = 0;
            $object->type = 'empty';
            $user = '';
        }
        if (!empty($JUser->id) && ($object->id != $JUser->id || $object->type != 'user')) {
            $data = new stdClass();
            $data->name = $JUser->name;
            $data->email = $JUser->email;
            $data->avatar = '';
            $data->id = $JUser->id;
            $data->type = 'user';
            $value = json_encode($data);
            $user = $value;
            self::setCommentsUser($value);
        }
        if (!empty($user)) {
            $data = json_decode($user);
            if (!empty($JUser->id)) {
                $data->email = $JUser->email;
            }
            self::$commentUser = $data;
        }
    }

    public static function getCommentsUserLoginHTML($type)
    {
        self::setCommentUser();
        $obj = new stdClass();
        $obj->status = '';
        if (!empty(self::$commentUser)) {
            $obj->status = 'login';
            $obj->str = self::getCommentsLoginedUserHTML(self::$commentUser, $type);
        } else {
            $obj->str = self::getCommentsLogoutedUserHTML($type);
        }

        return $obj;
    }

    public static function setCommentsModerators()
    {
        if (self::$website->comments_moderator_admins == 'super_user') {
            $db = JFactory::getDbo();
            $moderators = array();
            $query = $db->getQuery(true)
                ->select('u.id, u.name, g.id as level')
                ->from('`#__users` AS u')
                ->leftJoin('`#__user_usergroup_map` AS m ON '.$db->quoteName('u.id').' = '.$db->quoteName('m.user_id'))
                ->leftJoin('`#__usergroups` AS g ON '.$db->quoteName('g.id').' = '.$db->quoteName('m.group_id'));
            $db->setQuery($query);
            $users = $db->loadObjectList();
            foreach ($users as $value) {
                if ($value->level == 8) {
                    $moderators[] = $value->id;
                }
            }
        } else {
            $moderators = explode(',', self::$website->comments_moderator_admins);
        }
        self::$commentsModerators = $moderators;
    }

    public static function setReviewsModerators()
    {
        if (self::$website->reviews_moderator_admins == 'super_user') {
            $db = JFactory::getDbo();
            $moderators = array();
            $query = $db->getQuery(true)
                ->select('u.id, u.name, g.id as level')
                ->from('`#__users` AS u')
                ->leftJoin('`#__user_usergroup_map` AS m ON '.$db->quoteName('u.id').' = '.$db->quoteName('m.user_id'))
                ->leftJoin('`#__usergroups` AS g ON '.$db->quoteName('g.id').' = '.$db->quoteName('m.group_id'));
            $db->setQuery($query);
            $users = $db->loadObjectList();
            foreach ($users as $value) {
                if ($value->level == 8) {
                    $moderators[] = $value->id;
                }
            }
        } else {
            $moderators = explode(',', self::$website->reviews_moderator_admins);
        }
        self::$reviewsModerators = $moderators;
    }

    public static function getComments($id, $parent = 0, $level = 0, $replyName = '')
    {
        $db = JFactory::getDbo();
        $input = JFactory::getApplication()->input;
        $sort = $input->get('sort-by', 'recent', 'string');
        $moderators = self::$commentsModerators;
        $user = self::$commentUser;
        $order = 'c.date desc';
        if ($sort == 'oldest') {
            $order = 'c.date asc';
        } else if ($sort == 'popular') {
            $order = 'c.likes desc';
        }
        $query = $db->getQuery(true)
            ->select('c.*, u.email AS user_email')
            ->from('#__gridbox_comments AS c')
            ->where('c.page_id = '.$id)
            ->where('c.parent = '.$parent)
            ->leftJoin('#__users AS u ON u.id = c.user_id')
            ->order($order);
        if (empty($user) || $user->type != 'user' || !in_array($user->id, $moderators)) {
            $query->where('c.status = '.$db->quote('approved'));
        }
        $db->setQuery($query);
        $comments = $db->loadObjectList();
        $str = '';
        $level++;
        foreach ($comments as $comment) {
            if (empty($comment->avatar)) {
                if ($comment->user_type == 'user' && !empty($comment->user_email)) {
                    $comment->email = $comment->user_email;
                }
                $avatar = self::getCommentsUserAvatar($comment->email);
            } else {
                $avatar = $comment->avatar;
            }
            $message = str_replace("\n", '<br>', $comment->message);
            $status = self::getCommentLikeStatus($comment->id);
            $attachments = self::getCommentAttachments($comment->id);
            $time = time() - strtotime($comment->date);
            $hour = 60 * 60;
            if ($time < 60) {
                $comment->date = '1 '.JText::_('MINUTES_AGO');
            } else if ($time < $hour) {
                $comment->date = floor($time / 60).' '.JText::_('MINUTES_AGO');
            } else if ($time < 86400) {
                $comment->date = floor($time / $hour).' '.JText::_('HOURS_AGO');
            } else {
                $comment->date = self::getPostDate($comment->date);
            }
            include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/comments-box/comments-box-comment-pattern.php');
            $str .= $out;
            $reply = self::getComments($id, $comment->id, $level, $comment->name);
            if (!empty($reply)) {
                if ($level < 3) {
                    $str .= '<div class="ba-comment-reply-wrapper">';
                }
                $str .= $reply;
                if ($level < 3) {
                    $str .= '</div>';
                }
            }
        }

        return $str;
    }

    public static function getReview($id)
    {
        $moderators = self::$reviewsModerators;
        $user = self::$commentUser;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('c.*, u.email AS user_email')
            ->from('#__gridbox_reviews AS c')
            ->where('c.id = '.$id)
            ->leftJoin('#__users AS u ON u.id = c.user_id');
        if (empty($user) || $user->type != 'user' || !in_array($user->id, $moderators)) {
            $query->where('c.status = '.$db->quote('approved'));
        }
        $db->setQuery($query);
        $review = $db->loadObject();

        return $review;
    }

    public static function getReviewById($id, $replyName = '')
    {
        $moderators = self::$reviewsModerators;
        $user = self::$commentUser;
        $comment = self::getReview($id);
        if (empty($comment->avatar)) {
            if ($comment->user_type == 'user' && !empty($comment->user_email)) {
                $comment->email = $comment->user_email;
            }
            $avatar = self::getReviewsUserAvatar($comment->email);
        } else {
            $avatar = $comment->avatar;
        }
        $message = str_replace("\n", '<br>', $comment->message);
        $status = self::getReviewLikeStatus($comment->id);
        $attachments = self::getReviewAttachments($comment->id);
        $time = time() - strtotime($comment->date);
        $hour = 60 * 60;
        if ($time < 60) {
            $comment->date = '1 '.JText::_('MINUTES_AGO');
        } else if ($time < $hour) {
            $comment->date = floor($time / 60).' '.JText::_('MINUTES_AGO');
        } else if ($time < 86400) {
            $comment->date = floor($time / $hour).' '.JText::_('HOURS_AGO');
        } else {
            $comment->date = self::getPostDate($comment->date);
        }
        include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/reviews/reviews-comment-pattern.php');
        
        return $out;
    }

    public static function getReviews($id, $parent = 0, $level = 0, $replyName = '', $active = 1, $replyLimit = 2, $reviewID = 0)
    {
        $db = JFactory::getDbo();
        $input = JFactory::getApplication()->input;
        $sort = $input->get('sort-by', 'recent', 'string');
        $moderators = self::$reviewsModerators;
        $user = self::$commentUser;
        $order = 'c.date desc';
        $limit = 10 * $active;
        if (!empty($reviewID)) {
            self::$review = self::getReview($reviewID);
            $reviews = self::getReviewsCount($id);
            $limit = $reviews->count * 1;
        }
        if (!empty(self::$review) && self::$review->parent == $parent) {
            $replyLimit = 0;
        }
        if ($sort == 'oldest') {
            $order = 'c.date asc';
        } else if ($sort == 'popular') {
            $order = 'c.likes desc';
        }
        $query = $db->getQuery(true)
            ->select('c.*, u.email AS user_email')
            ->from('#__gridbox_reviews AS c')
            ->where('c.page_id = '.$id)
            ->where('c.parent = '.$parent)
            ->leftJoin('#__users AS u ON u.id = c.user_id')
            ->order($order);
        if (empty($user) || $user->type != 'user' || !in_array($user->id, $moderators)) {
            $query->where('c.status = '.$db->quote('approved'));
        }
        if ($level == 0) {
            $db->setQuery($query, 0, $limit);
        } else {
            $db->setQuery($query, 0, $replyLimit);
        }
        $comments = $db->loadObjectList();
        $str = '';
        $level++;
        foreach ($comments as $comment) {
            if (empty($comment->avatar)) {
                if ($comment->user_type == 'user' && !empty($comment->user_email)) {
                    $comment->email = $comment->user_email;
                }
                $avatar = self::getReviewsUserAvatar($comment->email);
            } else {
                $avatar = $comment->avatar;
            }
            $message = str_replace("\n", '<br>', $comment->message);
            $status = self::getReviewLikeStatus($comment->id);
            $attachments = self::getReviewAttachments($comment->id);
            $time = time() - strtotime($comment->date);
            $hour = 60 * 60;
            if ($time < 60) {
                $comment->date = '1 '.JText::_('MINUTES_AGO');
            } else if ($time < $hour) {
                $comment->date = floor($time / 60).' '.JText::_('MINUTES_AGO');
            } else if ($time < 86400) {
                $comment->date = floor($time / $hour).' '.JText::_('HOURS_AGO');
            } else {
                $comment->date = self::getPostDate($comment->date);
            }
            include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/reviews/reviews-comment-pattern.php');
            $str .= $out;
            $reply = self::getReviews($id, $comment->id, $level, $comment->name);
            if (!empty($reply)) {
                $str .= '<div class="ba-comment-reply-wrapper">';
                $str .= $reply;
                $str .= '</div>';
            }
        }
        if ($level == 1) {
            $str .= self::getReviewsPagination($id, $limit, $active);
        } else {
            $str .= self::getReviewsReplyPagination($id, $replyLimit, $parent);
        }

        return $str;
    }

    public static function getReviewsPagination($id, $limit, $active)
    {
        $reviews = self::getReviewsCount($id);
        $count = $reviews->count * 1;
        if ($count == 0) {
            return '';
        }
        $pages = ceil($count / $limit);
        if ($pages == 1) {
            return '';
        }
        $html = '<span class="ba-load-more-reviews-btn" data-next="'.($active + 1).'">'.JText::_('LOAD_MORE').'</span>';

        return $html;
    }

    public static function getReviewsReplyPagination($id, $limit, $parent)
    {
        $reviews = self::getReviewsCount($id, $parent);
        $count = $reviews->count * 1;
        if ($count == 0 || $limit == 0 || $count <= $limit) {
            return '';
        }
        $html = '<span class="ba-view-more-replies">'.JText::_('VIEW_MORE_REPLIES').' ('.($count - $limit).')</span>';

        return $html;
    }

    public static function removeTmpAttachment($id, $filename)
    {
        if (!empty($id) && !empty($filename)) {
            $db = JFactory::getDbo();
            $dir = JPATH_ROOT.'/components/com_gridbox/assets/uploads/comments/';
            if (JFile::exists($dir.$filename)) {
                JFile::delete($dir.$filename);
            }
            $query = $db->getQuery(true)
                ->delete('#__gridbox_comments_attachments')
                ->where('id = '.$id);
            $db->setQuery($query)
                ->execute();
        }
    }

    public static function removeTmpReviewsAttachment($id, $filename)
    {
        if (!empty($id) && !empty($filename)) {
            $db = JFactory::getDbo();
            $dir = JPATH_ROOT.'/components/com_gridbox/assets/uploads/reviews/';
            if (JFile::exists($dir.$filename)) {
                JFile::delete($dir.$filename);
            }
            $query = $db->getQuery(true)
                ->delete('#__gridbox_reviews_attachments')
                ->where('id = '.$id);
            $db->setQuery($query)
                ->execute();
        }
    }

    public static function getPerformance()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('email_encryption, compress_html, compress_css, compress_js, page_cache, browser_cache, compress_images,
                images_max_size, images_quality, images_lazy_load, adaptive_images, adaptive_quality, enable_canonical, defer_loading')
            ->from('#__gridbox_website');
        $db->setQuery($query);
        $obj = $db->loadObject();

        return $obj;
    }

    public static function checkPreloader()
    {
        if (self::$website->preloader == 1 && isset(self::$systemApps->preloader)) {
            $system = gridboxHelper::getSystemParamsByType('preloader');
            self::checkSystemCss($system->id);
            $doc = JFactory::getDocument();
            $db = JFactory::getDbo();
            $input = JFactory::getApplication()->input;
            $query = $db->getQuery(true)
                ->select('html, items')
                ->from('#__gridbox_system_pages')
                ->where('id = '.$system->id);
            $db->setQuery($query);
            $obj = $db->loadObject();
            if (empty($obj->html)) {
                $obj->html = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/system/preloader.html');
                $obj->items = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/system/preloader.json');
            }
            $data = json_decode($obj->items);
            $object = $data->{'item-15289771381'};
            if ($object->session->enable == true && $input->cookie->exists('gridbox-preloader')) {
                return;
            } else if ($object->session->enable == true && !$input->cookie->exists('gridbox-preloader')) {
                setcookie('gridbox-preloader', '1', time()+31104000);
            }
            $style = '<style>';
            $style .= JFile::read(JPATH_ROOT.'/components/com_gridbox/libraries/preloader/css/preloader.css');
            if ($object->layout == 'spinner') {
                $style .= "\n";
                $type = str_replace('ba-', '', $object->spinner);
                $style .= JFile::read(JPATH_ROOT.'/components/com_gridbox/libraries/preloader/css/'.$type.'.css');
            }
            $style .= "\n";
            $style .= JFile::read(JPATH_ROOT.'/components/com_gridbox/libraries/preloader/css/'.$object->animation.'.css');
            $style .= "\n".self::getPreloaderRules($object->desktop, 'item-15289771381');
            $style .= '</style>';
            $html = self::clearDOM($obj->html, $data);
            echo $style.$html;
        }
    }

    public static function getPageType($id, $view, $edit_type)
    {
        $type = '';
        if (empty($edit_type) && $view == 'gridbox') {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('p.app_id')
                ->from('`#__gridbox_pages` AS p')
                ->select('a.type')
                ->leftJoin('`#__gridbox_app` AS a ON p.app_id = a.id')
                ->where('p.id = '.$id);
            $db->setQuery($query);
            $app = $db->loadObject();
            $type = $app->type;
        }

        return $type;
    }

    public static function getPageClass($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('class_suffix')
            ->from('#__gridbox_pages')
            ->where('id = '.$id);
        $db->setQuery($query);
        $suffix = $db->loadResult();
        if (!$suffix) {
            $suffix = '';
        }

        return $suffix;
    }

    public static function setAppLicense($data)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_api')
            ->where('service = '.$db->quote('balbooa'));
        $db->setQuery($query);
        $balbooa = $db->loadObject();
        $balbooa->key = json_decode($balbooa->key);
        $balbooa->key->data = $data;
        if (empty($data)) {
            unset($balbooa->key->data);
        }
        $balbooa->key = json_encode($balbooa->key);
        $db->updateObject('#__gridbox_api', $balbooa, 'id');
    }

    public static function compressGridbox($body)
    {
        $performance = self::getPerformance();
        if ($performance->enable_canonical == 1) {
            $body = self::setCanonical($body);
        }
        if (isset(self::$systemApps->performance)) {
            if ($performance->compress_js == 1) {
                $body = self::minifyJs($body);
            }
            if ($performance->compress_css == 1) {
                $body = self::minifyCss($body);
            }
            if ($performance->compress_images == 1 || $performance->adaptive_images == 1) {
                $content = self::compressImages($body);
                if ($content) {
                    $body = $content;
                }
            }
            if ($performance->images_lazy_load == 1) {
                $body = self::setLazyLoad($body);
            }
            if ($performance->defer_loading == 1) {
                $body = self::setDeferredLoading($body);
            }
            if ($performance->compress_html == 1) {
                $body = preg_replace('/[\n\t\r]+/', '', $body);
                $body = preg_replace('/ +/', ' ', $body);
            }
        }

        return $body;
    }

    public static function setLazyLoad($body)
    {
        $body = preg_replace('#<noscript(.*?)>(.*?)</noscript>#is', '', $body);
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        include_once JPATH_ROOT.'/components/com_gridbox/libraries/php/phpQuery/phpQuery.php';
        $doc = phpQuery::newDocument($body);
        $str = '.ba-section, .ba-row, .ba-grid-column, .slideshow-content, .ba-instagram-image';
        $str .= ', .testimonials-img, .ba-blog-post-image > a, .intro-post-image, .comment-attachment-image-type';
        pq($str)->addClass('lazy-load-image');
        foreach (pq('img:not([itemprop]):not(.ba-gravatar-img)') as $img) {
            if (pq($img)->parent()->hasClass('ba-image')) {
                continue;
            }
            $src = pq($img)->attr('src');
            if (!empty($src)) {
                pq($img)->removeAttr('src');
                pq($img)->attr('data-gridbox-lazyload-src', $src);
                pq($img)->addClass('lazy-load-image');
            }
            $srcset = pq($img)->attr('srcset');
            if (!empty($srcset)) {
                pq($img)->removeAttr('srcset');
                pq($img)->attr('data-gridbox-lazyload-srcset', $srcset);
                pq($img)->addClass('lazy-load-image');
            }
        }
        $script = JUri::root().'components/com_gridbox/libraries/lazyload/js/lazyload.js';
        pq('body')->append('<script src="'.$script.'"></script>');
        $body = $doc->htmlOuter();

        return $body;
    }

    public static function compressImages($body)
    {
        $body = preg_replace('#<noscript(.*?)>(.*?)</noscript>#is', '', $body);
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        include_once JPATH_ROOT.'/components/com_gridbox/libraries/php/phpQuery/phpQuery.php';
        $doc = phpQuery::newDocument($body);
        $root = JUri::root();
        $array = explode('/', $root);
        $path = $array[count($array) - 2];
        $path = '/'.$path.'/';
        $n = 0;
        foreach (pq('img') as $img) {
            self::$breakpoint = '';
            $src = pq($img)->attr('src');
            $url = self::getCompressedImageURL($src);
            if ($url) {
                if (self::$website->compress_images == 1) {
                    $n++;
                    pq($img)->attr('src', $url);
                } else {
                    $url = $src;
                }
                if (self::$website->adaptive_images == 1 && !pq($img)->parent()->hasClass('ba-image')) {
                    $n++;
                    $srcsets = array();
                    $sizes = array();
                    foreach (self::$breakpoints as $key => $breakpoint) {
                        self::$breakpoint = $key;
                        $breakpointImg = self::getCompressedImageURL($src);
                        if (!$breakpointImg) {
                            continue;
                        }
                        $srcsets[$breakpoint] = $breakpointImg.' '.$breakpoint.'w';
                        $sizes[$breakpoint] = '(max-width: '.$breakpoint.'px) 100%';
                    }
                    if (!empty($srcsets)) {
                        ksort($srcsets);
                        ksort($sizes);
                        $srcset = implode(', ', $srcsets);
                        $size = implode(', ', $sizes);
                        $srcset .= ', '.$url.' '.self::$website->images_max_size.'w';
                        pq($img)->attr('srcset', $srcset);
                        pq($img)->attr('sizes', $size);
                    }
                }
            }
        }
        $styleStr = '';
        foreach (pq('[style*="background-image"]') as $ind => $value) {
            self::$breakpoint = '';
            $style = pq($value)->attr('style');
            preg_match_all('/url\(([^\)]*)\)/', $style, $matches);
            if (!empty($matches)) {
                $src = $matches[1][0];
                $url = self::getCompressedImageURL($src);
                if ($url) {
                    if (self::$website->compress_images == 1) {
                        $n++;
                    } else {
                        $url = $src;
                    }
                    if (self::$website->adaptive_images == 1) {
                        $n++;
                        pq($value)->addClass('ba-adaptive-image-'.($ind + 1));
                        pq($value)->removeAttr('style');
                        $styleStr .= '.ba-adaptive-image-'.($ind + 1).' {background-image: url('.$url.');}';
                        foreach (self::$breakpoints as $key => $breakpoint) {
                            self::$breakpoint = $key;
                            $url = self::getCompressedImageURL($src);
                            if (empty($url)) {
                                continue;
                            }
                            $styleStr .= "@media (max-width: ".$breakpoint."px) {";
                            $styleStr .= '.ba-adaptive-image-'.($ind + 1).' {background-image: url('.$url.');}';
                            $styleStr .= "}";
                        }
                    } else if (self::$website->compress_images == 1) {
                        $style = str_replace($matches[0][0], 'url('.$url.')', $style);
                        pq($value)->attr('style', $style);
                    }
                }
            }
        }
        if (!empty($styleStr)) {
            $styleStr = '<style type="text/css" data-id="adaptive-images">'.$styleStr.'</style>';
            pq('head')->append($styleStr);
        }
        if ($n == 0) {

            return false;
        } else {
            $body = $doc->htmlOuter();

            return $body;
        }
    }

    public static function getCompressedImageURL($src, $bg = false)
    {
        $root = JUri::root();
        $array = explode('/', $root);
        $path = $array[count($array) - 2];
        $path = '/'.$path.'/';
        if ($pos1 = strpos($src, '?')) {
            $src = substr($src, 0, $pos1);
        }
        if (strpos($src, $path) !== false || is_file(JPATH_ROOT.'/'.$src)) {
            $ext = JFile::getExt($src);
            $pngFlag = true;
            $gd_info = gd_info();
            if ($ext == 'png' && self::$website->adaptive_images == 1 && !empty(self::$breakpoint) && self::$breakpoint != 'desktop'
                && self::$website->adaptive_images_webp == 1 && $ext != 'webp' && $gd_info['WebP Support']) {
                $pngFlag = false;
            } else if ($ext == 'png' && self::$website->compress_images == 1
                && (empty(self::$breakpoint) || self::$breakpoint == 'desktop')
                && self::$website->compress_images_webp == 1 && $ext != 'webp' && $gd_info['WebP Support']) {
                $pngFlag = false;
            }
            if ($pngFlag && $ext != 'jpg' && $ext != 'jpeg' && $ext != 'webp') {
                
                return false;
            }
            if (($pos = strpos($src, $path)) !== false && !$bg) {
                $file = '/'.substr($src, $pos+strlen($path));
            } else if (strpos($src, '/') !== 0) {
                $file = '/'.$src;
            } else {
                $file = $src;
            }
            $array = explode('/', $file);
            $n = count($array);
            $dir = IMAGE_PATH.'/compressed';
            $compressImage = 'compressImage';
            if (self::$website->adaptive_images == 1 && !empty(self::$breakpoint) && self::$breakpoint != 'desktop') {
                $dir .= '/'.self::$breakpoint;
                $task = str_replace('tablet', 'tb', self::$breakpoint);
                $task = str_replace('phone', 'sm', $task);
                $task = str_replace('-portrait', 'pt', $task);
                $compressImage .= $task;
            }
            for ($i = 2; $i < $n; $i++) {
                $dir .= '/'.$array[$i];
            }
            if (self::$website->adaptive_images == 1 && !empty(self::$breakpoint) && self::$breakpoint != 'desktop'
                && self::$website->adaptive_images_webp == 1 && $ext != 'webp' && $gd_info['WebP Support']) {
                $name = JFile::getName($dir);
                $name = JFile::stripExt($name);
                $dir = str_replace($name.'.'.$ext, $name.'.webp', $dir);
            } else if (self::$website->compress_images == 1 && (empty(self::$breakpoint) || self::$breakpoint == 'desktop')
                && self::$website->compress_images_webp == 1 && $ext != 'webp' && $gd_info['WebP Support']) {
                $name = JFile::getName($dir);
                $name = JFile::stripExt($name);
                $dir = str_replace($name.'.'.$ext, $name.'.webp', $dir);
            }
            if (JFile::exists(JPATH_ROOT.$file)) {
                $url = JUri::root().'index.php?option=com_gridbox&task=gridbox.'.$compressImage.'&image='.urlencode($file);

                return $url;
            } else {

                return false;
            }
        }
    }

    public static function setCommentURL($id, $table, $hash)
    {
        $meta = '';
        if ($id == 0) {
            $url = JUri::root();
        } else {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('c.*, p.title, p.page_category, p.app_id')
                ->from($table.' AS c')
                ->where('c.id = '.$id)
                ->leftJoin('#__gridbox_pages AS p ON p.id = c.page_id');
            $db->setQuery($query);
            $obj = $db->loadObject();
            if (!empty($obj)) {
                if (!empty($obj->app_id)) {
                    $query = $db->getQuery(true)
                        ->select('type')
                        ->from('#__gridbox_app')
                        ->where('id = '.$obj->app_id);
                    $db->setQuery($query);
                    $type = $db->loadResult();
                } else {
                    $type = 'single';
                }
                $url = self::getGridboxPageLinks($obj->page_id, $type, $obj->app_id, $obj->page_category);
                $url = JRoute::_($url);
                $url .= $hash.$id;
                $meta .= '<meta property="og:url" content="'.$_SERVER['REQUEST_URI'].'">';
                $meta .= '<meta property="og:type" content="article">';
                $meta .= '<meta property="og:title" content="'.$obj->title.'">';
                $meta .= '<meta property="og:description" content="'.$obj->message.'">';
            } else {
                $url = JUri::root();
            }
        }
        $doc = JFactory::getDocument();
        $str = '<html prefix="og: http://ogp.me/ns#" xmlns="http://www.w3.org/1999/xhtml" lang="'.$doc->language;
        $str .= '" dir="'.$doc->direction.'"><head><meta http-equiv="content-type" content="text/html; charset=utf-8">';
        $str .= $meta.'<script type="text/javascript">window.location.href = "'.$url.'";</script>';
        $str .= '</head><body></body></html>';
        print_r($str);exit;
    }

    public static function setMicrodata($body)
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $body = preg_replace('#<noscript(.*?)>(.*?)</noscript>#is', '', $body);
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        include_once JPATH_ROOT.'/components/com_gridbox/libraries/php/phpQuery/phpQuery.php';
        $doc = phpQuery::newDocument($body);
        $id = $input->get('id', 0, 'int');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('title, meta_title')
            ->from('#__gridbox_pages')
            ->where('`id` = '.$id);
        $db->setQuery($query);
        $item = $db->loadObject();
        $title  = !empty($item->meta_title) ? $item->meta_title : $item->title;
        $menus = $app->getMenu();
        $menu = $menus->getActive();
        if (isset($menu) && $menu->query['view'] == 'page' && $menu->query['id'] == $id) {
            $params  = $menus->getParams($menu->id);
            $page_title = $params->get('page_title');
        } else {
            $page_title = '';
        }
        if (!empty($page_title)) {
            $title = $page_title;
        }
        $sitename = $app->get('sitename');
        if ($app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $sitename, $title);
        } else if ($app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $sitename);
        }
        $title = htmlspecialchars($title, ENT_QUOTES, 'utf-8');
        pq('.ba-item-star-ratings, .ba-item-reviews')->find('meta[itemprop="name"]')->attr('content', $title);
        $body = $doc->htmlOuter();

        return $body;
    }

    public static function setCanonical($html)
    {
        $url = '';
        $input = JFactory::getApplication()->input;
        $db = JFactory::getDbo();
        $view = $input->get('view', 'page', 'string');
        $author = $input->get('author', 0, 'int');
        $tag = $input->get('tag', 0, 'int');
        if ($view == 'page') {
            $id = $input->get('id', 0, 'int');
            $query = $db->getQuery(true)
                ->select('a.id, a.type, p.page_category')
                ->from('#__gridbox_pages AS p')
                ->where('p.id = '.$id)
                ->leftJoin('`#__gridbox_app` AS a ON p.app_id = a.id');
            $db->setQuery($query);
            $app = $db->loadObject();
            $type = empty($app->type) ? 'single' : $app->type;
            $url = self::getGridboxPageLinks($id, $type, $app->id, $app->page_category);
        } else if (!empty($author)) {
            $app = $input->get('app', 0, 'int');
            $url = self::getGridboxAuthorLinks($author, $app);
        } else if (!empty($tag)) {
            $app = $input->get('app', 0, 'int');
            $url = self::getGridboxTagLinks($tag, $app);
        } else {
            $app = $input->get('app', 0, 'int');
            $id = $input->get('id', 0, 'int');
            $url = self::getGridboxCategoryLinks($id, $app);
        }
        if (!empty($url)) {
            $url = JRoute::_($url);
            $url = self::$website->canonical_domain.$url;
            $str = "\n\t<link href=\"".$url."\" rel=\"canonical\">";
            $pos = strpos($html, '</head>');
            $head = substr($html, 0, $pos);
            $body = substr($html, $pos);
            $head = str_replace('</title>', '</title>'.$str, $head);
            $html = $head.$body;
        }

        return $html;
    }

    public static function minifyJs($body)
    {
        $body = preg_replace('#<noscript(.*?)>(.*?)</noscript>#is', '', $body);
        $root = JUri::root();
        $array = explode('/', $root);
        $path = $array[count($array) - 2];
        $path = '/'.$path.'/';
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        include_once JPATH_ROOT.'/components/com_gridbox/libraries/php/phpQuery/phpQuery.php';
        $doc = phpQuery::newDocument($body);
        $js = array();
        $content = array();
        $md5 = '';
        foreach (pq('script[src*=".js"]') as $value) {
            $key = pq($value)->attr('src');
            if ($key == '/plugins/system/gdpr/assets/js/cookieconsent.min.js' || $key == '/plugins/system/gdpr/assets/js/init.js') {
                continue;
            }
            $file = $key;
            if ($pos1 = strpos($file, '?')) {
                $file = substr($file, 0, $pos1);
            }
            if (strpos($key, $path) !== false || is_file(JPATH_ROOT.'/'.$file)) {
                $js[] = $file;
                $md5 .= $file;
                pq($value)->remove();
            }
        }
        $id = md5($md5);
        if (!JFile::exists(JPATH_ROOT.'/templates/gridbox/js/min/'.$id.'.min.js')) {
            foreach ($js as $key => $src) {
                if (($pos = strpos($src, $path)) !== false) {
                    $file = JPATH_ROOT.'/'.substr($src, $pos+strlen($path));
                } else if (strpos($src, '/') !== 0) {
                    $file = JPATH_ROOT.'/'.$src;
                } else {
                    $file = JPATH_ROOT.$src;
                }
                $str = JFile::read($file);
                $str = preg_replace('/\t/', "\n", $str);
                $str = preg_replace('/\r/', "\n", $str);
                $str = preg_replace('/[ ]{2,}/', " ", $str);
                $str = preg_replace('/\n /', "\n", $str);
                $str = preg_replace('/ \n/', "\n", $str);
                $str = preg_replace('/[\n]{2,}/', "\n", $str);
                /*
                $str = preg_replace('/;\n/', ";", $str);
                $str = preg_replace('/}\n}/', "}}", $str);
                $str = preg_replace('/\n}/', "}", $str);
                $str = preg_replace('/}\n}/', "}}", $str);
                $str = preg_replace('/{\n/', "{", $str);
                $str = preg_replace('/,\n/', ",", $str);
                $str = preg_replace('/\)\n}/', ")}", $str);
                */
                $str = str_replace(' =', "=", $str);
                $str = str_replace('= ', "=", $str);
                $str = str_replace(' &&', "&&", $str);
                $str = str_replace('&& ', "&&", $str);
                $str = str_replace(') {', "){", $str);
                $str = str_replace(")\n{", "){", $str);
                $content[] = "try {".$str."} catch (err) {console.info(err);console.info('Error in file ".$src."');}\n";
            }
            $fp = fopen(JPATH_ROOT.'/templates/gridbox/js/min/'.$id.'.min.js', 'w+');
            foreach ($content as $key => $string) {
                fwrite($fp, $string);
            }
            fclose($fp);
        }
        $src = JUri::root().'templates/gridbox/js/min/'.$id.'.min.js';
        $str = '<script src="'.$src.'" type="text/javascript"></script>';
        pq('head link[href*="gridbox/favicon.ico"]')->after($str);
        $body = $doc->htmlOuter();

        return $body;
    }

    public static function setDeferredLoading($body)
    {
        $body = preg_replace('#<noscript(.*?)>(.*?)</noscript>#is', '', $body);
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        include_once JPATH_ROOT.'/components/com_gridbox/libraries/php/phpQuery/phpQuery.php';
        $doc = phpQuery::newDocument($body);
        $gbody = pq('body');
        $gbody->append("\n");
        foreach (pq('link[href*=".css"], link[href*="fonts.googleapis]') as $value) {
            $gbody->append($value);
        }
        foreach (pq('script')->not('.exclude-deffer') as $value) {
            $gbody->append($value);
        }
        $gbody->attr('style', 'left: 300vw; position: absolute; overflow: hidden; margin: 0;');
        $body = $doc->htmlOuter();
        $colors = '';
        foreach (self::$colorVariables as $key => $value) {
            $colors .= str_replace('@', '--', $key).': '.$value->color.';';
        }
        $body = str_replace('<html', '<html style="'.$colors.'"', $body);

        return $body;
    }

    public static function minifyCss($body)
    {
        $body = preg_replace('#<noscript(.*?)>(.*?)</noscript>#is', '', $body);
        $root = JUri::root();
        $array = explode('/', $root);
        $path = $array[count($array) - 2];
        $path = '/'.$path.'/';
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        include_once JPATH_ROOT.'/components/com_gridbox/libraries/php/phpQuery/phpQuery.php';
        $doc = phpQuery::newDocument($body);
        $css = array();
        $content = array();
        $import = '';
        $md5 = '';
        $time = '';
        foreach (pq('link[href*=".css"]') as $value) {
            $key = pq($value)->attr('href');
            if ($key == '/plugins/system/gdpr/assets/css/cookieconsent.min.css' ||
                $key == '/plugins/system/gdpr/assets/css/jquery.fancybox.min.css') {
                continue;
            }
            $file = $key;
            if ($pos1 = strpos($file, '?')) {
                if (empty($time) && strpos($file, 'com_gridbox')) {
                    $time = substr($file, $pos1);
                }
                $file = substr($file, 0, $pos1);
            }
            if (strpos($key, $path) !== false || is_file(JPATH_ROOT.'/'.$file)) {
                $css[] = $file;
                $md5 .= $file;
                pq($value)->remove();
            }
        }
        $id = md5($md5);
        if (!JFile::exists(JPATH_ROOT.'/templates/gridbox/css/min/'.$id.'.min.css')) {
            foreach ($css as $key => $link) {
                $filePath = '';
                if (($pos = strpos($link, $path)) !== false) {
                    $filePath = '/'.substr($link, $pos+strlen($path));
                    $file = JPATH_ROOT.$filePath;
                } else if (strpos($link, '/') !== 0) {
                    $filePath = '/'.$link;
                    $file = JPATH_ROOT.'/'.$link;
                } else {
                    $filePath = $link;
                    $file = JPATH_ROOT.$link;
                }
                $pos2 = strrpos($filePath, '/');
                $filePath = substr($filePath, 0, $pos2);
                $str = JFile::read($file);
                $str = preg_replace("/[\n\t\r]+/", ' ', $str);
                $str = preg_replace("/\n+/", ' ', $str);
                $str = preg_replace('/ +/', ' ', $str);
                preg_match_all('/url\(([^\)]*)\)/', $str, $matches);
                foreach ($matches[1] as $key => $match) {
                    $image = preg_replace('/["\']/', '', $match);
                    if (strpos($image, 'http') !== 0 && strpos($image, '//') !== 0) {
                        if (strpos($match, 'danger.png')) {
                            print_r($image);exit();
                        }
                        $image = '../../../..'.$filePath.'/'.$image;
                        $str = str_replace($matches[0][$key], 'url('.$image.')', $str);
                    }
                }
                preg_match_all('/@import +url\(([^\)]*)\)[;]*/', $str, $matches);
                foreach ($matches[0] as $key => $match) {
                    $import .= $match.' ';
                }
                $content[] = $str;

            }
            $fp = fopen(JPATH_ROOT.'/templates/gridbox/css/min/'.$id.'.min.css', 'w+');
            fwrite($fp, $import);
            foreach ($content as $key => $string) {
                fwrite($fp, $string);
            }
            fclose($fp);
        }
        $src = JUri::root().'templates/gridbox/css/min/'.$id.'.min.css'.$time;
        $str = '<link href="'.$src.'" rel="stylesheet" type="text/css" />';
        pq('head link[href*="gridbox/favicon.ico"]')->after($str);
        $body = $doc->htmlOuter();

        return $body;
    }

    public static function getOpenWeatherKey()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('`key`')
            ->from('#__gridbox_api')
            ->where('service = '.$db->quote('openweathermap'));
        $db->setQuery($query);
        $key = $db->loadResult();

        return $key;
    }

    public static function getYandexMapsKey()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('`key`')
            ->from('#__gridbox_api')
            ->where('service = '.$db->quote('yandex_maps'));
        $db->setQuery($query);
        $key = $db->loadResult();

        return $key;
    }

    public static function getDefaultElementsStyle()
    {
        $dir = JPATH_COMPONENT.'/libraries/json/';
        $object = array();
        $files = JFolder::files($dir);
        foreach ($files as $file) {
            $str = JFile::read($dir.$file);
            $obj = json_decode($str);
            if (isset($obj->type)) {
                $object[$obj->type] = $obj;
            } else {
                foreach ($obj as $key => $value) {
                    if (is_object($value) && isset($value->type) && !isset($object[$value->type])) {
                        $object[$value->type] = $value;
                    }
                }
            }
        }
        $dir = JPATH_COMPONENT.'/views/layout/apps/blog/';
        $str = JFile::read($dir.'app.json');
        $obj = json_decode($str);
        foreach ($obj as $item) {
            if (!isset($object[$item->type])) {
                $object[$item->type] = $item;
            }
        }
        $str = JFile::read($dir.'default.json');
        $obj = json_decode($str);
        foreach ($obj as $item) {
            if (!isset($object[$item->type])) {
                $object[$item->type] = $item;
            }
        }
        $dir = JPATH_COMPONENT.'/views/layout/system/';
        $str = JFile::read($dir.'404.json');
        $obj = json_decode($str);
        foreach ($obj as $item) {
            if (!isset($object[$item->type])) {
                $object[$item->type] = $item;
            }
        }
        $str = JFile::read($dir.'offline.json');
        $obj = json_decode($str);
        foreach ($obj as $item) {
            if (!isset($object[$item->type])) {
                $object[$item->type] = $item;
            }
        }
        $str = json_encode($object);

        return $str;
    }

    public static function getDefaultElementsBox()
    {
        $dir = JPATH_COMPONENT.'/views/layout/';
        $array = array();
        $files = JFolder::files($dir);
        $span = array(12);
        $count = $data = 1;
        $obj = new stdClass();
        $obj->items = new stdClass();
        $now = 0;
        $edit_type = '';
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        include_once JPATH_ROOT.'/components/com_gridbox/libraries/php/phpQuery/phpQuery.php';
        foreach ($files as $key => $file) {
            if ($file == 'category-intro.php' || $file == 'intro-category-wrapper.php' || $file == 'cookies.php') {
                continue;
            }
            include $dir.$file;
            if (isset($out)) {
                $dom = phpQuery::newDocument($out);
                foreach (pq('.ba-item') as $value) {
                    $className = pq($value)->attr('class');
                    preg_match('/[-\w]+/', $className, $type);
                    if (!empty($type) && !in_array($type[0], $array)) {
                        $object = new stdClass();
                        $object->edit = '<div class="ba-edit-item">'.trim(pq($value)->find('> .ba-edit-item')->html()).'</div>';
                        $object->box = '<div class="ba-box-model">'.trim(pq($value)->find('> .ba-box-model')->html()).'</div>';
                        $array[$type[0]] = $object;
                    }
                }
            }
        }
        $str = include $dir.'section.php';
        $dom = phpQuery::newDocument($out);
        $obj = new stdClass();
        $obj->edit = '<div class="ba-edit-item">'.trim(pq('.ba-section')->find('> .ba-edit-item')->html()).'</div>';
        $obj->box = '<div class="ba-box-model">'.trim(pq('.ba-section')->find('> .ba-box-model')->html()).'</div>';
        $array['ba-section'] = $obj;
        $obj = new stdClass();
        $obj->edit = '<div class="ba-edit-item">'.trim(pq('.ba-row')->find('> .ba-edit-item')->html()).'</div>';
        $obj->box = '<div class="ba-box-model">'.trim(pq('.ba-row')->find('> .ba-box-model')->html()).'</div>';
        $array['ba-row'] = $obj;
        $obj = new stdClass();
        $obj->edit = '<div class="ba-edit-item">'.trim(pq('.ba-grid-column')->find('> .ba-edit-item')->html()).'</div>';
        $obj->box = '<div class="ba-box-model">'.trim(pq('.ba-grid-column')->find('> .ba-box-model')->html()).'</div>';
        $array['ba-grid-column'] = $obj;
        $str = json_encode($array);

        return $str;
    }

    public static function setAppLicenseBalbooa($data)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_api')
            ->where('service = '.$db->quote('balbooa_activation'));
        $db->setQuery($query);
        $balbooa = $db->loadObject();
        $balbooa->key = json_decode($balbooa->key);
        $balbooa->key->data = $data;
        if (empty($data)) {
            unset($balbooa->key->data);
        }
        $balbooa->key = json_encode($balbooa->key);
        $db->updateObject('#__gridbox_api', $balbooa, 'id');
    }

    public static function getFonts()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('font, styles, custom_src')
            ->from('`#__gridbox_fonts`')
            ->order($db->quoteName('font') . ' ASC');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $fonts = new stdClass();
        foreach ($items as $item) {
            if (empty($item->font)) {
                continue;
            }
            if (!isset($fonts->{$item->font})) {
                $fonts->{$item->font} = array();
            }
            $fonts->{$item->font}[] = $item;
        }
        foreach ($fonts as $key => $value) {
            usort($value, function($a, $b){
                if ($a->styles == $b->styles) {
                    return 0;
                }

                return ($a->styles < $b->styles) ? -1 : 1;
            });
            $fonts->{$key} = $value;
        }
        $str = json_encode($fonts);
        
        return $str;
    }

    public static function checkCreatePage($id)
    {
        $app = (int)$id;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('type')
            ->from('#__gridbox_app')
            ->where('id = '.$app);
        $db->setQuery($query);
        $type = $db->loadResult();
        
        return $type;
    }

    public static function getCorrectColor($key)
    {
        return strpos($key, '@') === false ? $key : 'var('.str_replace('@', '--', $key).')';
    }

    public static function compareFlipboxPresets($obj, $object)
    {
        $obj->parallax = $object->parallax;
        $obj->desktop->background = $object->desktop->background;
        $obj->desktop->overlay = $object->desktop->overlay;
        foreach (self::$breakpoints as $key => $value) {
            if (isset($object->{$key}->background)) {
                $obj->{$key}->background = $object->{$key}->background;
            }
            if (isset($object->{$key}->overlay)) {
                $obj->{$key}->overlay = $object->{$key}->overlay;
            }
        }
    }

    public static function comparePresets($obj)
    {
        if (!empty($obj->preset) && isset(self::$presets->{$obj->type}) && isset(self::$presets->{$obj->type}->{$obj->preset})) {
            $object = self::$presets->{$obj->type}->{$obj->preset};
            foreach (self::$presets->{$obj->type}->{$obj->preset}->data as $ind => $data) {
                if ($ind == 'desktop' || isset(self::$breakpoints->{$ind})) {
                    foreach ($data as $key => $value) {
                        $obj->{$ind}->{$key} = $value;
                    }
                } else if ($obj->type == 'flipbox' && $ind == 'sides') {
                    self::compareFlipboxPresets($obj->sides->backside, $object->data->{$ind}->backside);
                    self::compareFlipboxPresets($obj->sides->frontside, $object->data->{$ind}->frontside);
                } else {
                    $obj->{$ind} = $data;
                }
            }
        }
    }

    public static function getVersion()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('manifest_cache')
            ->from('#__extensions')
            ->where("type = " .$db->quote('component'))
            ->where('element = ' .$db->quote('com_gridbox'));
        $db->setQuery($query);
        $manifest = $db->loadResult();
        $obj = json_decode($manifest);

        return $obj->version;
    }

    public static function getGlobalItems()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('item')
            ->from('`#__gridbox_library`')
            ->where('`global_item` <> ' .$db->quote(''));
        $db->setQuery($query);
        $items = $db->loadObjectList();

        return $items;
    }

    public static function setBreakpoints()
    {
        if (self::$breakpoints) {
            return;
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_website')
            ->where('1');
        $db->setQuery($query);
        $website = $db->loadObject();
        if ($website->breakpoints != 'null' && !empty($website->breakpoints)) {
            $obj = json_decode($website->breakpoints);
        } else {
            $obj = new stdClass();
            $obj->laptop = 1200;
            $obj->tablet = 768;
            $obj->{'tablet-portrait'} = 768;
            $obj->phone = 480;
            $obj->{'phone-portrait'} = 480;
            $obj->menuBreakpoint = 768;
            self::siteRules($obj);
        }
        $params = JComponentHelper::getParams('com_gridbox');
        $image_path = $params->get('image_path', '');
        if (!empty($image_path)) {
            $website->image_path = $params->get('image_path', '');
            $website->file_types = $params->get('file_types', '');
            $website->email_encryption = $params->get('email_encryption', 0);
            $db->updateObject('#__gridbox_website', $website, 'id');
            $query = $db->getQuery(true)
                ->update('#__extensions')
                ->set('params = '.$db->quote('{}'))
                ->where('element = '.$db->quote('com_gridbox'))
                ->where('type = '.$db->quote('component'));
            $db->setQuery($query)
                ->execute();
        }
        if (empty($website->image_path)) {
            $website->image_path = 'images';
        }
        if (empty($website->file_types)) {
            $website->file_types = 'csv, doc, gif, ico, jpg, jpeg, pdf, png, txt, xls, svg, mp4, webp';
        }
        self::$website = $website;
        self::$dateFormat = $website->date_format;
        self::$menuBreakpoint = $obj->menuBreakpoint;
        unset($obj->menuBreakpoint);
        if (!isset($obj->laptop)) {
            $object = new stdClass();
            $object->laptop = 1200;
            $object->tablet = $obj->tablet;
            $object->{'tablet-portrait'} = $obj->{'tablet-portrait'};
            $object->phone = $obj->phone;
            $object->{'phone-portrait'} = $obj->{'phone-portrait'};
            $obj = $object;
        }
        self::$breakpoints = $obj;
        self::getSystemApps();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_api')
            ->where('service = '.$db->quote('balbooa_activation'));
        $db->setQuery($query);
        $balbooa = $db->loadObject();
        if (!$balbooa) {
            $obj = new stdClass();
            $obj->key = self::checkGridboxState();
            $obj->service = 'balbooa_activation';
            $db->insertObject('#__gridbox_api', $obj);
        }
        include JPATH_ROOT.'/components/com_gridbox/helpers/store.php';
        self::$storeHelper = new store();
        self::$store = self::$storeHelper->getSettings();
        $rates = new stdClass();
        $rates->categories = array();
        $rates->empty = array();
        foreach (self::$store->tax->rates as $key => $rate) {
            $rate->key = $key;
            if (!empty($rate->categories)) {
                $rates->categories[] = $rate;
            } else {
                $rates->empty[] = $rate;
            }
        }
        self::$taxRates = $rates;
    }

    public static function checkResponsive()
    {
        $file = JPATH_ROOT. '/templates/gridbox/css/storage/responsive.css';
        if (!JFile::exists($file)) {
            $empty = new stdClass();
            $obj = self::object_extend($empty, self::$breakpoints);
            $obj->menuBreakpoint = self::$menuBreakpoint;
            self::siteRules($obj);
        }
    }

    public static function setMediaRules($obj, $key, $callback)
    {
        $empty = new stdClass();
        $desktop = self::object_extend($empty, $obj->desktop);
        $type = 'theme';
        if (isset($obj->type)) {
            $type = $obj->type;
        }
        $str = '';
        if ((bool)self::$website->disable_responsive) {
            return $str;
        }
        foreach (self::$breakpoints as $ind => $value) {
            self::$breakpoint = $ind;
            if (!isset($obj->{$ind})) {
                $obj->{$ind} = new stdClass();
            }
            $object = self::object_extend($desktop, $obj->{$ind});
            $str .= "@media (max-width: ".$value."px) {";
            $str .= call_user_func(array('gridboxHelper', $callback), $object, $key, $type);
            $str .= "}";
            $desktop = self::object_extend($empty, $object);
        }
        
        return $str;
    }

    public static function stringURLSafe($string, $language = '')
    {
        if (\JFactory::getConfig()->get('unicodeslugs') == 1) {
            $output = \JFilterOutput::stringURLUnicodeSlug($string);
        } else {
            if ($language === '*' || $language === '') {
                $languageParams = JComponentHelper::getParams('com_languages');
                $language = $languageParams->get('site');
            }
            $output = \JFilterOutput::stringURLSafe($string, $language);
        }

        return $output;
    }

    public static function getAlias($alias, $table, $name = 'page_alias', $id = 0)
    {
        $originAlias = $alias;
        $alias = self::stringURLSafe(trim($alias));
        if (empty($alias)) {
            $alias = $originAlias;
            $alias = self::replace($alias);
            $alias = JFilterOutput::stringURLSafe($alias);
        }
        if (empty($alias)) {
            $alias = date('Y-m-d-H-i-s');
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from($table)
            ->where($db->quoteName($name).' = '.$db->quote($alias))
            ->where('`id` <> ' .$db->quote($id));
        $db->setQuery($query);
        $id = $db->loadResult();
        if (!empty($id)) {
            $alias = JString::increment($alias);
            $alias = self::getAlias($alias, $table, $name);
        }
        return $alias;
    }

    public static function checkGridboxState()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('`key`')
            ->from('#__gridbox_api')
            ->where('service = '.$db->quote('balbooa'));
        $db->setQuery($query);
        $balbooa = $db->loadResult();

        return $balbooa;
    }

    public static function sectionRules($obj, $up = '../../../../')
    {

        $str = '';
        self::$up = $up;
        foreach ($obj as $key => $value) {
            $str .= self::getPageCSS($value, $key);
        }
        return $str;
    }

    public static function presetsCompatibility($obj)
    {
        if ((empty($obj->type) || $obj->type == 'side-navigation-menu') && isset($obj->hamburger)) {
            $obj->layout->type = $obj->type;
            $obj->type = 'one-page';
        }
        switch ($obj->type) {
            case 'overlay-section':
            case 'lightbox':
            case 'cookies':
            case 'mega-menu-section':
            case 'row':
            case 'section':
            case 'footer':
            case 'header':
            case 'column':
                if (!isset($obj->desktop->full)) {
                    $obj->desktop->full = new stdClass();
                    $obj->desktop->full->fullscreen = $obj->desktop->fullscreen == '1';
                    if (isset($obj->{'max-width'})) {
                        $obj->desktop->full->fullwidth = $obj->{'max-width'} == '100%';
                    }
                    $obj->desktop->image = new stdClass();
                    $obj->desktop->image->image = $obj->desktop->background->image->image;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            if (isset($obj->{$ind}->fullscreen)) {
                                $obj->{$ind}->full = new stdClass();
                                $obj->{$ind}->full->fullscreen = $obj->{$ind}->fullscreen == '1';
                            }
                        }
                        if (isset($obj->{$ind}->background) && isset($obj->{$ind}->background->image)
                            && isset($obj->{$ind}->background->image->image)) {
                            $obj->{$ind}->image = new stdClass();
                            $obj->{$ind}->image->image = $obj->{$ind}->background->image->image;
                        }
                    }
                    if ($obj->type == 'column') {
                        foreach (self::$breakpoints as $ind => $value) {
                            if (isset($obj->{$ind}) && isset($obj->{$ind}->{'column-width'})) {
                                $obj->{$ind}->span = new stdClass();
                                $obj->{$ind}->span->width = $obj->{$ind}->{'column-width'};
                            }
                        }
                    } else if ($obj->type == 'row') {
                        $obj->desktop->view = new stdClass();
                        $obj->desktop->view->gutter = $obj->desktop->gutter == '1';
                        foreach (self::$breakpoints as $ind => $value) {
                            if (isset($obj->{$ind}) && isset($obj->{$ind}->gutter)) {
                                $obj->{$ind}->view = new stdClass();
                                $obj->{$ind}->view->gutter = $obj->{$ind}->gutter == '1';
                            }
                        }
                    } else if ($obj->type == 'overlay-section' || $obj->type == 'lightbox' || $obj->type == 'cookies') {
                        $obj->lightbox = new stdClass();
                        if (isset($obj->layout) && isset($obj->position)) {
                            $obj->lightbox->layout = $obj->layout;
                            $obj->lightbox->position = $obj->position;
                        } else if (isset($obj->layout)) {
                            $obj->lightbox->layout = $obj->layout;
                        } else if (isset($obj->position)) {
                            $obj->lightbox->layout = $obj->position;
                        }
                        if (isset($obj->{'background-overlay'})) {
                            $obj->lightbox->background = $obj->{'background-overlay'};
                        }
                        $obj->desktop->view = new stdClass();
                        $obj->desktop->view->width = $obj->desktop->width;
                        if (isset($obj->desktop->height)) {
                            $obj->desktop->view->height = $obj->desktop->height;
                        }
                        foreach (self::$breakpoints as $ind => $value) {
                            if (isset($obj->{$ind})) {
                                $obj->{$ind}->view = new stdClass();
                                if (isset($obj->{$ind}->width)) {
                                    $obj->{$ind}->view->width = $obj->{$ind}->width;
                                }
                                if (isset($obj->{$ind}->height)) {
                                    $obj->{$ind}->view->height = $obj->{$ind}->height;
                                }
                            }
                        }
                    } else if ($obj->type == 'mega-menu-section') {
                        $obj->view = new stdClass();
                        $obj->view->width = $obj->width;
                        $obj->view->position = $obj->position;
                    }
                }
                break;
            case 'button':
            case 'overlay-button':
            case 'scroll-to':
            case 'scroll-to-top':
                if (!isset($obj->desktop->icons)) {
                    $obj->desktop->icons = new stdClass();
                    $obj->desktop->icons->size = $obj->desktop->size;
                    if ($obj->type == 'scroll-to') {
                        $obj->desktop->icons->align = 'center';
                    }
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind}) && isset($obj->{$ind}->size)) {
                            $obj->{$ind}->icons = new stdClass();
                            $obj->{$ind}->icons->size = $obj->{$ind}->size;
                        }
                    }
                }
                if ($obj->type == 'scroll-to-top' && !isset($obj->text)) {
                    $obj->text =  new stdClass();
                    $obj->text->align = $obj->{"scrolltop-align"};
                }
                if ($obj->type == 'scroll-to' && !isset($obj->desktop->typography)) {
                    $obj->desktop->icons->position = 'after';
                    $typography = '{"font-family":"@default","font-size":10,"font-style":"normal","font-weight":"700",';
                    $typography .= '"letter-spacing":4,"line-height":26,"text-align":"center","text-decoration":"none",';
                    $typography .= '"text-transform":"uppercase"}';
                    $obj->desktop->typography = json_decode($typography);
                    $obj->desktop->typography->{"text-align"} = $obj->desktop->icons->align;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind}) && isset($obj->{$ind}->icons) && isset($obj->{$ind}->align)) {
                            $obj->{$ind}->typography = new stdClass();
                            $obj->{$ind}->typography->{"text-align"} = $obj->{$ind}->icons->align;
                        }
                    }
                }
            case 'scroll-to':
            case 'scroll-to-top':
            case 'tags':
            case 'post-tags':
            case 'icon':
            case 'social-icons':
                if (!isset($obj->desktop->normal)) {
                    $obj->desktop->normal = new stdClass();
                    $obj->desktop->normal->color = $obj->desktop->color;
                    $obj->desktop->normal->{'background-color'} = $obj->desktop->{'background-color'};
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            if (isset($obj->{$ind}->color) || isset($obj->{$ind}->{'background-color'})) {
                                $obj->{$ind}->normal = new stdClass();
                                if (isset($obj->{$ind}->color)) {
                                    $obj->{$ind}->normal->color = $obj->{$ind}->color;
                                }
                                if (isset($obj->{$ind}->{'background-color'})) {
                                    $obj->{$ind}->normal->{'background-color'} = $obj->{$ind}->{'background-color'};
                                }
                            }
                        }
                    }
                }
                break;
            case 'counter':
            case 'countdown':
                if (!isset($obj->desktop->background)) {
                    $obj->desktop->background = new stdClass();
                    $obj->desktop->background->color = $obj->desktop->color;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind}) && isset($obj->{$ind}->color)) {
                            $obj->{$ind}->background = new stdClass();
                            $obj->{$ind}->background->color = $obj->{$ind}->color;
                        }
                    }
                }
                break;
            case 'categories':
                if (!isset($obj->desktop->view)) {
                    $obj->desktop->view = new stdClass();
                    $obj->desktop->view->counter = $obj->desktop->counter;
                    $obj->desktop->view->sub = $obj->desktop->sub;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            $obj->{$ind}->view = new stdClass();
                            if (isset($obj->{$ind}->counter)) {
                                $obj->{$ind}->view->counter = $obj->{$ind}->counter;
                            }
                            if (isset($obj->{$ind}->sub)) {
                                $obj->{$ind}->view->sub = $obj->{$ind}->sub;
                            }
                        }
                    }
                }
                if (!isset($obj->layout)) {
                    $obj = self::prepareBlogCategories($obj);
                }
                break;
            case 'carousel':
            case 'slideset':
                if (!isset($obj->desktop->view)) {
                    $obj->desktop->view = new stdClass();
                    $obj->desktop->gutter = ($obj->gutter != '');
                    $obj->desktop->view->height = $obj->desktop->height;
                    $obj->desktop->view->size = $obj->desktop->size;
                    $obj->desktop->view->dots = $obj->desktop->dots->enable;
                    $obj->desktop->view->arrows = $obj->desktop->arrows->enable;
                    $obj->desktop->overlay =  new stdClass();
                    $obj->desktop->overlay->color = $obj->desktop->caption->color;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            $obj->{$ind}->view = new stdClass();
                            if (isset($obj->{$ind}->overflow)) {
                                $obj->{$ind}->view->overflow = $obj->{$ind}->overflow;
                            }
                            if (isset($obj->{$ind}->height)) {
                                $obj->{$ind}->view->height = $obj->{$ind}->height;
                            }
                            if (isset($obj->{$ind}->size)) {
                                $obj->{$ind}->view->size = $obj->{$ind}->size;
                            }
                        }
                    }
                }
                break;
            case 'slideshow':
                if (!isset($obj->desktop->view)) {
                    $obj->desktop->view = new stdClass();
                    $obj->desktop->view->fullscreen = $obj->desktop->fullscreen;
                    $obj->desktop->view->height = $obj->desktop->height;
                    $obj->desktop->view->size = $obj->desktop->size;
                    $obj->desktop->view->dots = $obj->desktop->dots->enable;
                    $obj->desktop->view->arrows = $obj->desktop->arrows->enable;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            $obj->{$ind}->view = new stdClass();
                            if (isset($obj->{$ind}->fullscreen)) {
                                $obj->{$ind}->view->fullscreen = $obj->{$ind}->fullscreen;
                            }
                            if (isset($obj->{$ind}->height)) {
                                $obj->{$ind}->view->height = $obj->{$ind}->height;
                            }
                            if (isset($obj->{$ind}->size)) {
                                $obj->{$ind}->view->size = $obj->{$ind}->size;
                            }
                        }
                    }
                }
                break;
            case 'accordion':
                if (!isset($obj->desktop->icon)) {
                    $obj->desktop->icon = new stdClass();
                    $obj->desktop->icon->position = $obj->{'icon-position'};
                    $obj->desktop->icon->size = $obj->desktop->size;
                    $color = $obj->desktop->background;
                    $obj->desktop->background = new stdClass();
                    $obj->desktop->background->color = $color;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            if (isset($obj->{$ind}->size)) {
                                $obj->{$ind}->icon = new stdClass();
                                $obj->{$ind}->icon->size = $obj->{$ind}->size;
                            }
                            if (isset($obj->{$ind}->background)) {
                                $color = $obj->{$ind}->background;
                                $obj->{$ind}->background = new stdClass();
                                $obj->{$ind}->background->color = $color;
                            }
                        }
                    }
                }
                break;
            case 'tabs':
                if (!isset($obj->desktop->icon)) {
                    $obj->desktop->icon = new stdClass();
                    $obj->desktop->icon->position = $obj->{'icon-position'};
                    $obj->desktop->icon->size = $obj->desktop->size;
                    $color = $obj->desktop->background;
                    $obj->desktop->background = new  stdClass();
                    $obj->desktop->background->color = $color;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            if (isset($obj->{$ind}->size)) {
                                $obj->{$ind}->icon = new stdClass();
                                $obj->{$ind}->icon->size = $obj->{$ind}->size;
                            }
                            if (isset($obj->{$ind}->background)) {
                                $color = $obj->{$ind}->background;
                                $obj->{$ind}->background = new stdClass();
                                $obj->{$ind}->background->color = $color;
                            }
                        }
                    }
                }
                break;
            case 'image':
                if (!isset($obj->desktop->style)) {
                    if (!isset($obj->desktop->width)) {
                        $obj->desktop->width = $obj->width;
                    }
                    $obj->popup = (bool)($obj->lightbox->enable * 1);
                    $obj->desktop->style = new stdClass();
                    $obj->desktop->style->width = $obj->desktop->width;
                    $obj->desktop->style->align = $obj->align;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            $obj->{$ind}->style = new stdClass();
                            if (isset($obj->{$ind}->width)) {
                                $obj->{$ind}->style->width = $obj->{$ind}->width;
                            }
                        }
                    }
                }
                break;
            case 'simple-gallery':
                if (!isset($obj->desktop->view)) {
                    $obj->desktop->view = new stdClass();
                    $obj->desktop->view->height = $obj->desktop->height;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            $obj->{$ind}->view = new stdClass();
                            if (isset($obj->{$ind}->count)) {
                                $obj->{$ind}->view->count = $obj->{$ind}->count;
                            }
                            if (isset($obj->{$ind}->height)) {
                                $obj->{$ind}->view->height = $obj->{$ind}->height;
                            }
                            if (isset($obj->{$ind}->gutter)) {
                                $obj->{$ind}->view->gutter = $obj->{$ind}->gutter;
                            }
                        }
                    }
                }
                break;
            case 'weather':
                if (!isset($obj->desktop->view)) {
                    $obj->desktop->view = new stdClass();
                    $obj->desktop->view->layout = $obj->layout;
                    $obj->desktop->view->forecast = $obj->desktop->forecast;
                    $obj->desktop->view->wind = $obj->desktop->wind;
                    $obj->desktop->view->humidity = $obj->desktop->humidity;
                    $obj->desktop->view->pressure = $obj->desktop->pressure;
                    $obj->desktop->view->{'sunrise-wrapper'} = $obj->desktop->{'sunrise-wrapper'};
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            $obj->{$ind}->view = new stdClass();
                            if (isset($obj->{$ind}->forecast)) {
                                $obj->{$ind}->view->forecast = $obj->{$ind}->forecast;
                            }
                            if (isset($obj->{$ind}->wind)) {
                                $obj->{$ind}->view->forecast = $obj->{$ind}->wind;
                            }
                            if (isset($obj->{$ind}->humidity)) {
                                $obj->{$ind}->view->humidityhumidity = $obj->{$ind}->humidity;
                            }
                            if (isset($obj->{$ind}->pressure)) {
                                $obj->{$ind}->view->pressure = $obj->{$ind}->pressure;
                            }
                            if (isset($obj->{$ind}->{'sunrise-wrapper'})) {
                                $obj->{$ind}->view->{'sunrise-wrapper'} = $obj->{$ind}->{'sunrise-wrapper'};
                            }
                        }
                    }
                }
                break;
            case "menu":
                if (!isset($obj->desktop->nav)) {
                    $nav ='{"padding":{"bottom":"15","left":"15","right":"15","top":"15"},"margin":{"left":"0","right":"0"}';
                    $nav .= ',"icon":{"size":24},"border":{"bottom":"0","left":"0","right":"0","top":"0","color":"#000000",';
                    $nav .= '"style":"solid","radius":"0","width":"0"},"normal":{"color":"color","background":"rgba(0,0,0,';
                    $nav .= '0)"},"hover":{"color":"color","background":"rgba(0,0,0,0)"}}';
                    $obj->desktop->nav = json_decode($nav);
                    $obj->desktop->nav->normal->color = $obj->desktop->{'nav-typography'}->color;
                    $obj->desktop->nav->hover->color = $obj->desktop->{'nav-hover'}->color;
                    $sub = '{"padding":{"bottom":"10","left":"20","right":"20","top":"10"},"icon":{"size":24},"border":{';
                    $sub .= '"bottom":"0","left":"0","right":"0","top":"0","color":"#000000","style":"solid","radius":"0",';
                    $sub .= '"width":"0"},"normal":{"color":"color","background":"rgba(0,0,0,0)"},"hover":{"color":"color",';
                    $sub .= '"background":"rgba(0,0,0,0)"}}';
                    $obj->desktop->sub = json_decode($sub);
                    $obj->desktop->sub->normal->color = $obj->desktop->{'sub-typography'}->color;
                    $obj->desktop->sub->hover->color = $obj->desktop->{'sub-hover'}->color;
                    $dropdown = '{"width":250,"animation":{"effect":"fadeInUp","duration":"0.2"},"padding":{"bottom":"10",';
                    $dropdown .= '"left":"0","right":"0","top":"10"}}';
                    $obj->desktop->dropdown = json_decode($dropdown);
                }
                if (!isset($obj->desktop->background)) {
                    $obj->desktop->background = new stdClass();
                    $obj->desktop->background->color = $obj->desktop->{'background-color'};
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind}) && isset($obj->{$ind}->{'background-color'})) {
                            $obj->{$ind}->background = new stdClass();
                            $obj->{$ind}->background->color = $obj->{$ind}->{'background-color'};
                        }
                    }
                    $layout = $obj->layout;
                    $obj->layout = new stdClass();
                    $obj->layout->layout = $layout;
                }
                break;
            case "one-page":
                if (!isset($obj->desktop->nav)) {
                    $nav ='{"padding":{"bottom":"15","left":"15","right":"15","top":"15"},"margin":{"left":"0","right":"0"}';
                    $nav .= ',"icon":{"size":24},"border":{"bottom":"0","left":"0","right":"0","top":"0","color":"#000000",';
                    $nav .= '"style":"solid","radius":"0","width":"0"},"normal":{"color":"color","background":"rgba(0,0,0,';
                    $nav .= '0)"},"hover":{"color":"color","background":"rgba(0,0,0,0)"}}';
                    $obj->desktop->nav = json_decode($nav);
                    $obj->desktop->nav->normal->color = $obj->desktop->{'nav-typography'}->color;
                    $obj->desktop->nav->hover->color = $obj->desktop->{'nav-hover'}->color;
                }
                if (gettype($obj->layout) == 'string') {
                    $layout = $obj->layout;
                    $obj->layout = new stdClass();
                    $obj->layout->layout = $layout;
                    $obj->layout->type = isset($obj->{'menu-type'}) ? $obj->{'menu-type'} : '';
                }
                break;
            case 'social':
                if (!isset($obj->view)) {
                    $obj->view = new stdClass();
                    $obj->view->layout = $obj->layout;
                    $obj->view->size = $obj->size;
                    $obj->view->style = $obj->style;
                    $obj->view->counters = $obj->counters;
                }
                break;
            case 'recent-posts-slider':
                if (!isset($obj->desktop->reviews)) {
                    $obj->desktop->reviews = new stdClass();
                    $obj->desktop->reviews->margin = new stdClass();
                    $obj->desktop->reviews->margin->top = 0;
                    $obj->desktop->reviews->margin->bottom = 25;
                    $obj->desktop->reviews->typography = new stdClass();
                    $obj->desktop->reviews->typography->color = "@title";
                    $obj->desktop->reviews->typography->{"font-family"} = "@default";
                    $obj->desktop->reviews->typography->{"font-size"} = 12;
                    $obj->desktop->reviews->typography->{"font-style"} = "normal";
                    $obj->desktop->reviews->typography->{"font-weight"} = "900";
                    $obj->desktop->reviews->typography->{"letter-spacing"}  = 0;
                    $obj->desktop->reviews->typography->{"line-height"} = 18;
                    $obj->desktop->reviews->typography->{"text-decoration"} = "none";
                    $obj->desktop->reviews->typography->{"text-align"} = "left";
                    $obj->desktop->reviews->typography->{"text-transform"} = "none";
                    $obj->desktop->reviews->hover = new stdClass();
                    $obj->desktop->reviews->hover->color = "@primary";
                }
                break;
            case 'recent-posts':
            case 'search-result':
            case 'store-search-result':
            case 'post-navigation':
            case 'related-posts':
            case 'blog-posts':
                if (!isset($obj->desktop->reviews)) {
                    $obj->desktop->reviews = new stdClass();
                    $obj->desktop->reviews->margin = new stdClass();
                    $obj->desktop->reviews->margin->top = 0;
                    $obj->desktop->reviews->margin->bottom = 25;
                    $obj->desktop->reviews->typography = new stdClass();
                    $obj->desktop->reviews->typography->color = "@title";
                    $obj->desktop->reviews->typography->{"font-family"} = "@default";
                    $obj->desktop->reviews->typography->{"font-size"} = 12;
                    $obj->desktop->reviews->typography->{"font-style"} = "normal";
                    $obj->desktop->reviews->typography->{"font-weight"} = "900";
                    $obj->desktop->reviews->typography->{"letter-spacing"}  = 0;
                    $obj->desktop->reviews->typography->{"line-height"} = 18;
                    $obj->desktop->reviews->typography->{"text-decoration"} = "none";
                    $obj->desktop->reviews->typography->{"text-align"} = "left";
                    $obj->desktop->reviews->typography->{"text-transform"} = "none";
                    $obj->desktop->reviews->hover = new stdClass();
                    $obj->desktop->reviews->hover->color = "@primary";
                }
                if (!isset($obj->desktop->view)) {
                    $obj->desktop->view = new stdClass();
                    $obj->desktop->view->count = $obj->desktop->count;
                    $obj->desktop->view->gutter = $obj->desktop->gutter;
                    if ($obj->type == 'blog-posts' && !isset($obj->desktop->image->show)) {
                        $obj->desktop->image->show = $obj->desktop->title->show = $obj->desktop->date = true;
                        $obj->desktop->category = $obj->desktop->intro->show = $obj->desktop->button->show = true;
                        $obj->desktop->hits = true;
                    } else if ($obj->type != 'blog-posts') {
                        $obj->desktop->hits = false;
                    }
                    $obj->desktop->view->image = $obj->desktop->image->show;
                    $obj->desktop->view->title = $obj->desktop->title->show;
                    $obj->desktop->view->intro = $obj->desktop->intro->show;
                    $obj->desktop->view->button = $obj->desktop->button->show;
                    $obj->desktop->view->date = $obj->desktop->date;
                    $obj->desktop->view->category = $obj->desktop->category;
                    $obj->desktop->view->hits = $obj->desktop->hits;
                    $color = $obj->desktop->overlay;
                    $obj->desktop->overlay = new stdClass();
                    $obj->desktop->overlay->color = $color;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            $obj->{$ind}->view = new stdClass();
                            if (isset($obj->{$ind}->count)) {
                                $obj->{$ind}->view->count = $obj->{$ind}->count;
                            }
                            if (isset($obj->{$ind}->gutter)) {
                                $obj->{$ind}->view->gutter = $obj->{$ind}->gutter;
                            }
                            if (isset($obj->{$ind}->date)) {
                                $obj->{$ind}->view->date = $obj->{$ind}->date;
                            }
                            if (isset($obj->{$ind}->category)) {
                                $obj->{$ind}->view->category = $obj->{$ind}->category;
                            }
                            if (isset($obj->{$ind}->hits)) {
                                $obj->{$ind}->view->hits = $obj->{$ind}->hits;
                            }
                            if (isset($obj->{$ind}->image) && isset($obj->{$ind}->image->show)) {
                                $obj->{$ind}->view->image = $obj->{$ind}->image->show;
                            }
                            if (isset($obj->{$ind}->title) && isset($obj->{$ind}->title->show)) {
                                $obj->{$ind}->view->title = $obj->{$ind}->title->show;
                            }
                            if (isset($obj->{$ind}->intro) && isset($obj->{$ind}->intro->show)) {
                                $obj->{$ind}->view->intro = $obj->{$ind}->intro->show;
                            }
                            if (isset($obj->{$ind}->button) && isset($obj->{$ind}->button->show)) {
                                $obj->{$ind}->view->button = $obj->{$ind}->button->show;
                            }
                        }
                    }
                    $layout = $obj->layout;
                    $obj->layout = new stdClass();
                    $obj->layout->layout = $layout;
                }
                break;
            case 'search':
                if (!isset($obj->desktop->icons)) {
                    $obj->desktop->icons = new stdClass();
                    $obj->desktop->icons->size = $obj->desktop->size;
                    $obj->desktop->icons->position = $obj->icon->position;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            $obj->{$ind}->icons = new stdClass();
                            if (isset($obj->{$ind}->size)) {
                                $obj->{$ind}->icons->size = $obj->{$ind}->size;
                            }
                        }
                    }
                }
                break;
            case 'category-intro':
            case 'post-intro':
                if (!isset($obj->desktop->info->hover)) {
                    $obj->desktop->info->hover = new stdClass();
                    $obj->desktop->info->hover->color = '#fc5859';
                }
                if (!isset($obj->desktop->image->show)) {
                    $obj->desktop->image->show = $obj->desktop->title->show = true;
                    $obj->desktop->date = $obj->desktop->category = $obj->desktop->hits = true;
                }
                if (!isset($obj->desktop->view)) {
                    $obj->desktop->view = new stdClass();
                    $obj->desktop->view->date = $obj->desktop->date;
                    $obj->desktop->view->category = $obj->desktop->category;
                    $obj->desktop->view->hits = $obj->desktop->hits;
                    $layout = $obj->layout;
                    $obj->layout = new stdClass();
                    $obj->layout->layout = $layout;
                    foreach (self::$breakpoints as $ind => $value) {
                        if (isset($obj->{$ind})) {
                            $obj->{$ind}->view = new stdClass();
                            if (isset($obj->{$ind}->date)) {
                                $obj->{$ind}->view->date = $obj->{$ind}->date;
                            }
                            if (isset($obj->{$ind}->category)) {
                                $obj->{$ind}->view->category = $obj->{$ind}->category;
                            }
                            if (isset($obj->{$ind}->hits)) {
                                $obj->{$ind}->view->hits = $obj->{$ind}->hits;
                            }
                        }
                    }
                }
                break;
        }
        if ($obj->type == 'icon' || $obj->type == 'social-icons') {
            if (!isset($obj->desktop->icon)) {
                $obj->desktop->icon = new stdClass();
                $obj->desktop->icon->size = $obj->desktop->size;
                $obj->desktop->icon->{'text-align'} = $obj->desktop->{'text-align'};
                foreach (self::$breakpoints as $ind => $value) {
                    if (isset($obj->{$ind})) {
                        $obj->{$ind}->icon = new stdClass();
                        if (isset($obj->{$ind}->size)) {
                            $obj->{$ind}->icon->size = $obj->{$ind}->size;
                        }
                        if (isset($obj->{$ind}->{'text-align'})) {
                            $obj->{$ind}->icon->{'text-align'} = $obj->{$ind}->{'text-align'};
                        }
                    }
                }
            }
        }

        return $obj;
    }

    public static function getPageCSS($obj, $key)
    {
        $obj = self::presetsCompatibility($obj);
        self::$editItem = $obj;
        self::comparePresets($obj);
        self::$breakpoint = 'desktop';
        switch ($obj->type) {
            case 'field' :
            case 'field-group' :
                $str = self::createFieldRules($obj, $key);
                break;
            case 'fields-filter' :
                $str = self::createFieldsFilterRules($obj, $key);
                break;
            case 'event-calendar':
                $str = self::createEventCalendarRules($obj, $key);
                break;
            case 'preloader' :
                $str = self::createPreloaderRules($obj, $key);
                break;
            case 'checkout-order-form' :
                $str = '';
                break;
            case 'checkout-form':
                $str = self::createCheckoutFormRules($obj, $key);
                break;
            case 'icon-list':
                $str = self::createIconListRules($obj, $key);
                break;
            case 'star-ratings':
                $str = self::createStarRatingsRules($obj, $key);
                break;
            case 'blog-posts':
            case 'search-result':
            case 'store-search-result':
            case 'recent-posts':
            case 'related-posts':
            case 'post-navigation':
                $str = self::createBlogPostsRules($obj, $key);
                break;
            case 'add-to-cart':
                $str = self::createAddToCartRules($obj, $key);
                break;
            case 'categories':
                $str = self::createCategoriesRules($obj, $key);
                break;
            case 'recent-comments':
            case 'recent-reviews':
                $str = self::createRecentCommentsRules($obj, $key);
                break;
            case 'author':
                $str = self::createAuthorRules($obj, $key);
                break;
            case 'post-intro':
            case 'category-intro':
                $str = self::createPostIntroRules($obj, $key);
                break;
            case 'blog-content':
                $str = '';
                break;
            case 'search':
            case 'store-search':
                $str = self::createSearchRules($obj, $key);
                break;
            case 'logo':
                $str = self::createLogoRules($obj, $key);
                break;
            case 'feature-box':
                $str = self::createFeatureBoxRules($obj, $key);
                break;
            case 'slideshow':
            case 'field-slideshow':
            case 'product-slideshow':
                $str = self::createSlideshowRules($obj, $key);
                break;
            case 'carousel':
            case 'slideset':
                $str = self::createCarouselRules($obj, $key);
                break;
            case 'testimonials-slider':
                $str = self::createTestimonialsRules($obj, $key);
                break;
            case 'recent-posts-slider':
            case 'related-posts-slider':
            case 'recently-viewed-products':
                $str = self::createRecentSliderRules($obj, $key);
                break;
            case 'content-slider':
                $str = self::createContentRules($obj, $key);
                break;
            case 'menu':
                $str = self::createMenuRules($obj, $key);
                break;
            case 'one-page':
                $str = self::createOnePageRules($obj, $key);
                break;
            case 'map':
            case 'field-google-maps':
            case 'yandex-maps':
            case 'openstreetmap':
            case 'google-maps-places':
                $str = self::createMapRules($obj, $key);
                break;
            case 'weather':
                $str = self::createWeatherRules($obj, $key);
                break;
            case 'scroll-to-top':
                $str = self::createScrollTopRules($obj, $key);
                break;
            case 'image':
            case 'image-field':
                $str = self::createImageRules($obj, $key);
                break;
            case 'video' :
            case 'field-video' :
                $str = self::createVideoRules($obj, $key);
                break;
            case 'tabs' :
                $str = self::createTabsRules($obj, $key);
                break;
            case 'accordion' :
                $str = self::createAccordionRules($obj, $key);
                break;
            case 'icon' :
            case 'social-icons' :
                $str = self::createIconRules($obj, $key);
                break;
            case 'cart':
            case 'button':
            case 'tags':
            case 'post-tags':
            case 'overlay-button':
            case 'scroll-to':
            case 'wishlist':
                $str = self::createButtonRules($obj, $key);
                break;
            case 'countdown' :
                $str = self::createCountdownRules($obj, $key);
                break;
            case 'counter' :
                $str = self::createCounterRules($obj, $key);
                break;
            case 'text':
            case 'headline':
                $str = self::createTextRules($obj, $key);
                break;
            case 'progress-bar' :
                $str = self::createProgressBarRules($obj, $key);
                break;
            case 'progress-pie' :
                $str = self::createProgressPieRules($obj, $key);
                break;
            case 'social' :
                $str = self::createSocialRules($obj, $key);
                break;
            case 'disqus' :
            case 'vk-comments' :
            case 'facebook-comments' :
            case 'hypercomments' :
            case 'modules' :
            case 'custom-html' :
            case 'gallery' :
            case 'forms' :
                $str = self::createModulesRules($obj, $key);
                break;
            case 'comments-box':
            case 'reviews':
                $str = self::createCommentsBoxRules($obj, $key);
                break;
            case 'instagram':
                $str = '';
                break;
            case 'simple-gallery':
            case 'field-simple-gallery':
            case 'product-gallery':
                $str = self::createSimpleGalleryRules($obj, $key);
                break;
            case 'mega-menu-section' :
                $str = self::createMegaMenuSectionRules($obj, $key);
                break;
            case 'flipbox' :
                $str = self::createFlipboxRules($obj, $key);
                break;
            case 'error-message' :
                $str = self::createErrorRules($obj, $key);
                break;
            case 'search-result-headline' :
                $str = self::createSearchHeadlineRules($obj, $key);
                break;
            default :
                $str = self::createSectionRules($obj, $key);
        }
        
        return $str;
    }

    public static function setItemsVisability($disable, $display)
    {
        if ($disable == 1) {
            $str = "display : none;";
        } else {
            $str = "display : ".$display.";";
        }

        return $str;
    }

    public static function setBoxModel($obj, $selector)
    {
        $str = '';
        if (isset($obj->margin) && isset($obj->margin->top)) {
            $str .= "#".$selector." > .ba-box-model:before {";
            $str .= "height: ".$obj->margin->top."px;";
            if (isset($obj->border) && isset($obj->border->width)) {
                if ((isset($obj->border->top) && $obj->border->top == 1) || !isset($obj->border->top)) {
                    $str .= "top: -".$obj->border->width."px;";
                } else {
                    $str .= "top: 0;";
                }
            }
            $str .= "}";
            $str .= "#".$selector." > .ba-box-model:after {";
            $str .= "height: ".$obj->margin->bottom."px;";
            if (isset($obj->border) && isset($obj->border->width)) {
                if ((isset($obj->border->bottom) && $obj->border->bottom == 1) || !isset($obj->border->bottom)) {
                    $str .= "bottom: -".$obj->border->width."px;";
                } else {
                    $str .= "bottom: 0;";
                }
            }
            $str .= "}";
        }
        if (isset($obj->padding)) {
            foreach ($obj->padding as $key => $value) {
                $str .= "#".$selector." > .ba-box-model .ba-bm-".$key." {";
                $str .= "width: ".$value."px; height: ".$value."px;}";
            }
        }

        return $str;
    }

    public static function createOnePageRules($obj, $key)
    {
        $str = self::getOnePageRules($obj->desktop, $key);
        $str .= "#".$key." .main-menu li a:hover {";
        $str .= "color : ".self::getCorrectColor($obj->desktop->nav->hover->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->desktop->nav->hover->background).";";
        $str .= "}";
        $str .= self::setMediaRules($obj, $key, 'getOnePageRules');
        if (!(bool)self::$website->disable_responsive) {
            $str .= "@media (max-width: ".self::$menuBreakpoint."px) {";
            $str .= "#".$key." .ba-hamburger-menu .main-menu {";
            $str .= "background-color : ".self::getCorrectColor($obj->hamburger->background).";";
            $str .= "}";
            $str .= "#".$key." .ba-hamburger-menu .open-menu {";
            $str .= "color : ".self::getCorrectColor($obj->hamburger->open).";";
            $str .= "text-align : ".$obj->hamburger->{'open-align'}.";";
            $str .= "}";
            $str .= "#".$key." .ba-hamburger-menu .close-menu {";
            $str .= "color : ".self::getCorrectColor($obj->hamburger->close).";";
            $str .= "text-align : ".$obj->hamburger->{'close-align'}.";";
            $str .= "}";
            $str .= "}";
        }

        return $str;
    }

    public static function createMenuRules($obj, $key)
    {
        $str = self::getMenuRules($obj->desktop, $key);
        $str .= "#".$key." ul.nav-child {";
        $str .= "width: ".$obj->desktop->dropdown->width."px;";
        $str .= "background-color : ".self::getCorrectColor($obj->desktop->background->color).";";
        $str .= "box-shadow: 0 ".($obj->desktop->shadow->value * 10);
        $str .= "px ".($obj->desktop->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->desktop->shadow->color).";";
        $str .= "animation-duration: ".$obj->desktop->dropdown->animation->duration."s;";
        $str .= "}";
        $str .= "#".$key." li.megamenu-item > .tabs-content-wrapper > .ba-section {";
        $str .= "box-shadow: 0 ".($obj->desktop->shadow->value * 10);
        $str .= "px ".($obj->desktop->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->desktop->shadow->color).";";
        $str .= "animation-duration: ".$obj->desktop->dropdown->animation->duration."s;";
        $str .= "}";
        $str .= "#".$key." .nav-child > .deeper:hover > .nav-child {";
        $str .= "top : -".$obj->desktop->dropdown->padding->top."px;";
        $str .= "}";
        $str .= self::setMediaRules($obj, $key, 'getMenuRules');
        if (!(bool)self::$website->disable_responsive) {
            $str .= "@media (max-width: ".self::$menuBreakpoint."px) {";
            $str .= "#".$key." .ba-hamburger-menu .main-menu {";
            $str .= "background-color : ".self::getCorrectColor($obj->hamburger->background).";";
            $str .= "}";
            $str .= "#".$key." .ba-hamburger-menu .open-menu {";
            $str .= "color : ".self::getCorrectColor($obj->hamburger->open).";";
            $str .= "text-align : ".$obj->hamburger->{'open-align'}.";";
            $str .= "}";
            $str .= "#".$key." .ba-hamburger-menu .close-menu {";
            $str .= "color : ".self::getCorrectColor($obj->hamburger->close).";";
            $str .= "text-align : ".$obj->hamburger->{'close-align'}.";";
            $str .= "}";
            $str .= "}";
        }

        return $str;
    }

    public static function createLogoRules($obj, $key)
    {
        $str = self::getLogoRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getLogoRules');

        return $str;
    }

    public static function createWeatherRules($obj, $key)
    {
        $str = self::getWeatherRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getWeatherRules');

        return $str;
    }

    public static function createScrollTopRules($obj, $key)
    {
        $str = self::getScrollTopRules($obj->desktop, $key);
        $str .= "#".$key." i.ba-btn-transition:hover {";
        $str .= "color : ".self::getCorrectColor($obj->hover->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->hover->{'background-color'}).";";
        $str .= "}";
        $str .= self::setMediaRules($obj, $key, 'getScrollTopRules');

        return $str;
    }

    public static function createCarouselRules($obj, $key)
    {
        $str = self::getCarouselRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getCarouselRules');

        return $str;
    }

    public static function createTestimonialsRules($obj, $key)
    {
        $str = self::getTestimonialsRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getTestimonialsRules');
        self::$breakpoint = 'desktop';
        foreach ($obj->slides as $ind => $slide) {
            if (!empty($slide->image)) {
                $str .= "#".$key." li.item:nth-child(".$ind.") .testimonials-img,";
                $str .= " #".$key." ul.style-6 .ba-slideset-dots > div:nth-child(".$ind.") {background-image: url(";
                $str .= self::setBackgroundImage($slide->image);
                $str .= ");";
                $str .= "}"; 
            }
        }
        if (self::$website->adaptive_images == 1) {
            foreach (self::$breakpoints as $point => $value) {
                self::$breakpoint = $point;
                $str .= "@media (max-width: ".$value."px) {";
                foreach ($obj->slides as $ind => $slide) {
                    if (!empty($slide->image)) {
                        $str .= "#".$key." li.item:nth-child(".$ind.") .testimonials-img,";
                        $str .= " #".$key." ul.style-6 .ba-slideset-dots > div:nth-child(".$ind.") {background-image: url(";
                        $str .= self::setBackgroundImage($slide->image);
                        $str .= ");";
                        $str .= "}"; 
                    }
                }
                $str .= "}";
            }
        }

        return $str;
    }

    public static function createRecentSliderRules($obj, $key)
    {
        self::$blogPostsInfo = self::$blogPostsFields = null;
        if (isset($obj->info)) {
            self::$blogPostsInfo = $obj->info;
        }
        if (isset($obj->fields)) {
            self::$blogPostsFields = $obj->fields;
        }
        if (!isset($obj->desktop->store)) {
            $obj->desktop->store = new stdClass();
            $obj->desktop->badge = true;
            $obj->desktop->wishlist = true;
            $obj->desktop->price = true;
            $obj->desktop->cart = true;
        }
        $str = self::getRecentSliderRules($obj->desktop, $key);
        if (isset($obj->fields)) {
            foreach ($obj->fields as $i => $value) {
                $str .= '#'.$key.' .ba-blog-post-field-row[data-id="'.$value.'"] {';
                $str .= "order: ".$i.";";
                $str .= "}";
            }
        }
        if (isset($obj->info)) {
            foreach ($obj->info as $i => $value) {
                $str .= '#'.$key.' .ba-blog-post-'.$value.' {';
                $str .= "order: ".$i.";";
                $str .= "}";
            }
        }
        $str .= self::setMediaRules($obj, $key, 'getRecentSliderRules');

        return $str;
    }

    public static function createContentRules($obj, $key)
    {
        $str = self::getContentSliderRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getContentSliderRules');
        foreach ($obj->slides as $ind => $value) {
            self::$breakpoint = 'desktop';
            $slideStr = "#".$key." > .slideshow-wrapper > .ba-slideshow > .slideshow-content > li.item:nth-child(".$ind.")";
            $str .= self::getContentSliderItemsRules($value->desktop, $slideStr);
            $str .= self::setMediaRules($value, $slideStr, 'getContentSliderItemsRules');
        }

        return $str;
    }

    public static function createFeatureBoxRules($obj, $key)
    {

        $str = self::getFeatureBoxRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getFeatureBoxRules');
        foreach ($obj->items as $ind => $item) {
            if ($item->type == 'image' && !empty($item->image)) {
                $str .= "#".$key." .ba-feature-box:nth-child(".($ind * 1 + 1).") .ba-feature-image {background-image: url(";
                $str .= self::setBackgroundImage($item->image).");";
                $str .= "}";
            }
        }

        return $str;
    }

    public static function createSlideshowRules($obj, $key)
    {
        $str = self::getSlideshowRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getSlideshowRules');
        if ($obj->type == 'field-slideshow' || $obj->type == 'product-slideshow') {
            $str .= "body.com_gridbox.gridbox #".$key." li.item .ba-slideshow-img,";
            $str .= "body.com_gridbox.gridbox #".$key." .thumbnails-dots div {";
            $str .= "background-image: url(".JUri::root()."components/com_gridbox/assets/images/default-theme.png);";
            $str .= "}";
            for ($i = 0; $i < 100; $i++) {
                $str .= "body:not(.gridbox) #".$key." .thumbnails-dots > div:nth-child(".($i + 1).") {";
                $str .= "background-image: var(--thumbnails-dots-image-".$i.");";
                $str .= "}";
            }
        }

        return $str;
    }

    public static function createAccordionRules($obj, $key)
    {
        $str = self::getAccordionRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getAccordionRules');

        return $str;
    }

    public static function createTabsRules($obj, $key)
    {
        $str = self::getTabsRules($obj->desktop, $key);
        $str .= "#".$key." ul.nav.nav-tabs li a:hover {";
        $str .= "color : ".self::getCorrectColor($obj->desktop->hover->color).";";
        $str .= "}";
        if ($obj->desktop->icon->position == 'icon-position-left') {
            $str .= '#'.$key.' .ba-tabs-wrapper > ul li a > span {direction: rtl;display: inline-flex;';
            $str .= 'flex-direction: row;}';
            $str .= '#'.$key.' .ba-tabs-wrapper > ul li a > span i {margin-bottom:0;}';
        } else if ($obj->desktop->icon->position == 'icon-position-top') {
            $str .= '#'.$key.' .ba-tabs-wrapper > ul li a > span {display: inline-flex;';
            $str .= 'flex-direction: column-reverse;}';
            $str .= '#'.$key.' .ba-tabs-wrapper > ul li a > span i {margin-bottom:10px;}';
        } else {
            $str .= '#'.$key.' .ba-tabs-wrapper > ul li a > span {direction: ltr;display: inline-flex;';
            $str .= 'flex-direction: row;}';
            $str .= '#'.$key.' .ba-tabs-wrapper > ul li a > span i {margin-bottom:0;}';
        }
        $str .= self::setMediaRules($obj, $key, 'getTabsRules');

        return $str;
    }

    public static function createMapRules($obj, $key)
    {
        $str = self::getMapRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getMapRules');

        return $str;
    }

    public static function createCounterRules($obj, $key)
    {
        $str = self::getCounterRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getCounterRules');

        return $str;
    }

    public static function createSearchRules($obj, $key)
    {
        $str = self::getSearchRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getSearchRules');

        return $str;
    }

    public static function createCountdownRules($obj, $key)
    {
        $str = self::getCountdownRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getCountdownRules');

        return $str;
    }

    public static function setOverlaySectionTrigger($obj, $trigger)
    {
        $array = array('border', 'margin', 'shadow');
        for ($i = 0; $i < count($array); $i++) {
            $obj->desktop->{$array[$i]} = $obj->sides->{$trigger}->desktop->{$array[$i]};
        }
        foreach (self::$breakpoints as $ind => $value) {
            if (isset($obj->{$ind})) {
                for ($i = 0; $i < count($array); $i++) {
                    if (isset($obj->sides->{$trigger}->{$ind}->{$array[$i]})) {
                        $obj->{$ind}->{$array[$i]} = $obj->sides->{$trigger}->{$ind}->{$array[$i]};
                    } else if (isset($obj->{$ind}->{$array[$i]})) {
                        unset($obj->{$ind}->{$array[$i]});
                    }
                }
            }
        }
    }

    public static function createCheckoutFormRules($obj, $key)
    {
        $str = self::getCheckoutFormRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getCheckoutFormRules');

        return $str;
    }

    public static function createIconListRules($obj, $key)
    {
        $str = self::getIconListRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getIconListRules');
        $str .= "#".$key." .ba-icon-list-wrapper ul li a:hover span {";
        $str .= "color : inherit;";
        $str .= "}";
        $str .= "#".$key." .ba-icon-list-wrapper ul li i, #".$key." ul li a:before, #".$key." ul li.list-item-without-link:before {";
        $str .= "order: ".($obj->icon->position == '' ? 0 : 2).";";
        $str .= "margin-".($obj->icon->position == '' ? 'right' : 'left').": 20px;";
        $str .= "}";

        return $str;
    }

    public static function createButtonRules($obj, $key)
    {
        if ($obj->type == 'overlay-button' && isset($obj->trigger) && $obj->trigger == 'button') {
            self::setOverlaySectionTrigger($obj, 'button');
        }
        $str = self::getButtonRules($obj->desktop, $key);
        $str .= "#".$key." .ba-button-wrapper a:hover {";
        $str .= "color : ".self::getCorrectColor($obj->hover->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->hover->{'background-color'}).";";
        $str .= "}";
        if (isset($obj->icon) && is_object($obj->icon)) {
            $str .= "#".$key." .ba-button-wrapper a {";
            if ($obj->icon->position == '') {
                $str .= 'flex-direction: row-reverse;';
            } else {
                $str .= 'flex-direction: row;';
            }
            $str .= "}";
            $str .= "#".$key." .ba-button-wrapper a i {";
            if ($obj->icon->position == '') {
                $str .= 'margin: 0 10px 0 0;';
            } else {
                $str .= 'margin: 0 0 0 10px;';
            }
            $str .= "}";
        }
        if ($obj->type == 'overlay-button' && isset($obj->trigger) && $obj->trigger == 'image') {
            self::setOverlaySectionTrigger($obj, 'image');
            $str = self::getImageRules($obj->desktop, $key);
            $str .= self::setMediaRules($obj, $key, 'getImageRules');
        }
        $str .= self::setMediaRules($obj, $key, 'getButtonRules');

        return $str;
    }

    public static function createCategoriesRules($obj, $key)
    {
        $str = self::getCategoriesRules($obj->desktop, $key);
        $str .= "#".$key." .ba-blog-post-title a:hover, #".$key." .ba-blog-post.active .ba-blog-post-title a {";
        $str .= "color: ".self::getCorrectColor($obj->desktop->title->hover->color).";";
        $str .= "}";
        $str .= "#".$key." .ba-blog-post-info-wrapper a:hover, #".$key." .ba-blog-post-info-wrapper a.active {";
        $str .= "color: ".self::getCorrectColor($obj->desktop->info->hover->color).";";
        $str .= "}";
        $str .= self::setMediaRules($obj, $key, 'getCategoriesRules');

        return $str;
    }

    public static function createAddToCartRules($obj, $key)
    {
        $str = self::getAddToCartRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getAddToCartRules');

        return $str;
    }

    public static function createBlogPostsRules($obj, $key)
    {
        self::$blogPostsInfo = self::$blogPostsFields = null;
        if (isset($obj->info)) {
            self::$blogPostsInfo = $obj->info;
        }
        if (isset($obj->fields)) {
            self::$blogPostsFields = $obj->fields;
        }
        if (!isset($obj->desktop->store)) {
            $obj->desktop->store = new stdClass();
            $obj->desktop->badge = true;
            $obj->desktop->wishlist = true;
            $obj->desktop->price = true;
            $obj->desktop->cart = true;
        }
        $str = self::getBlogPostsRules($obj->desktop, $key, $obj->type);
        $str .= "#".$key." .ba-blog-post-title a:hover {";
        $str .= "color: ".self::getCorrectColor($obj->desktop->title->hover->color).";";
        $str .= "}";
        $str .= "#".$key." .ba-blog-post-info-wrapper > * a:hover, #".$key." .ba-post-navigation-info a:hover {";
        $str .= "color: ".self::getCorrectColor($obj->desktop->info->hover->color).";";
        $str .= "}";
        if (isset($obj->fields)) {
            foreach ($obj->fields as $i => $value) {
                $str .= '#'.$key.' .ba-blog-post-field-row[data-id="'.$value.'"] {';
                $str .= "order: ".$i.";";
                $str .= "}";
            }
        }
        if (isset($obj->info)) {
            foreach ($obj->info as $i => $value) {
                $str .= '#'.$key.' .ba-blog-post-'.$value.' {';
                $str .= "order: ".$i.";";
                $str .= "}";
            }
        }
        $str .= self::setMediaRules($obj, $key, 'getBlogPostsRules');

        return $str;
    }

    public static function createRecentCommentsRules($obj, $key)
    {
        $str = self::getRecentCommentsRules($obj->desktop, $key, $obj->type);
        $str .= self::setMediaRules($obj, $key, 'getRecentCommentsRules');

        return $str;
    }

    public static function createAuthorRules($obj, $key)
    {
        $str = self::getAuthorRules($obj->desktop, $key);
        $str .= "#".$key." .ba-post-author-title a:hover {";
        $str .= "color: ".self::getCorrectColor($obj->desktop->title->hover->color).";";
        $str .= "}";
        $str .= self::setMediaRules($obj, $key, 'getAuthorRules');

        return $str;
    }

    public static function createPostIntroRules($obj, $key)
    {
        self::$blogPostsInfo = self::$blogPostsFields = null;
        if (isset($obj->info)) {
            self::$blogPostsInfo = $obj->info;
        }
        $str = self::getPostIntroRules($obj->desktop, $key);
        $str .= "#".$key." .intro-post-wrapper .intro-post-info > * a:hover {";
        $str .= "color: ".self::getCorrectColor($obj->desktop->info->hover->color).";";
        $str .= "}";
        if (isset($obj->info)) {
            foreach ($obj->info as $i => $value) {
                $str .= '#'.$key.' .intro-post-'.$value.' {';
                $str .= "order: ".$i.";";
                $str .= "}";
            }
        }
        $str .= self::setMediaRules($obj, $key, 'getPostIntroRules');

        return $str;
    }

    public static function createStarRatingsRules($obj, $key)
    {
        $str = self::getStarRatingsRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getStarRatingsRules');

        return $str;
    }

    public static function createSimpleGalleryRules($obj, $key)
    {
        $str = self::getSimpleGalleryRules($obj->desktop, $key);
        $str .= '#'.$key.' .ba-instagram-image {';
        $str .= 'cursor: zoom-in;';
        $str .= '}';
        $str .= self::setMediaRules($obj, $key, 'getSimpleGalleryRules');

        return $str;
    }

    public static function createIconRules($obj, $key)
    {
        $str = self::getIconRules($obj->desktop, $key);
        $str .= "#".$key." .ba-icon-wrapper i:hover {";
        $str .= "color : ".self::getCorrectColor($obj->hover->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->hover->{'background-color'}).";";
        $str .= "}";
        $str .= self::setMediaRules($obj, $key, 'getIconRules');

        return $str;
    }

    public static function createProgressBarRules($obj, $key)
    {
        $str = self::getProgressBarRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getProgressBarRules');

        return $str;
    }

    public static function createProgressPieRules($obj, $key)
    {
        $str = self::getProgressPieRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getProgressPieRules');

        return $str;
    }

    public static function createSocialRules($obj, $key)
    {
        $str = self::getModulesRules($obj->desktop, $key);
        $str .= '#'.$key.' .social-counter {display:'.($obj->view->counters ? 'inline-block' : 'none').'}';
        $str .= self::setMediaRules($obj, $key, 'getModulesRules');

        return $str;
    }

    public static function createEventCalendarRules($obj, $key)
    {
        $str = self::getEventCalendarRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getEventCalendarRules');

        return $str;
    }

    public static function createCommentsBoxRules($obj, $key)
    {
        $str = self::getCommentsBoxRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getCommentsBoxRules');
        if (!$obj->view->user) {
            $str .= "#".$key." .ba-user-login-wrapper {";
            $str .= "display: none;";
            $str .= "}";
        }
        if (!$obj->view->social) {
            $str .= "#".$key." .ba-social-login-wrapper {";
            $str .= "display: none;";
            $str .= "}";
        }
        if (!$obj->view->guest) {
            $str .= "#".$key." .ba-guest-login-wrapper {";
            $str .= "display: none;";
            $str .= "}";
        }
        if (!$obj->view->share) {
            $str .= "#".$key." .comment-share-action {";
            $str .= "display: none;";
            $str .= "}";
        }
        if (!$obj->view->rating) {
            $str .= '#'.$key.' .comment-likes-action-wrapper {';
            $str .= "display: none;";
            $str .= "}";
        }
        if (!$obj->view->files) {
            $str .= '#'.$key.' .ba-comments-attachment-file-wrapper[data-type="file"] {';
            $str .= "display: none;";
            $str .= "}";
        }
        if (!$obj->view->images) {
            $str .= '#'.$key.' .ba-comments-attachment-file-wrapper[data-type="image"] {';
            $str .= "display: none;";
            $str .= "}";
        }
        if (!$obj->view->report) {
            $str .= '#'.$key.' .comment-report-user-comment {';
            $str .= "display: none;";
            $str .= "}";
        }
        if (isset($obj->view->reply) && !$obj->view->reply) {
            $str .= '#'.$key.' .comment-reply-action {';
            $str .= "display: none;";
            $str .= "}";
        }

        return $str;
    }

    public static function createFieldRules($obj, $key)
    {
        $str = self::getFieldRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getFieldRules');

        return $str;
    }

    public static function createFieldsFilterRules($obj, $key)
    {
        self::$blogPostsFields = $obj->fields;
        $str = self::getFieldsFilterRules($obj->desktop, $key);
        foreach ($obj->fields as $i => $field) {
            $str .= '#'.$key.' .ba-field-filter[data-id="'.$field.'"] {';
            $str .= "order: ".$i.";";
            $str .= "}";
        }
        $str .= self::setMediaRules($obj, $key, 'getFieldsFilterRules');

        return $str;
    }

    public static function createModulesRules($obj, $key)
    {
        $str = self::getModulesRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getModulesRules');

        return $str;
    }

    public static function createErrorRules($obj, $key)
    {
        $str = self::getErrorRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getErrorRules');

        return $str;
    }

    public static function createSearchHeadlineRules($obj, $key)
    {
        $str = self::getSearchHeadlineRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getSearchHeadlineRules');

        return $str;
    }

    public static function createTextRules($obj, $key)
    {
        $array = array('h1' ,'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'links');
        if (isset($obj->global) && $obj->global) {
            unset($obj->global);
            foreach ($array as $value) {
                unset($obj->desktop->{$value});
                foreach (self::$breakpoints as $ind => $property) {
                    unset($obj->{$ind}->{$value});
                }
            }
        }
        if (!isset($obj->desktop->p)) {
            foreach ($array as $value) {
                if ($value == 'links') {
                    continue;
                }
                $obj->desktop->{$value} = new stdClass();
                foreach (self::$breakpoints as $ind => $property) {
                    if (!isset($obj->{$ind})) {
                        $obj->{$ind} = new stdClass();
                    }
                    $obj->{$ind}->{$value} = new stdClass();
                }
            }
        }
        $str = self::getTextRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getTextRules');

        return $str;
    }

    public static function createPreloaderRules($obj, $key)
    {
        $str = self::getPreloaderRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getPreloaderRules');

        return $str;
    }

    public static function createImageRules($obj, $key)
    {
        $str = self::getImageRules($obj->desktop, $key);
        if (isset($obj->link) && !empty($obj->link->link)) {
            $str .= '#'.$key.' .ba-image-wrapper { cursor: pointer; }';
        } else if (isset($obj->popup) && $obj->popup) {
            $str .= '#'.$key.' .ba-image-wrapper { cursor: zoom-in; }';
        } else {
            $str .= '#'.$key.' .ba-image-wrapper { cursor: default; }';
        }
        $str .= self::setMediaRules($obj, $key, 'getImageRules');

        return $str;
    }

    public static function createVideoRules($obj, $key)
    {
        $str = self::getVideoRules($obj->desktop, $key);
        $str .= self::setMediaRules($obj, $key, 'getVideoRules');

        return $str;
    }

    public static function createHeaderRules($obj, $view)
    {
        $str = "body header.header {";
        $str .= "position:".$obj->position.";";
        if ($obj->position == 'fixed' || $obj->position == 'absolute') {
            $str .= "top: 0;";
            $str .= "left: 0;";
        }
        $str .= "}";
        if (!isset($obj->width)) {
            $obj->width = 250;
        }
        $str .= "body {";
        $str .= "--sidebar-menu-width:".$obj->width."px;";
        $str .= "}";
        $str .= "body.com_gridbox.gridbox header.header {";
        if ($obj->position == 'fixed') {
            if ($view == 'desktop') {
                $str .= "width: calc(100% - 103px);";
                $str .= "left: 52px;";
            } else {
                $str .= "width: 100%;";
                $str .= "left: 0;";
            }
            $str .= "top: 40px;";
        } else {
            $str .= "width: 100%;";
            $str .= "left: 0;";
            $str .= "top: 0;";
        }
        if ($obj->position == 'relative') {
            $str .= "z-index: auro;";
        } else {
            $str .= "z-index: 40;";
        }
        $str .= "}";
        $str .= "body.com_gridbox.gridbox header.header:hover {";
        if ($obj->position == 'relative') {
            $str .= "z-index: 32;";
        } else {
            $str .= "z-index: 40;";
        }
        $str .= "}";
        if ($obj->position == 'fixed') {
            $str .= ".ba-container .header {margin-left: calc((100vw - 1280px)/2);";
            $str .= "max-width: 1170px;}";
        } else {
            $str .= ".ba-container .header {margin-left:0;max-width: none;}";
        }

        return $str;
    }

    public static function createMegaMenuSectionRules($obj, $key)
    {
        $str = self::createMegaMenuRules($obj->desktop, $key);
        if (isset($obj->parallax)) {
            $pHeight = 100 + $obj->parallax->offset * 2 * 200;
            $pTop = $obj->parallax->offset * 2 * -100;
            $str .= "#".$key." > .parallax-wrapper.scroll .parallax {";
            $str .= "height: ".$pHeight."%;";
            $str .= "top: ".$pTop."%;";
            $str .= "}";
        }
        $str .= "#".$key." { width: ".$obj->view->width."px; }";
        $str .= self::setMediaRules($obj, $key, 'createMegaMenuRules');
        
        return $str;
    }

    public static function setFlipboxSide($obj, $side)
    {
        $array = array('background', 'overlay', 'image', 'video');
        $obj->parallax = $obj->sides->{$side}->parallax;
        for ($i = 0; $i < count($array); $i++) {
            $obj->desktop->{$array[$i]} = $obj->sides->{$side}->desktop->{$array[$i]};
        }
        foreach (self::$breakpoints as $ind => $value) {
            if (isset($obj->{$ind})) {
                for ($i = 0; $i < count($array); $i++) {
                    if (isset($obj->sides->{$side}->{$ind}->{$array[$i]})) {
                        $obj->{$ind}->{$array[$i]} = $obj->sides->{$side}->{$ind}->{$array[$i]};
                    } else if (isset($obj->{$ind}->{$array[$i]})) {
                        unset($obj->{$ind}->{$array[$i]});
                    }
                }
            }
        }
    }

    public static function createFlipboxRules($obj, $key)
    {
        self::setFlipboxSide($obj, $obj->side);
        $str = self::getFlipboxRules($obj->desktop, $key);
        $empty = new stdClass();
        $object = self::object_extend($empty, $obj);
        $str .= self::setMediaRules($obj, $key, 'getFlipboxRules');
        self::setFlipboxSide($object, 'frontside');
        $key1 = $key.' > .ba-flipbox-wrapper > .ba-flipbox-frontside > .ba-grid-column-wrapper > .ba-grid-column';
        $pHeight = 100 + $object->parallax->offset * 2 * 200;
        $pTop = $object->parallax->offset * 2 * -100;
        $str .= "#".$key1." > .parallax-wrapper.scroll .parallax {";
        $str .= "height: ".$pHeight."%;";
        $str .= "top: ".$pTop."%;";
        $str .= "}";
        self::$breakpoint = 'desktop';
        $str .= self::getFlipsidesRules($object->desktop, $key1);
        $str .= self::setMediaRules($object, $key1, 'getFlipsidesRules');
        self::setFlipboxSide($object, 'backside');
        $key1 = $key.' > .ba-flipbox-wrapper > .ba-flipbox-backside > .ba-grid-column-wrapper > .ba-grid-column';
        $pHeight = 100 + $object->parallax->offset * 2 * 200;
        $pTop = $object->parallax->offset * 2 * -100;
        $str .= "#".$key1." > .parallax-wrapper.scroll .parallax {";
        $str .= "height: ".$pHeight."%;";
        $str .= "top: ".$pTop."%;";
        $str .= "}";
        self::$breakpoint = 'desktop';
        $str .= self::getFlipsidesRules($object->desktop, $key1);
        $str .= self::setMediaRules($object, $key1, 'getFlipsidesRules');
        
        return $str;
    }

    public static function createSectionRules($obj, $key)
    {
        self::$cssRulesFlag = 'desktop';
        $str = self::createPageRules($obj->desktop, $key, $obj->type);
        if ($obj->type == 'lightbox') {
            $str .= ".ba-lightbox-backdrop[data-id=".$key."] .close-lightbox {";
            $str .= "color: ".self::getCorrectColor($obj->close->color).";";
            $str .= "text-align: ".$obj->close->{'text-align'}.";";
            $str .= "}";
            $str .= "body.gridbox .ba-lightbox-backdrop[data-id=".$key."] > .ba-lightbox-close {";
            $str .= "background-color: ".self::getCorrectColor($obj->lightbox->background).";";
            $str .= "}";
            $str .= "body:not(.gridbox) .ba-lightbox-backdrop[data-id=".$key."] {";
            $str .= "background-color: ".self::getCorrectColor($obj->lightbox->background).";";
            $str .= "}";
        }
        if ($obj->type == 'overlay-section') {
            $str .= ".ba-overlay-section-backdrop[data-id=".$key."] .close-overlay-section {";
            $str .= "color: ".self::getCorrectColor($obj->close->color).";";
            $str .= "text-align: ".$obj->close->{'text-align'}.";";
            $str .= "}";
            $str .= "body.gridbox .ba-overlay-section-backdrop[data-id=".$key."] > .ba-overlay-section-close {";
            $str .= "background-color: ".self::getCorrectColor($obj->lightbox->background).";";
            $str .= "}";
            $str .= "body:not(.gridbox) .ba-overlay-section-backdrop[data-id=".$key."] {";
            $str .= "background-color: ".self::getCorrectColor($obj->lightbox->background).";";
            $str .= "}";
        }
        if (isset($obj->parallax)) {
            $pHeight = 100 + $obj->parallax->offset * 2 * 200;
            $pTop = $obj->parallax->offset * 2 * -100;
            $str .= "#".$key." > .parallax-wrapper.scroll .parallax {";
            $str .= "height: ".$pHeight."%;";
            $str .= "top: ".$pTop."%;";
            $str .= "}";
        }
        if ($obj->type == 'column' && isset($obj->sticky) && $obj->sticky->enable) {
            $str .= "#".$key." {";
            $str .= "top: ".$obj->sticky->offset."px;";
            $str .= "}";
        }
        self::$cssRulesFlag = 'tablet';
        $str .= self::setMediaRules($obj, $key, 'createPageRules');
        
        return $str;
    }

    public static function createFooterStyle($obj)
    {
        $str = "";
        foreach ($obj as $key => $value) {
            switch($key) {
                case 'links' : 
                    $str .= "body footer a {";
                    $str .= "color : ".self::getCorrectColor($value->color).";";
                    $str .= "}";
                    $str .= "body footer a:hover {";
                    $str .= "color : ".self::getCorrectColor($value->{'hover-color'}).";";
                    $str .= "}";
                    break;
                case 'body':
                    $str .= "body footer, footer ul, footer ol, footer table, footer blockquote";
                    $str .= " {";
                    $str .= self::getTypographyRule($value);
                    $str .= "}";
                    break;
                case 'p' :
                case 'h1' :
                case 'h2' :
                case 'h3' :
                case 'h4' :
                case 'h5' :
                case 'h6' :
                    $str .= "footer ".$key;
                    $str .= " {";
                    $str .= self::getTypographyRule($value);
                    $str .= "}";
                    break;
            }
        }
        return $str;
    }

    public static function createMegaMenuRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= "min-height: 50px;";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        if ($obj->border->width == '') {
            $obj->border->width = 0;
        }
        $str .= "border-bottom-width : ".($obj->border->width * $obj->border->bottom)."px;";
        $str .= "border-color : ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-left-width : ".($obj->border->width * $obj->border->left)."px;";
        $str .= "border-right-width : ".($obj->border->width * $obj->border->right)."px;";
        $str .= "border-style : ".$obj->border->style.";";
        $str .= "border-top-width : ".($obj->border->width * $obj->border->top)."px;";
        $str .= "}";
        $str .= 'li.deeper > .tabs-content-wrapper[data-id="'.$selector.'"] + a > i.zmdi-caret-right {';
        $str .= self::setItemsVisability($obj->disable, "inline-block");
        $str .= "}";
        if (!empty($obj->background->image->image)) {
            $str .= "#".$selector." > .parallax-wrapper .parallax {";
            $str .= "background-image: url(".self::setBackgroundImage($obj->background->image->image).");";
            $str .= "}";
        } else {
            $str .= "#".$selector." > .parallax-wrapper .parallax {";
            $str .= "background-image: none;";
            $str .= "}";
        }
        $str .= self::backgroundRule($obj, '#'.$selector, self::$up);

        return $str;
    }

    public static function setBackgroundImage($image)
    {
        if (strpos($image, 'balbooa.com') === false) {
            $url = self::$up.$image;
            if ((self::$website->compress_images == 1 && (empty(self::$breakpoint) || self::$breakpoint == 'desktop'))
                || (self::$website->adaptive_images == 1 && !empty(self::$breakpoint) && self::$breakpoint != 'desktop')) {
                $src = self::getCompressedImageURL($image, true);
                if ($src) {
                    $url = $src;
                }
            }
        } else {
            $url = $image;
        }

        return str_replace(' ', '%20', $url);
    }

    public static function getFlipboxRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." > .ba-flipbox-wrapper {";
        $str .= "height: ".$obj->view->height."px;";
        $str .= "}";
        $str .= "#".$selector." > .ba-flipbox-wrapper > .column-wrapper > .ba-grid-column-wrapper > .ba-grid-column {";
        if ($obj->full->fullscreen) {
            $str .= "justify-content: center;";
            $str .= "min-height: 100vh;";
        } else {
            $str .= "min-height: 50px;";
        }
        $str .= "}";
        $str .= "#".$selector." > .ba-flipbox-wrapper > .column-wrapper {";
        $str .= "transition-duration: ".$obj->animation->duration."s;";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getFlipsidesRules($obj, $selector)
    {
        $str = '#'.$selector." {";
        if ($obj->border->width == '') {
            $obj->border->width = 0;
        }
        $str .= "border-bottom-width : ".($obj->border->width * $obj->border->bottom)."px;";
        $str .= "border-color : ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-left-width : ".($obj->border->width * $obj->border->left)."px;";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "border-right-width : ".($obj->border->width * $obj->border->right)."px;";
        $str .= "border-style : ".$obj->border->style.";";
        $str .= "border-top-width : ".($obj->border->width * $obj->border->top)."px;";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= self::backgroundRule($obj, '#'.$selector, self::$up);

        return $str;
    }

    public static function createPageRules($obj, $selector, $type)
    {
        $str = "#".$selector." {";
        if ($obj->border->bottom == 1) {
            $str .= "border-bottom-width : ".$obj->border->width."px;";
        } else {
            $str .= "border-bottom-width : 0;";
        }
        $str .= "border-color : ".self::getCorrectColor($obj->border->color).";";
        if ($obj->border->left == 1) {
            $str .= "border-left-width : ".$obj->border->width."px;";
        } else {
            $str .= "border-left-width : 0;";
        }
        $str .= "border-radius : ".$obj->border->radius."px;";
        if ($obj->border->right == 1) {
            $str .= "border-right-width : ".$obj->border->width."px;";
        } else {
            $str .= "border-right-width : 0;";
        }
        $str .= "border-style : ".$obj->border->style.";";
        if ($obj->border->top == 1) {
            $str .= "border-top-width : ".$obj->border->width."px;";
        } else {
            $str .= "border-top-width : 0;";
        }
        $str .= "animation-duration: ".$obj->animation->duration."s;";
        if (isset($obj->animation->delay)) {
            $str .= "animation-delay: ".$obj->animation->delay."s;";
        }
        if (!empty($obj->animation->effect)) {
            $str .= "opacity: 0;";
        } else {
            $str .= "opacity: 1;";
        }
        if ($obj->full->fullscreen) {
            if ($type != 'column') {
                $str .= "align-items: center;";
            }
            $str .= "justify-content: center;";
            if ($type != 'lightbox') {
                $str .= "min-height: 100vh;";
            } else {
                $str .= "min-height: calc(100vh - 50px);";
            }
            $str .= self::setItemsVisability($obj->disable, "flex;");
        } else {
            if (isset($obj->view) && isset($obj->view->height)) {
                $str .= "min-height: ".$obj->view->height."px;";
            } else {
                $str .= "min-height: 50px;";
            }
            $str .= self::setItemsVisability($obj->disable, "block");
        }
        if (isset($obj->view) && isset($obj->view->width)) {
            $str .= "width: ".$obj->view->width."px;";
        }
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector.".visible {opacity: 1;}";
        if (!empty($obj->background->image->image)) {
            $image = $obj->background->image->image;
            if (isset($obj->image)) {
                $image = $obj->image->image;
            }
            $str .= "#".$selector." > .parallax-wrapper .parallax {";
            $str .= "background-image: url(".self::setBackgroundImage($image).");";
            $str .= "}";
        } else {
            $str .= "#".$selector." > .parallax-wrapper .parallax {";
            $str .= "background-image: none;";
            $str .= "}";
        }
        if (isset($obj->shape)) {
            $str .= self::getShapeRules($selector, $obj->shape->bottom, 'bottom');
            $str .= self::getShapeRules($selector, $obj->shape->top, 'top');
        }
        $str .= self::backgroundRule($obj, '#'.$selector, self::$up);
        $str .= self::setBoxModel($obj, $selector);
        if ($type == 'header') {
            $str .= self::createHeaderRules($obj, self::$cssRulesFlag);
        }
        if ($type == 'footer') {
            $str .= self::createFooterStyle($obj);
        }

        return $str;
    }

    public static function getShapeRules($selector, $obj, $type)
    {
        $str = "#".$selector." > .ba-shape-divider.ba-shape-divider-".$type." {";
        if ($obj->effect == 'arrow') {
            $arrow = '';
            $arrow .= "clip-path: polygon(100% ".(100 - $obj->value);
            $arrow .= "%, 100% 100%, 0 100%, 0 ".(100 - $obj->value);
            $arrow .= "%, ".(50 - $obj->value / 2)."% ".(100 - $obj->value);
            $arrow .= "%, 50% 100%, ".(50 + $obj->value / 2)."% ";
            $arrow .= (100 - $obj->value)."%);";
            $str .= $arrow;
        } else if ($obj->effect == 'zigzag') {
            $pyramids = "clip-path: polygon(";
            $delta = 0;
            $delta2 = 100 / ($obj->value * 2);
            for ($i = 0; $i < $obj->value; $i++) {
                if ($i != 0) {
                    $pyramids .= ",";
                }
                $pyramids .= $delta."% 100%,";
                $pyramids .= $delta2."% calc(100% - 15px),";
                $delta += 100 / $obj->value;
                $delta2 += 100 / $obj->value;
                $pyramids .= $delta."% 100%";
            }
            $pyramids .= ");";
            $str .= $pyramids;
        } else if ($obj->effect == 'circle') {
            $str .= "clip-path: circle(".$obj->value."% at 50% 100%);";
        } else if ($obj->effect == 'vertex') {
            $str .= "clip-path: polygon(20% calc(".(100 - $obj->value)."% + 15%), 35%  calc(".(100 - $obj->value);
            $str .= "% + 45%), 65%  ".(100 - $obj->value)."%, 100% 100%, 100% 100%, 0% 100%, 0  calc(";
            $str .= (100 - $obj->value)."% + 10%), 10%  calc(".(100 - $obj->value)."% + 30%));";
        } else if ($obj->effect != 'arrow' && $obj->effect != 'zigzag' &&
            $obj->effect != 'circle' && $obj->effect != 'vertex') {
            $str .= "clip-path: none;";
            $str .= "background: none;";
            $str .= "color: ".self::getCorrectColor($obj->color).";";
        }
        if ($obj->effect == 'arrow' || $obj->effect == 'zigzag' ||
            $obj->effect == 'circle' || $obj->effect == 'vertex') {
            $str .= "background-color: ".self::getCorrectColor($obj->color).";";
        }
        if ($obj->effect == '') {
            $str .= 'display: none;';
        } else {
            $str .= 'display: block;';
        }
        $str .= "}";
        $str .= "#".$selector." > .ba-shape-divider.ba-shape-divider-".$type." svg:not(.shape-divider-".$obj->effect.") {";
        $str .= "display: none;";
        $str .= "}";
        $str .= "#".$selector." > .ba-shape-divider.ba-shape-divider-".$type." svg.shape-divider-".$obj->effect." {";
        $str .= "display: block;";
        $str .= "height: ".($obj->value * 10)."px;";
        $str .= "}";

        return $str;
    }

    public static function getOnePageRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .integration-wrapper > ul > li {";
        foreach ($obj->nav->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." i.ba-menu-item-icon {";
        $str .= "font-size: ".$obj->nav->icon->size."px;";
        $str .= "}";
        $str .= "#".$selector." .main-menu li a {";
        $str .= self::getTypographyRule($obj->{'nav-typography'});
        $str .= "color : ".self::getCorrectColor($obj->nav->normal->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->nav->normal->background).";";
        foreach ($obj->nav->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        if ($obj->nav->border->width == '') {
            $obj->nav->border->width = 0;
        }
        $str .= "border-bottom-width : ".($obj->nav->border->width * $obj->nav->border->bottom)."px;";
        $str .= "border-color : ".self::getCorrectColor($obj->nav->border->color).";";
        $str .= "border-left-width : ".($obj->nav->border->width * $obj->nav->border->left)."px;";
        $str .= "border-radius : ".$obj->nav->border->radius."px;";
        $str .= "border-right-width : ".($obj->nav->border->width * $obj->nav->border->right)."px;";
        $str .= "border-style : ".$obj->nav->border->style.";";
        $str .= "border-top-width : ".($obj->nav->border->width * $obj->nav->border->top)."px;";
        $str .= "}";
        if ($obj->nav->border->left == 1 && $obj->nav->border->right == 1 &&
            $obj->nav->margin->left == 0 && $obj->nav->margin->right == 0) {
            $str .= "#".$selector." > .ba-menu-wrapper:not(.vertical-menu) > .main-menu:not(.visible-menu)";
            $str .= " > .integration-wrapper > ul > li:not(:last-child) > a, #".$selector."> .ba-menu-wrapper:not(.vertical-menu)";
            $str .= " > .main-menu:not(.visible-menu) .integration-wrapper > ul > li:not(:last-child) > span {";
            $str .= "border-right: none";
            $str .= "}";
        }
        if ($obj->nav->border->top == 1 && $obj->nav->border->bottom == 1) {
            $str .= "#".$selector." > .ba-menu-wrapper.vertical-menu > .main-menu";
            $str .= " > .integration-wrapper > ul > li:not(:last-child) > a, #".$selector."> .ba-menu-wrapper.vertical-menu";
            $str .= " > .main-menu .integration-wrapper > ul > li:not(:last-child) > span, #";
            $str .= $selector." > .ba-menu-wrapper > .main-menu.visible-menu";
            $str .= " > .integration-wrapper > ul > li:not(:last-child) > a, #".$selector."> .ba-menu-wrapper";
            $str .= " > .main-menu.visible-menu .integration-wrapper > ul > li:not(:last-child) > span {";
            $str .= "border-bottom: none";
            $str .= "}";
        }
        $str .= "#".$selector." .main-menu li a:hover {";
        $str .= "color : ".self::getCorrectColor($obj->nav->normal->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->nav->normal->background).";";
        $str .= "}";
        $str .= "#".$selector." ul {";
        $str .= "text-align : ".$obj->{'nav-typography'}->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .main-menu li.active > a {";
        $str .= "color : ".self::getCorrectColor($obj->nav->hover->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->nav->hover->background).";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getMenuRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul > li {";
        foreach ($obj->nav->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul > li > a > i.ba-menu-item-icon, #";
        $str .= $selector." .integration-wrapper > ul > li > span > i.ba-menu-item-icon {";
        $str .= "font-size: ".$obj->nav->icon->size."px;";
        $str .= "}";
        $str .= "#".$selector." > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul > li > a, #";
        $str .= $selector." .integration-wrapper > ul > li > span {";
        $str .= self::getTypographyRule($obj->{'nav-typography'});
        $str .= "color : ".self::getCorrectColor($obj->nav->normal->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->nav->normal->background).";";
        foreach ($obj->nav->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        if ($obj->nav->border->width == '') {
            $obj->nav->border->width = 0;
        }
        $str .= "border-bottom-width : ".($obj->nav->border->width * $obj->nav->border->bottom)."px;";
        $str .= "border-color : ".self::getCorrectColor($obj->nav->border->color).";";
        $str .= "border-left-width : ".($obj->nav->border->width * $obj->nav->border->left)."px;";
        $str .= "border-radius : ".$obj->nav->border->radius."px;";
        $str .= "border-right-width : ".($obj->nav->border->width * $obj->nav->border->right)."px;";
        $str .= "border-style : ".$obj->nav->border->style.";";
        $str .= "border-top-width : ".($obj->nav->border->width * $obj->nav->border->top)."px;";
        $str .= "}";
        if ($obj->nav->border->left == 1 && $obj->nav->border->right == 1 &&
            $obj->nav->margin->left == 0 && $obj->nav->margin->right == 0) {
            $str .= "#".$selector." > .ba-menu-wrapper:not(.vertical-menu) > .main-menu:not(.visible-menu)";
            $str .= " > .integration-wrapper > ul > li:not(:last-child) > a, #".$selector."> .ba-menu-wrapper:not(.vertical-menu)";
            $str .= " > .main-menu:not(.visible-menu) .integration-wrapper > ul > li:not(:last-child) > span {";
            $str .= "border-right: none";
            $str .= "}";
        }
        if ($obj->nav->border->top == 1 && $obj->nav->border->bottom == 1) {
            $str .= "#".$selector." > .ba-menu-wrapper.vertical-menu > .main-menu";
            $str .= " > .integration-wrapper > ul > li:not(:last-child) > a, #".$selector."> .ba-menu-wrapper.vertical-menu";
            $str .= " > .main-menu .integration-wrapper > ul > li:not(:last-child) > span, #";
            $str .= $selector." > .ba-menu-wrapper > .main-menu.visible-menu";
            $str .= " > .integration-wrapper > ul > li:not(:last-child) > a, #".$selector."> .ba-menu-wrapper";
            $str .= " > .main-menu.visible-menu .integration-wrapper > ul > li:not(:last-child) > span {";
            $str .= "border-bottom: none";
            $str .= "}";
        }
        $str .= "#".$selector." .main-menu .nav-child li i.ba-menu-item-icon {";
        $str .= "font-size: ".$obj->sub->icon->size."px;";
        $str .= "}";
        $str .= "#".$selector." .main-menu .nav-child li a,#".$selector." .main-menu .nav-child li span {";
        $str .= self::getTypographyRule($obj->{'sub-typography'});
        $str .= "color : ".self::getCorrectColor($obj->sub->normal->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->sub->normal->background).";";
        foreach ($obj->sub->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        if ($obj->sub->border->width == '') {
            $obj->sub->border->width = 0;
        }
        $str .= "border-bottom-width : ".($obj->sub->border->width * $obj->sub->border->bottom)."px;";
        $str .= "border-color : ".self::getCorrectColor($obj->sub->border->color).";";
        $str .= "border-left-width : ".($obj->sub->border->width * $obj->sub->border->left)."px;";
        $str .= "border-radius : ".$obj->sub->border->radius."px;";
        $str .= "border-right-width : ".($obj->sub->border->width * $obj->sub->border->right)."px;";
        $str .= "border-style : ".$obj->sub->border->style.";";
        $str .= "border-top-width : ".($obj->sub->border->width * $obj->sub->border->top)."px;";
        $str .= "}";
        if ($obj->sub->border->top == 1 && $obj->sub->border->bottom == 1) {
            $str .= "#".$selector." .main-menu .nav-child li:not(:last-child) > a,#";
            $str .= $selector." .main-menu .nav-child li:not(:last-child) > span {";
            $str .= "border-bottom: none";
            $str .= "}";
        }
        $hoverColor = $obj->nav->hover->color;
        $hoverBackground = $obj->nav->hover->background;
        if (self::$breakpoint != 'desktop' && self::$breakpoint != 'laptop') {
            $hoverColor = $obj->nav->normal->color;
            $hoverBackground = $obj->nav->normal->background;
        }
        $str .= "#".$selector." > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul > li > a:hover,#";
        $str .= $selector." .main-menu li > span:hover {";
        $str .= "color : ".self::getCorrectColor($hoverColor).";";
        $str .= "background-color : ".self::getCorrectColor($hoverBackground).";";
        $str .= "}";
        $hoverColor = $obj->sub->hover->color;
        $hoverBackground = $obj->sub->hover->background;
        if (self::$breakpoint != 'desktop' && self::$breakpoint != 'laptop') {
            $hoverColor = $obj->sub->normal->color;
            $hoverBackground = $obj->sub->normal->background;
        }
        $str .= "#".$selector." .main-menu .nav-child li a:hover,#".$selector." .main-menu .nav-child li span:hover {";
        $str .= "color : ".self::getCorrectColor($hoverColor).";";
        $str .= "background-color : ".self::getCorrectColor($hoverBackground).";";
        $str .= "}";
        $str .= "#".$selector." > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul {";
        $str .= "text-align : ".$obj->{'nav-typography'}->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul > li.active > a,#";
        $str .= $selector." .main-menu li.active > span {";
        $str .= "color : ".self::getCorrectColor($obj->nav->hover->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->nav->hover->background).";";
        $str .= "}";
        $str .= "#".$selector." .main-menu .nav-child li.active > a,#".$selector." .main-menu .nav-child li.active > span {";
        $str .= "color : ".self::getCorrectColor($obj->sub->hover->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->sub->hover->background).";";
        $str .= "}";
        $str .= "#".$selector." ul.nav-child {";
        foreach ($obj->dropdown->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getWeatherRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .weather .city {";
        $str .= self::getTypographyRule($obj->city);
        $str .= "}";
        $str .= "#".$selector." .weather .condition {";
        $str .= self::getTypographyRule($obj->condition);
        $str .= "}";
        $str .= "#".$selector." .weather-info > div,#".$selector." .weather .date {";
        $str .= self::getTypographyRule($obj->info);
        $str .= "}";
        $str .= "#".$selector." .forecast > span {";
        $str .= self::getTypographyRule($obj->forecasts);
        $str .= "}";
        $str .= "#".$selector." .weather-info .wind {";
        if ($obj->view->wind) {
            $str .= "display : inline;";
        } else {
            $str .= "display : none;";
        }
        $str .= "}";
        $str .= "#".$selector." .weather-info .humidity {";
        if ($obj->view->humidity) {
            $str .= "display : inline-block;";
        } else {
            $str .= "display : none;";
        }
        $str .= "}";
        $str .= "#".$selector." .weather-info .pressure {";
        if ($obj->view->pressure) {
            $str .= "display : inline-block;";
        } else {
            $str .= "display : none;";
        }
        $str .= "}";
        $str .= "#".$selector." .weather-info .sunrise-wrapper {";
        if ($obj->view->{'sunrise-wrapper'}) {
            $str .= "display : block;";
        } else {
            $str .= "display : none;";
        }
        $str .= "}";
        if ($obj->view->layout == 'forecast-block') {
            $str .= "#".$selector.' .forecast > span {display: block;width: initial;}';
            $str .= "#".$selector.' .weather-info + div {text-align: center;}';
            $str .= "#".$selector.' .ba-weather div.forecast {margin: 0 20px 0 10px;}';
            $str .= "#".$selector.' .ba-weather div.forecast .day-temp,';
            $str .= "#".$selector.' .ba-weather div.forecast .night-temp {margin: 0 5px;}';
            $str .= "#".$selector.' .ba-weather div.forecast span.night-temp,';
            $str .= "#".$selector.' .ba-weather div.forecast span.day-temp {padding-right: 0;width: initial;}';
        } else {
            $str .= "#".$selector.' .forecast > span {display: inline-block;width: 33.3%;}';
            $str .= "#".$selector.' .weather-info + div {text-align: left;}';
            $str .= "#".$selector.' .ba-weather div.forecast .day-temp,';
            $str .= "#".$selector.' .ba-weather div.forecast .night-temp {margin: 0;}';
            $str .= "#".$selector.' .ba-weather div.forecast {margin: 0;}';
            $str .= "#".$selector.' .ba-weather div.forecast span.night-temp,';
            $str .= "#".$selector.' .ba-weather div.forecast span.day-temp {padding-right: 1.5%;width: 14%;}';
        }
        $str .= "#".$selector." .forecast:nth-child(n) {";
        $str .= "display : none;";
        $str .= "}";
        for ($i = 0; $i < $obj->view->forecast; $i++) {
            $str .= "#".$selector."  .forecast:nth-child(".($i + 1).")";
            if ($i != $obj->view->forecast - 1 ) {
                $str .= ",";
            }
        }
        $str .= " {display: ".($obj->view->layout == 'forecast-block' ? 'inline-block' : 'block').";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getAccordionRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .accordion-group, #".$selector." .accordion-inner {";
        $str .= "border-color: ".self::getCorrectColor($obj->border->color).";"; 
        $str .= "}";
        $str .= "#".$selector." .accordion-inner {";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "background-color: ".self::getCorrectColor($obj->background->color).";";
        $str .= "}";
        $str .= "#".$selector." .accordion-heading a {";
        $str .= self::getTypographyRule($obj->typography, 'text-decoration');
        $str .= "}";
        $str .= "#".$selector." .accordion-heading span.accordion-title {";
        $str .= "text-decoration: ".$obj->typography->{'text-decoration'}.";";
        $str .= "}";
        $str .= "#".$selector." .accordion-heading a i {";
        $str .= "font-size: ".$obj->icon->size."px;";
        $str .= "}";
        $str .= "#".$selector." .accordion-heading {";
        $str .= "background-color: ".self::getCorrectColor($obj->header->color).";";
        $str .= "}";
        if ($obj->icon->position == 'icon-position-left') {
            $str .= "#".$selector.' .accordion-toggle > span {flex-direction: row-reverse;}';
        } else {
            $str .= "#".$selector.' .accordion-toggle > span {flex-direction: row;}';
        }
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getSimpleGalleryRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        if (isset($obj->images)) {
            foreach ($obj->images as $ind => $image) {
                $str .= '#'.$selector.' .ba-instagram-image:nth-child('.($ind * 1 + 1).'):not(.lazy-load-image) {';
                $str .= "background-image: url(".self::setBackgroundImage($image).") !important;";
                $str .= '}';
            }
        }
        $str .= "#".$selector." .ba-instagram-image {";
        if (isset($obj->border)) {
            $str .= "border : ".$obj->border->width."px ".$obj->border->style." ";
            $str .= self::getCorrectColor($obj->border->color).";";
            $str .= "border-radius : ".$obj->border->radius."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image {";
        if ($obj->gutter) {
            $str .= "width: calc((100% / ".$obj->count.") - ".(($obj->count * 10 - 10) / $obj->count)."px);";
        } else {
            $str .= "width: calc(100% / ".$obj->count.");";
        }
        $str .= "height: ".$obj->view->height."px;";
        $str .= "}";
        $str .= "#".$selector." .simple-gallery-masonry-layout {";
        $str .= "grid-template-columns: repeat(auto-fill, minmax(calc((100% / ".$obj->count.") - 20px),1fr));";
        $str .= "}";
        $str .= "#".$selector." .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image:nth-child(n) {";
        $str .= "margin-top: ".($obj->gutter ? 10 : 0)."px;";
        $str .= "}";
        for ($i = 0; $i < $obj->count; $i++) {
            $str .= "#".$selector." .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image:nth-child(".($i + 1).") {";
            $str .= "margin-top: 0;";
            $str .= "}";
        }
        $str .= "#".$selector." .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image:not(:nth-child(".$obj->count."n)) {";
        $str .= "margin-right: ".($obj->gutter ? 5 : 0)."px;";
        $str .= "}";
        $str .= "#".$selector." .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image:not(:nth-child(".$obj->count."n + 1)) {";
        $str .= "margin-left: ".($obj->gutter ? 5 : 0)."px;";
        $str .= "}";
        $str .= "#".$selector." .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image:nth-child(".$obj->count."n) {";
        $str .= "margin-right: 0;";
        $str .= "}";
        $str .= "#".$selector." .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image:nth-child(".$obj->count."n + 1) {";
        $str .= "margin-left: 0;";
        $str .= "}";
        if (isset($obj->overlay)) {
            $str .= "#".$selector." .ba-instagram-image > * {";
            $str .= "transition-duration: ".$obj->animation->duration."s;";
            $str .= "}";
            $str .= "#".$selector." .ba-simple-gallery-caption .ba-caption-overlay {background-color :";
            if (!isset($obj->overlay->type) || $obj->overlay->type == 'color'){
                $str .= self::getCorrectColor($obj->overlay->color).";";
                $str .= 'background-image: none';
            } else if ($obj->overlay->type == 'none') {
                $str .= 'rgba(0, 0, 0, 0);';
                $str .= 'background-image: none;';
            } else {
                $str .= 'rgba(0, 0, 0, 0);';
                $str .= 'background-image: '.$obj->overlay->gradient->effect.'-gradient(';
                if ($obj->overlay->gradient->effect == 'linear') {
                    $str .= $obj->overlay->gradient->angle.'deg';
                } else {
                    $str .= 'circle';
                }
                $str .= ', '.self::getCorrectColor($obj->overlay->gradient->color1).' ';
                $str .= $obj->overlay->gradient->position1.'%, '.self::getCorrectColor($obj->overlay->gradient->color2);
                $str .= ' '.$obj->overlay->gradient->position2.'%);';
                $str .= 'background-attachment: scroll;';
            }
            $str .= "}";
            $str .= "#".$selector." .ba-simple-gallery-title {";
            $str .= self::getTypographyRule($obj->title->typography);
            foreach ($obj->title->margin as $key => $value) {
                $str .= "margin-".$key." : ".$value."px;";
            }
            $str .= "}";
            $str .= "#".$selector." .ba-simple-gallery-description {";
            $str .= self::getTypographyRule($obj->description->typography);
            foreach ($obj->description->margin as $key => $value) {
                $str .= "margin-".$key." : ".$value."px;";
            }
            $str .= "}";
        }
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getProgressBarRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-progress-bar {";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        $str .= 'height: '.$obj->view->height.'px;';
        $str .= "background-color: ".self::getCorrectColor($obj->view->background).";";
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ";
        $str .= self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-animated-bar {";
        $str .= "background-color: ".self::getCorrectColor($obj->view->bar).";";
        $str .= self::getTypographyRule($obj->typography);
        $str .= "}";
        $str .= "#".$selector." .progress-bar-title {display: ";
        if ($obj->display->label) {
            $str .= 'inline-block;';
        } else {
            $str .= 'none;';
        }
        $str .= "}";
        $str .= "#".$selector." .progress-bar-number {display: ";
        if ($obj->display->target) {
            $str .= 'inline-block;';
        } else {
            $str .= 'none;';
        }
        $str .= "}";

        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getProgressPieRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-progress-pie {";
        $str .= 'width: '.$obj->view->width.'px;';
        $str .= self::getTypographyRule($obj->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-progress-pie canvas {";
        $str .= 'width: '.$obj->view->width.'px;';
        $str .= "}";
        $str .= "#".$selector." .progress-pie-number {display: ";
        if ($obj->display->target) {
            $str .= 'inline-block;';
        } else {
            $str .= 'none;';
        }
        $str .= "}";

        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getEventCalendarRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-event-calendar-title-wrapper {";
        $str .= self::getTypographyRule($obj->months->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-event-calendar-header * {";
        $str .= self::getTypographyRule($obj->weeks->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-event-calendar-body * {";
        $str .= self::getTypographyRule($obj->days->typography);
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getCommentsBoxRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-comment-message, #".$selector." .user-comment-wrapper {";
        $str .= "background-color : ".self::getCorrectColor($obj->background->color).";";
        $str .= "border-top-width : ".$obj->border->width * $obj->border->top."px;";
        $str .= "border-right-width : ".$obj->border->width * $obj->border->right."px;";
        $str .= "border-bottom-width : ".$obj->border->width * $obj->border->bottom."px;";
        $str .= "border-left-width : ".$obj->border->width * $obj->border->left."px;";
        $str .= "border-style : ".$obj->border->style.";";
        $str .= "border-color : ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .comment-message, #".$selector." .ba-comment-message::placeholder, ";
        $str .= "#".$selector." .ba-comments-total-count-wrapper select, #".$selector." .ba-comment-message, ";
        $str .= "#".$selector." .comment-delete-action, #".$selector." .comment-edit-action, ";
        $str .= "#".$selector." .comment-likes-action-wrapper > span > span, ";
        $str .= "#".$selector." .ba-review-rate-title, ";
        $str .= "#".$selector." span.ba-comment-attachment-trigger, ";
        $str .= "#".$selector." .comment-likes-wrapper .comment-action-wrapper > span.comment-reply-action > span, ";
        $str .= "#".$selector." .comment-likes-wrapper .comment-action-wrapper > span.comment-share-action > span, ";
        $str .= "#".$selector." .comment-user-date, #".$selector." .ba-social-login-wrapper > span, ";
        $str .= "#".$selector." .ba-user-login-btn, #".$selector." .ba-guest-login-btn, #".$selector." .comment-logout-action, ";
        $str .= "#".$selector." .comment-user-name, #".$selector." .ba-comments-total-count {";
        $str .= self::getTypographyRule($obj->typography);
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getFieldRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-field-wrapper {";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "border-top-width : ".$obj->border->width * $obj->border->top."px;";
        $str .= "border-right-width : ".$obj->border->width * $obj->border->right."px;";
        $str .= "border-bottom-width : ".$obj->border->width * $obj->border->bottom."px;";
        $str .= "border-left-width : ".$obj->border->width * $obj->border->left."px;";
        $str .= "border-style : ".$obj->border->style.";";
        $str .= "border-color : ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-field-label, #".$selector." .ba-field-label *:not(i):not(.ba-tooltip) {";
        $str .= self::getTypographyRule($obj->title->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-field-label i {";
        $str .= "color : ".self::getCorrectColor($obj->icons->color).";";
        $str .= "font-size : ".$obj->icons->size."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-field-content {";
        $str .= self::getTypographyRule($obj->value->typography);
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getFieldsFilterRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "background-color : ".self::getCorrectColor($obj->background->color).";";
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-field-filter-label, #".$selector." .ba-selected-filter-values-title {";
        $str .= self::getTypographyRule($obj->title->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-field-filter-value-wrapper, #".$selector." .ba-selected-filter-values-remove-all {";
        $str .= self::getTypographyRule($obj->value->typography);
        $str .= '--filter-value-line-height: '.$obj->value->typography->{'line-height'}.'px;';
        $str .= "}";
        $justify = str_replace('right', 'flex-start', $obj->value->typography->{'text-align'});
        $justify = str_replace('left', 'flex-end', $justify);
        $str .= "#".$selector." .ba-checkbox-wrapper {";
        $str .= "justify-content: ".$justify.";";
        $str .= "}";
        $str .= "#".$selector." .ba-field-filter {";
        $str .= "display: none;";
        $str .= "}";
        $visibleField = null;
        foreach (self::$blogPostsFields as $field) {
            if ($obj->fields->{$field}) {
               $visibleField = $field;
            }
            $str .= '#'.$selector.' .ba-field-filter[data-id="'.$field.'"] {';
            $str .= "display: ".($obj->fields->{$field} ? 'flex' : 'none').";";
            $str .= "}";
        }
        if ($visibleField) {
            $str .= '#'.$selector.' .ba-field-filter[data-id="'.$visibleField.'"] {';
            $str .= "margin-bottom: 0;";
            $str .= "}";
        }
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getModulesRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getErrorRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." h1.ba-error-code {";
        $str .= self::getTypographyRule($obj->code->typography);
        foreach ($obj->code->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "display: ".($obj->view->code ? "block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." p.ba-error-message {";
        $str .= self::getTypographyRule($obj->message->typography);
        foreach ($obj->message->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "display: ".($obj->view->message ? "block" : "none").";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getSearchHeadlineRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .search-result-headline-wrapper > * {";
        $str .= self::getTypographyRule($obj->typography);
        $str .= "}";        
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getTextRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $array = array('p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6');
        foreach ($array as $key => $value) {
            if (isset($obj->{$value}->{'font-style'}) && $obj->{$value}->{'font-style'} == '@default') {
                unset($obj->{$value}->{'font-style'});
            }
            $str .= "#".$selector." ".$value." {";
            $str .= self::getTypographyRule($obj->{$value}, '', $value);
            if (isset($obj->animation)) {
                $str .= 'animation-duration: '.$obj->animation->duration.'s;';
            }
            $str .= ";}";
        }
        if (isset($obj->links) && isset($obj->links->color)) {
            $str .= "#".$selector.' a {';
            $str .= 'color:'.self::getCorrectColor($obj->links->color).';';
            $str .= '}';
        }
        if (isset($obj->links) && isset($obj->links->{'hover-color'})) {
            $str .= "#".$selector.' a:hover {';
            $str .= 'color:'.self::getCorrectColor($obj->links->{'hover-color'}).';';
            $str .= '}';
        }
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getTabsRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $align = str_replace('left', 'flex-start', $obj->typography->{'text-align'});
        $align = str_replace('right', 'flex-end', $align);
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .tab-content {";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "background-color: ".self::getCorrectColor($obj->background->color).";";
        $str .= "}";
        $str .= "#".$selector." ul.nav.nav-tabs li a {";
        $str .= self::getTypographyRule($obj->typography, 'text-decoration');
        $str .= 'align-items:'.$align.';';
        $str .= "}";
        $str .= "#".$selector." li span.tabs-title {";
        $str .= "text-decoration : ".$obj->typography->{'text-decoration'}.";";
        $str .= "}";
        $str .= "#".$selector." ul.nav.nav-tabs li a i {";
        $str .= "font-size: ".$obj->icon->size."px;";
        $str .= "}";
        $str .= "#".$selector." ul.nav.nav-tabs li.active a {";
        $str .= "color : ".self::getCorrectColor($obj->hover->color).";";
        $str .= "}";
        $str .= "#".$selector." ul.nav.nav-tabs li.active a:before {";
        $str .= "background-color : ".self::getCorrectColor($obj->hover->color).";";
        $str .= "}";
        $str .= "#".$selector." ul.nav.nav-tabs {";
        $str .= "background-color: ".self::getCorrectColor($obj->header->color).";";
        $str .= "border-color: ".self::getCorrectColor($obj->header->border).";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getCounterRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "text-align : ".$obj->counter->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-counter span.counter-number {";
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "background-color: ".self::getCorrectColor($obj->background->color).";";
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        $str .= self::getTypographyRule($obj->counter, 'text-align');
        $str .= "width : ".$obj->counter->{'line-height'}."px;";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getCountdownRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-countdown > span {";
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "background-color: ".self::getCorrectColor($obj->background->color).";";
        $str .= "}";
        $str .= "#".$selector." .countdown-time {";
        $str .= self::getTypographyRule($obj->counter);
        $str .= "}";
        $str .= "#".$selector." .countdown-label {";
        $str .= self::getTypographyRule($obj->label);
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function prepareParentFonts($params)
    {
        self::$parentFonts = $params;
    }

    public static function getTextParentFamily($key)
    {
        if (!isset(self::$parentFonts->desktop->body)) {
            $empty = new stdClass();
            self::$parentFonts->desktop->body = self::object_extend($empty, self::$parentFonts->desktop->p);
        }
        $family = self::$parentFonts->desktop->{$key}->{'font-family'};
        if ($family == '@default') {
            $family = self::$parentFonts->desktop->body->{'font-family'};
        }

        return $family;
    }

    public static function getTextParentWeight($key)
    {
        $weight = self::$parentFonts->desktop->{$key}->{'font-weight'};
        if ($weight == '@default') {
            $weight = self::$parentFonts->desktop->body->{'font-weight'};
        }

        return $weight;
    }

    public static function getTextParentCustom($key)
    {
        $obj = self::$parentFonts->desktop->{$key};
        $custom = isset($obj->custom) ? $obj->custom : '';
        $family = $obj->{'font-family'};
        if ($family == '@default') {
            $body = self::$parentFonts->desktop->body;
            $custom = isset($body->custom) ? $body->custom : '';
        }

        return $custom;
    }

    public static function getTypographyRule($obj, $not = '', $ind = null, $variables = false, $varKey = '')
    {
        $str = "";
        $family = $weight = $custom = '';
        $font = $ind ? $ind : 'body';
        foreach ($obj as $key => $value) {
            if ($key == $not) {
                continue;
            }
            if ($key != 'custom') {
                $str .= ($variables ? $varKey.'-' : '').$key.": ";
            }
            if ($key == 'font-family') {
                $family = $value;
                if ($family == '@default') {
                    $family = self::getTextParentFamily($font);
                }
                $str .= "'".str_replace('+', ' ', $family)."'";
            } else if ($key == 'font-weight') {
                $weight = $value;
                if ($weight == '@default') {
                    $weight = self::getTextParentWeight($font);
                }
                $str .= str_replace('i', '', $weight);
            } else if ($key == 'color') {
                $str .= self::getCorrectColor($value);
            } else if ($key != 'custom') {
                $str .= $value;
            } else if ($key = 'custom') {
                $custom = $value;
            }
            if ($key == 'letter-spacing' || $key == 'font-size' || $key == 'line-height') {
                $str .= "px";
            }
            $str .= ";";
        }
        if (isset($obj->{'font-family'}) && $obj->{'font-family'} == '@default') {
            $custom = self::getTextParentCustom($font);
        }
        if (!empty($family)) {
            if (!empty($custom) && $custom != 'web-safe-fonts') {
                if (!isset(self::$customFonts[$family])) {
                    self::$customFonts[$family] = array();
                }
                if (!in_array($weight, self::$customFonts[$family])) {
                    self::$customFonts[$family][$weight] = $custom;
                }
            } else if (empty($custom)) {
                if (!isset(self::$fonts[$family])) {
                    self::$fonts[$family] = array();
                }
                if (!in_array($weight, self::$fonts[$family])) {
                    self::$fonts[$family][] = $weight;
                }
            }
        }
        
        return $str;
    }

    public static function getSearchRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-search-wrapper input::-webkit-input-placeholder {";
        $str .= self::getTypographyRule($obj->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-search-wrapper input::-moz-placeholder {";
        $str .= self::getTypographyRule($obj->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-search-wrapper input {";
        $str .= self::getTypographyRule($obj->typography, 'text-align');
        $str .= "height : ".$obj->typography->{'line-height'}."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-search-wrapper {";
        if ($obj->border->bottom == 1) {
            $str .= "border-bottom-width : ".$obj->border->width."px;";
        } else {
            $str .= "border-bottom-width : 0;";
        }
        $str .= "border-color : ".self::getCorrectColor($obj->border->color).";";
        if ($obj->border->left == 1) {
            $str .= "border-left-width : ".$obj->border->width."px;";
        } else {
            $str .= "border-left-width : 0;";
        }
        $str .= "border-radius : ".$obj->border->radius."px;";
        if ($obj->border->right == 1) {
            $str .= "border-right-width : ".$obj->border->width."px;";
        } else {
            $str .= "border-right-width : 0;";
        }
        $str .= "border-style : ".$obj->border->style.";";
        if ($obj->border->top == 1) {
            $str .= "border-top-width : ".$obj->border->width."px;";
        } else {
            $str .= "border-top-width : 0;";
        }
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-search-wrapper i {";
        $str .= "color: ".self::getCorrectColor($obj->typography->color).";";
        $str .= "font-size : ".$obj->icons->size."px;";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getCheckoutFormRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "--margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "body {";
        $str .= self::getTypographyRule($obj->title->typography, '', '', true, '--title');
        if (isset($obj->headline)) {
            $str .= self::getTypographyRule($obj->headline->typography, '', '', true, '--headline');
        }
        $str .= "}";
        $str .= "body {";
        $str .= "--background-color: ".self::getCorrectColor($obj->field->background->color).";";
        $str .= "--border-bottom-width: ".($obj->field->border->width * (int)$obj->field->border->bottom)."px;";
        $str .= "--border-color: ".self::getCorrectColor($obj->field->border->color).";";
        $str .= "--border-left-width: ".($obj->field->border->width * (int)$obj->field->border->left)."px;";
        $str .= "--border-radius: ".$obj->field->border->radius."px;";
        $str .= "--border-right-width: ".($obj->field->border->width * (int)$obj->field->border->right)."px;";
        $str .= "--border-style: ".$obj->field->border->style.";";
        $str .= "--border-top-width: ".($obj->field->border->width * (int)$obj->field->border->top)."px;";
        $str .= self::getTypographyRule($obj->field->typography, '', '', true, '--field');
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getIconListRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $align = $obj->body->{'text-align'};
        $align = str_replace('left', 'flex-start', $align);
        $align = str_replace('right', 'flex-end', $align);
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-icon-list-wrapper ul {";
        $str .= "align-items: ".$align.";";
        $str .= "justify-content: ".$align.";";
        $str .= "}";
        if (isset($obj->padding)) {
            $str .= "#".$selector." .ba-icon-list-wrapper ul li {";
            $str .= "background-color:".self::getCorrectColor($obj->background->color).';';
            $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
            $str .= "border-radius : ".$obj->border->radius."px;";
            $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
            $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
            foreach ($obj->padding as $key => $value) {
                $str .= "padding-".$key." : ".$value."px;";
            }
            $str .= "}";
        }
        $str .= "#".$selector." .ba-icon-list-wrapper ul li span {";
        $str .= self::getTypographyRule($obj->body);
        $str .= "}";
        $str .= "#".$selector." .ba-icon-list-wrapper ul li {";
        if (isset($obj->body->{'line-height'})) {
            $str .= '--icon-list-line-height: '.$obj->body->{'line-height'}.'px;';
        }
        $str .= "}";
        $str .= "#".$selector." .ba-icon-list-wrapper ul li i, #".$selector." ul li a:before, #";
        $str .= $selector." ul li.list-item-without-link:before {";
        $str .= "color: ".self::getCorrectColor($obj->icons->color).";";
        $str .= "font-size: ".$obj->icons->size."px;";
        if (isset($obj->icons->background)) {
            $str .= "background-color: ".self::getCorrectColor($obj->icons->background).";";
            $str .= "padding: ".$obj->icons->padding."px;";
            $str .= "border-radius: ".$obj->icons->radius."px;";
        }
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getButtonRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-button-wrapper {";
        $str .= "text-align: ".$obj->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-button-wrapper a span {";
        $str .= self::getTypographyRule($obj->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-button-wrapper a {";
        $str .= "color : ".self::getCorrectColor($obj->normal->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->normal->{'background-color'}).";";
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        if (isset($obj->icons)) {
            $str .= "#".$selector." .ba-button-wrapper a i {";
            $str .= "font-size : ".$obj->icons->size."px;";
            $str .= "}";
        }
        if (isset($obj->icons) && isset($obj->icons->position)) {
            $str .= "#".$selector." .ba-button-wrapper a {";
            if ($obj->icons->position == '') {
                $str .= 'flex-direction: row-reverse;';
            } else {
                $str .= 'flex-direction: row;';
            }
            $str .= "}";
            if ($obj->icons->position == '') {
                $str .= "#".$selector." .ba-button-wrapper a i {";
                $str .= 'margin: 0 10px 0 0;';
                $str .= "}";
            } else {
                $str .= "#".$selector." .ba-button-wrapper a i {";
                $str .= 'margin: 0 0 0 10px;';
                $str .= "}";
            }
        }
        if (isset($obj->view) && isset($obj->view->subtotal)) {
            $str .= "#".$selector." .ba-button-wrapper a span.ba-cart-subtotal {";
            $str .= 'display: '.($obj->view->subtotal ? 'flex' : 'none').';';
            $str .= "}";
        }
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getCategoriesRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-masonry-layout {";
        $str .= "grid-template-columns: repeat(auto-fill, minmax(calc((100% / ".$obj->view->count.") - 21px),1fr));";
        $str .= "}";
        $str .= "#".$selector." .ba-grid-layout .ba-blog-post, #".$selector." .ba-classic-layout .ba-blog-post {";
        $str .= "width: calc((100% / ".$obj->view->count.") - 21px);";
        $str .= "}";
        $str .= "#".$selector." .ba-one-column-grid-layout .ba-blog-post {";
        $str .= "width: calc(100% - 21px);";
        $str .= "}";
        $str .= "#".$selector." .ba-grid-layout .ba-blog-post:nth-child(n) {";
        $str .= "margin-top: 30px;";
        $str .= "}";
        $str .= "#".$selector." .ba-classic-layout .ba-blog-post:nth-child(n) {";
        $str .= "margin-top: ".($obj->view->image ? 30 : 0)."px;";
        $str .= "}";
        for ($i = 0; $i < $obj->view->count; $i++) {
            $str .= "#".$selector." .ba-grid-layout .ba-blog-post:nth-child(".($i + 1)."), #";
            $str .= $selector." .ba-classic-layout .ba-blog-post:nth-child(".($i + 1).") {";
            $str .= "margin-top: 0;";
            $str .= "}";
        }
        $str .= "#".$selector." .ba-cover-layout .ba-blog-post:nth-child(n) {";
        $str .= "margin-top: ".($obj->view->gutter ? 30 : 0)."px;";
        $str .= "}";
        for ($i = 0; $i < $obj->view->count; $i++) {
            $str .= "#".$selector." .ba-cover-layout .ba-blog-post:nth-child(".($i + 1).") {";
            $str .= "margin-top: 0;";
            $str .= "}";
        }
        $str .= "#".$selector." .ba-overlay {background-color:";
        if (!isset($obj->overlay->type) || $obj->overlay->type == 'color'){
            $str .= self::getCorrectColor($obj->overlay->color).";";
            $str .= 'background-image: none';
        } else if ($obj->overlay->type == 'none') {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: none;';
        } else {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: '.$obj->overlay->gradient->effect.'-gradient(';
            if ($obj->overlay->gradient->effect == 'linear') {
                $str .= $obj->overlay->gradient->angle.'deg';
            } else {
                $str .= 'circle';
            }
            $str .= ', '.self::getCorrectColor($obj->overlay->gradient->color1).' ';
            $str .= $obj->overlay->gradient->position1.'%, '.self::getCorrectColor($obj->overlay->gradient->color2);
            $str .= ' '.$obj->overlay->gradient->position2.'%);';
            $str .= 'background-attachment: scroll;';
        }
        $str .= '}';
        if ($obj->view->gutter) {
            $str .= "#".$selector." .ba-cover-layout .ba-blog-post {";
            $str .= "margin-left: 10px;margin-right: 10px;";
            $str .= "width: calc((100% / ".$obj->view->count.") - 21px);";
            $str .= "}";
            $str .= "#".$selector." .ba-cover-layout {margin-left: -10px;margin-right: -10px;}";
        } else {
            $str .= "#".$selector." .ba-cover-layout .ba-blog-post {";
            $str .= "margin-left: 0;margin-right: 0;";
            $str .= "width: calc(100% / ".$obj->view->count.");";
            $str .= "}";
            $str .= "#".$selector." .ba-cover-layout {margin-left: 0;margin-right: 0;}";
        }
        $str .= "#".$selector." .ba-blog-post {";
        $str .= "background-color:".self::getCorrectColor($obj->background->color).';';
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-image {";
        $str .= "background-size: ".$obj->image->size.";";
        $str .= "border : ".$obj->image->border->width."px ";
        $str .= $obj->image->border->style." ".self::getCorrectColor($obj->image->border->color).";";
        $str .= "border-radius : ".$obj->image->border->radius."px;";
        $str .= "display:".($obj->view->image ? "block" : "none").";";
        $str .= "width :".$obj->image->width."px;";
        $str .= "height :".$obj->image->height."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post {";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-masonry-layout .ba-blog-post-image {";
        $str .= "width: 100%;";
        $str .= "height: auto;";
        $str .= "}";
        $str .= "#".$selector." .ba-cover-layout .ba-blog-post {";
        $str .= "height :".$obj->image->height."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-title {";
        foreach ($obj->title->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= self::getTypographyRule($obj->title->typography);
        $str .= "display:".($obj->view->title ? "block" : "none").";";
        $str .= "}";
        $justify = str_replace('left', 'flex-start', $obj->info->typography->{'text-align'});
        $justify = str_replace('right', 'flex-end', $justify);
        $str .= "#".$selector." .ba-blog-post-info-wrapper {";
        $str .= "display:".($obj->view->sub ? "block" : "none").";";
        foreach ($obj->info->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "justify-content :".$justify.";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-info-wrapper > * {";
        $str .= self::getTypographyRule($obj->info->typography, 'text-align');
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-intro-wrapper {";
        $str .= "display:".($obj->view->intro ? "block" : "none").";";
        $str .= self::getTypographyRule($obj->intro->typography);
        foreach ($obj->intro->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-app-category-counter {";
        $str .= "display:".($obj->view->counter ? "inline" : "none").";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getAddToCartRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-add-to-cart-stock {";
        $str .= "display:".($obj->view->availability ? "flex" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-add-to-cart-sku {";
        $str .= "display:".($obj->view->sku ? "flex" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-add-to-cart-quantity {";
        $str .= "display:".($obj->view->quantity ? "flex" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-add-to-cart-button-wrapper a {";
        $str .= "display:".($obj->view->button ? "flex" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-add-to-wishlist {";
        $str .= "display:".($obj->view->wishlist ? "flex" : "none").";";
        $str .= "}";
        $justify = $obj->price->typography->{'text-align'};
        $justify = $justify == 'left' ? 'flex-start' : ($justify == 'right' ? 'flex-end' : $justify);
        $str .= "#".$selector." .ba-add-to-cart-price {";
        $str .= "align-items: ".$justify.";";
        $str .= "justify-content :".$justify.";";
        foreach ($obj->price->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= self::getTypographyRule($obj->price->typography, 'text-align');
        $str .= "}";
        $str .= "#".$selector." .ba-add-to-cart-info {";
        foreach ($obj->info->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= self::getTypographyRule($obj->info->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-add-to-cart-variations, #".$selector." .ba-add-to-cart-extra-options {";
        $str .= self::getTypographyRule($obj->info->typography);
        $str .= "}";
        $family = $obj->button->typography->{'font-family'};
        if ($family == '@default') {
            $family = self::getTextParentFamily('body');
        }
        $str .= "#".$selector." .ba-add-to-cart-quantity {";
        $str .= "font-family: '".str_replace('+', ' ', $family)."';";
        $str .= 'font-size: '.$obj->button->typography->{'font-size'}.'px;';
        $str .= 'letter-spacing: '.$obj->button->typography->{'letter-spacing'}.'px;';
        $str .= 'color: '.self::getCorrectColor($obj->price->typography->color).';';
        $str .= "}";
        $justify = $obj->button->typography->{'text-align'};
        $justify = $justify == 'left' ? 'flex-start' : ($justify == 'right' ? 'flex-end' : $justify);
        $str .= "#".$selector." .ba-add-to-cart-button-wrapper {";
        $str .= "justify-content :".$justify.";";
        foreach ($obj->button->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-add-to-cart-buttons-wrapper {";
        $str .= "background-color: ".self::getCorrectColor($obj->button->normal->background).";";
        $str .= "border-color : ".self::getCorrectColor($obj->button->border->color).";";
        $str .= "border-style : ".$obj->button->border->style.";";
        $str .= "--border-width : ".$obj->button->border->width."px;";
        $str .= "--border-radius : ".$obj->button->border->radius."px;";
        $str .= "--display-wishlist: ".($obj->view->wishlist ? 0 : 1).";";
        $str .= "box-shadow: 0 ".($obj->button->shadow->value * 10);
        $str .= "px ".($obj->button->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->button->shadow->color).";";
        foreach ($obj->button->padding as $key => $value) {
            $str .= "--padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-add-to-cart-button-wrapper a, #".$selector." .ba-add-to-wishlist {";
        $str .= self::getTypographyRule($obj->button->typography, 'text-align');
        $str .= "background-color: ".self::getCorrectColor($obj->button->normal->background).";";
        $str .= "color: ".self::getCorrectColor($obj->button->normal->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-add-to-cart-button-wrapper a:hover, #".$selector." .ba-add-to-wishlist:hover {";
        $str .= "background-color: ".self::getCorrectColor($obj->button->hover->background).";";
        $str .= "color: ".self::getCorrectColor($obj->button->hover->color).";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getBlogPostsRules($obj, $selector, $type)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-masonry-layout {";
        $str .= "grid-template-columns: repeat(auto-fill, minmax(calc((100% / ".$obj->view->count.") - 21px),1fr));";
        $str .= "}";
        $str .= "#".$selector." .ba-grid-layout .ba-blog-post {";
        $str .= "width: calc((100% / ".$obj->view->count.") - 21px);";
        $str .= "}";
        $str .= "#".$selector." .ba-one-column-grid-layout .ba-blog-post {";
        $str .= "width: calc(100% - 21px);";
        $str .= "}";
        $str .= "#".$selector." .ba-grid-layout .ba-blog-post:nth-child(n) {";
        $str .= "margin-top: 30px;";
        $str .= "}";
        for ($i = 0; $i < $obj->view->count; $i++) {
            $str .= "#".$selector." .ba-grid-layout .ba-blog-post:nth-child(".($i + 1).") {";
            $str .= "margin-top: 0;";
            $str .= "}";
        }
        $str .= "#".$selector." .ba-cover-layout .ba-blog-post:nth-child(n) {";
        $str .= "margin-top: ".($obj->view->gutter ? 30 : 0)."px;";
        $str .= "}";
        for ($i = 0; $i < $obj->view->count; $i++) {
            $str .= "#".$selector." .ba-cover-layout .ba-blog-post:nth-child(".($i + 1).") {";
            $str .= "margin-top: 0;";
            $str .= "}";
        }
        $str .= "#".$selector." .ba-overlay {background-color:";
        if (!isset($obj->overlay->type) || $obj->overlay->type == 'color'){
            $str .= self::getCorrectColor($obj->overlay->color).";";
            $str .= 'background-image: none';
        } else if ($obj->overlay->type == 'none') {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: none;';
        } else {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: '.$obj->overlay->gradient->effect.'-gradient(';
            if ($obj->overlay->gradient->effect == 'linear') {
                $str .= $obj->overlay->gradient->angle.'deg';
            } else {
                $str .= 'circle';
            }
            $str .= ', '.self::getCorrectColor($obj->overlay->gradient->color1).' ';
            $str .= $obj->overlay->gradient->position1.'%, '.self::getCorrectColor($obj->overlay->gradient->color2);
            $str .= ' '.$obj->overlay->gradient->position2.'%);';
            $str .= 'background-attachment: scroll;';
        }
        $str .= '}';
        if ($obj->view->gutter) {
            $str .= "#".$selector." .ba-cover-layout .ba-blog-post {";
            $str .= "margin-left: 10px;margin-right: 10px;";
            $str .= "width: calc((100% / ".$obj->view->count.") - 21px);";
            $str .= "}";
            $str .= "#".$selector." .ba-cover-layout {margin-left: -10px;margin-right: -10px;}";
        } else {
            $str .= "#".$selector." .ba-cover-layout .ba-blog-post {";
            $str .= "margin-left: 0;margin-right: 0;";
            $str .= "width: calc(100% / ".$obj->view->count.");";
            $str .= "}";
            $str .= "#".$selector." .ba-cover-layout {margin-left: 0;margin-right: 0;}";
        }
        if (isset($obj->background)) {
            $str .= "#".$selector." .ba-blog-post {";
            $str .= "background-color:".self::getCorrectColor($obj->background->color).';';
            $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
            $str .= "border-radius : ".$obj->border->radius."px;";
            $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
            $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
            $str .= "}";
        }
        if (isset($obj->image->border)) {
            $str .= "#".$selector." .ba-blog-post-image {";
            $str .= "border : ".$obj->image->border->width."px ";
            $str .= $obj->image->border->style." ".self::getCorrectColor($obj->image->border->color).";";
            $str .= "border-radius : ".$obj->image->border->radius."px;";
            $str .= "}";
        }
        if (isset($obj->padding)) {
            $str .= "#".$selector." .ba-blog-post {";
            foreach ($obj->padding as $key => $value) {
                $str .= "padding-".$key." : ".$value."px;";
            }
            $str .= "}";
        }
        if (!isset($obj->view->author)) {
            $obj->view->author = false;
        }
        if (!isset($obj->view->comments)) {
            $obj->view->comments = false;
        }
        if (!isset($obj->view->reviews)) {
            $obj->view->reviews = false;
        }
        if (isset($obj->view->sorting)) {
            $str .= "#".$selector." .blog-posts-sorting-wrapper {";
            $str .= "display:".($obj->view->sorting ? "flex" : "none").";";
            $str .= "}";
        }
        $str .= "#".$selector." .ba-blog-post-image {";
        $str .= "display:".($obj->view->image ? "block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-title-wrapper {";
        $str .= "display:".($obj->view->title ? "block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-author {";
        $str .= "display:".($obj->view->author ? "inline-block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-date {";
        $str .= "display:".($obj->view->date ? "inline-block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-category {";
        $str .= "display:".($obj->view->category ? "inline-block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-hits {";
        $str .= "display:".($obj->view->hits ? "inline-block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-comments {";
        $str .= "display:".($obj->view->comments ? "inline-block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-product-options {";
        $str .= "display: none;";
        $str .= "}";
        if (isset($obj->store)) {
            $str .= "#".$selector." .ba-blog-post-badge-wrapper {";
            $str .= "display:".(isset($obj->store->badge) && $obj->store->badge ? "flex" : "none").";";
            $str .= "}";
            $str .= "#".$selector." .ba-blog-post-wishlist-wrapper {";
            $str .= "display:".(isset($obj->store->wishlist) && $obj->store->wishlist ? "flex" : "none").";";
            $str .= "}";
            $str .= "#".$selector." .ba-blog-post-add-to-cart-price {";
            $str .= "display:".(isset($obj->store->price) && $obj->store->price ? "flex" : "none").";";
            $str .= "}";
            $str .= "#".$selector." .ba-blog-post-add-to-cart-button {";
            $str .= "display:".(isset($obj->store->cart) && $obj->store->cart ? "flex" : "none").";";
            $str .= "}";
            foreach ($obj->store as $key => $value) {
                if ($key == 'badge' || $key == 'wishlist' || $key == 'price' || $key == 'cart') {
                    continue;
                }
                $str .= "#".$selector.' .ba-blog-post-product-options[data-key="'.$key.'"] {';
                $str .= "display:".($value ? "flex" : "none").";";
                $str .= "}";
            }
        }
        $blogInfoOrder = self::$blogPostsInfo ? self::$blogPostsInfo : array('author', 'date', 'category', 'hits', 'comments');
        $blogInfoVisible = false;
        foreach ($blogInfoOrder as $i => $value) {
            if (isset($obj->view->{$value}) && $obj->view->{$value}) {
                for ($j = $i + 1; $j < count($blogInfoOrder); $j++) {
                    $str .= "#".$selector." .ba-blog-post-".$blogInfoOrder[$j].":before {";
                    $str .= 'margin: 0 10px;content: "'.($blogInfoOrder[$j] == 'author' ? '' : '\2022').'";color: inherit;';
                    $str .= "}";
                }
                $blogInfoVisible = true;
                break;
            }
        }
        $str .= "#".$selector." .ba-blog-post-reviews {";
        $str .= "display:".($obj->view->reviews ? "flex" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-intro-wrapper {";
        $str .= "display:".($obj->view->intro ? "block" : "none").";";
        $str .= "}";
        $str .= '#'.$selector.' .ba-blog-post-field-row {';
        $str .= "display: none;";
        $str .= "}";
        if (isset($obj->fields)) {
            $visibleField = null;
            foreach (self::$blogPostsFields as $i => $value) {
                if ($obj->fields->{$value}) {
                    $visibleField = $i;
                }
                $str .= '#'.$selector.' .ba-blog-post-field-row[data-id="'.$value.'"] {';
                $str .= "display: ".($obj->fields->{$value} ? 'flex' : 'none').";";
                $str .= "margin-bottom: 10px;";
                $str .= "}";
            }
            if ($visibleField) {
                $str .= '#'.$selector.' .ba-blog-post-field-row[data-id="'.$visibleField.'"] {';
                $str .= "margin-bottom: 0;";
                $str .= "}";
            }
        }
        $str .= "#".$selector." .ba-blog-post-button-wrapper {";
        $str .= "display:".($obj->view->button ? "block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-image {";
        $str .= "width :".$obj->image->width."px;";
        $str .= "height :".$obj->image->height."px;";
        $str .= "background-size: ".(isset($obj->image->size) ? $obj->image->size : 'cover').";";
        $str .= "}";
        $str .= "#".$selector." .ba-masonry-layout .ba-blog-post-image {";
        $str .= "width: 100%;";
        $str .= "height: auto;";
        $str .= "}";
        $str .= "#".$selector." .ba-cover-layout .ba-blog-post {";
        $str .= "height :".$obj->image->height."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-title {";
        foreach ($obj->title->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-title, #".$selector." .ba-blog-post-add-to-cart-price {";
        $str .= self::getTypographyRule($obj->title->typography, 'text-align');
        if ($type == 'post-navigation' && $obj->title->typography->{'text-align'} == 'left') {
            $str .= "text-align :right;";
        } else if ($type == 'post-navigation' && $obj->title->typography->{'text-align'} == 'right') {
            $str .= "text-align :left;";
        } else {
            $str .= "text-align :".$obj->title->typography->{'text-align'}.";";
        }
        $str .= "}";
        if ($type == 'post-navigation') {
            $str .= "#".$selector." .ba-blog-post:first-child .ba-blog-post-title {";
            $str .= "text-align :".$obj->title->typography->{'text-align'}.";";
            $str .= "}";
        }
        $justify = str_replace('left', 'flex-start', $obj->reviews->typography->{'text-align'});
        $justify = str_replace('right', 'flex-end', $justify);
        $str .= "#".$selector." .ba-blog-post-reviews {";
        if ($type == 'post-navigation' && $obj->reviews->typography->{'text-align'} == 'left') {
            $str .= "justify-content :flex-end;";
        } else if ($type == 'post-navigation' && $obj->reviews->typography->{'text-align'} == 'right') {
            $str .= "justify-content :flex-start;";
        } else {
            $str .= "justify-content :".$justify.";";
        }
        $str .= self::getTypographyRule($obj->reviews->typography, 'text-align');
        foreach ($obj->reviews->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-reviews a:hover {";
        $str .= "color: ".self::getCorrectColor($obj->reviews->hover->color).";";
        $str .= "}";
        if (isset($obj->postFields)) {
            $str .= "#".$selector." .ba-blog-post-field-row-wrapper {";
            $str .= self::getTypographyRule($obj->postFields->typography, 'text-align');
            foreach ($obj->postFields->margin as $key => $value) {
                $str .= "margin-".$key." : ".$value."px;";
            }
            $str .= "}";
        }
        $justify = str_replace('left', 'flex-start', $obj->info->typography->{'text-align'});
        $justify = str_replace('right', 'flex-end', $justify);
        $str .= "#".$selector." .ba-blog-post-info-wrapper {";
        foreach ($obj->info->margin as $key => $value) {
            $str .= "margin-".$key." : ".($blogInfoVisible ? $value : 0)."px;";
        }
        if ($type == 'post-navigation' && $obj->info->typography->{'text-align'} == 'left') {
            $str .= "justify-content :flex-end;";
        } else if ($type == 'post-navigation' && $obj->info->typography->{'text-align'} == 'right') {
            $str .= "justify-content :flex-start;";
        } else {
            $str .= "justify-content :".$justify.";";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post:first-child .ba-blog-post-info-wrapper {";
        $str .= "justify-content :".$justify.";";
        $str .= "}";
        $str .= "#".$selector." .ba-post-navigation-info {";
        if ($type == 'post-navigation' && $obj->info->typography->{'text-align'} == 'left') {
            $str .= "text-align :right;";
        } else if ($type == 'post-navigation' && $obj->info->typography->{'text-align'} == 'right') {
            $str .= "text-align :left;";
        } else {
            $str .= "text-align :".$obj->info->typography->{'text-align'}.";";
        }
        $str .= "}";
        if ($type == 'post-navigation') {
            $str .= "#".$selector." .ba-blog-post:first-child .ba-post-navigation-info {";
            $str .= "text-align :".$obj->info->typography->{'text-align'}.";";
            $str .= "}";
        }
        $str .= "#".$selector." .ba-blog-post-info-wrapper > *, #".$selector." .ba-post-navigation-info a {";
        $str .= self::getTypographyRule($obj->info->typography, 'text-align');
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-intro-wrapper {";
        $str .= self::getTypographyRule($obj->intro->typography, 'text-align');
        foreach ($obj->intro->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-intro-wrapper {";
        if ($type == 'post-navigation' && $obj->intro->typography->{'text-align'} == 'left') {
            $str .= "text-align :right;";
        } else if ($type == 'post-navigation' && $obj->intro->typography->{'text-align'} == 'right') {
            $str .= "text-align :left;";
        } else {
            $str .= "text-align :".$obj->intro->typography->{'text-align'}.";";
        }
        $str .= "}";
        if ($type == 'post-navigation') {
            $str .= "#".$selector." .ba-blog-post:first-child .ba-blog-post-intro-wrapper {";
            $str .= "text-align :".$obj->intro->typography->{'text-align'}.";";
            $str .= "}";
        }
        $str .= "#".$selector." .ba-blog-post-button-wrapper {";
        if ($type == 'post-navigation' && $obj->button->typography->{'text-align'} == 'left') {
            $str .= "text-align :right;";
        } else if ($type == 'post-navigation' && $obj->button->typography->{'text-align'} == 'right') {
            $str .= "text-align :left;";
        } else {
            $str .= "text-align :".$obj->button->typography->{'text-align'}.";";
        }
        $str .= "}";
        if ($type == 'post-navigation') {
            $str .= "#".$selector." .ba-blog-post:first-child .ba-blog-post-button-wrapper {";
            $str .= "text-align :".$obj->button->typography->{'text-align'}.";";
            $str .= "}";
        }
        $str .= "#".$selector." .ba-blog-post-button-wrapper a {";
        foreach ($obj->button->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-button-wrapper a, #".$selector." .ba-blog-post-add-to-cart {";
        $str .= self::getTypographyRule($obj->button->typography, 'text-align');
        $str .= "border : ".$obj->button->border->width."px ";
        $str .= $obj->button->border->style." ".self::getCorrectColor($obj->button->border->color).";";
        $str .= "border-radius : ".$obj->button->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->button->shadow->value * 10);
        $str .= "px ".($obj->button->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->button->shadow->color).";";
        $str .= "background-color: ".self::getCorrectColor($obj->button->normal->background).";";
        $str .= "color: ".self::getCorrectColor($obj->button->normal->color).";";
        foreach ($obj->button->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-button-wrapper a:hover, #".$selector." .ba-blog-post-add-to-cart:hover {";
        $str .= "background-color: ".self::getCorrectColor($obj->button->hover->background).";";
        $str .= "color: ".self::getCorrectColor($obj->button->hover->color).";";
        $str .= "}";
        if (isset($obj->pagination) && !isset($obj->pagination->typography)) {
            $str .= "#".$selector." .ba-blog-posts-pagination span a {";
            $str .= "color: ".self::getCorrectColor($obj->pagination->color).";";
            $str .= "}";
            $str .= "#".$selector." .ba-blog-posts-pagination span.active a,#".$selector;
            $str .= " .ba-blog-posts-pagination span:hover a {";
            $str .= "color: ".self::getCorrectColor($obj->pagination->hover).";";
            $str .= "}";
        } else if (isset($obj->pagination) && isset($obj->pagination->typography)) {
            $str .= "#".$selector." .ba-blog-posts-pagination {";
            $str .= "text-align :".$obj->pagination->typography->{'text-align'}.";";
            $str .= "}";
            $str .= "#".$selector." .ba-blog-posts-pagination a {";
            foreach ($obj->pagination->margin as $key => $value) {
                $str .= "margin-".$key." : ".$value."px;";
            }
            $str .= self::getTypographyRule($obj->pagination->typography, 'text-align');
            $str .= "border : ".$obj->pagination->border->width."px ";
            $str .= $obj->pagination->border->style." ".self::getCorrectColor($obj->pagination->border->color).";";
            $str .= "border-radius : ".$obj->pagination->border->radius."px;";
            $str .= "box-shadow: 0 ".($obj->pagination->shadow->value * 10);
            $str .= "px ".($obj->pagination->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->pagination->shadow->color).";";
            $str .= "background-color: ".self::getCorrectColor($obj->pagination->normal->background).";";
            $str .= "color: ".self::getCorrectColor($obj->pagination->normal->color).";";
            foreach ($obj->pagination->padding as $key => $value) {
                $str .= "padding-".$key." : ".$value."px;";
            }
            $str .= "}";
            $str .= "#".$selector." .ba-blog-posts-pagination a:hover {";
            $str .= "background-color: ".self::getCorrectColor($obj->pagination->hover->background).";";
            $str .= "color: ".self::getCorrectColor($obj->pagination->hover->color).";";
            $str .= "}";
        }
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getRecentCommentsRules($obj, $selector, $type)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        if (isset($obj->view->count)) {
            $str .= "#".$selector." .ba-masonry-layout {";
            $str .= "grid-template-columns: repeat(auto-fill, minmax(calc((100% / ".$obj->view->count.") - 21px),1fr));";
            $str .= "}";
            $str .= "#".$selector." .ba-grid-layout .ba-blog-post {";
            $str .= "width: calc((100% / ".$obj->view->count.") - 21px);";
            $str .= "}";
            $str .= "#".$selector." .ba-grid-layout .ba-blog-post:nth-child(n) {";
            $str .= "margin-top: 30px;";
            $str .= "}";
            for ($i = 0; $i < $obj->view->count; $i++) {
                $str .= "#".$selector." .ba-grid-layout .ba-blog-post:nth-child(".($i + 1).") {";
                $str .= "margin-top: 0;";
                $str .= "}";
            }
        }
        $str .= "#".$selector." .ba-blog-post {";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "background-color:".self::getCorrectColor($obj->background->color).';';
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-image {";
        $str .= "border : ".$obj->image->border->width."px ";
        $str .= $obj->image->border->style." ".self::getCorrectColor($obj->image->border->color).";";
        $str .= "border-radius : ".$obj->image->border->radius."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-image {";
        $str .= "display:".($obj->view->image ? "block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-date {";
        $str .= "display:".($obj->view->date ? "inline-block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-intro-wrapper {";
        $str .= "display:".($obj->view->intro ? "block" : "none").";";
        $str .= "}";
        if (isset($obj->view->source)) {
            $str .= "#".$selector." .ba-reviews-source {";
            $str .= "display:".($obj->view->source ? "inline-block" : "none").";";
            $str .= "}";
            $str .= "#".$selector." .ba-reviews-name {";
            $str .= "display:".($obj->view->title ? "inline-block" : "none").";";
            $str .= "}";
            $str .= "#".$selector." .ba-blog-post-title-wrapper {";
            $str .= "display:".($obj->view->title || $obj->view->source ? "block" : "none").";";
            $str .= "}";
        } else {
            $str .= "#".$selector." .ba-blog-post-title-wrapper {";
            $str .= "display:".($obj->view->title ? "block" : "none").";";
            $str .= "}";
        }
        $str .= "#".$selector." .ba-blog-post-image {";
        $str .= "width :".$obj->image->width."px;";
        $str .= "height :".$obj->image->height."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-title {";
        foreach ($obj->title->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= self::getTypographyRule($obj->title->typography, 'text-align');
        $str .= "text-align :".$obj->title->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-info-wrapper {";
        foreach ($obj->info->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "text-align :".$obj->info->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-info-wrapper > * {";
        $str .= self::getTypographyRule($obj->info->typography, 'text-align');
        $str .= "}";
        if (isset($obj->stars)) {
            $justify = str_replace('left', 'flex-start', $obj->stars->icon->{'text-align'});
            $justify = str_replace('right', 'flex-end', $justify);
            $str .= "#".$selector." .ba-review-stars-wrapper {";
            foreach ($obj->stars->margin as $key => $value) {
                $str .= "margin-".$key." : ".$value."px;";
            }
            $str .= "font-size: ".$obj->stars->icon->size."px;";
            $str .= "justify-content: ".$justify.";";
            $str .= "}";
        }
        $str .= "#".$selector." .ba-blog-post-intro-wrapper {";
        $str .= self::getTypographyRule($obj->intro->typography, 'text-align');
        foreach ($obj->intro->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-intro-wrapper {";
        $str .= "text-align :".$obj->intro->typography->{'text-align'}.";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getAuthorRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-posts-author-wrapper .ba-post-author {";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-grid-layout .ba-post-author {";
        $str .= "width: calc((100% / ".$obj->view->count.") - 21px);";
        $str .= "}";
        $str .= "#".$selector." .ba-grid-layout .ba-post-author:nth-child(n) {";
        $str .= "margin-top: 30px;";
        $str .= "}";
        for ($i = 0; $i < $obj->view->count; $i++) {
            $str .= "#".$selector." .ba-grid-layout .ba-post-author:nth-child(".($i + 1).") {";
            $str .= "margin-top: 0;";
            $str .= "}";
        }
        $str .= "#".$selector." .ba-overlay {background-color:";
        if (!$obj->overlay->type || $obj->overlay->type == 'color') {
            $str .= self::getCorrectColor($obj->overlay->color).";";
            $str .= 'background-image: none;';
        } else if ($obj->overlay->type == 'none') {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: none;';
        } else {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: '.$obj->overlay->gradient->effect.'-gradient(';
            if ($obj->overlay->gradient->effect == 'linear') {
                $str .= $obj->overlay->gradient->angle.'deg';
            } else {
                $str .= 'circle';
            }
            $str .= ', '.self::getCorrectColor($obj->overlay->gradient->color1).' ';
            $str .= $obj->overlay->gradient->position1.'%, '.self::getCorrectColor($obj->overlay->gradient->color2);
            $str .= ' '.$obj->overlay->gradient->position2.'%);';
            $str .= 'background-attachment: scroll;';
        }
        $str .= '}';
        $str .= "#".$selector." .ba-post-author {";
        $str .= "background-color:".self::getCorrectColor($obj->background->color).';';
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-post-author-image {";
        $str .= "border : ".$obj->image->border->width."px ".$obj->image->border->style." ";
        $str .= self::getCorrectColor($obj->image->border->color).";";
        $str .= "border-radius : ".$obj->image->border->radius."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-post-author-image {";
        $str .= "display:".($obj->view->image ? "block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-post-author-title-wrapper {";
        $str .= "display:".($obj->view->title ? "block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-post-author-description {";
        $str .= "display:".($obj->view->intro ? "block" : "none").";";
        $str .= "}";
        $str .= "#".$selector." .ba-post-author-image {";
        $str .= "width :".$obj->image->width."px;";
        $str .= "height :".$obj->image->height."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-post-author-title {";
        foreach ($obj->title->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= self::getTypographyRule($obj->title->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-post-author-social-wrapper {";
        $str .= "text-align: ".$obj->intro->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-post-author-social-wrapper a {";
        $str .= "color: ".self::getCorrectColor($obj->intro->typography->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-post-author-description {";
        $str .= self::getTypographyRule($obj->intro->typography);
        foreach ($obj->intro->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getPostIntroRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .intro-post-wrapper.fullscreen-post {";
        $str .= "height :".$obj->image->height."px;";
        if ($obj->image->fullscreen) {
            $str .= "min-height: 100vh;";
        } else {
            $str .= "min-height: auto;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-overlay {background-color:";
        if (!isset($obj->image->type) || $obj->image->type == 'color'){
            $str .= self::getCorrectColor($obj->image->color).";";
            $str .= 'background-image: none';
        } else if ($obj->image->type == 'none') {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: none;';
        } else {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: '.$obj->image->gradient->effect.'-gradient(';
            if ($obj->image->gradient->effect == 'linear') {
                $str .= $obj->image->gradient->angle.'deg';
            } else {
                $str .= 'circle';
            }
            $str .= ', '.self::getCorrectColor($obj->image->gradient->color1).' ';
            $str .= $obj->image->gradient->position1.'%, '.self::getCorrectColor($obj->image->gradient->color2);
            $str .= ' '.$obj->image->gradient->position2.'%);';
            $str .= 'background-attachment: scroll;';
        }
        $str .= '}';
        $str .= "#".$selector." .intro-post-image {";
        $str .= "height :".$obj->image->height."px;";
        $str .= "background-attachment: ".$obj->image->attachment.";";
        $str .= "background-position: ".$obj->image->position.";";
        $str .= "background-repeat: ".$obj->image->repeat.";";
        $str .= "background-size: ".$obj->image->size.";";
        if ($obj->image->fullscreen) {
            $str .= "min-height: 100vh;";
        } else {
            $str .= "min-height: auto;";
        }
        $str .= "}";
        $str .= "#".$selector." .intro-post-title-wrapper {";
        $str .= "text-align :".$obj->title->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .intro-post-title {";
        $str .= self::getTypographyRule($obj->title->typography, 'text-align');
        foreach ($obj->title->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $justify = str_replace('left', 'flex-start', $obj->info->typography->{'text-align'});
        $justify = str_replace('right', 'flex-end', $justify);
        $str .= "#".$selector." .intro-post-info {";
        $str .= "text-align :".$obj->info->typography->{'text-align'}.";";
        $str .= "justify-content: ".$justify.";";
        foreach ($obj->info->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        if (isset($obj->info->show)) {
            $str .= 'display:'.($obj->info->show ? 'block' : 'none').';';
        }
        if (!isset($obj->image->show)) {
            $obj->image->show = $obj->title->show = $obj->date = $obj->category = $obj->hits = true;
        }
        $str .= "}";
        $str .= "#".$selector." .intro-post-info *:not(i):not(a) {";
        $str .= self::getTypographyRule($obj->info->typography);
        $str .= "}";
        $str .= "#".$selector." .intro-category-author-social-wrapper {";
        if (isset($obj->info->show)) {
            $str .= 'display:'.($obj->info->show ? 'block' : 'none').';';
        }
        $str .= "text-align: ".$obj->info->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .intro-category-author-social-wrapper a {";
        $str .= "color: ".self::getCorrectColor($obj->info->typography->color).";";
        $str .= "}";
        $str .= "#".$selector." .intro-post-wrapper:not(.fullscreen-post) .intro-post-image-wrapper {";
        $str .= 'display:'.($obj->image->show ? 'block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .intro-post-title-wrapper {";
        $str .= 'display:'.($obj->title->show ? 'block' : 'none').';';
        $str .= "}";
        if (!isset($obj->view->author)) {
            $obj->view->author = false;
        }
        if (!isset($obj->view->comments)) {
            $obj->view->comments = false;
        }
        if (!isset($obj->view->reviews)) {
            $obj->view->reviews = false;
        }
        $str .= "#".$selector." .intro-post-author {";
        $str .= 'display:'.($obj->view->author ? 'inline-block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .intro-post-date {";
        $str .= 'display:'.($obj->view->date ? 'inline-block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .intro-post-category {";
        $str .= 'display:'.($obj->view->category ? 'inline-block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .intro-post-comments {";
        $str .= 'display:'.($obj->view->comments ? 'inline-block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .intro-post-hits {";
        $str .= 'display:'.($obj->view->hits ? 'inline-block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .intro-post-reviews {";
        $str .= 'display:'.($obj->view->reviews ? 'inline-flex' : 'none').';';
        $str .= "}";
        $blogInfoOrder = self::$blogPostsInfo ? self::$blogPostsInfo : array('author', 'date', 'category', 'comments', 'hits', 'reviews');
        foreach ($blogInfoOrder as $i => $value) {
            if (isset($obj->view->{$value}) && $obj->view->{$value}) {
                for ($j = $i + 1; $j < count($blogInfoOrder); $j++) {
                    $str .= "#".$selector." .intro-post-".$blogInfoOrder[$j].":before {";
                    $str .= 'margin: 0 10px;content: "'.($blogInfoOrder[$j] == 'author' ? '' : '\2022').'";color: inherit;';
                    $str .= "}";
                }
                break;
            }
        }
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getStarRatingsRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .star-ratings-wrapper {";
        $str .= "text-align: ".$obj->icon->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .rating-wrapper {";
        $str .= self::setItemsVisability(!$obj->view->rating, "inline");
        $str .= "}";
        $str .= "#".$selector." .votes-wrapper {";
        $str .= self::setItemsVisability(!$obj->view->votes, "inline");
        $str .= "}";
        $str .= "#".$selector." .stars-wrapper {";
        $str .= "color:".self::getCorrectColor($obj->icon->color).";";
        $str .= "}";
        $str .= "#".$selector." .star-ratings-wrapper i {";
        $str .= "font-size:".$obj->icon->size."px;";
        $str .= "}";
        $str .= "#".$selector." .star-ratings-wrapper i.active, #".$selector." .star-ratings-wrapper i.active + i:after";
        $str .= ", #".$selector." .stars-wrapper:hover i {";
        $str .= "color:".self::getCorrectColor($obj->icon->hover).";";
        $str .= "}";
        $str .= "#".$selector." .info-wrapper * {";
        $str .= self::getTypographyRule($obj->info, 'text-align');
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getIconRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= "text-align: ".$obj->icon->{'text-align'}.";";
        if (isset($obj->inline) && $obj->inline) {
            $str .= self::setItemsVisability($obj->disable, "inline-block");
            $str .= "margin : 0 10px;";
            $str .= "width: auto;";
        } else {
            $str .= self::setItemsVisability($obj->disable, "block");
            $str .= "margin : 0;";
        }
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-icon-wrapper i {";
        $str .= "width : ".$obj->icon->size."px;";
        $str .= "height : ".$obj->icon->size."px;";
        $str .= "font-size : ".$obj->icon->size."px;";
        $str .= "color : ".self::getCorrectColor($obj->normal->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->normal->{'background-color'}).";";
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        if (isset($obj->shadow)) {
            $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
            $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        }
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getTestimonialsRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $margin = 30 * ($obj->slideset->count - 1);
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= self::setItemsVisability($obj->disable, "block", '#'.$selector);
        $str .= "}";
        $str .= "#".$selector." li {";
        $str .= "width: calc((100% - ".$margin."px) / ".$obj->slideset->count.");";
        $str .= "}";
        $str .= "#".$selector." ul.style-6 li {";
        $str .= "width: 100%;";
        $str .= "}";
        $str .= "#".$selector." .slideshow-content .testimonials-wrapper, #".$selector." .testimonials-info {";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "background-color: ".self::getCorrectColor($obj->background->color).";";
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        $str .= "}";
        $str .= "#".$selector." .testimonials-info:before {";
        $str .= "border-color: ".self::getCorrectColor($obj->background->color).";";
        $str .= "left:".($obj->image->width / 2)."px";
        $str .= "}";
        $str .= "#".$selector." .testimonials-icon-wrapper i {";
        $str .= "width : ".$obj->icon->size."px;";
        $str .= "height : ".$obj->icon->size."px;";
        $str .= "font-size : ".$obj->icon->size."px;";
        $str .= "color : ".self::getCorrectColor($obj->icon->color).";";
        $str .= "}";
        $str .= "#".$selector." .testimonials-img {";
        $str .= "width:".$obj->image->width."px;";
        $str .= "height:".$obj->image->width."px;";
        $str .= "border : ".$obj->image->border->width."px ".$obj->image->border->style;
        $str .= " ".self::getCorrectColor($obj->image->border->color).";";
        $str .= "border-radius : ".$obj->image->border->radius."px;";
        $str .= "}";
        $str .= "#".$selector." ul.style-6 .ba-slideset-dots div {";
        $str .= "width:".$obj->image->width."px;";
        $str .= "height:".$obj->image->width."px;";
        $str .= "border : ".$obj->image->border->width."px ".$obj->image->border->style;
        $str .= " ".self::getCorrectColor($obj->image->border->color).";";
        $str .= "border-radius : ".$obj->image->border->radius."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-testimonials-name {";
        $str .= self::getTypographyRule($obj->name->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-testimonials-testimonial {";
        $str .= self::getTypographyRule($obj->testimonial->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-testimonials-caption {";
        $str .= self::getTypographyRule($obj->caption->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-nav {";
        $str .= 'display:'.($obj->view->arrows == 1 ? 'block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .testimonials-slideshow-content-wrapper {";
        if ($obj->view->arrows == 1) {
            $str .= "width: calc(100% - ".((40 + ($obj->arrows->padding * 2) + $obj->arrows->size * 1 ) * 2)."px);";
        } else {
            $str .= "width: calc(100% - 50px);";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-nav a {";
        $str .= "font-size: ".$obj->arrows->size."px;";
        $str .= "width: ".$obj->arrows->size."px;";
        $str .= "height: ".$obj->arrows->size."px;";
        $str .= "background-color: ".self::getCorrectColor($obj->arrows->normal->background).";";
        $str .= "color: ".self::getCorrectColor($obj->arrows->normal->color).";";
        $str .= "padding : ".$obj->arrows->padding."px;";
        $str .= "box-shadow: 0 ".($obj->arrows->shadow->value * 10);
        $str .= "px ".($obj->arrows->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->arrows->shadow->color).";";
        $str .= "border : ".$obj->arrows->border->width."px ".$obj->arrows->border->style;
        $str .= " ".self::getCorrectColor($obj->arrows->border->color).";";
        $str .= "border-radius : ".$obj->arrows->border->radius."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-nav a:hover {";
        $str .= "background-color: ".self::getCorrectColor($obj->arrows->hover->background).";";
        $str .= "color: ".self::getCorrectColor($obj->arrows->hover->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-dots {";
        $str .= 'display:'.($obj->view->dots == 1 ? 'flex' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-dots > div {";
        $str .= "font-size: ".$obj->dots->size."px;";
        $str .= "width: ".$obj->dots->size."px;";
        $str .= "height: ".$obj->dots->size."px;";
        $str .= "color: ".self::getCorrectColor($obj->dots->normal->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-dots > div:hover,#".$selector." .ba-slideset-dots > div.active {";
        $str .= "color: ".self::getCorrectColor($obj->dots->hover->color).";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getRecentSliderRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $margin = $obj->gutter ? 30 : 0;
        $margin = $margin * ($obj->slideset->count - 1);
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= self::setItemsVisability($obj->disable, "block", '#'.$selector);
        $str .= "}";
        if ($obj->overflow) {
            $str .= "#".$selector." ul.carousel-type .slideshow-content {";
            $str .= "width: calc(100% + (100% / ".$obj->slideset->count.") * 2);";
            $str .= "margin-left: calc((100% / ".$obj->slideset->count.") * -1);";
            $str .= "}";
        } else {
            $str .= "#".$selector." ul.carousel-type .slideshow-content {";
            $str .= "width: 100%;";
            $str .= "margin-left: auto;";
            $str .= "}";
        }
        $str .= "#".$selector." ul.carousel-type li {";
        $str .= "width: calc((100% - ".$margin."px) / ".$obj->slideset->count.");";
        $str .= "}";
        $str .= "#".$selector." ul.carousel-type:not(.slideset-loaded) li {";
        $str .= "position: relative; float:left;";
        $str .= "}";
        $str .= "#".$selector." ul.carousel-type:not(.slideset-loaded) li.item.active:not(:first-child) {";
        $str .= "margin-left: ".($obj->gutter ? 30 : 0)."px;";
        $str .= "}";
        $str .= "#".$selector." ul.slideshow-type {";
        if ($obj->view->fullscreen) {
            $str .= "min-height: 100vh;";
        } else {
            $str .= "min-height: auto;";
        }
        $str .= "height:".$obj->view->height."px;";
        $str .= "}";
        $str .= "#".$selector." ul.carousel-type .ba-slideshow-img {";
        $str .= "height:".$obj->view->height."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-slideshow-img {";
        $str .= "background-size :".$obj->view->size.";";
        $str .= "}";
        $str .= "#".$selector." ul.carousel-type .ba-slideshow-caption, #".$selector." .ba-overlay {background-color :";
        if (!isset($obj->overlay->type) || $obj->overlay->type == 'color') {
            $str .= self::getCorrectColor($obj->overlay->color).";";
            $str .= 'background-image: none';
        } else if ($obj->overlay->type == 'none') {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: none;';
        } else {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: '.$obj->overlay->gradient->effect.'-gradient(';
            if ($obj->overlay->gradient->effect == 'linear') {
                $str .= $obj->overlay->gradient->angle.'deg';
            } else {
                $str .= 'circle';
            }
            $str .= ', '.self::getCorrectColor($obj->overlay->gradient->color1).' ';
            $str .= $obj->overlay->gradient->position1.'%, '.self::getCorrectColor($obj->overlay->gradient->color2);
            $str .= ' '.$obj->overlay->gradient->position2.'%);';
            $str .= 'background-attachment: scroll;';
        }
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-title {";
        foreach ($obj->title->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= 'display:'.($obj->view->title ? 'block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-title, #".$selector." .ba-blog-post-add-to-cart-price {";
        $str .= self::getTypographyRule($obj->title->typography);
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-title:hover {";
        $str .= "color: ".self::getCorrectColor($obj->title->hover->color).";";
        $str .= "}";
        $justify = str_replace('left', 'flex-start', $obj->reviews->typography->{'text-align'});
        $justify = str_replace('right', 'flex-end', $justify);
        $str .= "#".$selector." .ba-blog-post-reviews {";
        $str .= "justify-content: ".$justify.";";
        $str .= self::getTypographyRule($obj->reviews->typography, 'text-align');
        foreach ($obj->reviews->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-reviews a:hover {";
        $str .= "color: ".self::getCorrectColor($obj->reviews->hover->color).";";
        $str .= "}";
        if (isset($obj->postFields)) {
            $str .= "#".$selector." .ba-blog-post-field-row-wrapper {";
            $str .= self::getTypographyRule($obj->postFields->typography, 'text-align');
            foreach ($obj->postFields->margin as $key => $value) {
                $str .= "margin-".$key." : ".$value."px;";
            }
            $str .= "}";
        }
        $justify = str_replace('left', 'flex-start', $obj->info->typography->{'text-align'});
        $justify = str_replace('right', 'flex-end', $justify);
        $str .= "#".$selector." .ba-blog-post-info-wrapper {";
        foreach ($obj->info->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "justify-content: ".$justify.";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-product-options {";
        $str .= "display: none;";
        $str .= "}";
        if (isset($obj->store)) {
            $str .= "#".$selector." .ba-blog-post-badge-wrapper {";
            $str .= "display:".($obj->store->badge ? "flex" : "none").";";
            $str .= "}";
            $str .= "#".$selector." .ba-blog-post-wishlist-wrapper {";
            $str .= "display:".($obj->store->wishlist ? "flex" : "none").";";
            $str .= "}";
            $str .= "#".$selector." .ba-blog-post-add-to-cart-price {";
            $str .= "display:".($obj->store->price ? "flex" : "none").";";
            $str .= "}";
            $str .= "#".$selector." .ba-blog-post-add-to-cart-button {";
            $str .= "display:".($obj->store->cart ? "flex" : "none").";";
            $str .= "}";
            foreach ($obj->store as $key => $value) {
                if ($key == 'badge' || $key == 'wishlist' || $key == 'price' || $key == 'cart') {
                    continue;
                }
                $str .= "#".$selector.' .ba-blog-post-product-options[data-key="'.$key.'"] {';
                $str .= "display:".($value ? "flex" : "none").";";
                $str .= "}";
            }
        }
        $str .= "#".$selector." .ba-blog-post-info-wrapper > * {";
        $str .= self::getTypographyRule($obj->info->typography, 'text-align');
        $str .= "}";
        if (!isset($obj->view->author)) {
            $obj->view->author = false;
        }
        if (!isset($obj->view->comments)) {
            $obj->view->comments = false;
        }
        if (!isset($obj->view->reviews)) {
            $obj->view->reviews = false;
        }
        $str .= "#".$selector." .ba-blog-post-info-wrapper span.ba-blog-post-author {";
        $str .= 'display:'.($obj->view->author ? 'inline-block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-info-wrapper span.ba-blog-post-date {";
        $str .= 'display:'.($obj->view->date ? 'inline-block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-info-wrapper span.ba-blog-post-category {";
        $str .= 'display:'.($obj->view->category ? 'inline-block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-info-wrapper span.ba-blog-post-comments {";
        $str .= 'display:'.($obj->view->comments ? 'inline-block' : 'none').';';
        $str .= "}";
        $blogInfoOrder = self::$blogPostsInfo ? self::$blogPostsInfo : array('author', 'date', 'category', 'hits', 'comments');
        foreach ($blogInfoOrder as $i => $value) {
            if (isset($obj->view->{$value}) && $obj->view->{$value}) {
                for ($j = $i + 1; $j < count($blogInfoOrder); $j++) {
                    $str .= "#".$selector." .ba-blog-post-".$blogInfoOrder[$j].":before {";
                    $str .= 'margin: 0 10px;content: "'.($blogInfoOrder[$j] == 'author' ? '' : '\2022').'";color: inherit;';
                    $str .= "}";
                }
                break;
            }
        }
        $str .= "#".$selector." .ba-blog-post-reviews {";
        $str .= 'display:'.($obj->view->reviews ? 'flex' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-info-wrapper > * a:hover {";
        $str .= "color: ".self::getCorrectColor($obj->info->hover->color).";";
        $str .= "}";
        $str .= "#".$selector." .slideshow-button {";
        $str .= "text-align :".$obj->button->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-intro-wrapper {";
        $str .= self::getTypographyRule($obj->intro->typography);
        foreach ($obj->intro->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= 'display:'.($obj->view->intro ? 'block' : 'none').';';
        $str .= "}";
        $str .= '#'.$selector.' .ba-blog-post-field-row {';
        $str .= "display: none;";
        $str .= "}";
        if (isset($obj->fields)) {
            $visibleField = null;
            foreach (self::$blogPostsFields as $i => $value) {
                if ($obj->fields->{$value}) {
                    $visibleField = $i;
                }
                $str .= '#'.$selector.' .ba-blog-post-field-row[data-id="'.$value.'"] {';
                $str .= "display: ".($obj->fields->{$value} ? 'flex' : 'none').";";
                $str .= "margin-bottom: 10px;";
                $str .= "}";
            }
            if ($visibleField) {
                $str .= '#'.$selector.' .ba-blog-post-field-row[data-id="'.$visibleField.'"] {';
                $str .= "margin-bottom: 0;";
                $str .= "}";
            }
        }
        $str .= "#".$selector." .ba-blog-post-button-wrapper {";
        $str .= "text-align :".$obj->button->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-button-wrapper a {";
        foreach ($obj->button->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= 'display:'.($obj->view->button ? 'inline-block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-button-wrapper a, #".$selector." .ba-blog-post-add-to-cart {";
        $str .= self::getTypographyRule($obj->button->typography, 'text-align');
        $str .= "border : ".$obj->button->border->width."px ".$obj->button->border->style;
        $str .= " ".self::getCorrectColor($obj->button->border->color).";";
        $str .= "border-radius : ".$obj->button->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->button->shadow->value * 10);
        $str .= "px ".($obj->button->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->button->shadow->color).";";
        $str .= "background-color: ".self::getCorrectColor($obj->button->normal->background).";";
        $str .= "color: ".self::getCorrectColor($obj->button->normal->color).";";
        foreach ($obj->button->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-blog-post-button-wrapper a:hover, #".$selector." .ba-blog-post-add-to-cart:hover {";
        $str .= "background-color: ".self::getCorrectColor($obj->button->hover->background).";";
        $str .= "color: ".self::getCorrectColor($obj->button->hover->color).";";
        $str .= "}";
        if (self::$editItem->type == 'recently-viewed-products') {
            $str .= "#".$selector." .enabled-carousel-sliding .ba-slideset-nav {";
            $str .= "display: ".($obj->view->arrows ? "block" : "none").";";
            $str .= "}";
            $str .= "#".$selector." .ba-slideset-nav {";
            $str .= "display: none;";
            $str .= "}";
        } else {
            $str .= "#".$selector." .ba-slideset-nav {";
            $str .= self::setItemsVisability(!$obj->view->arrows, "block");
            $str .= "}";
        }
        $str .= "#".$selector." .ba-slideset-nav a {";
        $str .= "font-size: ".$obj->arrows->size."px;";
        $str .= "width: ".$obj->arrows->size."px;";
        $str .= "height: ".$obj->arrows->size."px;";
        $str .= "background-color: ".self::getCorrectColor($obj->arrows->normal->background).";";
        $str .= "color: ".self::getCorrectColor($obj->arrows->normal->color).";";
        $str .= "padding : ".$obj->arrows->padding."px;";
        $str .= "box-shadow: 0 ".($obj->arrows->shadow->value * 10);
        $str .= "px ".($obj->arrows->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->arrows->shadow->color).";";
        $str .= "border : ".$obj->arrows->border->width."px ".$obj->arrows->border->style;
        $str .= " ".self::getCorrectColor($obj->arrows->border->color).";";
        $str .= "border-radius : ".$obj->arrows->border->radius."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-nav a:hover {";
        $str .= "background-color: ".self::getCorrectColor($obj->arrows->hover->background).";";
        $str .= "color: ".self::getCorrectColor($obj->arrows->hover->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-dots {";
        $str .= self::setItemsVisability(!$obj->view->dots, "flex;");
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-dots > div {";
        $str .= "font-size: ".$obj->dots->size."px;";
        $str .= "width: ".$obj->dots->size."px;";
        $str .= "height: ".$obj->dots->size."px;";
        $str .= "color: ".self::getCorrectColor($obj->dots->normal->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-dots > div:hover,#".$selector." .ba-slideset-dots > div.active {";
        $str .= "color: ".self::getCorrectColor($obj->dots->hover->color).";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getCarouselRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $margin = $obj->gutter ? 30 : 0;
        $count = $obj->slideset->count * 1;
        $margin = $margin * ($count - 1);
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        if ($obj->overflow) {
            $str .= "#".$selector." .slideshow-content {";
            $str .= "width: calc(100% + (100% / ".$obj->slideset->count.") * 2);";
            $str .= "margin-left: calc((100% / ".$obj->slideset->count.") * -1);";
            $str .= "}";
        } else {
            $str .= "#".$selector." .slideshow-content {";
            $str .= "width: 100%;";
            $str .= "margin-left: auto;";
            $str .= "}";
        }
        $str .= "#".$selector." li {";
        $str .= "width: calc((100% - ".$margin."px) / ".$count.");";
        $str .= "}";
        $str .= "#".$selector." ul:not(.slideset-loaded) li {";
        $str .= "position: relative; float:left;";
        $str .= "}";
        $str .= "#".$selector." ul:not(.slideset-loaded) li.item.active:not(:first-child) {";
        $str .= "margin-left: ".($obj->gutter ? 30 : 0)."px;";
        $str .= "}";
        foreach ($obj->slides as $key => $value) {
            if (!empty($value->image)) {
                $str .= "#".$selector." li.item:nth-child(".$key.") .ba-slideshow-img {";
                $str .= "background-image: url(".self::setBackgroundImage($value->image).");";
                $str .= "}"; 
            }
        }
        $str .= "#".$selector." .ba-slideshow-img {";
        $str .= "background-size :".$obj->view->size.";";
        $str .= "height:".$obj->view->height."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-slideshow-caption {background-color :";
        if (!isset($obj->overlay->type) || $obj->overlay->type == 'color'){
            $str .= self::getCorrectColor($obj->overlay->color).";";
            $str .= 'background-image: none';
        } else if ($obj->overlay->type == 'none') {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: none;';
        } else {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: '.$obj->overlay->gradient->effect.'-gradient(';
            if ($obj->overlay->gradient->effect == 'linear') {
                $str .= $obj->overlay->gradient->angle.'deg';
            } else {
                $str .= 'circle';
            }
            $str .= ', '.self::getCorrectColor($obj->overlay->gradient->color1).' ';
            $str .= $obj->overlay->gradient->position1.'%, '.self::getCorrectColor($obj->overlay->gradient->color2);
            $str .= ' '.$obj->overlay->gradient->position2.'%);';
            $str .= 'background-attachment: scroll;';
        }
        $str .= "}";
        $str .= "#".$selector." .slideshow-title-wrapper {";
        $str .= "text-align :".$obj->title->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideshow-title {";
        $str .= self::getTypographyRule($obj->title->typography, 'text-align');
        foreach ($obj->title->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .slideshow-description-wrapper {";
        $str .= "text-align :".$obj->description->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideshow-description {";
        $str .= self::getTypographyRule($obj->description->typography, 'text-align');
        foreach ($obj->description->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .slideshow-button {";
        $str .= "text-align :".$obj->button->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .slideshow-button:not(.empty-content) a {";
        foreach ($obj->button->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= self::getTypographyRule($obj->button->typography, 'text-align');
        $str .= "border : ".$obj->button->border->width."px ";
        $str .= $obj->button->border->style." ".self::getCorrectColor($obj->button->border->color).";";
        $str .= "border-radius : ".$obj->button->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->button->shadow->value * 10);
        $str .= "px ".($obj->button->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->button->shadow->color).";";
        $str .= "background-color: ".self::getCorrectColor($obj->button->normal->background).";";
        $str .= "color: ".self::getCorrectColor($obj->button->normal->color).";";
        foreach ($obj->button->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .slideshow-button a:hover {";
        $str .= "background-color: ".self::getCorrectColor($obj->button->hover->background).";";
        $str .= "color: ".self::getCorrectColor($obj->button->hover->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-nav {";
        $str .= self::setItemsVisability(!$obj->view->arrows, "block");
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-nav a {";
        $str .= "font-size: ".$obj->arrows->size."px;";
        $str .= "width: ".$obj->arrows->size."px;";
        $str .= "height: ".$obj->arrows->size."px;";
        $str .= "background-color: ".self::getCorrectColor($obj->arrows->normal->background).";";
        $str .= "color: ".self::getCorrectColor($obj->arrows->normal->color).";";
        $str .= "padding : ".$obj->arrows->padding."px;";
        $str .= "box-shadow: 0 ".($obj->arrows->shadow->value * 10);
        $str .= "px ".($obj->arrows->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->arrows->shadow->color).";";
        $str .= "border : ".$obj->arrows->border->width."px ".$obj->arrows->border->style;
        $str .= " ".self::getCorrectColor($obj->arrows->border->color).";";
        $str .= "border-radius : ".$obj->arrows->border->radius."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-nav a:hover {";
        $str .= "background-color: ".self::getCorrectColor($obj->arrows->hover->background).";";
        $str .= "color: ".self::getCorrectColor($obj->arrows->hover->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-dots {";
        $str .= self::setItemsVisability(!$obj->view->dots, "flex;");
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-dots > div {";
        $str .= "font-size: ".$obj->dots->size."px;";
        $str .= "width: ".$obj->dots->size."px;";
        $str .= "height: ".$obj->dots->size."px;";
        $str .= "color: ".self::getCorrectColor($obj->dots->normal->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideset-dots > div:hover,#".$selector." .ba-slideset-dots > div.active {";
        $str .= "color: ".self::getCorrectColor($obj->dots->hover->color).";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getContentSliderItemsRules($obj, $selector)
    {
        $str = '';
        $str .= $selector." > .ba-overlay {background-color: ";
        if ($obj->overlay->type == 'color'){
            $str .= self::getCorrectColor($obj->overlay->color).";";
            $str .= 'background-image: none';
        } else if ($obj->overlay->type == 'none') {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: none;';
        } else {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: '.$obj->overlay->gradient->effect.'-gradient(';
            if ($obj->overlay->gradient->effect == 'linear') {
                $str .= $obj->overlay->gradient->angle.'deg';
            } else {
                $str .= 'circle';
            }
            $str .= ', '.self::getCorrectColor($obj->overlay->gradient->color1).' ';
            $str .= $obj->overlay->gradient->position1.'%, '.self::getCorrectColor($obj->overlay->gradient->color2);
            $str .= ' '.$obj->overlay->gradient->position2.'%);';
            $str .= 'background-attachment: scroll;';
        }
        $str .= "}";
        $str .= $selector." > .ba-slideshow-img {";
        switch ($obj->background->type) {
            case 'image' :
                $image = $obj->background->image->image;
                $str .= "background-image: url(".self::setBackgroundImage($image).");";
                foreach ($obj->background->image as $key => $value) {
                    if ($key != 'image') {
                        $str .= "background-".$key.": ".$value.";";
                    }
                }
                $str .= "background-color: rgba(0, 0, 0, 0);";
                break;
            case 'color' :
                $str .= "background-color: ".self::getCorrectColor($obj->background->color).";";
                $str .= "background-image: none;";
                break;
            case 'gradient':
                $str .= 'background-image: '.$obj->background->gradient->effect.'-gradient(';
                if ($obj->background->gradient->effect == 'linear') {
                    $str .= $obj->background->gradient->angle.'deg';
                } else {
                    $str .= 'circle';
                }
                $str .= ', '.self::getCorrectColor($obj->background->gradient->color1).' ';
                $str .= $obj->background->gradient->position1.'%, '.self::getCorrectColor($obj->background->gradient->color2);
                $str .= ' '.$obj->background->gradient->position2.'%);';
                $str .= "background-color: rgba(0, 0, 0, 0);";
                $str .= 'background-attachment: scroll;';
                break;
            default :
                $str .= "background-image: none;";
                $str .= "background-color: rgba(0, 0, 0, 0);";
        }
        $str .= "}";
        
        return $str;
    }

    public static function getContentSliderRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." > .slideshow-wrapper > .ba-slideshow {";
        $str .= "border-bottom-width : ".($obj->border->width * $obj->border->bottom)."px;";
        $str .= "border-color : ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-left-width : ".($obj->border->width * $obj->border->left)."px;";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "border-right-width : ".($obj->border->width * $obj->border->right)."px;";
        $str .= "border-style : ".$obj->border->style.";";
        $str .= "border-top-width : ".($obj->border->width * $obj->border->top)."px;";
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        $str .= "}";
        $str .= "#".$selector." > .slideshow-wrapper {";
        $str .= "min-height: ".($obj->view->fullscreen ? "100vh" : "auto").";";
        $str .= "}";
        $str .= "#".$selector." > .slideshow-wrapper > ul > .slideshow-content, #".$selector." > .slideshow-wrapper > ul > .empty-list {";
        $str .= "height:".$obj->view->height."px;";
        $str .= "}";
        $str .= "#".$selector." > .slideshow-wrapper > .ba-slideshow > .slideshow-content > li.item > .ba-grid-column {";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-nav {";
        $str .= 'display:'.($obj->view->arrows == 1 ? 'block' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-nav a {";
        $str .= "font-size: ".$obj->arrows->size."px;";
        $str .= "width: ".$obj->arrows->size."px;";
        $str .= "height: ".$obj->arrows->size."px;";
        $str .= "background-color: ".self::getCorrectColor($obj->arrows->normal->background).";";
        $str .= "color: ".self::getCorrectColor($obj->arrows->normal->color).";";
        $str .= "padding : ".$obj->arrows->padding."px;";
        $str .= "box-shadow: 0 ".($obj->arrows->shadow->value * 10);
        $str .= "px ".($obj->arrows->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->arrows->shadow->color).";";
        $str .= "border : ".$obj->arrows->border->width."px ".$obj->arrows->border->style." ";
        $str .= self::getCorrectColor($obj->arrows->border->color).";";
        $str .= "border-radius : ".$obj->arrows->border->radius."px;";
        $str .= "}";
        $str .= "#".$selector." > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-nav a:hover {";
        $str .= "background-color: ".self::getCorrectColor($obj->arrows->hover->background).";";
        $str .= "color: ".self::getCorrectColor($obj->arrows->hover->color).";";
        $str .= "}";
        $str .= "#".$selector." > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-dots {";
        $str .= 'display:'.($obj->view->dots == 1 ? 'flex' : 'none').';';
        $str .= "}";
        $str .= "#".$selector." > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-dots > div {";
        $str .= "font-size: ".$obj->dots->size."px;";
        $str .= "width: ".$obj->dots->size."px;";
        $str .= "height: ".$obj->dots->size."px;";
        $str .= "color: ".self::getCorrectColor($obj->dots->normal->color).";";
        $str .= "}";
        $str .= "#".$selector." > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-dots > div:hover,#".$selector;
        $str .= " > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-dots > div.active {";
        $str .= "color: ".self::getCorrectColor($obj->dots->hover->color).";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getFeatureBoxRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-feature-box {";
        $str .= "width: calc((100% - ".(($obj->view->count - 1) * 30)."px) / ".$obj->view->count.")";
        $str .= "}";
        $str .= "#".$selector." .ba-feature-box:nth-child(n) {";
        $str .= "margin-right: 30px;";
        $str .= "margin-top: 30px;";
        $str .= "}";
        $str .= "#".$selector." .ba-feature-box:nth-child(".$obj->view->count."n) {";
        $str .= "margin-right: 0;";
        $str .= "}";
        for ($i = 0; $i < $obj->view->count; $i++) {
            $str .= "#".$selector." .ba-feature-box:nth-child(".($i + 1).") {";
            $str .= "margin-top: 0;";
            $str .= "}";
        }
        $str .= "#".$selector." .ba-feature-box:hover {";
        $str .= "background-color: ".self::getCorrectColor($obj->background->hover->color).";";
        $str .= "box-shadow: 0 ".($obj->shadow->hover->value * 10);
        $str .= "px ".($obj->shadow->hover->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->hover->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-feature-box:hover .ba-feature-image-wrapper i {";
        $str .= "color : ".self::getCorrectColor($obj->icon->hover->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->icon->hover->background).";";
        $str .= "}";
        $str .= "#".$selector." .ba-feature-box:hover .ba-feature-title {";
        $str .= "color : ".self::getCorrectColor($obj->title->hover->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-feature-box:hover .ba-feature-description-wrapper * {";
        $str .= "color : ".self::getCorrectColor($obj->description->hover->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-feature-box {";
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->shadow->normal->value * 10);
        $str .= "px ".($obj->shadow->normal->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->normal->color).";";
        $str .= "background-color: ".self::getCorrectColor($obj->background->normal->color).";";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= '#'.$selector.' .ba-feature-image-wrapper[data-type="icon"] {';
        $str .= "text-align: ".$obj->icon->{'text-align'}.";";
        $str .= "}";
        $str .= '#'.$selector.' .ba-feature-image-wrapper:not([data-type="icon"]) {';
        $str .= "text-align: ".$obj->image->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-feature-image-wrapper .ba-feature-image {";
        $str .= "width : ".$obj->image->width."px;";
        $str .= "height : ".$obj->image->height."px;";
        $str .= "border : ".$obj->image->border->width."px ".$obj->image->border->style." ";
        $str .= self::getCorrectColor($obj->image->border->color).";";
        $str .= "border-radius : ".$obj->image->border->radius."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-feature-image-wrapper i {";
        $str .= "padding : ".$obj->icon->padding."px;";
        $str .= "font-size : ".$obj->icon->size."px;";
        $str .= "border : ".$obj->icon->border->width."px ".$obj->icon->border->style." ";
        $str .= self::getCorrectColor($obj->icon->border->color).";";
        $str .= "border-radius : ".$obj->icon->border->radius."px;";
        $str .= "color : ".self::getCorrectColor($obj->icon->normal->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->icon->normal->background).";";
        $str .= "}";
        $str .= "#".$selector." .ba-feature-title {";
        $str .= self::getTypographyRule($obj->title->typography);
        if (isset($obj->title->margin)) {
            foreach ($obj->title->margin as $key => $value) {
                $str .= "margin-".$key." : ".$value."px;";
            }
        }
        $str .= "}";
        $str .= "#".$selector." .ba-feature-description-wrapper {";
        $str .= "text-align :".$obj->description->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-feature-description-wrapper * {";
        $str .= self::getTypographyRule($obj->description->typography, 'text-align');
        foreach ($obj->description->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-feature-button {";
        $str .= "text-align :".$obj->button->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-feature-button:not(.empty-content) a {";
        foreach ($obj->button->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= self::getTypographyRule($obj->button->typography, 'text-align');
        $str .= "border : ".$obj->button->border->width."px ".$obj->button->border->style;
        $str .= " ".self::getCorrectColor($obj->button->border->color).";";
        $str .= "border-radius : ".$obj->button->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->button->shadow->value * 10);
        $str .= "px ".($obj->button->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->button->shadow->color).";";
        $str .= "background-color: ".self::getCorrectColor($obj->button->normal->background).";";
        $str .= "color: ".self::getCorrectColor($obj->button->normal->color).";";
        foreach ($obj->button->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-feature-button a:hover {";
        $str .= "background-color: ".self::getCorrectColor($obj->button->hover->background).";";
        $str .= "color: ".self::getCorrectColor($obj->button->hover->color).";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getSlideshowRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        foreach ($obj->slides as $key => $value) {
            if ($value->type == 'image') {
                $str .= "#".$selector." li.item:nth-child(".$key.") .ba-slideshow-img {";
                $str .= "background-image: url(".self::setBackgroundImage($value->image).");";
                $str .= "}";
                $str .= "#".$selector.' .thumbnails-dots div[data-ba-slide-to="'.($key * 1 - 1).'"] {';
                $str .= "background-image: url(".self::setBackgroundImage($value->image).");";
                $str .= "}";
            } else if ($value->type == 'video' && $value->video->type == 'youtube') {
                $str .= "#".$selector.' .thumbnails-dots div[data-ba-slide-to="'.($key * 1 - 1).'"] {';
                $str .= 'background-image: url(https://img.youtube.com/vi/'.$value->video->id.'/maxresdefault.jpg);';
                $str .= "}";
            } else if ($value->type == 'video' && $value->video->type == 'vimeo' && isset($value->video->thumbnail)) {
                $str .= "#".$selector.' .thumbnails-dots div[data-ba-slide-to="'.($key * 1 - 1).'"] {';
                $str .= 'background-image: url('.$value->video->thumbnail.');';
                $str .= "}";
            } else if ($value->type == 'video' && !isset($value->video->thumbnail)) {
                $str .= "#".$selector.' .thumbnails-dots div[data-ba-slide-to="'.($key * 1 - 1).'"] {';
                $str .= 'background-image: url('.JUri::root().'components/com_gridbox/assets/images/thumb-square.png);';
                $str .= "}";
            }
        }
        $str .= "#".$selector." .slideshow-wrapper {";
        if ($obj->view->fullscreen) {
            $str .= "min-height: 100vh;";
        } else {
            $str .= "min-height: auto;";
        }
        $str .= "}";
        $str .= "#".$selector." .slideshow-content, #".$selector." .empty-list {";
        $str .= "height:".$obj->view->height."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-slideshow-img, #".$selector." .thumbnails-dots div {";
        $str .= "background-size :".$obj->view->size.";";
        $str .= "}";
        $str .= "#".$selector." .ba-overlay {background-color :";
        if (!isset($obj->overlay->type) || $obj->overlay->type == 'color'){
            $str .= self::getCorrectColor($obj->overlay->color).";";
            $str .= 'background-image: none;';
        } else if ($obj->overlay->type == 'none') {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: none;';
        } else {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: '.$obj->overlay->gradient->effect.'-gradient(';
            if ($obj->overlay->gradient->effect == 'linear') {
                $str .= $obj->overlay->gradient->angle.'deg';
            } else {
                $str .= 'circle';
            }
            $str .= ', '.self::getCorrectColor($obj->overlay->gradient->color1).' ';
            $str .= $obj->overlay->gradient->position1.'%, '.self::getCorrectColor($obj->overlay->gradient->color2);
            $str .= ' '.$obj->overlay->gradient->position2.'%);';
            $str .= 'background-attachment: scroll;';
        }
        $str .= "height:".$obj->view->height."px;";
        $str .= "}";
        $str .= "#".$selector." .slideshow-title-wrapper {";
        $str .= "text-align :".$obj->title->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideshow-title {";
        $str .= "animation-duration :".$obj->title->animation->duration."s;";
        $str .= "animation-delay :".(isset($obj->title->animation->delay) ? $obj->title->animation->delay : 0)."s;";
        $str .= self::getTypographyRule($obj->title->typography, 'text-align');
        if (isset($obj->title->margin)) {
            foreach ($obj->title->margin as $key => $value) {
                $str .= "margin-".$key." : ".$value."px;";
            }
        }
        $str .= "}";
        $str .= "#".$selector." .slideshow-description-wrapper {";
        $str .= "text-align :".$obj->description->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideshow-description {";
        $str .= "animation-duration :".$obj->description->animation->duration."s;";
        $str .= "animation-delay :".(isset($obj->description->animation->delay) ? $obj->description->animation->delay : 0)."s;";
        $str .= self::getTypographyRule($obj->description->typography, 'text-align');
        foreach ($obj->description->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .slideshow-button {";
        $str .= "text-align :".$obj->button->typography->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." .slideshow-button:not(.empty-content) a {";
        $str .= "animation-duration :".$obj->button->animation->duration."s;";
        $str .= "animation-delay :".(isset($obj->button->animation->delay) ? $obj->button->animation->delay : 0)."s;";
        foreach ($obj->button->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= self::getTypographyRule($obj->button->typography, 'text-align');
        $str .= "border : ".$obj->button->border->width."px ".$obj->button->border->style;
        $str .= " ".self::getCorrectColor($obj->button->border->color).";";
        $str .= "border-radius : ".$obj->button->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->button->shadow->value * 10);
        $str .= "px ".($obj->button->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->button->shadow->color).";";
        $str .= "background-color: ".self::getCorrectColor($obj->button->normal->background).";";
        $str .= "color: ".self::getCorrectColor($obj->button->normal->color).";";
        foreach ($obj->button->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .slideshow-button a:hover {";
        $str .= "background-color: ".self::getCorrectColor($obj->button->hover->background).";";
        $str .= "color: ".self::getCorrectColor($obj->button->hover->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideshow-nav {";
        $str .= self::setItemsVisability(!$obj->view->arrows, "block");
        $str .= "}";
        $str .= "#".$selector." .ba-slideshow-nav a {";
        $str .= "font-size: ".$obj->arrows->size."px;";
        $str .= "width: ".$obj->arrows->size."px;";
        $str .= "height: ".$obj->arrows->size."px;";
        $str .= "background-color: ".self::getCorrectColor($obj->arrows->normal->background).";";
        $str .= "color: ".self::getCorrectColor($obj->arrows->normal->color).";";
        $str .= "padding : ".$obj->arrows->padding."px;";
        $str .= "box-shadow: 0 ".($obj->arrows->shadow->value * 10);
        $str .= "px ".($obj->arrows->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->arrows->shadow->color).";";
        $str .= "border : ".$obj->arrows->border->width."px ".$obj->arrows->border->style;
        $str .= " ".self::getCorrectColor($obj->arrows->border->color).";";
        $str .= "border-radius : ".$obj->arrows->border->radius."px;";
        $str .= "}";
        $str .= "#".$selector." .ba-slideshow-nav a:hover {";
        $str .= "background-color: ".self::getCorrectColor($obj->arrows->hover->background).";";
        $str .= "color: ".self::getCorrectColor($obj->arrows->hover->color).";";
        $str .= "}";
        if (!isset($obj->thumbnails)) {
            $str .= "#".$selector." .ba-slideshow-dots {";
            $str .= self::setItemsVisability(!$obj->view->dots, "flex;");
            $str .= "}";
        } else {
            $str .= "#".$selector." .slideshow-wrapper {";
            $str .= "--thumbnails-count:".$obj->thumbnails->count.";";
            $str .= "--bottom-thumbnails-height: ".$obj->thumbnails->height."px;";
            if (isset($obj->thumbnails->width)) {
                $str .= "--left-thumbnails-width: ".$obj->thumbnails->width."px;";
            }
            $str .= "}";
        }
        $str .= "#".$selector." .ba-slideshow-dots:not(.thumbnails-dots) > div {";
        $str .= "font-size: ".$obj->dots->size."px;";
        $str .= "width: ".$obj->dots->size."px;";
        $str .= "height: ".$obj->dots->size."px;";
        $str .= "color: ".self::getCorrectColor($obj->dots->normal->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-slideshow-dots:not(.thumbnails-dots) > div:hover,#".$selector;
        $str .= " .ba-slideshow-dots:not(.thumbnails-dots) > div.active {";
        $str .= "color: ".self::getCorrectColor($obj->dots->hover->color).";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);
        
        return $str;
    }

    public static function getOverlayImageRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->image->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-image-wrapper {";
        $str .= "text-align: ".$obj->image->style->align.";}";
        $str .= "#".$selector." img {";
        $str .= "border : ".$obj->image->border->width."px ".$obj->image->border->style;
        $str .= " ".self::getCorrectColor($obj->image->border->color).";";
        $str .= "border-radius : ".$obj->image->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->image->shadow->value * 10);
        $str .= "px ".($obj->image->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->image->shadow->color).";";
        $str .= "width: ".$obj->image->style->width."px;";
        $str .= "}";        
        $str .= self::setBoxModel($obj->image, $selector);

        return $str;
    }

    public static function getPreloaderRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        $str .= "}";
        $str .= "#".$selector." .preloader-wrapper, #".$selector." .preloader-wrapper:before, ";
        $str .= "#".$selector." .preloader-wrapper:after {";
        $str .= "background-color: ".self::getCorrectColor($obj->background).";";
        $str .= "}";
        $str .= "#".$selector." .preloader-wrapper:before, ";
        $str .= "#".$selector." .preloader-wrapper:after {";
        $str .= "border-color: ".self::getCorrectColor($obj->background).";";
        $str .= "}";
        $str .= "#".$selector." .preloader-point-wrapper {";
        $str .= "width: ".$obj->size."px;";
        $str .= "height: ".$obj->size."px;";
        $str .= "}";
        $str .= "#".$selector." .preloader-point-wrapper div, #".$selector." .preloader-point-wrapper div:before {";
        $str .= "background-color: ".self::getCorrectColor($obj->color).";";
        $str .= "}";
        $str .= "#".$selector." .preloader-image-wrapper {";
        $str .= "width: ".$obj->width."px;";
        $str .= "}";

        return $str;
    }

    public static function getImageRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= "text-align: ".$obj->style->align.";";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-image-wrapper {";
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        $str .= "width: ".$obj->style->width."px;";
        $str .= "}";
        if (isset($obj->overlay)) {
            $str .= "#".$selector." .ba-image-wrapper {";
            $str .= "transition-duration: ".$obj->animation->duration."s;";
            $str .= "}";
            $str .= "#".$selector." .ba-image-item-caption .ba-caption-overlay {background-color :";
            if (!isset($obj->overlay->type) || $obj->overlay->type == 'color'){
                $str .= self::getCorrectColor($obj->overlay->color).";";
                $str .= 'background-image: none';
            } else if ($obj->overlay->type == 'none') {
                $str .= 'rgba(0, 0, 0, 0);';
                $str .= 'background-image: none;';
            } else {
                $str .= 'rgba(0, 0, 0, 0);';
                $str .= 'background-image: '.$obj->overlay->gradient->effect.'-gradient(';
                if ($obj->overlay->gradient->effect == 'linear') {
                    $str .= $obj->overlay->gradient->angle.'deg';
                } else {
                    $str .= 'circle';
                }
                $str .= ', '.self::getCorrectColor($obj->overlay->gradient->color1).' ';
                $str .= $obj->overlay->gradient->position1.'%, '.self::getCorrectColor($obj->overlay->gradient->color2);
                $str .= ' '.$obj->overlay->gradient->position2.'%);';
                $str .= 'background-attachment: scroll;';
            }
            $str .= "}";
            $str .= "#".$selector." .ba-image-item-title {";
            $str .= self::getTypographyRule($obj->title->typography);
            foreach ($obj->title->margin as $key => $value) {
                $str .= "margin-".$key." : ".$value."px;";
            }
            $str .= "}";
            $str .= "#".$selector." .ba-image-item-description {";
            $str .= self::getTypographyRule($obj->description->typography);
            foreach ($obj->description->margin as $key => $value) {
                $str .= "margin-".$key." : ".$value."px;";
            }
            $str .= "}";
        }
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getVideoRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "}";
        $str .= "#".$selector." .ba-video-wrapper {";
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        $str .= "padding-bottom: calc(56.24% - ".$obj->border->width."px);";
        $str .= "}";        
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getScrollTopRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        if (isset($obj->margin)) {
            foreach ($obj->margin as $key => $value) {
                $str .= "margin-".$key." : ".$value."px;";
            }
        }
        if (isset($obj->icons->align)) {
            $str .= "text-align : ".$obj->icons->align.";";
        }
        $str .= "}";
        $str .= "#".$selector." i.ba-btn-transition {";
        foreach ($obj->padding as $key => $value) {
            $str .= "padding-".$key." : ".$value."px;";
        }
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        $str .= "border : ".$obj->border->width."px ".$obj->border->style." ".self::getCorrectColor($obj->border->color).";";
        $str .= "border-radius : ".$obj->border->radius."px;";
        $str .= "font-size : ".$obj->icons->size."px;";
        $str .= "width : ".$obj->icons->size."px;";
        $str .= "height : ".$obj->icons->size."px;";
        $str .= "color : ".self::getCorrectColor($obj->normal->color).";";
        $str .= "background-color : ".self::getCorrectColor($obj->normal->{'background-color'}).";";
        $str .= "}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getLogoRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "text-align: ".$obj->{'text-align'}.";";
        $str .= "}";
        $str .= "#".$selector." img {";
        $str .= "width: ".$obj->width."px;}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function getMapRules($obj, $selector)
    {
        $str = "#".$selector." {";
        $str .= self::setItemsVisability($obj->disable, "block");
        foreach ($obj->margin as $key => $value) {
            $str .= "margin-".$key." : ".$value."px;";
        }
        $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
        $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        $str .= "}";
        $str .= "#".$selector." .ba-map-wrapper {";
        $str .= "height: ".$obj->height."px;}";
        $str .= self::setBoxModel($obj, $selector);

        return $str;
    }

    public static function object_extend($obj1, $obj2) {
        $obj = json_encode($obj1);
        $obj = json_decode($obj);
        foreach ($obj2 as $key => $value) {
            if (is_object($value)) {
                if (!isset($obj1->{$key})) {
                    $obj->{$key} = $value;
                } else {
                    $obj->{$key} = self::object_extend($obj1->{$key}, $value);
                }
            } else {
                $obj->{$key} = $value;
            }
        }

        return $obj;
    }

    public static function createRules($obj)
    {
        $str = "";
        self::$editItem = null;
        foreach ($obj as $key => $value) {
            if ($key == 'padding') {
                $str .= "body {";
                foreach ($value as $ind => $val) {
                    $str .= $key.'-'.$ind." : ".$val."px;";
                }
                $str .= "}";
                $str .= ".page-layout {left: calc(100% + ".($value->right * 1 + 1)."px);}";
            } else if ($key == 'links') {
                $str .= "body a {";
                $str .= "color : ".self::getCorrectColor($value->color).";";
                $str .= "}";
                $str .= "body a:hover {";
                $str .= "color : ".self::getCorrectColor($value->{'hover-color'}).";";
                $str .= "}";
            } else if ($key != 'background' && $key != 'overlay' && $key != 'shadow' && $key != 'video' && $key != 'image') {
                $str .= $key;
                if ($key == 'body') {
                    $str .= ' , ul, ol, table, blockquote';
                }
                $str .= " {";
                $str .= self::getTypographyRule($value);
                $str .= "}";
                if ($key == 'body') {
                    $str .= $key.' {';
                    $str .= '--icon-list-line-height: '.$obj->body->{'line-height'}.'px;';
                    $str .= "}";
                }
            }
        }
        $str .= self::backgroundRule($obj, 'body', '../../../../');
        
        return $str;
    }

    public static function backgroundRule($obj, $selector, $up)
    {
        $str = '';
        $str .= $selector." > .ba-overlay {background-color: ";
        if (!isset($obj->overlay->type) || $obj->overlay->type == 'color'){
            $str .= self::getCorrectColor($obj->overlay->color).";";
            $str .= 'background-image: none';
        } else if ($obj->overlay->type == 'none') {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: none;';
        } else {
            $str .= 'rgba(0, 0, 0, 0);';
            $str .= 'background-image: '.$obj->overlay->gradient->effect.'-gradient(';
            if ($obj->overlay->gradient->effect == 'linear') {
                $str .= $obj->overlay->gradient->angle.'deg';
            } else {
                $str .= 'circle';
            }
            $str .= ', '.self::getCorrectColor($obj->overlay->gradient->color1).' ';
            $str .= $obj->overlay->gradient->position1.'%, '.self::getCorrectColor($obj->overlay->gradient->color2);
            $str .= ' '.$obj->overlay->gradient->position2.'%);';
            $str .= 'background-attachment: scroll;';
        }
        $str .= "}";
        $str .= $selector." > .ba-video-background {display: ";
        if ($obj->background->type == 'video') {
            $str .= 'block';
        } else {
            $str .= 'none';
        }
        $str .= ";}";
        $str .= $selector." {";
        switch ($obj->background->type) {
            case 'image' :
                $image = $obj->background->image->image;
                if (isset($obj->image)) {
                    $image = $obj->image->image;
                }
                $str .= "background-image: url(".self::setBackgroundImage($image).");";
                foreach ($obj->background->image as $key => $value) {
                    if ($key != 'image') {
                        $str .= "background-".$key.": ".$value.";";
                    }
                }
                $str .= "background-color: rgba(0, 0, 0, 0);";
                break;
            case 'color' :
                $str .= "background-color: ".self::getCorrectColor($obj->background->color).";";
                $str .= "background-image: none;";
                break;
            case 'gradient':
                $str .= 'background-image: '.$obj->background->gradient->effect.'-gradient(';
                if ($obj->background->gradient->effect == 'linear') {
                    $str .= $obj->background->gradient->angle.'deg';
                } else {
                    $str .= 'circle';
                }
                $str .= ', '.self::getCorrectColor($obj->background->gradient->color1).' ';
                $str .= $obj->background->gradient->position1.'%, '.self::getCorrectColor($obj->background->gradient->color2);
                $str .= ' '.$obj->background->gradient->position2.'%);';
                $str .= "background-color: rgba(0, 0, 0, 0);";
                $str .= 'background-attachment: scroll;';
                break;
            default :
                $str .= "background-image: none;";
                $str .= "background-color: rgba(0, 0, 0, 0);";
                
        }
        if (isset($obj->shadow)) {
            $str .= "box-shadow: 0 ".($obj->shadow->value * 10);
            $str .= "px ".($obj->shadow->value * 20)."px 0 ".self::getCorrectColor($obj->shadow->color).";";
        }
        $str .= "}";
        
        return $str;
    }

    public static function siteRules($obj)
    {
        $delete = false;
        foreach (self::$breakpoints as $key => $value) {
            if ($value != $obj->{$key}) {
                $delete = true;
                break;
            }
        }
        if (self::$menuBreakpoint != $obj->menuBreakpoint) {
            $delete = true;
        }
        if ($delete) {
            $folder = JPATH_ROOT. '/templates/gridbox/css/storage/';
            $files = JFolder::files($folder);
            foreach ($files as $file) {
                if (strpos($file, 'code-editor') === false && strpos($file, 'index.') === false) {
                    JFile::delete($folder.$file);
                }
            }
        }
        $object = new stdClass();
        $object->id = 1;
        $object->breakpoints = json_encode($obj);
        $db = JFactory::getDbo();
        $db->updateObject('#__gridbox_website', $object, 'id');
        self::$menuBreakpoint = $obj->menuBreakpoint;
        unset($obj->menuBreakpoint);
        self::$breakpoints = $obj;
        $patern = self::getSiteCssPaterns();
        $str = "body:not(.com_gridbox) .body .main-body, .ba-overlay-section-backdrop.horizontal-top";
        $str .= " .ba-overlay-section.ba-container .ba-row-wrapper.ba-container, ";
        $str .= ".ba-overlay-section-backdrop.horizontal-bottom .ba-overlay-section.ba-container ";
        $str .= ".ba-row-wrapper.ba-container, .ba-container:not(.ba-overlay-section), ";
        $str .= ".intro-post-wrapper > *:not(.intro-post-image-wrapper) {";
        $str .= "width: ".self::$website->container."px;";
        $str .= "}";
        $str .= "\n@media (min-width: ".(self::$breakpoints->tablet + 1)."px) {\n";
        $str .= $patern->desktop;
        $str .= "\n}";
        if (!(bool)self::$website->disable_responsive) {
            $str .= "@media (min-width: ".(self::$menuBreakpoint + 1)."px) {\n";
            $str .= $patern->desktopMenu;
            $str .= "\n}";
            $str .= "@media (max-width: ".self::$menuBreakpoint."px) {\n";
            $str .= $patern->menu;
            $str .= "\n}";
            $str .= "\n@media (max-width: ".self::$breakpoints->laptop."px) {\n";
            $str .= $patern->laptop;
            $str .= "}";
            $str .= "\n@media (max-width: ".self::$breakpoints->tablet."px) {\n";
            $str .= $patern->tablet;
            $str .= "}";
            $str .= "\n@media (max-width: ".self::$breakpoints->{'tablet-portrait'}."px) {\n";
            $str .= $patern->tabletPortrait;
            $str .= "\n}";
            $str .= "\n@media (min-width: ".(self::$breakpoints->phone + 1)."px) and (max-width: ".self::$breakpoints->tablet."px){\n";
            $str .= $patern->tabletPhone;
            $str .= "}";
            $str .= "\n@media (max-width: ".self::$breakpoints->phone."px) {\n";
            $str .= $patern->phone;
            $str .= "\n}";
            $str .= "\n@media (max-width: ".self::$breakpoints->{'phone-portrait'}."px) {\n";
            $str .= $patern->phonePortrait;
            $str .= "\n}";
        } else {
            $str .= 'body {min-width: '.self::$website->container.'px;}';
            $str .= '.main-menu > .ba-item {display: none !important;}';
        }
        $file = JPATH_ROOT. '/templates/gridbox/css/storage/responsive.css';
        JFile::write($file, $str);
    }

    public static function themeRules($obj, $id)
    {
        $theme = $obj->params;
        foreach ($obj->footer->items as $value) {
            if ($value->type == 'footer') {
                $footer = $value;
            }
        }
        $str = 'html {';
        foreach (self::$colorVariables as $key => $value) {
            $str .= str_replace('@', '--', $key).': '.$value->color.';';
        }
        $str .= '}';
        self::$parentFonts = $footer;
        $str .= self::sectionRules($obj->footer->items, '../../../../');
        self::$parentFonts = $theme;
        self::$breakpoint = 'desktop';
        $str .= self::createRules($theme->desktop);
        $str .= self::setMediaRules($theme, 'body', 'createRules');
        $str .= self::sectionRules($obj->header->items, '../../../../');
        $str .= self::prepareCustomFonts();
        $file = JPATH_ROOT. '/templates/gridbox/css/storage/style-'.$id.'.css';
        JFile::write($file, $str);
    }

    public static function getSiteCssPaterns()
    {
        $obj = new stdClass();
        $obj->menu = JFile::read(JPATH_ROOT. '/components/com_gridbox/views/layout/css/menu.css');
        $obj->desktopMenu = JFile::read(JPATH_ROOT. '/components/com_gridbox/views/layout/css/desktop-menu.css');
        $obj->desktop = JFile::read(JPATH_ROOT. '/components/com_gridbox/views/layout/css/desktop.css');
        $obj->laptop = JFile::read(JPATH_ROOT. '/components/com_gridbox/views/layout/css/laptop.css');
        $obj->tablet = JFile::read(JPATH_ROOT. '/components/com_gridbox/views/layout/css/tablet.css');
        $obj->tabletPortrait = JFile::read(JPATH_ROOT. '/components/com_gridbox/views/layout/css/tablet-portrait.css');
        $obj->phone = JFile::read(JPATH_ROOT. '/components/com_gridbox/views/layout/css/phone.css');
        $obj->phonePortrait = JFile::read(JPATH_ROOT. '/components/com_gridbox/views/layout/css/phone-portrait.css');
        $obj->tabletPhone = JFile::read(JPATH_ROOT. '/components/com_gridbox/views/layout/css/tablet-phone.css');

        return $obj;
    }

    public static function returnSystemStyle($doc)
    {
        $str = '';
        foreach ($doc->_styleSheets as $key => $link) {
            $str .= '<link href="'.$key.'" type="text/css"';
            if (isset($script['media']) && !empty($link['media'])) {
                $str .= ' media="'.$link['media'].'"';
            }
            $str .= " rel='stylesheet'>\n\t";
        }
        foreach ($doc->_style as $key => $style) {
            $str .= '<style type="'.$key.'">'.$style."</style>\n\t";
        }
        foreach ($doc->_scripts as $key => $script) {
            $str .= '<script src="'.$key.'" type="text/javascript"';
            if (isset($script['defer']) && !empty($script['defer'])) {
                $str .= ' defer';
            }
            if (isset($script['async']) && !empty($script['async'])) {
                $str .= ' async';
            }
            $str .= "></script>\n\t";
        }
        foreach ($doc->_script as $key => $script) {
            $str .= '<script type="'.$key.'">'.$script."</script>\n\t";
        }

        return $str;
    }

    public static function getSystemParams($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_system_pages')
            ->where('id = '.$id);
        $db->setQuery($query);
        $obj = $db->loadObject();
        if (empty($obj->html)) {
            $obj->html = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/system/'.$obj->type.'.html');
            $obj->items = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/system/'.$obj->type.'.json');
        }

        return $obj;
    }

    public static function getSystemParamsByType($type)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_system_pages')
            ->where('type = '.$db->quote($type));
        $db->setQuery($query);
        $obj = $db->loadObject();

        return $obj;
    }

    public static function getSystemPageByType($type)
    {
        $db = JFactory::getDbo();        
        $query = $db->getQuery(true)
            ->select('alias')
            ->from('#__gridbox_system_pages')
            ->where('type = '.$db->quote($type));
        $db->setQuery($query);
        $alias = $db->loadResult();

        return $alias;
    }

    public static function getSystemPageByAlias($alias)
    {
        $db = JFactory::getDbo();        
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__gridbox_system_pages')
            ->where('alias = '.$db->quote($alias));
        $db->setQuery($query);
        $id = $db->loadResult();

        return $id;
    }

    public static function checkSystemTheme($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, theme')
            ->from('`#__gridbox_system_pages`')
            ->where('id = '.$id);
        $db->setQuery($query);
        $obj = $db->loadObject();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from('#__template_styles')
            ->where('`id` = ' .$db->quote($obj->theme));
        $db->setQuery($query);
        $theme = $db->loadResult();
        if ($theme != $obj->theme) {
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__template_styles')
                ->where('`client_id` = 0')
                ->where('`template` = ' .$db->quote('gridbox'))
                ->where('`home` = 1');
            $db->setQuery($query);
            $default = $db->loadResult();
            if (!$default) {
                $query = $db->getQuery(true)
                    ->select('id')
                    ->from('#__template_styles')
                    ->where('`client_id` = 0')
                    ->where('`template` = ' .$db->quote('gridbox'));
                $db->setQuery($query);
                $default = $db->loadResult();
            }
            $obj->theme = $default;
            $db->updateObject('#__gridbox_system_pages', $obj, 'id');
        }
    }

    public static function checkAccountCss()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('items')
            ->from('`#__gridbox_system_pages`')
            ->where('type = '.$db->quote('checkout'));
        $db->setQuery($query);
        $object = $db->loadObject();
        $file = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/account.css';
        if (!JFile::exists($file)) {
            if (empty($object->items)) {
                $items = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/system/checkout.json');
                $obj = json_decode($items);
            } else {
                $obj = json_decode($object->items);
            }
            $data = new stdClass();
            foreach ($obj as $key => $value) {
                if ($value->type == 'checkout-form') {
                    $data->{$key} = $value;
                    break;
                }
            }
            self::$fonts = array();
            self::$customFonts = array();
            $str = self::sectionRules($data, '../../../../');
            $str .= self::prepareCustomFonts();
            JFile::write($file, $str);
        }
    }

    public static function checkSystemCss($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('type, items')
            ->from('`#__gridbox_system_pages`')
            ->where('id = '.$id);
        $db->setQuery($query);
        $object = $db->loadObject();
        $type = $object->type;
        $name = str_replace('404', 'error', $type);
        $file = JPATH_ROOT. '/templates/gridbox/css/storage/'.$name.'.css';
        if (!JFile::exists($file)) {
            if (empty($object->items)) {
                $item = new stdClass();
                $item->html = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/system/'.$type.'.html');
                $item->items = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/system/'.$type.'.json');
                $item->id = $id;
                $obj = json_decode($item->items);
            } else {
                $obj = json_decode($object->items);
            }
            self::$fonts = array();
            self::$customFonts = array();
            $str = self::sectionRules($obj, '../../../../');
            $str .= self::prepareCustomFonts();
            JFile::write($file, $str);
            if (empty($object->items)) {
                $item->fonts = json_encode(self::$fonts);
                $item->saved_time = date('Y-m-d-H-i-s');
                $db->updateObject('#__gridbox_system_pages', $item, 'id');
            }
        }

        return $type;
    }

    public static function checkPageCss($id)
    {
        $file = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/style-'.$id.'.css';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('p.app_id')
            ->from('`#__gridbox_pages` AS p')
            ->select('a.type')
            ->leftJoin('`#__gridbox_app` AS a'
                . ' ON '
                . $db->quoteName('p.app_id')
                . ' = ' 
                . $db->quoteName('a.id')
            )
            ->where('p.id = '.$id);
        $db->setQuery($query);
        $app = $db->loadObject();
        if (!JFile::exists($file)) {
            $query = $db->getQuery(true)
                ->select('style')
                ->from('`#__gridbox_pages`')
                ->where('id = '.$id);
            $db->setQuery($query);
            $style = $db->loadResult();
            $obj = json_decode($style);
            self::$fonts = array();
            self::$customFonts = array();
            $str = self::sectionRules($obj, '../../../../../');
            $str .= self::prepareCustomFonts();
            $object = new stdClass();
            $object->id = $id;
            $object->fonts = json_encode(self::$fonts);
            $object->saved_time = date('Y-m-d-H-i-s');
            $db->updateObject('#__gridbox_pages', $object, 'id');
            JFile::write($file, $str);
        }
        if (!empty($app->type) && $app->type != 'single') {
            self::checkPostCss($app->app_id);
        }
    }

    public static function checkAppCss($id)
    {
        $file = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/app-'.$id.'.css';
        if (!JFile::exists($file)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('app_items, type')
                ->from('`#__gridbox_app`')
                ->where('id = '.$id);
            $db->setQuery($query);
            $item = $db->loadObject();
            $str = $item->app_items;
            if (empty($str)) {
                $str = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/apps/'.$item->type.'/app.json');
            }
            $obj = json_decode($str);
            if (!isset($obj->{'item-15003687281'})) {
                $obj->{'item-15003687281'} = self::getOptions('category-intro');
                $object = new stdClass();
                $object->app_items = json_encode($obj);
                $object->id = $id;
                $db->updateObject('#__gridbox_app', $object, 'id');
            }
            self::$fonts = array();
            self::$customFonts = array();
            $str = self::sectionRules($obj, '../../../../../');
            $str .= self::prepareCustomFonts();
            $object->id = $id;
            $object->app_fonts = json_encode(self::$fonts);
            $object->saved_time = date('Y-m-d-H-i-s');
            $db->updateObject('#__gridbox_app', $object, 'id');
            JFile::write($file, $str);
        }
    }

    public static function checkPostCss($id)
    {
        $file = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/post-'.$id.'.css';
        if (!JFile::exists($file)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('page_items, type')
                ->from('`#__gridbox_app`')
                ->where('id = '.$id);
            $db->setQuery($query);
            $item = $db->loadObject();
            $str = $item->page_items;
            if (empty($str)) {
                $str = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/apps/'.$item->type.'/default.json');
            }
            $obj = json_decode($str);
            self::$fonts = array();
            self::$customFonts = array();
            $str = self::sectionRules($obj, '../../../../../');
            $str .= self::prepareCustomFonts();
            $object->id = $id;
            $object->page_fonts = json_encode(self::$fonts);
            $object->saved_time = date('Y-m-d-H-i-s');
            $db->updateObject('#__gridbox_app', $object, 'id');
            JFile::write($file, $str);
        }
    }

    public static function pageRules($obj, $id)
    {
        $str = self::sectionRules($obj, '../../../../../');
        $str .= self::prepareCustomFonts();
        $file = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/style-'.$id.'.css';
        JFile::write($file, $str);
    }

    public static function prepareCustomFonts()
    {
        $str = '';
        $fontsStr = self::getFonts();
        $fonts = json_decode($fontsStr);
        if (!is_array(self::$customFonts)) {
            self::$customFonts = array();
        }
        foreach (self::$customFonts as $key => $custom) {
            $url = '';
            if (!isset($fonts->{$key})) {
                continue;
            }
            $font = $fonts->{$key};
            foreach ($font as $obj) {
                if (isset($custom[$obj->styles])) {
                    $str .= "@font-face {font-family: '".str_replace('+', ' ', $key)."'; ";
                    $str .= "font-weight: ".$obj->styles."; ";
                    $str .= "src: url(".self::$up."templates/gridbox/library/fonts/".$obj->custom_src.");} ";
                }
            }
        }

        return $str;
    }

    public static function saveAppLayout($obj, $id)
    {
        $db = JFactory::getDbo();
        self::$fonts = array();
        self::$customFonts = array();
        $str = self::sectionRules($obj->style, '../../../../../');
        $str .= self::prepareCustomFonts();
        $object = new stdClass();
        $object->id = $id;
        $object->app_layout = $obj->params;
        $object->app_items = json_encode($obj->style);
        $object->app_fonts = json_encode(self::$fonts);
        $object->saved_time = date('Y-m-d-H-i-s');
        $db->updateObject('#__gridbox_app', $object, 'id');
        $file = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/app-'.$object->id.'.css';
        JFile::write($file, $str);
    }

    public static function savePageFields($fields, $id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_page_fields')
            ->where('page_id = '.$id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $desktopFiles = self::getDesktopSavedFieldFiles($id);
        foreach ($items as $item) {
            if (!isset($fields->{$item->field_id})) {
                continue;
            }
            if ($item->field_type == 'checkbox' && !isset($fields->{$item->field_id}->value)) {
                $value = array();
            } else {
                $value = $fields->{$item->field_id}->value;
            }
            if ($item->field_type == 'checkbox' || $item->field_type == 'url' || $item->field_type == 'image-field') {
                $item->value = json_encode($value);
            } else {
                $item->value = $value;
            }
            if ($item->field_type == 'image-field') {
                if (is_numeric($value->src) && isset($desktopFiles->{$value->src})) {
                    unset($desktopFiles->{$value->src});
                }
            } else if ($item->field_type == 'field-simple-gallery' || $item->field_type == 'field-slideshow'
                || $item->field_type == 'product-slideshow' || $item->field_type == 'product-gallery') {
                $data = json_decode($value);
                foreach ($data as $object) {
                    if (is_numeric($object->img) && isset($desktopFiles->{$object->img})) {
                        unset($desktopFiles->{$object->img});
                    }
                }
            } else if ($item->field_type == 'field-video') {
                $data = json_decode($value);
                if (!empty($value) && is_numeric($data->file) && isset($desktopFiles->{$data->file})) {
                    unset($desktopFiles->{$data->file});
                }
            } else if ($item->field_type == 'file') {
                if (is_numeric($value) && isset($desktopFiles->{$value})) {
                    unset($desktopFiles->{$value});
                }
            }
            $db->updateObject('#__gridbox_page_fields', $item, 'id');
            unset($fields->{$item->field_id});
        }
        foreach ($fields as $key => $field) {
            $obj = new stdClass();
            $obj->page_id = $id;
            $obj->field_id = $field->field_id;
            $obj->field_type = $field->type;
            if ($field->type == 'checkbox' || $field->type == 'url' || $field->type == 'image-field') {
                $obj->value = json_encode($field->value);
            } else {
                $obj->value = $field->value;
            }
            if ($field->type == 'image-field') {
                if (is_numeric($field->value->src) && isset($desktopFiles->{$field->value->src})) {
                    unset($desktopFiles->{$field->value->src});
                }
            } else if ($field->type == 'field-simple-gallery' || $field->type == 'field-slideshow'
                || $field->type == 'product-slideshow' || $field->type == 'product-gallery') {
                $data = json_decode($field->value);
                foreach ($data as $object) {
                    if (is_numeric($object->img) && isset($desktopFiles->{$object->img})) {
                        unset($desktopFiles->{$object->img});
                    }
                }
            } else if ($field->type == 'field-video') {
                $data = json_decode($field->value);
                if (!empty($data) && is_numeric($data->file) && isset($desktopFiles->{$data->file})) {
                    unset($desktopFiles->{$data->file});
                }
            } else if ($field->type == 'file') {
                if (is_numeric($field->value) && isset($desktopFiles->{$field->value})) {
                    unset($desktopFiles->{$field->value});
                }
            }
            $db->insertObject('#__gridbox_page_fields', $obj);
        }
        $desktopArray = array();
        foreach ($desktopFiles as $file) {
            $desktopArray[] = $file->id;
            $dir = JPATH_ROOT.'/components/com_gridbox/assets/uploads/app-'.$file->app_id.'/';
            $path = $dir.$file->filename;
            if (JFile::exists($path)) {
                JFile::delete($path);
            }
        }
        if (!empty($desktopArray)) {
            $desktopStr = implode(',', $desktopArray);
            $query = $db->getQuery(true)
                    ->delete('#__gridbox_fields_desktop_files')
                    ->where('id IN ('.$desktopStr.')');
                $db->setQuery($query)
                    ->execute();
        }
    }

    public static function saveAppFields($fields, $id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_fields')
            ->where('app_id = '.$id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as $item) {
            if (!isset($fields->{$item->field_key})) {
                $query = $db->getQuery(true)
                    ->delete('#__gridbox_fields')
                    ->where('id = '.$item->id);
                $db->setQuery($query)
                    ->execute();
                $query = $db->getQuery(true)
                    ->delete('#__gridbox_page_fields')
                    ->where('field_id = '.$item->id);
                $db->setQuery($query)
                    ->execute();
                $query = $db->getQuery(true)
                    ->delete('#__gridbox_fields_data')
                    ->where('field_id = '.$item->id);
                $db->setQuery($query)
                    ->execute();
            } else {
                $obj = new stdClass();
                $obj->id = $item->id;
                $obj->label = $fields->{$item->field_key}->label;
                $obj->required = $fields->{$item->field_key}->required;
                $obj->options = json_encode($fields->{$item->field_key}->options);
                $options = $fields->{$item->field_key}->options;
                $obj->field_type = $options->type;
                $db->updateObject('#__gridbox_fields', $obj, 'id');
                if ($options->type != 'select' && $options->type != 'radio' && $options->type != 'checkbox'
                    || $obj->field_type != $item->field_type) {
                    $query = $db->getQuery(true)
                        ->delete('#__gridbox_fields_data')
                        ->where('field_id = '.$item->id);
                    $db->setQuery($query)
                        ->execute();
                    if ($obj->field_type != $item->field_type) {
                        $query = $db->getQuery(true)
                            ->delete('#__gridbox_page_fields')
                            ->where('field_id = '.$item->id);
                        $db->setQuery($query)
                            ->execute();
                    }
                } else {
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__gridbox_fields_data')
                        ->where('field_id = '.$item->id);
                    $db->setQuery($query);
                    $fields_data = $db->loadObjectList();
                    $optionData = new stdClass();
                    foreach ($fields_data as $value) {
                        $optionData->{$value->option_key} = $value;
                    }
                    foreach ($options->items as $option) {
                        if (isset($optionData->{$option->key})) {
                            $object = $optionData->{$option->key};
                            $object->value = $option->title;
                            $db->updateObject('#__gridbox_fields_data', $object, 'id');
                            unset($optionData->{$option->key});
                        } else {
                            self::insertFieldsData($db, $item->id, $options->type, $option);
                        }
                    }
                    foreach ($optionData as $value) {
                        $query = $db->getQuery(true)
                            ->delete('#__gridbox_fields_data')
                            ->where('id = '.$value->id);
                        $db->setQuery($query)
                            ->execute();
                    }
                }
                unset($fields->{$item->field_key});
            }
        }
        foreach ($fields as $key => $field) {
            $obj = new stdClass();
            $obj->label = $field->label;
            $obj->app_id = $id;
            $obj->required = $field->required;
            $obj->options = json_encode($field->options);
            $obj->field_type = $field->options->type;
            $obj->field_key = $key;
            $db->insertObject('#__gridbox_fields', $obj);
            if ($field->options->type == 'select' || $field->options->type == 'radio' || $field->options->type == 'checkbox') {
                $fieldId = $db->insertid();
                foreach ($field->options->items as $value) {
                    self::insertFieldsData($db, $fieldId, $field->options->type, $value);
                }
            }
        }
    }

    public static function insertFieldsData($db, $fieldId, $type, $obj)
    {
        $object = new stdClass();
        $object->field_id = $fieldId;
        $object->field_type = $type;
        $object->option_key = $obj->key;
        $object->value = $obj->title;
        $db->insertObject('#__gridbox_fields_data', $object);
    }

    public static function savePostLayout($obj, $id)
    {
        $db = JFactory::getDbo();
        self::$fonts = array();
        self::$customFonts = array();
        $str = self::sectionRules($obj->style, '../../../../../');
        $str .= self::prepareCustomFonts();
        $fields = new stdClass();
        foreach ($obj->style as $key => $value) {
            if ($value->type == 'field' || $value->type == 'image-field' || $value->type == 'field-simple-gallery'
                || $value->type == 'field-slideshow' || $value->type == 'product-slideshow' || $value->type == 'product-gallery'
                || $value->type == 'field-google-maps' || $value->type == 'field-video') {
                $fields->{$key} = new stdClass();
                $fields->{$key}->label = $value->label;
                $fields->{$key}->required = $value->required;
                $fields->{$key}->options = $value->options;
            } else if ($value->type == 'field-group') {
                foreach ($value->items as $item) {
                    $fields->{$item->field_key} = new stdClass();
                    $fields->{$item->field_key}->label = $item->label;
                    $fields->{$item->field_key}->required = $item->required;
                    $fields->{$item->field_key}->options = $item->options;
                }
            }
        }
        self::saveAppFields($fields, $id);
        $object = new stdClass();
        $object->id = $id;
        $object->page_layout = $obj->params;
        $object->page_items = json_encode($obj->style);
        $object->page_fonts = json_encode(self::$fonts);
        $object->post_editor_wrapper = $obj->post_editor_wrapper;
        $object->saved_time = date('Y-m-d-H-i-s');
        $db->updateObject('#__gridbox_app', $object, 'id');
        $file = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/post-'.$id.'.css';
        JFile::write($file, $str);
    }

    public static function saveTheme($obj, $id)
    {
        if (!isset($obj->params->colorVariables)) {
            $obj->params->colorVariables = self::getOptions('color-variables');
        }
        if (!isset($obj->params->presets)) {
            $obj->params->presets = new stdClass();
        }
        if (!isset($obj->params->defaultPresets)) {
            $obj->params->defaultPresets = new stdClass();
        }
        self::$presets = $obj->params->presets;
        self::$colorVariables = $obj->params->colorVariables;
        $folder = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/';
        $files = JFolder::files($folder);
        foreach ($files as $file) {
            if (strpos($file, 'index.') === false) {
                JFile::delete($folder.$file);
            }
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('type')
            ->from("#__gridbox_system_pages");
        $db->setQuery($query);
        $list = $db->loadObjectList();
        foreach ($list as $item) {
            $type = str_replace('404', 'error', $item->type);
            $file = JPATH_ROOT. '/templates/gridbox/css/storage/'.$type.'.css';
            if (JFile::exists($file)) {
                JFile::delete($file);
            }
        }
        $folder = JPATH_ROOT. '/templates/gridbox/css/min/';
        $files = JFolder::files($folder);
        foreach ($files as $file) {
            if (strpos($file, 'index.') === false) {
                JFile::delete($folder.$file);
            }
        }
        $folder = JPATH_ROOT. '/templates/gridbox/js/min/';
        $files = JFolder::files($folder);
        foreach ($files as $file) {
            if (strpos($file, 'index.') === false) {
                JFile::delete($folder.$file);
            }
        }
        $db = JFactory::getDbo();
        self::$fonts = array();
        self::$customFonts = array();
        if (!isset($obj->header)) {
            $object = self::getThemeParams($id);
            $obj->footer = $object->get('footer');
            $obj->header = $object->get('header');
            foreach ($obj->header->items as $value) {
                if (isset($value->type) && $value->type == 'header') {
                    $obj->layout = $value->layout;
                    break;
                }
            }
        }
        self::themeRules($obj, $id);
        $obj->fonts = json_encode(self::$fonts);
        if (isset($obj->params->image)) {
            $obj->image = $obj->params->image;
        }
        $obj->time = date('Y-m-d-H-i-s');
        $theme = new stdClass();
        $theme->id = $id;
        $theme->params = json_encode($obj);
        $db->updateObject('#__template_styles', $theme, 'id');
        //self::exportFooter($obj->footer, 'footer');
        //self::exportFooter($obj->header, 'header');
        return $obj->fonts;
    }

    public static function saveSystemPage($obj, $id)
    {
        $db = JFactory::getDbo();
        self::$fonts = array();
        self::$customFonts = array();
        $str = self::sectionRules($obj->style, '../../../../');
        $str .= self::prepareCustomFonts();
        $type = str_replace('404', 'error', $obj->type);
        $file = JPATH_ROOT. '/templates/gridbox/css/storage/'.$type.'.css';
        JFile::write($file, $str);
        $obj->fonts = json_encode(self::$fonts);
        $obj->saved_time = date('Y-m-d-H-i-s');        
        $obj->items = json_encode($obj->style);
        if ($type == 'checkout') {
            $customer = $obj->customer;
            unset($obj->customer);
        }
        unset($obj->style);
        $obj->html = $obj->params;
        unset($obj->params);
        $db->updateObject('#__gridbox_system_pages', $obj, 'id');
        if ($type == 'checkout') {
            $pks = array();
            foreach ($customer as $info) {
                $info->options = json_encode($info->settings);
                unset($info->settings);
                if ($info->id != 0) {
                    $db->updateObject('#__gridbox_store_customer_info', $info, 'id');
                } else {
                    $db->insertObject('#__gridbox_store_customer_info', $info);
                    $info->id = $db->insertid();
                }
                $pks[] = $info->id;
            }
            $query = $db->getQuery(true)
                ->delete('#__gridbox_store_customer_info');
            if (!empty($pks)) {
                $str = implode(', ', $pks);
                $query->where('id NOT IN ('.$str.')');
            }
            $db->setQuery($query)
                ->execute();
        }
    }

    public static function savePage($obj, $id)
    {
        $db = JFactory::getDbo();
        self::$fonts = array();
        self::$customFonts = array();
        self::pageRules($obj->style, $id);
        $obj->fonts = json_encode(self::$fonts);
        $obj->saved_time = date('Y-m-d-H-i-s');
        if (empty($obj->page_alias)) {
            $obj->page_alias = $obj->title;
        }
        $tags = $obj->meta_tags;
        $author = $obj->author;
        unset($obj->meta_tags);
        unset($obj->author);
        $obj->page_alias = self::getAlias($obj->page_alias, '#__gridbox_pages', 'page_alias', $obj->id);
        $obj->style = json_encode($obj->style);
        $object = new stdClass();
        $object->params = $obj->params;
        $object->id = $id;
        unset($obj->params);
        $db->updateObject('#__gridbox_pages', $obj, 'id');
        $db->updateObject('#__gridbox_pages', $object, 'id');
        self::saveMetaTags($tags, $id);
        if (!empty($author)) {
            $authors = explode(',', $author);
        } else {
            $authors = array();
        }
        $query = $db->getQuery(true)
            ->delete('#__gridbox_authors_map')
            ->where('page_id = '.$id);
        if (!empty($author)) {
            $query->where('author_id NOT IN ('.$author.')');
        }
        $db->setQuery($query)
            ->execute();
        foreach ($authors as $value) {
            $query = $db->getQuery(true)
                ->select('COUNT(id)')
                ->from('#__gridbox_authors_map')
                ->where('page_id = '.$id)
                ->where('author_id = '.$value);
            $db->setQuery($query);
            $count = $db->loadResult();
            if ($count == 0) {
                $obj = new stdClass();
                $obj->page_id = $id;
                $obj->author_id = $value;
                $db->insertObject('#__gridbox_authors_map', $obj);
            }
        }
        //self::exportBlock($id);
    }

    public static function saveMetaTags($tags, $id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, tag_id')
            ->from('#__gridbox_tags_map')
            ->where('`page_id` = '. $id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as $item) {
            if (!in_array($item->tag_id, $tags)) {
                $query = $db->getQuery(true)
                    ->delete('#__gridbox_tags_map')
                    ->where('id = '.$item->id);
                $db->setQuery($query);
                $db->execute();
            }
        }
        foreach ($tags as $tag) {
            if (!empty($tag)) {
                if (strpos($tag, 'new$') !== false) {
                    $tag = substr($tag, 4);
                    $object = new stdClass();
                    $object->title = $tag;
                    $object->alias = $object->title;
                    $object->alias = self::getAlias($object->alias, '#__gridbox_tags', 'alias');
                    $db->insertObject('#__gridbox_tags', $object);
                    $obj = new stdClass();
                    $obj->page_id = $id;
                    $obj->tag_id = $db->insertid();
                    $db->insertObject('#__gridbox_tags_map', $obj);
                } else {
                    $query = $db->getQuery(true)
                        ->select('id')
                        ->from('#__gridbox_tags_map')
                        ->where('`page_id` = '.$id)
                        ->where('`tag_id` = '.$tag);
                    $db->setQuery($query);
                    $item = $db->loadResult();
                    if (empty($item)) {
                        $obj = new stdClass();
                        $obj->page_id = $id;
                        $obj->tag_id = $tag;
                        $db->insertObject('#__gridbox_tags_map', $obj);
                    }
                }
            }
        }
    }

    public static function exportFooter($obj, $name)
    {
        $config = JFactory::getConfig();
        $file =  $config->get('tmp_path') . '/'.$name.'.json';
        JFile::write($file, json_encode($obj->items));
        $file =  $config->get('tmp_path') . '/'.$name.'.php';
        JFile::write($file, $obj->html);
    }

    public static function exportBlock($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('style, params, title')
            ->from('#__gridbox_pages')
            ->where('`id` = '.$id);
        $db->setQuery($query);
        $obj = $db->loadObject();
        $object = new stdClass();
        $object->html = $obj->params;
        $object->items = $obj->style;
        $string = json_encode($object);
        $doc = new DOMDocument('1.0');
        $doc->formatOutput = true;
        $root = $doc->createElement('gridbox');
        $root = $doc->appendChild($root);
        $page = $doc->createElement('data');
        $page = $root->appendChild($page);
        $data = $doc->createTextNode($string);
        $page->appendChild($data);
        $config = JFactory::getConfig();
        $file = $config->get('tmp_path').'/'.$obj->title.'.xml';
        $doc->save($file);
    }

    public static function createGlobalCss()
    {
        $db = JFactory::getDbo();
        $str = '';
        $query = $db->getQuery(true)
            ->select('item')
            ->from('`#__gridbox_library`')
            ->where('`global_item` <> '.$db->quote(''));
        $db->setQuery($query);
        $items = $db->loadObjectList();
        self::$fonts = array();
        self::$customFonts = array();
        foreach ($items as $key => $value) {
            $item = json_decode($value->item);
            $str .= self::sectionRules($item->items, '../../../../');
        }
        $str .= self::prepareCustomFonts();
        $fonts = json_encode(self::$fonts);
        $query = $db->getQuery(true)
            ->update('`#__gridbox_api`')
            ->set('`key` = '.$db->quote($fonts))
            ->where('`service` = '.$db->quote('library_font'));
        $db->setQuery($query)
            ->execute();
        $file = JPATH_ROOT. '/templates/gridbox/css/storage/global-library.css';
        JFile::write($file, $str);
    }

    public static function saveGlobalItems($obj)
    {
        $db = JFactory::getDbo();
        foreach ($obj as $key => $value) {
            $item = json_encode($value);
            $query = $db->getQuery(true)
                ->update('`#__gridbox_library`')
                ->set('`item` = '.$db->quote($item))
                ->where('`global_item` = '.$db->quote($key));
            $db->setQuery($query)
                ->execute();
        }
        self::createGlobalCss();
    }

    public static function getFontUrl()
    {
        if (empty(self::$fonts)) {
            return '';
        }
        $url = '//fonts.googleapis.com/css?family=';
        foreach (self::$fonts as $key => $family) {
            $url .= $key.':';
            foreach ($family as $ind => $weight) {
                $url .= $weight;
                if ($ind != count($family) - 1) {
                    $url .= ',';
                } else {
                    $url .= '%7C';
                }
            }
        }
        $pos = strripos($url, '%7C');
        $url = substr($url, 0, $pos);
        $url .= '&subset=latin,cyrillic,greek,latin-ext,greek-ext,vietnamese,cyrillic-ext';

        return $url;
    }

    public static function saveCodeEditor($obj, $id)
    {
        $file = JPATH_ROOT. '/templates/gridbox/css/storage/code-editor-'.$id.'.css';
        JFile::write($file, $obj->css);
        $file = JPATH_ROOT. '/templates/gridbox/js/storage/code-editor-'.$id.'.js';
        JFile::write($file, $obj->js);
    }

    public static function saveWebsite($obj)
    {
        $obj->id = 1;
        $db = JFactory::getDbo();
        $db->updateObject('#__gridbox_website', $obj, 'id');
    }

    public static function createFavicon()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('favicon')
            ->from('#__gridbox_website')
            ->where('`id` = 1');
        $db->setQuery($query);
        $favicon = $db->loadResult();
        if (!empty($favicon) && JFile::exists(JPATH_ROOT.'/'.$favicon)) {
            JFile::delete(JPATH_ROOT. '/templates/gridbox/favicon.ico');
            JFile::copy(JPATH_ROOT.'/'.$favicon, JPATH_ROOT. '/templates/gridbox/favicon.ico');
        }
    }

    public static function getWebsiteCode()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('header_code, body_code')
            ->from('#__gridbox_website')
            ->where('`id` = 1');
        $db->setQuery($query);
        $result = $db->loadObject();

        return $result;
    }

    public static function getComBa($element)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('extension_id')
            ->from('`#__extensions`')
            ->where('`element` = '.$db->quote($element));
        $db->setQuery($query);
        $id = $db->loadResult();

        return $id;
    }

    public static function getContentsCurl($url)
    {
        $http = JHttpFactory::getHttp();
        $body = '';
        $host = 'balbooa.com';
        if ($socket =@ fsockopen($host, 80, $errno, $errstr, 30)) {
            $data = $http->get($url);
            $body = $data->body;
            fclose($socket);
        }
        
        return $body;
    }
    
    public static function parseHttpRequest()
    {
        $input = file_get_contents('php://input');
        $data = array();
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        if (!count($matches)) {
            parse_str(urldecode($input), $data);
            return $data;
        }
        $boundary = $matches[1];
        $blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);
        foreach ($blocks as $id => $block) {
            if (empty($block))
                continue;
            if (strpos($block, 'application/octet-stream') !== FALSE) {
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                $data['files'][$matches[1]] = $matches[2];
            } else {
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                $data[$matches[1]] = $matches[2];
            }
        }
        
        return $data;
    }

    public static function checkPostData()
    {
        if (function_exists('file_get_contents') && function_exists('parse_str')) {
            /*$data = self::parseHttpRequest();
            $input = JFactory::getApplication()->input;
            foreach ($data as $key => $value) {
                $input->set($key, $value);
            }*/
        }
    }

    public static function setNewMenuItem()
    {
        $db = JFactory::getDbo();
        $input = JFactory::getApplication()->input;
        $query = $db->getQuery(true)
            ->select('extension_id')
            ->from('`#__extensions`')
            ->where('`element` = '.$db->quote('com_gridbox'))
            ->where('`type` = '.$db->quote('component'));
        $db->setQuery($query);
        $component = $db->loadResult();
        $parent = $input->get('parent', 0, 'int');
        $menu = self::getMenu();
        $object = json_decode($menu);
        $obj = array();
        $obj['title'] = $input->get('title', '', 'string');
        $obj['menutype'] = $object->menutype;
        $alias = $obj['title'];
        $obj['alias'] = self::getNewMenuAlias($alias, '');
        $obj['link'] = $input->get('link', '', 'string');
        $obj['type'] = 'component';
        $obj['published'] = 1;
        $obj['parent_id'] = $parent;
        $obj['component_id'] = $component;
        $obj['access'] = 1;
        $obj['language'] = '*';
        $obj['params'] = '{"show_title":"","link_titles":"","show_intro":"","info_block_position":"","info_block_show_title":"",';
        $obj['params'] .= '"show_category":"","link_category":"","show_parent_category":"","link_parent_category":"",';
        $obj['params'] .= '"show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"",';
        $obj['params'] .= '"show_item_navigation":"","show_vote":"","show_icons":"","show_print_icon":"","show_email_icon":"",';
        $obj['params'] .= '"show_hits":"","show_tags":"","show_noauth":"","urls_position":"","menu-anchor_title":"","menu-anchor_css":"",';
        $obj['params'] .= '"menu_image":"","menu_text":1,"menu_show":1,"page_title":"","show_page_heading":"1","page_heading":"",';
        $obj['params'] .= '"pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}';
        JTable::addIncludePath(JPATH_ROOT.'/administrator/components/com_gridbox/tables/');
        $table = JTable::getInstance('Menu', 'gridboxTable', array());
        $table->setLocation($obj['parent_id'], 'last-child');
        $table->bind($obj);
        $table->store();
    }

    public static function setMenuItem()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('extension_id')
            ->from('`#__extensions`')
            ->where('`element` = '.$db->quote('com_gridbox'))
            ->where('`type` = '.$db->quote('component'));
        $db->setQuery($query);
        $component = $db->loadResult();
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $parent = $input->get('parent', 0, 'int');
        $obj = array();
        $obj['title'] = $input->get('title', '', 'string');
        $obj['menutype'] = $input->get('menutype', '', 'string');
        $alias = $obj['title'];
        $obj['alias'] = self::getNewMenuAlias($alias, '');
        $edit_type = $input->get('edit_type', '', 'string');
        if (empty($edit_type)) {
            $obj['link'] = 'index.php?option=com_gridbox&view=page&id='.$id;
        } else if ($edit_type == 'blog') {
            $obj['link'] = 'index.php?option=com_gridbox&view=blog&app='.$id.'&id=0';
        }
        $obj['type'] = 'component';
        $obj['published'] = 1;
        $obj['parent_id'] = $parent;
        $obj['component_id'] = $component;
        $obj['access'] = 1;
        $obj['language'] = '*';
        $obj['params'] = '{"show_title":"","link_titles":"","show_intro":"","info_block_position":"","info_block_show_title":"",';
        $obj['params'] .= '"show_category":"","link_category":"","show_parent_category":"","link_parent_category":"",';
        $obj['params'] .= '"show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"",';
        $obj['params'] .= '"show_item_navigation":"","show_vote":"","show_icons":"","show_print_icon":"","show_email_icon":"",';
        $obj['params'] .= '"show_hits":"","show_tags":"","show_noauth":"","urls_position":"","menu-anchor_title":"","menu-anchor_css":"",';
        $obj['params'] .= '"menu_image":"","menu_text":1,"menu_show":1,"page_title":"","show_page_heading":"1","page_heading":"",';
        $obj['params'] .= '"pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}';
        JTable::addIncludePath(JPATH_ROOT.'/administrator/components/com_gridbox/tables/');
        $table = JTable::getInstance('Menu', 'gridboxTable', array());
        $table->setLocation($obj['parent_id'], 'last-child');
        $table->bind($obj);
        $table->store();

        return $table->id;
    }

    public static function getNewMenuAlias($type, $orig)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(id)')
            ->from('#__menu')
            ->where('`alias` = '.$db->quote($type));
        $db->setQuery($query);
        $n = $db->loadResult();
        if (!empty($n)) {
            if (empty($orig)) {
                $type = JString::increment($type);
            } else {
                $type = JString::increment($orig);
            }
            $orig = $type;
            $type = self::stringURLSafe($type);
            if (empty($type)) {
                $type = $orig;
                $type = self::replace($type);
                $type = JFilterOutput::stringURLSafe($type);
            }
            if (empty($type)) {
                $type = date('Y-m-d-H-i-s');
            }
            $type = self::getNewMenuAlias($type, $orig);
        }

        return $type;
    }

    public static function getMenu()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('params')
            ->from('#__modules')
            ->where('`id` = '.$id);
        $db->setQuery($query);
        $menu = $db->loadResult();
        $menu = json_decode($menu);
        $obj = new stdClass();
        $obj->menutype = $menu->menutype;
        $obj->items = self::getMenuItems($menu->menutype);
        
        return json_encode($obj);
    }

    public static function getMenuItems($menutype)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, title')
            ->from('#__menu')
            ->where('`menutype` = '.$db->quote($menutype));
        $db->setQuery($query);
        $items = $db->loadObjectList();
        
        return $items;
    }

    public static function checkFooter()
    {
        $obj = new stdClass();
        $obj->items = self::getOptions('footer');
        include JPATH_ROOT.'/components/com_gridbox/views/layout/footer.php';
        $obj->html = $out;
        
        return $obj;
    }

    public static function checkHeader()
    {
        $obj = new stdClass();
        $obj->items = self::getOptions('header');
        include JPATH_ROOT.'/components/com_gridbox/views/layout/header.php';
        $obj->html = $out;
        
        return $obj;
    }

    public static function checkGridboxLanguage()
    {
        $language = JFactory::getLanguage();
        $paths = $language->getPaths('com_gridbox');
        if (empty($paths)) {
            $language->load('com_gridbox');
        }
    }

    public static function loadGridboxLanguage()
    {
        $path = JPATH_ROOT.'/administrator/components/com_gridbox/language/site/en-GB/en-GB.com_gridbox.ini';
        $result = array('ERROR' => JText::_('ERROR'));
        if (JFile::exists($path)) {
            $contents = JFile::read($path);
            $contents = str_replace('_QQ_', '"\""', $contents);
            $data = parse_ini_string($contents);
            foreach ($data as $ind => $value) {
                $result[$ind] = JText::_($ind);
            }
        }
        if (phpversion() < '7.2.0') {
            $json = json_encode($result);
        } else {
            $json = json_encode($result, JSON_INVALID_UTF8_IGNORE);
        }
        $data = 'var gridboxLanguage = '.$json.';';

        return $data;
    }

    public static function loadModule()
    {
        $input = JFactory::getApplication()->input;
        $module = $input->get('module', '', 'string');
        if ($module == 'setCalendar') {
            $data = self::setCalendar();
            $data .= " app.modules.calendar = true;
            if (app.actionStack['calendar']) {
                while (app.actionStack['calendar'].length > 0) {
                    var func = app.actionStack['calendar'].pop();
                    func();
                }
            }";
        } else if ($module == 'defaultElementsStyle') {
            $defaultElementsStyle = self::getDefaultElementsStyle();
            $data = 'var defaultElementsStyle = '.$defaultElementsStyle.';';
        } else if ($module == 'gridboxLanguage') {
            $data = self::loadGridboxLanguage();
        } else if ($module == 'shapeDividers') {
            $shape = self::getShapeObject();
            $data = 'var shapeDividers = '.json_encode($shape).';';
        } else if ($module == 'presetsPatern') {
            $presetsPatern = self::getOptions('presetsPatern');
            $data = 'var presetsPatern = '.json_encode($presetsPatern).';';
        } else {
            $data = JFile::read(JPATH_ROOT.'/components/com_gridbox/libraries/modules/'.$module.'.js');
        }

        return $data;
    }

    public static function getShapeObject()
    {
        $folder = JPATH_ROOT.'/components/com_gridbox/assets/images/shape-dividers/';
        $files = JFolder::files($folder);
        $shape = array();
        foreach ($files as $file) {
            $ext = JFile::getExt($file);
            if ($ext == 'svg') {
                $key = str_replace('.svg', '', $file);
                $shape[$key] = JFile::read($folder.$file);
            }
        }

        return $shape;
    }

    public static function getOptions($type)
    {
        $json = JFile::read(JPATH_ROOT.'/components/com_gridbox/libraries/json/'.$type.'.json');
        
        return json_decode($json);
    }

    public static function checkBalbooaGridboxState()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('`key`')
            ->from('#__gridbox_api')
            ->where('service = '.$db->quote('balbooa_activation'));
        $db->setQuery($query);
        $balbooa = $db->loadResult();
        $galleryState = json_decode($balbooa);

        return isset($galleryState->data);
    }

    public static function createFontString($fonts)
    {
        $html = '';
        foreach ($fonts->items as $key => $font) {
            $str = json_encode($font->variants);
            $str = str_replace('regular', '400', $str);
            $html .= '<li data-style="'.htmlspecialchars($str, ENT_QUOTES).'" data-value="';
            $html .= $font->family.'">'.$font->family.'</li>';
        }
        
        return $html;
    }

    public static function getAccess()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title')
            ->from('#__viewlevels')
            ->order($db->quoteName('ordering') . ' ASC')
            ->order($db->quoteName('title') . ' ASC');
        $db->setQuery($query);
        $array = $db->loadObjectList();
        $access = array();
        foreach ($array as $value) {
            $access[$value->id] = $value->title;
        }

        return $access;
    }

    public static function replace($str)
    {
        $str = mb_strtolower($str, 'utf-8');
        $search = array('?', '!', '.', ',', ':', ';', '*', '(', ')', '{', '}', '***91;',
            '***93;', '%', '#', '№', '@', '$', '^', '-', '+', '/', '\\', '=',
            '|', '"', '\'', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'з', 'и', 'й',
            'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ъ',
            'ы', 'э', ' ', 'ж', 'ц', 'ч', 'ш', 'щ', 'ь', 'ю', 'я');
        $replace = array('-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-',
            '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-',
            'a', 'b', 'v', 'g', 'd', 'e', 'e', 'z', 'i', 'y', 'k', 'l', 'm', 'n',
            'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'j', 'i', 'e', '-', 'zh', 'ts',
            'ch', 'sh', 'shch', '', 'yu', 'ya');
        $str = str_replace($search, $replace, $str);
        $str = trim($str);
        $str = preg_replace("/_{2,}/", "-", $str);

        return $str;
    }

    public static function getLanguages()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('lang_code, title')
            ->from('#__languages')
            ->where('published >= 0')
            ->order('title');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $languages = array();
        $languages['*'] = JText::_('JALL');
        foreach ($items as $key => $value) {
            $languages[$value->lang_code] = $value->title;
        }

        return $languages;
    }

    public static function checkGridboxLoginData()
    {
        $input = JFactory::getApplication()->input;
        if ($input->cookie->exists('gridbox_username')) {
            $username = $input->cookie->get('gridbox_username');
            self::userLogin($username);
            setcookie('gridbox_username', '', time() - 3600, '/');
        }
    }

    public static function userLogin($username)
    {
        $user = JUser::getInstance();
        $id = (int) JUserHelper::getUserId($username);
        if ($id) {
            $db = JFactory::getDbo();
            $user->load($id);
            $result = $user->authorise('core.login.site');
            if ($result) {
                $user->guest = 0;
                $session = JFactory::getSession();
                $oldSessionId = $session->getId();
                $session->fork();
                $session->set('user', $user);
                $app = JFactory::getApplication();
                $app->checkSession();
                $query = $db->getQuery(true)
                    ->delete('#__session')
                    ->where($db->quoteName('session_id') . ' = ' . $db->quote($oldSessionId));
                try {
                    $db->setQuery($query)->execute();
                } catch (RuntimeException $e) {
                    
                }
                $user->setLastVisit();
                $app->input->cookie->set(
                    'joomla_user_state',
                    'logged_in',
                    0,
                    $app->get('cookie_path', '/'),
                    $app->get('cookie_domain', ''),
                    $app->isHttpsForced(),
                    true
                );
            }
        }
    }

    public static function setCalendar()
    {
        $_DN = array(JText::_('SUNDAY'), JText::_('MONDAY'), JText::_('TUESDAY'), JText::_('WEDNESDAY'),
            JText::_('THURSDAY'), JText::_('FRIDAY'), JText::_('SATURDAY'), JText::_('SUNDAY'));
        $_SDN = array(JText::_('SUN'), JText::_('MON'), JText::_('TUE'), JText::_('WED'), JText::_('THU'),
            JText::_('FRI'), JText::_('SAT'), JText::_('SUN'));
        $_MN = array(JText::_('JANUARY'), JText::_('FEBRUARY'), JText::_('MARCH'), JText::_('APRIL'),
            JText::_('MAY'), JText::_('JUNE'), JText::_('JULY'), JText::_('AUGUST'), JText::_('SEPTEMBER'),
            JText::_('OCTOBER'), JText::_('NOVEMBER'), JText::_('DECEMBER'));
        $_SMN = array(JText::_('JANUARY_SHORT'), JText::_('FEBRUARY_SHORT'), JText::_('MARCH_SHORT'),
            JText::_('APRIL_SHORT'), JText::_('MAY_SHORT'), JText::_('JUNE_SHORT'), JText::_('JULY_SHORT'),
            JText::_('AUGUST_SHORT'), JText::_('SEPTEMBER_SHORT'), JText::_('OCTOBER_SHORT'),
            JText::_('NOVEMBER_SHORT'), JText::_('DECEMBER_SHORT'));
        $today = " " . JText::_('JLIB_HTML_BEHAVIOR_TODAY') . " ";
        $_TT = array('INFO' => JText::_('JLIB_HTML_BEHAVIOR_ABOUT_THE_CALENDAR'),
            'ABOUT' => "DHTML Date/Time Selector\n"
            . "(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n"
            . "For latest version visit: http://www.dynarch.com/projects/calendar/\n"
            . "Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details."
            . "\n\n" . JText::_('JLIB_HTML_BEHAVIOR_DATE_SELECTION')
            . JText::_('JLIB_HTML_BEHAVIOR_YEAR_SELECT')
            . JText::_('JLIB_HTML_BEHAVIOR_MONTH_SELECT')
            . JText::_('JLIB_HTML_BEHAVIOR_HOLD_MOUSE'),
            'ABOUT_TIME' => "\n\n"
            . "Time selection:\n"
            . "- Click on any of the time parts to increase it\n"
            . "- or Shift-click to decrease it\n"
            . "- or click and drag for faster selection.",
            'PREV_YEAR' => JText::_('JLIB_HTML_BEHAVIOR_PREV_YEAR_HOLD_FOR_MENU'),
            'PREV_MONTH' => JText::_('JLIB_HTML_BEHAVIOR_PREV_MONTH_HOLD_FOR_MENU'),
            'GO_TODAY' => JText::_('JLIB_HTML_BEHAVIOR_GO_TODAY'),
            'NEXT_MONTH' => JText::_('JLIB_HTML_BEHAVIOR_NEXT_MONTH_HOLD_FOR_MENU'),
            'SEL_DATE' => JText::_('JLIB_HTML_BEHAVIOR_SELECT_DATE'),
            'DRAG_TO_MOVE' => JText::_('JLIB_HTML_BEHAVIOR_DRAG_TO_MOVE'),
            'PART_TODAY' => $today,
            'DAY_FIRST' => JText::_('JLIB_HTML_BEHAVIOR_DISPLAY_S_FIRST'),
            'WEEKEND' => JFactory::getLanguage()->getWeekEnd(),
            'CLOSE' => JText::_('JLIB_HTML_BEHAVIOR_CLOSE'),
            'TODAY' => JText::_('JLIB_HTML_BEHAVIOR_TODAY'),
            'TIME_PART' => JText::_('JLIB_HTML_BEHAVIOR_SHIFT_CLICK_OR_DRAG_TO_CHANGE_VALUE'),
            'DEF_DATE_FORMAT' => "%Y-%m-%d",
            'TT_DATE_FORMAT' => JText::_('JLIB_HTML_BEHAVIOR_TT_DATE_FORMAT'),
            'WK' => JText::_('JLIB_HTML_BEHAVIOR_WK'),
            'TIME' => JText::_('JLIB_HTML_BEHAVIOR_TIME')
        );

        return 'Calendar._DN = ' . json_encode($_DN) . ';'
            . ' Calendar._SDN = ' . json_encode($_SDN) . ';'
            . ' Calendar._FD = 0;'
            . ' Calendar._MN = ' . json_encode($_MN) . ';'
            . ' Calendar._SMN = ' . json_encode($_SMN) . ';'
            . ' Calendar._TT = ' . json_encode($_TT) . ';';
    }

    public static function checkMeta()
    {
        $app = JFactory::getApplication();
        $doc = JFactory::getDocument();
        $option = $app->input->getCmd('option', '');
        $view = $app->input->getCmd('view', '');
        $edit_type = $app->input->getCmd('edit_type', '');
        $tag = $app->input->getCmd('tag', '');
        $author = $app->input->getCmd('author', '');
        $str = '';
        if ($option == 'com_gridbox' && empty($edit_type) && ($view == 'page' || $view == 'gridbox' || $view == 'blog')) {
            $id = $app->input->getCmd('id', 0);
            if ($id == 0 && $view != 'blog') {
                return;
            }
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if ($view == 'blog') {
                $query->select('title, meta_title, meta_description, image AS intro_image, share_image, share_title, share_description');
                if (!empty($tag)) {
                    $id = $tag;
                    $query->from('#__gridbox_tags');
                } else if (!empty($author)) {
                    $id = $author;
                    $query->from('#__gridbox_authors');
                } else if ($id != 0) {
                    $query->from('#__gridbox_categories');
                } else {
                    $id = $app->input->getCmd('app', 0);
                    $query->from('#__gridbox_app');
                }
            } else {
                $query->select('title, meta_title, meta_description, intro_image, share_image, share_title, share_description')
                    ->from('#__gridbox_pages');
            }
            $query->where('`id` = '.$id);
            $db->setQuery($query);
            $item = $db->loadObject();
            $image = $item->share_image != 'share_image' && !empty($item->share_image) ? $item->share_image : $item->intro_image;
            $menus = $app->getMenu();
            $menu = $menus->getActive();
            $title  = !empty($item->share_title) ? $item->share_title : $item->meta_title;
            $desc = !empty($item->share_description) ? $item->share_description : $item->meta_description;
            if (empty($title)) {
                $title = $item->title;
            }
            if (isset($menu) && $menu->query['view'] == $view && $menu->query['id'] == $id) {
                $params  = $menus->getParams($menu->id);
                $page_title = $params->get('page_title');
                $page_desc = $params->get('menu-meta_description');
            } else {
                $page_title = '';
                $page_desc = '';
                $page_key = '';
            }
            if (!empty($page_title)) {
                $title = $page_title;
            }
            if (!empty($page_desc)) {
                $desc = $page_desc;
            }
            if ($app->get('sitename_pagetitles', 0) == 1) {
                $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
            } else if ($app->get('sitename_pagetitles', 0) == 2) {
                $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
            }
            $path = JPATH_ROOT . '/components/com_bagallery/helpers/bagallery.php';
            JLoader::register('bagalleryHelper', $path);
            $loaded = JLoader::getClassList();
            if (isset($loaded['bagalleryhelper']) && method_exists('bagalleryhelper', 'checkGalleryUri')
                && bagalleryhelper::checkGalleryUri()) {
                return "\n";
            }
            $str = "\t<meta property=\"og:type\" content=\"article\" />\n\t";
            $str .= "<meta property=\"og:title\" content=\"".htmlspecialchars($title, ENT_QUOTES)."\">\n\t";
            $str .= "<meta property=\"og:description\" content=\"".htmlspecialchars($desc, ENT_QUOTES)."\">\n\t";
            $str .= "<meta property=\"og:url\" content=\"".$doc->getBase()."\">\n\t";
            if (!empty($image) && file_exists(JPATH_ROOT.'/'.$image)) {
                $str .= "<meta property=\"og:image\" content=\"".JUri::root().$image."\">\n\t";
                $ext = JFile::getExt($image);
                $imageCreate = self::imageCreate($ext);
                if ($img = $imageCreate(JPATH_ROOT.'/'.$image)) {
                    $width = imagesx($img);
                    $height = imagesy($img);
                    $str .= "<meta property=\"og:image:width\" content=\"".$width."\">\n\t";
                    $str .= "<meta property=\"og:image:height\" content=\"".$height."\">\n";
                }
            } else if (!empty($image) && strpos($image, 'balbooa.com') !== false) {
                $str .= "<meta property=\"og:image\" content=\"".$image."\">\n\t";
            } else {
                $str .= "<meta property=\"og:image\" content=\"\">\n";
            }
        }

        return $str;
    }

    public static function imageCreate($ext) {
        switch ($ext) {
            case 'png':
                $imageCreate = 'imagecreatefrompng';
                break;
            case 'gif':
                $imageCreate = 'imagecreatefromgif';
                break;
            case 'webp':
                $imageCreate = 'imagecreatefromwebp';
                break;
            default:
                $imageCreate = 'imagecreatefromjpeg';
        }
        return $imageCreate;
    }

    public static function checkExt($ext)
    {
        switch($ext) {
            case 'jpg':
            case 'png':
            case 'gif':
            case 'jpeg':
                return true;
            default:
                return false;
        }
    }

    public static function aboutUs()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("manifest_cache");
        $query->from("#__extensions");
        $query->where("type=" .$db->quote('component'))
            ->where('element=' .$db->quote('com_gridbox'));
        $db->setQuery($query);
        $about = $db->loadResult();
        $about = json_decode($about);
        return $about;
    }

    public static function getMapsKey()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('`key`')
            ->from('`#__gridbox_api`')
            ->where('`service` = '.$db->quote('google_maps'));
        $db->setQuery($query);
        $key = $db->loadResult();
        return $key;
    }

    public static function checkPlugin($title)
    {
        $default = array('bagallery' => 1, 'baforms' => 1, 'modules' => 1, 'recent-posts' => 1, 'fields-filter' => 1,
            'blog-content' => 1, 'post-intro' => 1, 'field-google-maps' => 1, 'field-video' => 1, 'field-group' => 1,
            'field' => 1, 'image-field' => 1, 'field-simple-gallery' => 1, 'field-slideshow' => 1,
            'product-slideshow' => 1, 'product-gallery' => 1, 'event-calendar' => 1, 'store-search' => 1,
            'checkout-order-form' => 1, 'checkout-form' => 1, 'recent-comments' => 1, 'wishlist' => 1,
            'logo' => 1, 'menu' => 1, 'post-tags' => 1, 'tags' => 1, 'categories' => 1, 'author' => 1,
            'recent-reviews' => 1, 'reviews' => 1, 'google-maps-places' => 1, 'add-to-cart' => 1, 'cart' => 1,
            'related-posts' => 1, 'post-navigation' => 1, 'search' => 1, 'recent-posts-slider' => 1, 'comments-box' => 1,
            'related-posts-slider' => 1, 'recently-viewed-products' => 1);
        if (isset($default[$title])) {
            return 1;
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from('#__gridbox_plugins')
            ->where('`title` = ' .$db->quote('ba-'.$title));
        $db->setQuery($query);
        $id = $db->loadResult();

        return $id;
    }

    public static function checkMoreScripts($html)
    {
        $doc = JFactory::getDocument();
        $pageTitle = $doc->getTitle();
        if (strpos($pageTitle, 'Gridbox Editor') === false) {
            if (strpos($html, 'ba-item-map') || strpos($html, 'ba-item-field-google-maps')
                || strpos($html, 'ba-item-google-maps-places')) {
                $key = self::getMapsKey();
                $doc->addScript('https://maps.googleapis.com/maps/api/js?libraries=places&key='.$key);
            }
        }
        if (strpos($html, 'ba-item-yandex-maps')) {
            $key = self::getYandexMapsKey();
            $doc->addScript('https://api-maps.yandex.ru/2.1/?apikey='.$key.'&lang=ru_RU');
            $doc->addScriptDeclaration('
                if (window.ymaps) {
                    ymaps.ready(function(){
                        app.ymaps = true;
                        if ("initYandexMaps" in app) {
                            app.initYandexMaps(null, null);
                        }
                    });
                }
            ');
        }
        if (strpos($html, 'ba-item-openstreetmap')) {
            $doc->addStyleSheet('https://unpkg.com/leaflet@1.4.0/dist/leaflet.css');
            $doc->addScript('https://unpkg.com/leaflet@1.4.0/dist/leaflet.js');
            $doc->addScriptDeclaration('document.addEventListener("DOMContentLoaded", function(){
                app.openstreetmap = true;
            });');
        }
    }

    public static function getMainMenu()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__modules')
            ->where('client_id = 0')
            ->where('published = 1')
            ->where('module = '.$db->quote('mod_menu'));
        $db->setQuery($query);
        $menu = $db->loadResult();

        return $menu;
    }

    public static function prepareFonts($fonts, $option, $id, $edit_type)
    {
        $app = JFactory::getApplication();
        $view = $app->input->getCmd('view', '');
        $option = $app->input->getCmd('option', '');
        if ($view == 'blog' && $edit_type != 'system') {
            $edit_type = 'blog';
            $id = $app->input->getCmd('app', '');
        }
        if ($option == 'com_gridbox' && $view != 'page' && $view != 'blog') {
            self::$fonts = array('Roboto' => array(300, 400, 500, 700));
        }
        $fonts = json_decode($fonts);
        self::updateFonts($fonts);
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('`key`')
            ->from('`#__gridbox_api`')
            ->where('`service` = '.$db->quote('library_font'));
        $db->setQuery($query);
        $libraryFonts = $db->loadResult();
        if (!empty($libraryFonts)) {
            $libraryFonts = json_decode($libraryFonts);
            self::updateFonts($libraryFonts);
        }
        if ($option == 'com_gridbox' && empty($edit_type)) {
            $query = $db->getQuery(true)
                ->select('p.fonts')
                ->from('#__gridbox_pages AS p')
                ->where('p.id = '.$id)
                ->select('a.page_fonts')
                ->leftJoin('`#__gridbox_app` AS a'
                    . ' ON '
                    . $db->quoteName('p.app_id')
                    . ' = ' 
                    . $db->quoteName('a.id')
                );
            $db->setQuery($query);
            $pageFonts = $db->loadObject();
            if (!empty($pageFonts->fonts)) {
                $pageFonts->fonts = json_decode($pageFonts->fonts);
                self::updateFonts($pageFonts->fonts);
            }
            if (!empty($pageFonts->page_fonts)) {
                $pageFonts->page_fonts = json_decode($pageFonts->page_fonts);
                self::updateFonts($pageFonts->page_fonts);
            }
        } else if ($edit_type != 'system') {
            $query = $db->getQuery(true)
                ->select('app_fonts')
                ->from('#__gridbox_app')
                ->where('id = '.$id);
            $db->setQuery($query);
            $font = $db->loadResult();
            if (!empty($font)) {
                $font = json_decode($font);
                self::updateFonts($font);
            }
        } else if ($edit_type == 'system') {
            $query = $db->getQuery(true)
                ->select('fonts')
                ->from('#__gridbox_system_pages')
                ->where('id = '.$id);
            $db->setQuery($query);
            $font = $db->loadResult();
            if (!empty($font)) {
                $font = json_decode($font);
                self::updateFonts($font);
            }
        }
        $url = self::getFontUrl();
        
        return $url;
    }

    public static function updateFonts($fonts)
    {
        foreach ($fonts as $key => $font) {
            if (!isset(self::$fonts[$key])) {
                self::$fonts[$key] = array();
            }
            foreach ($font as $weight) {
                if (!in_array($weight, self::$fonts[$key])) {
                    self::$fonts[$key][] = $weight;
                }
            }
        }
    }

    public static function getValidId()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__template_styles')
            ->where('`client_id` = 0')
            ->where('`home` = 1');
        $db->setQuery($query);
        $id = $db->loadResult();

        return $id;
    }

    public static function getGridboxItems($id, $theme, $edit_type, $view)
    {
        $gridbox = self::getThemeParams($theme);
        $params = $gridbox->get('params');
        $params->image = $gridbox->get('image', '');
        $footer = $gridbox->get('footer');
        $header = $gridbox->get('header');
        $pageParams = self::createPageParams($params, $header->items, $footer->items, $id, $edit_type, $view);

        return $pageParams;
    }

    public static function preparePresets($data)
    {
        foreach ($data as $key => $value) {
            $data->{$key} = self::presetsCompatibility($value);
            self::comparePresets($data->{$key});
        }

        return $data;
    }

    public static function createPageParams($params, $header, $footer, $id, $edit_type, $view)
    {
        if (!isset($params->presets)) {
            $params->presets = new stdClass();
        }
        if (!isset($params->defaultPresets)) {
            $params->defaultPresets = new stdClass();
        }
        self::$presets = $params->presets;
        $header = self::preparePresets($header);
        $footer = self::preparePresets($footer);
        $library = self::getGlobalItems();
        $array = array('theme' => $params, 'header' => $header,
            'footer' => $footer, 'library' => new stdClass());
        foreach ($library as $value) {
            $globItem = json_decode($value->item);
            $globItem->items = self::preparePresets($globItem->items);
            foreach ($globItem->items as $key => $glob) {
                $array['library']->{$key} = $glob;
            }
        }
        $db = JFactory::getDbo();
        if (empty($edit_type) && $view != 'blog' && $id != 0) {
            $query = $db->getQuery(true)
                ->select('p.style')
                ->from('#__gridbox_pages AS p')
                ->where('p.id = '.$id)
                ->select('a.page_items, a.type')
                ->leftJoin('`#__gridbox_app` AS a'
                    . ' ON '
                    . $db->quoteName('p.app_id')
                    . ' = ' 
                    . $db->quoteName('a.id')
                );
            $db->setQuery($query);
            $item = $db->loadObject();
            $page = json_decode($item->style);
            $page = self::preparePresets($page);
            if (!empty($item->type) && $item->type != 'single' && $view != 'gridbox') {
                if (empty($item->page_items) || $item->page_items == null || $item->page_items == 'null') {
                    $item->page_items = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/apps/'.$item->type.'/default.json');
                }
                $page_items = json_decode($item->page_items);
                $page_items = self::preparePresets($page_items);
                $products = array();
                foreach ($page_items as $key => $value) {
                    $page->{$key} = $value;
                    if ($value->type == 'add-to-cart') {
                        $products[] = $value;
                    }
                }
                $db = JFactory::getDbo();
                $query = $db->getQuery(true)
                    ->select('pf.value, f.field_key')
                    ->from('#__gridbox_page_fields as pf')
                    ->where('pf.page_id = '.$id)
                    ->where('pf.field_type = '.$db->quote('field-google-maps'))
                    ->leftJoin('`#__gridbox_fields` AS f ON pf.field_id = f.id');
                $db->setQuery($query);
                $fieldGoogleMaps = $db->loadObjectList();
                foreach ($fieldGoogleMaps as $fieldMap) {
                    if (isset($page->{$fieldMap->field_key})) {
                        $fieldValue = json_decode($fieldMap->value);
                        $page->{$fieldMap->field_key}->map->center = $fieldValue->center;
                        $page->{$fieldMap->field_key}->map->zoom = $fieldValue->zoom;
                        if (isset($fieldValue->marker) && isset($fieldValue->marker->position)) {
                            $page->{$fieldMap->field_key}->marker->{0}->place = $fieldValue->marker->place;
                            $page->{$fieldMap->field_key}->marker->{0}->position = $fieldValue->marker->position;
                        }
                    }
                }
                if (!empty($products)) {
                    $productData = new stdClass();
                    $productData->data = self::getProductData($id);
                    $productData->thousand = self::$store->currency->thousand;
                    $productData->separator = self::$store->currency->separator;
                    $productData->decimals = self::$store->currency->decimals;
                    $variationsMap = self::getProductVariationsMap($id);
                    $variations = self::getProductVariations($productData->data->variations, $variationsMap);
                    $currency = self::$store->currency;
                    $productData->variations = new stdClass();
                    $productData->images = new stdClass();
                    foreach ($variationsMap as $variation) {
                        $productData->images->{$variation->option_key} = json_decode($variation->images);
                    }
                    foreach ($variations as $key => $variation) {
                        $productData->variations->{$key} = $variation;
                    }
                    foreach ($products as $product) {
                        $product->productData = $productData;
                    }
                }
            } else if (!empty($item->type) && $item->type != 'single' && $view == 'gridbox') {
                $array['header'] = $array['footer'] = new stdClass();
            }
            $array['page'] = $page;
        } else if ($edit_type == 'post-layout') {
            $query = $db->getQuery(true)
                ->select('page_items, type')
                ->from('#__gridbox_app')
                ->where('id = '.$id);
            $db->setQuery($query);
            $item = $db->loadObject();
            if (empty($item->page_items) || $item->page_items == null || $item->page_items == 'null') {
                $item->page_items = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/apps/'.$item->type.'/default.json');
            }
            $page = json_decode($item->page_items);
            $page = self::preparePresets($page);
            $array['page'] = $page;
        } else if ($edit_type == 'blog' || $view == 'blog') {
            $query = $db->getQuery(true)
                ->select('app_items, type')
                ->from('#__gridbox_app')
                ->where('id = '.$id);
            $db->setQuery($query);
            $item = $db->loadObject();
            if (empty($item->app_items) || $item->app_items == null || $item->app_items == 'null') {
                $item->app_items = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/apps/'.$item->type.'/app.json');
            }
            $page = json_decode($item->app_items);
            $page = self::preparePresets($page);
            $array['page'] = $page;
        } else if ($edit_type == 'system') {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('items, type')
                ->from('#__gridbox_system_pages')
                ->where('id = '.$id);
            $db->setQuery($query);
            $item = $db->loadObject();
            if (empty($item->items)) {
                $item->items = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/system/'.$item->type.'.json');
            }
            $page = json_decode($item->items);
            if ($item->type == 'checkout') {
                $page->{'item-15289771305'}->items = self::getCustomerInfo();
            }
            $page = self::preparePresets($page);
            $array['page'] = $page;
        }
        $array = json_encode($array);

        return $array;
    }
    
    public static function checkCustom($id, $view, $time)
    {
        $str = '';
        $doc = JFactory::getDocument();
        $file = JPATH_ROOT. '/templates/gridbox/css/custom.css';
        if (JFile::exists($file) && filesize($file) != 0) {
            $file = JUri::root() . 'templates/gridbox/css/custom.css';
            $doc->addStyleSheet($file);
        }
        $file = JPATH_ROOT. '/templates/gridbox/css/storage/global-library.css';
        if (!JFile::exists($file)) {
            self::createGlobalCss();
        }
        if (filesize($file) != 0) {
            $file = JUri::root() . 'templates/gridbox/css/storage/global-library.css'.$time;
            $doc->addStyleSheet($file);
        }
        $db = JFactory::getDbo();
        if ($id == 0) {
            $query = $db->getQuery(true);
            $query->select('id')
                ->from('#__template_styles')
                ->where('`client_id` = 0')
                ->where('`home` = 1');
            $db->setQuery($query);
            $id = $db->loadResult();
        }
        $file = JPATH_ROOT.'/templates/gridbox/css/storage/style-'.$id.'.css';
        if (!JFile::exists($file)) {
            $query = $db->getQuery(true)
                ->select('params')
                ->from('`#__template_styles`')
                ->where('`id` = ' .$db->quote($id));
            $db->setQuery($query);
            $params = $db->loadResult();
            $params = json_decode($params);
            self::themeRules($params, $id);
        }
        $pageTitle = $doc->getTitle();
        if ($view != 'gridbox' || strpos($pageTitle, 'Gridbox Editor') === false) {
            $file = JPATH_ROOT.'/templates/gridbox/css/storage/code-editor-'.$id.'.css';
            if (isset(self::$systemApps->{'code-editor'}) && JFile::exists($file) && filesize($file) != 0) {
                $file = JUri::root().'templates/gridbox/css/storage/code-editor-'.$id.'.css'.$time;
                $doc->addStyleSheet($file);
            }
            $file = JPATH_ROOT.'/templates/gridbox/js/storage/code-editor-'.$id.'.js';
            if (isset(self::$systemApps->{'code-editor'}) && JFile::exists($file) && filesize($file) != 0) {
                $file = JUri::root().'templates/gridbox/js/storage/code-editor-'.$id.'.js'.$time;
                $doc->addScript($file);
            }
        }

        return $str;
    }
    
    public static function getThemeParams($id)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('params, id')
            ->from('`#__template_styles`');
        if ($id > 0) {
            $query->where('`id` = ' .$db->quote($id));
        } else {
            $query->where('`client_id` = 0')
                ->where('`template` = '.$db->quote('gridbox'));
        }
        $db->setQuery($query);
        $obj = $db->loadObject();
        $params = json_decode($obj->params);
        if (!isset($params->params)) {
            self::setBreakpoints();
            $params = new stdClass();
            $params->params = self::getOptions('theme');
            $params->footer = self::checkFooter();
            $params->header = self::checkHeader();
            $params->layout = '';
            $params->fonts = self::saveTheme($params, $obj->id);
        }
        if (!isset($params->params->colorVariables)) {
            $params->params->colorVariables = self::getOptions('color-variables');
        }
        if (!isset($params->params->presets)) {
            $params->params->presets = new stdClass();
        }
        if (!isset($params->params->defaultPresets)) {
            $params->params->defaultPresets = new stdClass();
        }
        self::$presets = $params->params->presets;
        self::$colorVariables = $params->params->colorVariables;
        $params = json_encode($params);
        $obj = new Registry;
        $obj->loadString($params);
        
        return $obj;
    }

    public static function getPostLayoutPage($app)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__gridbox_pages')
            ->where('app_id = '.$app)
            ->where('language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('page_category <> '.$db->quote('trashed'))
            ->order('id ASC');
        $db->setQuery($query);
        $id = $db->loadResult();
        
        return $id;
    }
    
    public static function getTheme($id, $blog = false, $edit_type = '')
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true)
            ->select('theme');
        if ($edit_type == 'post-layout') {
            $page = self::getPostLayoutPage($id);
            if ($page) {
                $id = $page;
                $blog = false;
            }
        }
        if ($edit_type == 'system') {
            $query->from('#__gridbox_system_pages');
        } else if (!$blog) {
            $query->from('#__gridbox_pages');
        } else {
            $query->from('#__gridbox_app');
        }
        $query->where('`id` = ' .$db->quote($id));
        $db->setQuery($query);
        $theme = $db->loadResult();
        
        return $theme;
    }

    public static function checkMainMenu($body)
    {
        $regex = '/\[main_menu=+(.*?)\]/i';
        $app = JFactory::getApplication();
        $view = $app->input->getCmd('view', '');
        preg_match_all($regex, $body, $matches, PREG_SET_ORDER);
        if ($matches) {
            foreach ($matches as $index => $match) {
                $module = $match[1];
                if (isset($module)) {
                    $db = JFactory::getDBO();
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__modules')
                        ->where('client_id = 0')
                        ->where('published = 1')
                        ->where('module = '.$db->quote('mod_menu'))
                        ->where('id = ' . $db->quote($module));
                    $query->order('ordering');
                    $db->setQuery($query);
                    $module = $db->loadObject();
                    $access = self::checkModuleAccess($module);
                    if ($access) {
                        $document = JFactory::getDocument();
                        $document->_type = 'html';
                        $renderer = $document->loadRenderer('module');
                        $html = $renderer->render($module);
                    } else {
                        $html = '';
                    }
                    if (!empty($html) || $view != 'gridbox') {
                        $body = @preg_replace("|\[main_menu=".$match[1]."\]|", $html, $body, 1);
                    }
                }
            }
        }

        return $body;
    }

    public static function checkModuleAccess($module)
    {
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        if (!in_array($module->access, $groups)) {
            return false;
        } else {
            return true;
        }
    }

    public static function clearDOM($body, $items)
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        include_once JPATH_ROOT.'/components/com_gridbox/libraries/php/phpQuery/phpQuery.php';
        $doc = phpQuery::newDocument($body);
        foreach ($items as $key => $item) {
            $access = isset($item->access_view) ? $item->access_view * 1 : 1;
            $user = JFactory::getUser();
            $groups = $user->getAuthorisedViewLevels();
            if (!in_array($access, $groups)) {
                if ($item->type == 'lightbox' || $item->type == 'cookies') {
                    $parent = pq('#'.$key)->parent()->parent()->remove();
                } else {
                    pq('#'.$key)->remove();
                }
            }
        }
        $search = '.ba-edit-item, .ba-box-model, .empty-item, .column-info, .ba-column-resizer,';
        $search .= ' .ba-edit-wrapper, .empty-list';
        foreach (pq($search) as $value) {
            pq($value)->remove();
        }
        foreach (pq('.content-text, .headline-wrapper > *') as $value) {
            pq($value)->removeAttr('contenteditable');
        }
        pq('.ba-item-main-menu > .ba-menu-wrapper > .main-menu > .add-new-item')->remove();
        $db = JFactory::getDbo();
        $state = self::checkBalbooaGridboxState();
        if ($state) {
            $query = $db->getQuery(true)
                ->select('title')
                ->from('#__gridbox_plugins');
            $db->setQuery($query);
            $result = $db->loadObjectList();
            $array = array('ba-blog-posts', 'ba-post-intro', 'ba-blog-content', 'ba-post-tags', 'ba-search', 'ba-store-search',
                'ba-preloader', 'ba-search-result', 'ba-store-search-result', 'ba-tags', 'ba-categories', 'ba-recent-posts',
                'ba-comments-box', 'ba-search-result-headline', 'ba-wishlist',
                'ba-field-google-maps', 'ba-related-posts', 'ba-author', 'ba-field', 'ba-image-field', 'ba-recent-comments',
                'ba-recent-reviews', 'ba-reviews', 'ba-fields-filter', 'ba-google-maps-places', 'ba-add-to-cart', 'ba-cart',
                'ba-field-simple-gallery', 'ba-product-gallery', 'ba-field-slideshow', 'ba-product-slideshow', 'ba-field-video',
                'ba-event-calendar', 'ba-field-group', 'ba-post-navigation', 'ba-checkout-order-form', 'ba-checkout-form',
                'ba-category-intro', 'ba-error-message', 'ba-recent-posts-slider', 'ba-related-posts-slider',
                'ba-recently-viewed-products');
            foreach ($result as $object) {
                $array[] = str_replace('ba-menu', 'ba-main-menu', $object->title);
            }
        } else {
            $array = array('ba-image', 'ba-text', 'ba-button', 'ba-logo', 'ba-menu', 'ba-modules', 'ba-forms', 'ba-gallery',
                'ba-error-message', 'ba-main-menu');
        }
        foreach (pq('.ba-item') as $key => $value) {
            $class = pq($value)->attr('class');
            $class = str_replace('-item', '', $class);
            $flag = false;
            $class = explode(' ', $class);
            foreach ($class as $name) {
                if (in_array($name, $array)) {
                    $flag = true;
                }
            }
            if (!$flag) {
                pq($value)->remove();
            }
        }
        foreach (pq('.ba-item-preloader') as $value) {
            $id = pq($value)->attr('id');
            pq($value)->attr('data-delay', $items->{$id}->delay);
        }
        foreach (pq('.ba-lightbox-backdrop:not(.ba-item-cookies)') as $key => $value) {
            if (!in_array('ba-lightbox', $array)) {
                pq($value)->remove();
            }
        }
        foreach (pq('.ba-item-cookies') as $key => $value) {
            if (!in_array('ba-cookies', $array)) {
                pq($value)->remove();
            }
        }
        $search = '.ba-slideshow-title, .ba-slideshow-description, .slideshow-button a';
        foreach (pq('.ba-item-carousel, .ba-item-slideset, .ba-item-slideshow')->find($search) as $value) {
            $text = pq($value)->text();
            if (empty($text) && !pq($value)->hasClass('ba-overlay-slideshow-button')) {
                pq($value)->parent()->remove();
            }
        }
        $search = '.ba-image-item-title, .ba-image-item-description, .ba-simple-gallery-title, .ba-simple-gallery-description';
        $item = '.ba-item-image, .ba-item-simple-gallery, .ba-item-overlay-section > .ba-image-wrapper > .ba-image-item-caption';
        foreach (pq($item)->find($search) as $value) {
            $text = pq($value)->text();
            if (empty($text)) {
                pq($value)->remove();
            }
        }
        $str = $doc->htmlOuter();
        
        return $str;
    }

    public static function setCustomIcons()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('DISTINCT path')
            ->from('#__gridbox_custom_user_icons');
        $db->setQuery($query);
        $icons = $db->loadObjectList();
        $doc = JFactory::getDocument();
        foreach ($icons as $key => $icon) {
            $doc->addStyleSheet(JUri::root().'templates/gridbox/library/icons/custom-icons/'.$icon->path.'/font.css');
        }
    }

    public static function getMapsPlaces($app, $menuItem, $pagesList = '')
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('pf.value, p.intro_image, p.title, c.title as category, p.app_id, p.id, p.page_category, p.created, p.hits')
            ->from('#__gridbox_page_fields as pf')
            ->where('pf.field_type = '.$db->quote('field-google-maps'))
            ->where('f.app_id = '.$app)
            ->leftJoin('#__gridbox_fields AS f ON pf.field_id = f.id')
            ->leftJoin('#__gridbox_pages AS p ON pf.page_id = p.id');
        if ($pagesList != '') {
            $query->where('p.id IN ('.$pagesList.')');
        }
        $date = date("Y-m-d H:i:s");
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $nullDate = $db->quote($db->getNullDate());
        $query->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->leftJoin('`#__gridbox_categories` AS c ON p.page_category = c.id')
            ->where('c.published = 1')
            ->where('c.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('c.access in ('.$groups.')');
        $db->setQuery($query);
        $pages = $db->loadObjectList();
        $events = array();
        foreach ($pages as $page) {
            $map = json_decode($page->value);
            if (empty($page->value) || !isset($map->marker->position)) {
                continue;
            }
            $page->map = $map;
            unset($page->value);
            $page->created = self::getPostDate($page->created);
            $url = self::getGridboxPageLinks($page->id, 'blog', $page->app_id, $page->page_category);
            if (strpos($url, '&Itemid=') === false && !empty($menuItem)) {
                $url .= '&Itemid='.$menuItem;
            }
            $page->url = JRoute::_($url);
            $url = self::getGridboxCategoryLinks($page->page_category, $page->app_id);
            if (strpos($url, '&Itemid=') === false && !empty($menuItem)) {
                $url .= '&Itemid='.$menuItem;
            }
            $page->catUrl = JRoute::_($url);
            $comments = self::getCommentsCount($page->id);
            $page->comments = '<span class="event-calendar-event-item-comments"><a href="'.$page->url.'#total-count-wrapper">';
            if ($comments == 0) {
                $page->comments .= JText::_('LEAVE_COMMENT');
            } else {
                $page->comments .= $comments.' '.JText::_('COMMENTS');
            }
            $page->comments .= '</a></span>';
            $reviews = self::getReviewsCount($page->id);
            if ($reviews->count == 0) {
                $reviews->rating = 0;
            }
            $page->reviews = '<div class="event-calendar-event-item-reviews"><span class="ba-blog-post-rating-stars">';
            $floorRating = floor($reviews->rating);
            for ($i = 1; $i < 6; $i++) {
                $width = 'auto';
                if ($i == $floorRating + 1) {
                    $width = (($reviews->rating - $floorRating) * 100).'%';
                }
                $page->reviews .= '<i class="zmdi zmdi-star'.($i <= $floorRating ? ' active' : '').'" style="width: '.$width.'"></i>';
            }
            $page->reviews .= '</span><a class="ba-blog-post-rating-count" href="'.$page->url.'#total-reviews-count-wrapper">';
            if ($reviews->count == 0) {
                $page->reviews .= JText::_('LEAVE_REVIEW');
            } else {
                $page->reviews .= $reviews->count.' '.JText::_('REVIEWS');
            }
            $page->reviews .= '</a></div>';
            $authors = self::getRecentPostAuthor($page->id);
            $page->authors = self::getAuthorsHtml($authors, 'event-calendar-event-item-author', $page->app_id);
            $fields = self::getCategoryListFields($page->id, $page->app_id);
            if (!empty($fields) && (empty(self::$editItem) ||
                (!empty(self::$editItem) && self::$editItem->type != 'search-result' && self::$editItem->type != 'store-search-result'))) {
                $desktopFiles = self::getDesktopFieldFiles($page->id);
                $page->fields = '<div class="ba-blog-post-fields"><div class="ba-blog-post-field-row-wrapper">';
                foreach ($fields as $field) {
                    if (!isset($field->value)) {
                        $field->value = '';
                    }
                    if (empty($field->value) || $field->value == '[]') {
                        continue;
                    }
                    $options = json_decode($field->options);
                    $label = $field->label;
                    $value = '';
                    if (empty($field->value)) {
                        $value = $field->value;
                    } else if ($field->field_type == 'select' || $field->field_type == 'radio') {
                        foreach ($options->items as $option) {
                            if ($option->key == $field->value) {
                                if (!empty($value)) {
                                    $value .= ', ';
                                }
                                $value .= $option->title;
                            }
                        }
                    } else if ($field->field_type == 'checkbox') {
                        $fieldValue = json_decode($field->value);
                        foreach ($options->items as $option) {
                            if (in_array($option->key, $fieldValue)) {
                                $value .= '<span class="ba-blog-post-field-checkbox-value">'.$option->title.'</span>';
                            }
                        }
                    } else if ($field->field_type == 'url') {
                        $fieldOptions = json_decode($field->options);
                        $valueOptions = json_decode($field->value);
                        $link = self::prepareGridboxLinks($valueOptions->link);
                        if (empty($link)) {
                            continue;
                        }
                        $value = '<a href="'.$link.'" '.$fieldOptions->download.' target="'.$fieldOptions->target;
                        $value .= '">'.$valueOptions->label.'</a>';
                    } else if ($field->field_type == 'tag') {
                        $value = self::getPostTags($page->id);
                    } else if ($field->field_type == 'time') {
                        if (!empty($field->value)) {
                            $valueOptions = json_decode($field->value);
                            $value = $valueOptions->hours.':'.$valueOptions->minutes.' '.$valueOptions->format;
                        }
                    } else if ($field->field_type == 'date' || $field->field_type == 'event-date') {
                        if (!empty($field->value)) {
                            $value = self::getPostDate($field->value);
                        }
                    } else if ($field->field_type == 'price' && !empty($field->value)) {
                        $fieldOptions = json_decode($field->options);
                        $thousand = $fieldOptions->thousand;
                        $separator = $fieldOptions->separator;
                        $decimals = $fieldOptions->decimals;
                        $value = self::preparePrice($field->value, $thousand, $separator, $decimals);
                        if ($fieldOptions->position == '') {
                            $value = $fieldOptions->symbol.$value;
                        } else {
                            $value .= $fieldOptions->symbol;
                        }
                    } else if ($field->field_type == 'file') {
                        if (!empty($field->value)) {
                            $fieldOptions = json_decode($field->options);
                            if (is_numeric($field->value) && isset($desktopFiles->{$field->value})) {
                                $desktopFile = $desktopFiles->{$field->value};
                                $src = 'components/com_gridbox/assets/uploads/app-'.$desktopFile->app_id.'/'.$desktopFile->filename;
                            } else {
                                $src = $field->value;
                            }
                            $value = '<a href="'.JUri::root().$src.'" download>'.$fieldOptions->title.'</a>';
                        }
                    } else if ($field->field_type == 'text') {
                        $value = htmlspecialchars($field->value);
                    } else {
                        $value = $field->value;
                    }
                    $page->fields .= '<div class="ba-blog-post-field-row" data-id="'.$field->field_key.
                        '"><div class="ba-blog-post-field-title">';
                    $page->fields .= $label.'</div><div class="ba-blog-post-field-value">'.$value.'</div></div>';
                }
                $page->fields .= '</div></div>';
            } else {
                $page->fields = '';
            }

            $events[] = $page;
        }

        return $events;
    }

    public static function getAppEventDates($app, $menuItem)
    {
        $db = JFactory::getDbo();
        $date = date("Y-m-d H:i:s");
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('pf.value, p.intro_image, p.title, c.title as category, p.app_id, p.id, p.page_category, p.created, p.hits')
            ->from('#__gridbox_page_fields as pf')
            ->where('pf.field_type = '.$db->quote('event-date'))
            ->where('f.app_id = '.$app)
            ->leftJoin('#__gridbox_fields AS f ON pf.field_id = f.id')
            ->leftJoin('#__gridbox_pages AS p ON pf.page_id = p.id')
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->leftJoin('`#__gridbox_categories` AS c ON p.page_category = c.id')
            ->where('c.published = 1')
            ->where('c.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('c.access in ('.$groups.')');
        $db->setQuery($query);
        $pages = $db->loadObjectList();
        $events = array();
        foreach ($pages as $page) {
            if (!isset($events[$page->value])) {
                $events[$page->value] = array();
            }
            $page->created = self::getPostDate($page->created);
            $url = self::getGridboxPageLinks($page->id, 'blog', $page->app_id, $page->page_category);
            if (strpos($url, '&Itemid=') === false && !empty($menuItem)) {
                $url .= '&Itemid='.$menuItem;
            }
            $page->url = JRoute::_($url);
            $url = self::getGridboxCategoryLinks($page->page_category, $page->app_id);
            if (strpos($url, '&Itemid=') === false && !empty($menuItem)) {
                $url .= '&Itemid='.$menuItem;
            }
            $page->catUrl = JRoute::_($url);
            $comments = self::getCommentsCount($page->id);
            $page->comments = '<span class="event-calendar-event-item-comments"><a href="'.$page->url.'#total-count-wrapper">';
            if ($comments == 0) {
                $page->comments .= JText::_('LEAVE_COMMENT');
            } else {
                $page->comments .= $comments.' '.JText::_('COMMENTS');
            }
            $page->comments .= '</a></span>';
            $reviews = self::getReviewsCount($page->id);
            if ($reviews->count == 0) {
                $reviews->rating = 0;
            }
            $page->reviews = '<div class="event-calendar-event-item-reviews"><span class="ba-blog-post-rating-stars">';
            $floorRating = floor($reviews->rating);
            for ($i = 1; $i < 6; $i++) {
                $width = 'auto';
                if ($i == $floorRating + 1) {
                    $width = (($reviews->rating - $floorRating) * 100).'%';
                }
                $page->reviews .= '<i class="zmdi zmdi-star'.($i <= $floorRating ? ' active' : '').'" style="width: '.$width.'"></i>';
            }
            $page->reviews .= '</span><a class="ba-blog-post-rating-count" href="'.$page->url.'#total-reviews-count-wrapper">';
            if ($reviews->count == 0) {
                $page->reviews .= JText::_('LEAVE_REVIEW');
            } else {
                $page->reviews .= $reviews->count.' '.JText::_('REVIEWS');
            }
            $page->reviews .= '</a></div>';
            $authors = self::getRecentPostAuthor($page->id);
            $page->authors = self::getAuthorsHtml($authors, 'event-calendar-event-item-author', $page->app_id);
            $fields = self::getCategoryListFields($page->id, $page->app_id);
            if (!empty($fields) && (empty(self::$editItem) ||
                (!empty(self::$editItem) && self::$editItem->type != 'search-result' && self::$editItem->type != 'store-search-result'))) {
                $desktopFiles = self::getDesktopFieldFiles($page->id);
                $page->fields = '<div class="ba-blog-post-fields"><div class="ba-blog-post-field-row-wrapper">';
                foreach ($fields as $field) {
                    if (!isset($field->value)) {
                        $field->value = '';
                    }
                    if (empty($field->value) || $field->value == '[]') {
                        continue;
                    }
                    $options = json_decode($field->options);
                    $label = $field->label;
                    $value = '';
                    if (empty($field->value)) {
                        $value = $field->value;
                    } else if ($field->field_type == 'select' || $field->field_type == 'radio') {
                        foreach ($options->items as $option) {
                            if ($option->key == $field->value) {
                                if (!empty($value)) {
                                    $value .= ', ';
                                }
                                $value .= $option->title;
                            }
                        }
                    } else if ($field->field_type == 'checkbox') {
                        $fieldValue = json_decode($field->value);
                        foreach ($options->items as $option) {
                            if (in_array($option->key, $fieldValue)) {
                                $value .= '<span class="ba-blog-post-field-checkbox-value">'.$option->title.'</span>';
                            }
                        }
                    } else if ($field->field_type == 'url') {
                        $fieldOptions = json_decode($field->options);
                        $valueOptions = json_decode($field->value);
                        $link = self::prepareGridboxLinks($valueOptions->link);
                        if (empty($link)) {
                            continue;
                        }
                        $value = '<a href="'.$link.'" '.$fieldOptions->download.' target="'.$fieldOptions->target;
                        $value .= '">'.$valueOptions->label.'</a>';
                    } else if ($field->field_type == 'tag') {
                        $value = self::getPostTags($page->id);
                    } else if ($field->field_type == 'time') {
                        if (!empty($field->value)) {
                            $valueOptions = json_decode($field->value);
                            $value = $valueOptions->hours.':'.$valueOptions->minutes.' '.$valueOptions->format;
                        }
                    } else if ($field->field_type == 'date' || $field->field_type == 'event-date') {
                        if (!empty($field->value)) {
                            $value = self::getPostDate($field->value);
                        }
                    } else if ($field->field_type == 'price' && !empty($field->value)) {
                        $fieldOptions = json_decode($field->options);
                        $thousand = $fieldOptions->thousand;
                        $separator = $fieldOptions->separator;
                        $decimals = $fieldOptions->decimals;
                        $value = self::preparePrice($field->value, $thousand, $separator, $decimals);
                    } else if ($field->field_type == 'file') {
                        if (!empty($field->value)) {
                            $fieldOptions = json_decode($field->options);
                            if (is_numeric($field->value) && isset($desktopFiles->{$field->value})) {
                                $desktopFile = $desktopFiles->{$field->value};
                                $src = 'components/com_gridbox/assets/uploads/app-'.$desktopFile->app_id.'/'.$desktopFile->filename;
                            } else {
                                $src = $field->value;
                            }
                            $value = '<a href="'.JUri::root().$src.'" download>'.$fieldOptions->title.'</a>';
                        }
                    } else if ($field->field_type == 'text') {
                        $value = htmlspecialchars($field->value);
                    } else {
                        $value = $field->value;
                    }
                    $page->fields .= '<div class="ba-blog-post-field-row" data-id="'.$field->field_key.
                        '"><div class="ba-blog-post-field-title">';
                    $page->fields .= $label.'</div><div class="ba-blog-post-field-value">'.$value.'</div></div>';
                }
                $page->fields .= '</div></div>';
            } else {
                $page->fields = '';
            }
            $events[$page->value][] = $page;
        }

        return $events;
    }

    public static function renderEventCalendarData($time, $app = 0, $menuItem = 0, $start = 0)
    {
        $end = $start + 6;
        $obj = new stdClass();
        $dateData = new stdClass();
        $dateData->days = array(JText::_('SUN'), JText::_('MON'), JText::_('TUE'), JText::_('WED'), JText::_('THU'),
            JText::_('FRI'), JText::_('SAT'), JText::_('SUN'));
        $dateData->month = array(JText::_('JANUARY'), JText::_('FEBRUARY'), JText::_('MARCH'), JText::_('APRIL'),
            JText::_('MAY'), JText::_('JUNE'), JText::_('JULY'), JText::_('AUGUST'), JText::_('SEPTEMBER'),
            JText::_('OCTOBER'), JText::_('NOVEMBER'), JText::_('DECEMBER'));
        $year = date('Y', $time);
        $month = date('n', $time);
        $today = date('j');
        $nowDate = date('n Y');
        $todayDate = date('n Y', $time);
        $obj->year = $year;
        $obj->month = $month - 1;
        $obj->title = $dateData->month[$month - 1].' '.$year;
        $obj->header = '';
        for ($i = $start; $i <= $end; $i++) { 
            $obj->header .= '<div class="ba-event-calendar-day-name">'.$dateData->days[$i].'</div>';
        }
        $obj->body = '';
        $firstDay = date('w', mktime(0, 0, 0, $month, 1, $year));
        if ($firstDay == 0 && $start == 1) {
            $firstDay = 7;
        }
        $daysInMonth = date('t', $time);
        $pages = self::getAppEventDates($app, $menuItem);
        $obj->eventList = $pages;
        $date = 1;
        for ($i = 0; $i < 6; $i++) {
            if ($date > $daysInMonth) {
                break;
            }
            $obj->body .= '<div class="ba-event-calendar-row">';
            for ($j = $start; $j <= $end; $j++) {
                if (($i === 0 && $j < $firstDay) || $date > $daysInMonth) {
                    $obj->body .= '<div class="ba-empty-date-cell"></div>';
                } else {
                    $obj->body .= '<div class="ba-date-cell'.($date == $today && $nowDate == $todayDate ? ' ba-curent-date' : '');
                    $eventDate = date('Y-m-d', mktime(0, 0, 0, $month, $date, $year));
                    if (isset($pages[$eventDate])) {
                        $obj->body .= ' ba-event-date';
                    }
                    $obj->body .= '" data-date="'.$eventDate.'">'.$date.'</div>';
                    $date++;
                }

            }
            $obj->body .= '</div>';
        }

        return $obj;
    }

    public static function getCommentsSocial()
    {
        $obj = new stdClass();
        if (self::$website->comments_facebook_login == 1) {
            $obj->facebook = self::$website->comments_facebook_login_key;
        }
        if (self::$website->comments_google_login == 1) {
            $obj->google = self::$website->comments_google_login_key;
        }
        if (self::$website->comments_vk_login == 1) {
            $obj->vk = self::$website->comments_vk_login_key;
        }

        return $obj;
    }

    public static function getReviewsSocial()
    {
        $obj = new stdClass();
        if (self::$website->reviews_facebook_login == 1) {
            $obj->facebook = self::$website->reviews_facebook_login_key;
        }
        if (self::$website->reviews_google_login == 1) {
            $obj->google = self::$website->reviews_google_login_key;
        }
        if (self::$website->reviews_vk_login == 1) {
            $obj->vk = self::$website->reviews_vk_login_key;
        }

        return $obj;
    }

    public static function checkDOM($html, $obj)
    {
        $obj = self::preparePresets($obj);
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        include_once JPATH_ROOT.'/components/com_gridbox/libraries/php/phpQuery/phpQuery.php';
        self::$editItem = null;
        self::setReviewsModerators();
        $app = JFactory::getApplication();
        $page = new stdClass();
        $input = $app->input;
        $page->option = $input->getCmd('option', 'option');
        $page->view = $input->getCmd('view', 'view');
        $view = $page->view;
        if ($page->option == 'com_gridbox' && $page->view == 'gridbox') {
            $page->view = 'page';
        }
        $page->id = $input->get('id', 0, 'int');
        $dom = phpQuery::newDocument($html);
        $doc = JFactory::getDocument();
        pq('.ba-video-background')->remove();
        pq('.ba-add-section')->remove();
        $search = '.ba-item-slideshow, .ba-item-content-slider, .ba-item-field-slideshow, .ba-item-product-slideshow, ';
        $search .= '.ba-item-recent-posts-slider ul.slideshow-type, .ba-item-related-posts-slider ul.slideshow-type, ';
        $search .= '.ba-item-recently-viewed-products ul.slideshow-type';
        $slideshow = pq($search);
        self::setCustomIcons();
        $doc->addStyleSheet(JUri::root().'components/com_gridbox/libraries/animation/css/animate.css');
        pq('script[data-pagespeed-no-defer]')->remove();
        if ($view == 'gridbox') {
            foreach (pq('.ba-item-field-simple-gallery, .ba-item-product-gallery')->find('.ba-instagram-image') as $key => $value) {
                $img = pq($value)->find('img');
                $image = 'components/com_gridbox/assets/images/default-theme.png';
                pq($img)->attr('src', JUri::root().$image);
                pq($value)->attr('style', 'background-image: url('.JUri::root().$image.');');
            }
        }
        foreach ($slideshow as $key => $value) {
            $doc->addStyleSheet(JUri::root().'components/com_gridbox/libraries/slideshow/css/animation.css');
            break;
        }
        foreach (pq('.ba-blog-posts-wrapper.ba-masonry-layout, .ba-categories-wrapper.ba-masonry-layout') as $key => $value) {
            $doc->addScript(JUri::root().'components/com_gridbox/libraries/modules/initMasonryBlog.js');
            break;
        }
        foreach (pq('.instagram-wrapper.simple-gallery-masonry-layout') as $key => $value) {
            $doc->addScript(JUri::root().'components/com_gridbox/libraries/modules/setGalleryMasonryHeight.js');
            break;
        }
        foreach (pq('.ba-item-flipbox') as $value) {
            if ($view == 'gridbox') {
                $doc->addStyleSheet(JUri::root().'components/com_gridbox/libraries/flipbox/css/animation-editor.css');
            } else {
                $doc->addStyleSheet(JUri::root().'components/com_gridbox/libraries/flipbox/css/animation.css');
            }
            break;
        }
        $str = '.ba-item-simple-gallery, .ba-item-image, .ba-item-overlay-section > .ba-image-wrapper';
        foreach (pq($str) as $value) {
            $doc->addStyleSheet(JUri::root().'components/com_gridbox/libraries/animation/css/image-animation.css');
            break;
        }
        if (!isset(self::$systemApps->comments)) {
            pq('.ba-item-comments-box')->remove();
        }
        foreach (pq('.ba-item-comments-box') as $value) {
            $sortBy = 'recent';
            $userStatus = self::getCommentsUserLoginHTML('comments-box');
            self::setCommentsModerators();
            $str = self::getCommentsCountHTML($page->id, $view, $sortBy);
            pq($value)->find('.ba-comments-total-count-wrapper')->html($str);
            if ($page->option == 'com_gridbox' && $view == 'page') {
                $str = self::getComments($page->id);
                pq($value)->find('.users-comments-wrapper')->html($str);
                pq($value)->find('.ba-comments-login-wrapper')->html($userStatus->str);
                include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/comments-box/comments-box-message-pattern.php');
                pq($value)->find('.ba-comment-message-wrapper')->html($string);
                pq($value)->find('.comment-reply-form-wrapper .ba-submit-comment')->attr('data-type', 'reply');
                if ($userStatus->status == 'login') {
                    pq($value)->find('.ba-submit-comment')->text(JText::_('COMMENT'));
                    $editStr = '<span class="ba-submit-comment-wrapper"><span class="ba-submit-cancel">';
                    $editStr .= JText::_('CANCEL').'</span><span class="ba-submit-comment" data-type="edit">';
                    $editStr .= JText::_('SAVE').'</span></span>';
                    pq($value)->find('.comment-edit-form-wrapper .ba-submit-comment')->replaceWith($editStr);
                } else {
                    pq($value)->find('.ba-submit-comment')->remove();
                    pq($value)->find('textarea.ba-comment-message')->attr('disabled', 'disabled');
                }
                $commentsSocial = self::getCommentsSocial();
                $str = json_encode($commentsSocial);
                $doc->addScriptDeclaration('var commentsSocial = '.$str.';');
                if (empty(self::$commentUser) || (self::$website->comments_recaptcha_guests == 1 && !empty(self::$commentUser) &&
                        (self::$commentUser->type == 'user' || self::$commentUser->type == 'social'))) {
                    pq('.ba-comments-captcha-wrapper')->remove();
                }
                $captcha = self::setCommentsCaptcha();
                if (!$captcha) {
                    pq('.ba-comments-captcha-wrapper')->remove();
                }
            } else {
                $str = self::getCommentsLogoutedUserHTML('comments-box');
                pq($value)->find('.ba-comments-login-wrapper')->html($str);
                pq($value)->find('.ba-submit-comment')->remove();
                $str = self::getDefaultComment('comments-box');
                pq($value)->find('.users-comments-wrapper')->html($str);
                include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/comments-box/comments-box-message-pattern.php');
                pq($value)->find('.ba-comment-message-wrapper')->html($string);
            }
        }
        foreach (pq('.ba-item-reviews') as $value) {
            include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/reviews/reviews-box-wrapper.php';
            pq($value)->find('.ba-comments-box-wrapper')->html($string);
            $sortBy = 'recent';
            $userStatus = self::getCommentsUserLoginHTML('reviews');
            $str = self::getReviewsCountHTML($page->id, $view, $sortBy);
            pq($value)->find('.ba-comments-total-count-wrapper')->html($str);
            include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/reviews/reviews-rate-pattern.php');
            pq($value)->find('> .ba-comments-box-wrapper > .ba-review-rate-wrapper')->html($string);
            if ($page->option == 'com_gridbox' && $view == 'page') {
                $str = self::getReviews($page->id);
                pq($value)->find('.users-comments-wrapper')->html($str);
                pq($value)->find('.ba-comments-login-wrapper')->html($userStatus->str);
                include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/reviews/reviews-message-pattern.php');
                pq($value)->find('.ba-comment-message-wrapper')->html($string);
                pq($value)->find('.comment-reply-form-wrapper .ba-submit-comment')->attr('data-type', 'reply');
                pq($value)->find('.comment-reply-form-wrapper .ba-submit-comment')->text(JText::_('COMMENT'));
                pq($value)->find('.comment-reply-form-wrapper, .ba-comment-reply-wrapper')->find('.ba-comments-attachments-wrapper')
                    ->remove();
                pq($value)->find('.comment-reply-form-wrapper, .ba-comment-reply-wrapper')->find('.ba-comment-message')
                    ->attr('placeholder', JText::_('WRITE_COMMENT_HERE'));
                if ($userStatus->status == 'login') {
                    $editStr = '<span class="ba-submit-cancel">';
                    $editStr .= JText::_('CANCEL').'</span><span class="ba-submit-comment" data-type="edit">';
                    $editStr .= JText::_('SAVE').'</span>';
                    pq($value)->find('.comment-edit-form-wrapper .ba-submit-comment-wrapper')->html($editStr);
                } else {
                    pq($value)->find('.ba-submit-comment')->remove();
                    pq($value)->find('textarea.ba-comment-message')->attr('disabled', 'disabled');
                }
                $commentsSocial = self::getReviewsSocial();
                $str = json_encode($commentsSocial);
                $doc->addScriptDeclaration('var commentsSocial = '.$str.';');
                if (empty(self::$commentUser) || (self::$website->reviews_recaptcha_guests == 1 && !empty(self::$commentUser) &&
                        (self::$commentUser->type == 'user' || self::$commentUser->type == 'social'))) {
                    pq('.ba-comments-captcha-wrapper')->remove();
                }
                $captcha = self::setReviewsCaptcha();
                if (!$captcha) {
                    pq('.ba-comments-captcha-wrapper')->remove();
                }
            } else {
                $str = self::getCommentsLogoutedUserHTML('reviews');
                pq($value)->find('.ba-comments-login-wrapper')->html($str);
                pq($value)->find('.ba-submit-comment')->remove();
                $str = self::getDefaultComment('reviews');
                pq($value)->find('.users-comments-wrapper')->html($str);
                include(JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/reviews/reviews-message-pattern.php');
                pq($value)->find('.ba-comment-message-wrapper')->html($string);
            }
        }
        if (!isset(self::$systemApps->reviews)) {
            pq('.ba-item-reviews')->remove();
        }
        foreach (pq('.ba-item-headline') as $value) {
            $id = pq($value)->attr('id');
            if (!empty($obj->{$id}->desktop->animation->effect)) {
                $doc->addStyleSheet(JUri::root().'components/com_gridbox/libraries/headline/css/animation.css');
                break;
            }
        }
        foreach (pq('.headline-wrapper > *') as $value) {
            pq($value)->removeAttr('contenteditable');
        }
        $scrollToTop = array();
        foreach (pq('.ba-item-scroll-to-top') as $value) {
            $id = pq($value)->attr('id');
            if (in_array($id, $scrollToTop)) {
                pq($value)->remove();
                continue;
            }
            $scrollToTop[] = $id;
            pq($value)->removeClass('scroll-btn-left');
            pq($value)->removeClass('scroll-btn-right');
            pq($value)->addClass('scroll-btn-'.$obj->{$id}->text->align);
        }
        $itemSocial = array();
        foreach (pq('.ba-item-social') as $value) {
            $id = pq($value)->attr('id');
            if (in_array($id, $itemSocial)) {
                pq($value)->remove();
                continue;
            }
            $itemSocial[] = $id;
        }
        foreach (pq('.ba-item-social') as $value) {
            $id = pq($value)->attr('id');
            if (!isset($obj->{$id})) {
                continue;
            }
            $keys = array('facebook', 'linkedin', 'pinterest', 'twitter', 'vk');
            $count = 0;
            foreach ($keys as $key) {
                if ($obj->{$id}->{$key}) {
                    $count++;
                }
            }
            pq($value)->attr('style', '--social-count: '.$count.';');
            pq($value)->attr('data-size', $obj->{$id}->view->size);
            pq($value)->attr('data-style', $obj->{$id}->view->style);
        }
        foreach (pq('.ba-section, .ba-row, .ba-column') as $value) {
            $id = pq($value)->attr('id');
            if (isset($obj->{$id})) {
                if (isset($obj->{$id}->preset) && !empty($obj->{$id}->preset) && isset($obj->{$id}->desktop->shape)) {
                    pq($value)->find(' > .ba-shape-divider')->remove();
                    $shape = self::getShapeObject();
                    $topKeys = array();
                    $bottomKeys = array();
                    if (!empty($obj->{$id}->desktop->shape->bottom->effect)) {
                        $bottomKeys[] = $obj->{$id}->desktop->shape->bottom->effect;
                    }
                    if (!empty($obj->{$id}->desktop->shape->top->effect)) {
                        $topKeys[] = $obj->{$id}->desktop->shape->top->effect;
                    }
                    foreach (self::$breakpoints as $key => $point) {
                        if (isset($obj->{$id}->{$key}) && isset($obj->{$id}->{$key}->shape)) {
                            if (isset($obj->{$id}->{$key}->shape->bottom) && isset($obj->{$id}->{$key}->shape->bottom->effect)) {
                                $bottomKeys[] = $obj->{$id}->{$key}->shape->bottom->effect;
                            }
                            if (isset($obj->{$id}->{$key}->shape->top) && isset($obj->{$id}->{$key}->shape->top->effect)) {
                                $topKeys[] = $obj->{$id}->{$key}->shape->top->effect;
                            }
                        }
                    }
                    if ($count = count($bottomKeys) > 0) {
                        $str = '<div class="ba-shape-divider ba-shape-divider-bottom">';
                        for ($i = 0; $i < $count; $i++) {
                            $str .= $shape[$bottomKeys[$i]] ? $shape[$bottomKeys[$i]] : '';
                        }
                        $str .= '</div>';
                        pq($value)->find('> .ba-overlay')->after($str);
                    }
                    if ($count = count($topKeys) > 0) {
                        $str = '<div class="ba-shape-divider ba-shape-divider-top">';
                        for ($i = 0; $i < $count; $i++) {
                            $str .= $shape[$topKeys[$i]] ? $shape[$topKeys[$i]] : '';
                        }
                        $str .= '</div>';
                        pq($value)->find('> .ba-overlay')->after($str);
                    }
                }
                if ($obj->{$id}->type == 'row') {
                    if ($obj->{$id}->desktop->view->gutter) {
                        pq($value)->removeClass('no-gutter-desktop');
                    } else {
                        pq($value)->addClass('no-gutter-desktop');
                    }
                } else if ($obj->{$id}->type == 'column') {
                    $parent = pq($value)->parent();
                    foreach (self::$breakpoints as $ind => $point) {
                        $name = str_replace('tablet-portrait', 'ba-tb-pt-', $ind);
                        $name = str_replace('tablet', 'ba-tb-la-', $name);
                        $name = str_replace('phone-portrait', 'ba-sm-pt-', $name);
                        $name = str_replace('phone', 'ba-sm-la-', $name);
                        for ($i = 1; $i <= 12; $i++) {
                            pq($parent)->removeClass($name.$i);
                        }
                        if (isset($obj->{$id}->{$ind}) && isset($obj->{$id}->{$ind}->span) && isset($obj->{$id}->{$ind}->span->width)) {
                            pq($parent)->addClass($name.$obj->{$id}->{$ind}->span->width);
                        }
                        $name .= 'order-';
                        for ($i = 1; $i <= 12; $i++) {
                            pq($parent)->removeClass($name.$i);
                        }
                        if (isset($obj->{$id}->{$ind}) && isset($obj->{$id}->{$ind}->span) && isset($obj->{$id}->{$ind}->span->order)) {
                            pq($parent)->addClass($name.$obj->{$id}->{$ind}->span->order);
                        }
                    }
                }
            }
        }
        foreach (pq('.ba-item-scroll-to .ba-scroll-to') as $value) {
            $id = pq($value)->parent()->attr('id');
            $icon = $obj->{$id}->icon;
            $str = '<div class="ba-button-wrapper"><a class="ba-btn-transition"><span class="empty-textnode">';
            $str .= '</span><i class="'.$obj->{$id}->icon.'"></i></a></div>';
            pq($value)->replaceWith($str);
        }
        foreach (pq('.ba-item-simple-gallery .ba-instagram-image') as $value) {
            $img = pq($value)->find('img');
            $image = pq($img)->attr('data-src');
            if (strpos($image, 'balbooa.com') === false) {
                pq($img)->attr('src', JUri::root().$image);
                pq($value)->attr('style', 'background-image: url('.JUri::root().$image.');');
            }
        }
        foreach (pq('.ba-item-logo') as $key => $value) {
            $id = pq($value)->attr('id');
            $link = $obj->{$id}->link->link;
            if (empty($link)) {
                $link = JUri::root();
            }
            $link = self::prepareGridboxLinks($link);
            pq($value)->find('.ba-logo-wrapper a')->attr('href', $link);
        }
        foreach (pq('.ba-item-image, .ba-item-icon, .ba-item-button') as $key => $value) {
            $id = pq($value)->attr('id');
            $link = $obj->{$id}->link->link;
            if (strpos($link, IMAGE_PATH) === 0) {
                $link = JUri::root().$link;
            }
            $link = self::prepareGridboxLinks($link);
            pq($value)->find('a[onclick="return false;"]')->removeAttr('onclick');
            pq($value)->find('a')->attr('href', $link);
        }
        foreach (pq('.ba-grid-column') as $key => $value) {
            $id = pq($value)->attr('id');
            if (isset($obj->{$id}->link)) {
                $link = $obj->{$id}->link->link;
                if (strpos($link, IMAGE_PATH) === 0) {
                    $link = JUri::root().$link;
                }
                $link = self::prepareGridboxLinks($link);
                pq($value)->find('> a')->attr('href', $link);
            }
        }
        foreach (pq('.ba-item-feature-box') as $value) {
            $id = pq($value)->attr('id');
            foreach (pq($value)->find('.ba-feature-box') as $key => $box) {
                if (!empty($obj->{$id}->items->{$key}->button->href)) {
                    $link = self::prepareGridboxLinks($obj->{$id}->items->{$key}->button->href);
                    pq($box)->find('.ba-feature-button a')->attr('href', $link);
                }
            }
        }
        foreach (pq('.ba-item-icon-list') as $value) {
            $id = pq($value)->attr('id');
            $childs = pq($value)->find('ul li');
            foreach ($obj->{$id}->list as $key => $listValue) {
                if (empty($listValue->link)) {
                    continue;
                }
                $link = self::prepareGridboxLinks($listValue->link);
                foreach (pq($childs) as $ind => $child) {
                    if ($ind == $key - 1) {
                        pq($child)->find('a')->attr('href', $link);
                        break;
                    }
                }
            }
        }
        pq('.ba-slideshow-dots.thumbnails-dots')->empty();
        foreach (pq('.ba-item-slideshow, .ba-item-slideset, .ba-item-carousel') as $value) {
            $id = pq($value)->attr('id');
            if (pq($value)->find('.ba-slideshow-dots')->hasClass('dots-position-outside')) {
                pq($value)->find('.ba-slideshow-dots')->removeClass('dots-position-outside');
                pq($value)->find('.slideshow-wrapper')->addClass('dots-position-outside');
            }
        }
        foreach (pq('.ba-item-slideshow, .ba-item-slideset, .ba-item-carousel') as $value) {
            $id = pq($value)->attr('id');
            $list = $obj->{$id}->desktop->slides;
            pq($value)->find('.slideshow-content')->removeAttr('style');
            pq($value)->find('.slideshow-content > li')->removeAttr('style');
            foreach (pq($value)->find('li.item') as $key => $li) {
                $btn = pq($li)->find('.slideshow-button a');
                if (isset($list->{$key + 1}->link) && !empty($list->{$key + 1}->link)) {
                    $link = $list->{$key + 1}->link;
                    $link = self::prepareGridboxLinks($link);
                    pq($btn)->attr('href', $link);
                } else {
                    $link = pq($btn)->attr('href');
                    $pos = strpos($link, '/'.IMAGE_PATH.'/');
                    if ($pos !== false) {
                        $link = substr($link, $pos + 1);
                        pq($btn)->attr('href', $link);
                    }
                }
            }
        }
        foreach (pq('.ba-item-content-slider') as $value) {
            $id = pq($value)->attr('id');
            $list = $obj->{$id}->slides;
            foreach (pq($value)->find('> .slideshow-wrapper > ul > .slideshow-content > li.item') as $key => $li) {
                if (!empty($list->{$key + 1}->link->href)) {
                    $link = $list->{$key + 1}->link->href;
                    if (strpos($link, IMAGE_PATH) === 0) {
                        $link = JUri::root().$link;
                    }
                    $link = self::prepareGridboxLinks($link);
                    pq($li)->find('> a')->attr('href', $link);
                }
            }
        }
        foreach (pq('.ba-item-video') as $value) {
            $id = pq($value)->attr('id');
            if ($view != 'gridbox' && $obj->{$id}->video->type == 'youtube'
                && isset($obj->{$id}->lazyLoad) && $obj->{$id}->lazyLoad) {
                $id = $obj->{$id}->video->id;
                $str = '<div class="video-lazy-load-thumbnail" style="background-image: url(';
                $str .= 'https://img.youtube.com/vi/'.$id.'/maxresdefault.jpg);"><i class="zmdi zmdi-play-circle"></i></div>';
                pq($value)->find('.ba-video-wrapper')->html($str);
            }
        }
        foreach (pq('.ba-item-event-calendar') as $value) {
            $id = pq($value)->attr('id');
            $eventTime = time();
            if (!$obj->{$id}->start) {
                $obj->{$id}->start = 0;
            }
            $eventData = self::renderEventCalendarData($eventTime, $obj->{$id}->app, 0, $obj->{$id}->start * 1);
            $menus = $app->getMenu('site');
            $menu = $menus->getActive();
            pq($value)->find('.ba-event-calendar-title')->html($eventData->title);
            pq($value)->find('.ba-event-calendar-header')->html($eventData->header);
            pq($value)->find('.ba-event-calendar-body')->html($eventData->body);
            pq($value)->find('.event-calendar-wrapper')->attr('data-year', $eventData->year);
            pq($value)->find('.event-calendar-wrapper')->attr('data-month', $eventData->month);
            pq($value)->find('.event-calendar-wrapper')->attr('data-menuitem', $menu->id);
        }
        foreach (pq('.ba-item-one-page-menu') as $value) {
            $itemId = pq($value)->attr('id');
            pq($value)->find('> .ba-menu-backdrop')->remove();
            pq($value)->append('<div class="ba-menu-backdrop"></div>');
            $wrapper = pq($value)->find('.ba-menu-wrapper');
            pq($wrapper)->removeClass('ba-menu-position-left');
            pq($wrapper)->removeClass('ba-hamburger-menu');
            pq($wrapper)->removeClass('ba-menu-position-center');
            if ($obj->{$itemId}->hamburger->enable) {
                pq($wrapper)->addClass('ba-hamburger-menu');
            }
            pq($wrapper)->addClass($obj->{$itemId}->hamburger->position);
        }
        foreach (pq('.ba-item-main-menu') as $value) {
            $menuId = pq($value)->attr('id');
            pq($value)->find('> .ba-menu-backdrop')->remove();
            pq($value)->append('<div class="ba-menu-backdrop"></div>');
            if (!isset($obj->{$menuId}->desktop->dropdown)) {
                $effect = 'fadeInUp';
            } else {
                $effect = $obj->{$menuId}->desktop->dropdown->animation->effect;
            }
            pq($value)->find('ul.nav-child')->addClass($effect);
            if (isset($obj->{$menuId}->items)) {
                foreach ($obj->{$menuId}->items as $key => $item) {
                    $li = pq($value)->find('li.item-'.$key.':first');
                    if (!empty($item->icon)) {
                        pq($li)->find(' > a, > span')->prepend('<i class="ba-menu-item-icon '.$item->icon.'"></i>');
                    }
                    if ($item->megamenu) {
                        pq($li)->addClass('megamenu-item');
                        pq($li)->addClass('deeper');
                        pq($li)->addClass('parent');
                        pq($li)->prepend(pq('#'.$menuId.' .ba-wrapper[data-megamenu="item-'.$key.'"]'));
                    }
                }
            }
            $i = '<i class="zmdi zmdi-caret-right"></i>';
            pq($value)->find('li.deeper.parent')->find('> a, > span')->find('> i.zmdi-caret-right')->remove();
            pq($value)->find('li.deeper.parent')->find('> a, > span')->append($i);
            $wrapper = pq($value)->find(' > .ba-menu-wrapper');
            pq($wrapper)->removeClass('ba-menu-position-left');
            pq($wrapper)->removeClass('ba-hamburger-menu');
            pq($wrapper)->removeClass('ba-menu-position-center');
            pq($wrapper)->removeClass('ba-collapse-submenu');
            if ($obj->{$menuId}->hamburger->enable) {
                pq($wrapper)->addClass('ba-hamburger-menu');
            }
            if (isset($obj->{$menuId}->hamburger->collapse) && $obj->{$menuId}->hamburger->collapse) {
                pq($wrapper)->addClass('ba-collapse-submenu');
            }
            pq($wrapper)->addClass($obj->{$menuId}->hamburger->position);
        }
        foreach (pq('.ba-item-image, .ba-item-logo, .ba-item-overlay-section') as $value) {
            $itemId = pq($value)->attr('id');
            if ($obj->{$itemId}->type == 'overlay-button') {
                $img = pq($value)->find(' > .ba-image-wrapper img');
            } else {
                $img = pq($value)->find('img');
            }
            $src = $obj->{$itemId}->image;
            if (strpos($src, 'balbooa.com') === false) {
                $img->attr('src', JUri::root().$src);
            }
        }
        foreach (pq('.ba-item-image-field') as $value) {
            $img = pq($value)->find('img');
            $src = JUri::root().'components/com_gridbox/assets/images/default-theme.png';
            $img->attr('src', $src);
        }
        $stars = pq('.ba-item-star-ratings');
        foreach ($stars as $value) {
            $id = pq($value)->attr('id');
            include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/plugins/star-ratings.php';
            pq($value)->find('> div[itemscope]')->replaceWith($out);
            list($str, $rating) = self::getStarRatings($id, $page);
            $width = ($rating - floor($rating)) * 100;
            $rating = floor($rating);
            $stars = pq($value)->find('.stars-wrapper i');
            pq($stars)->removeClass('active');
            pq($stars)->removeAttr('style');
            foreach (pq($stars) as $key => $star) {
                if ($key < $rating) {
                    pq($star)->addClass('active');
                    pq($star)->attr('style', '');
                    $last = $star;
                }
            }
            if ($rating == 0) {
                pq($stars)->addClass('active');
            }
            if ($rating != 5 && isset($last)) {
                $next = pq($last)->next();
                $next->attr('style', 'width:'.$width.'%');
            }
            pq($value)->find('.info-wrapper')->replaceWith($str);
        }
        if ($page->option == 'com_gridbox' && $view == 'gridbox') {
            foreach (pq('.ba-item-text .content-text a[href]') as $value) {
                $href = pq($value)->attr('href');
                pq($value)->attr('data-link', $href);
            }
        }
        foreach (pq('.ba-item-text .content-text a[href]') as $value) {
            $link = pq($value)->attr('href');
            if (strpos($link, IMAGE_PATH) === 0) {
                $link = JUri::root().$link;
            }
            $link = self::prepareGridboxLinks($link);
            pq($value)->attr('href', $link);
        }
        foreach (pq('.ba-item-tags') as $value) {
            $tagsApp = pq($value)->attr('data-app');
            $tagsCat = pq($value)->attr('data-category');
            $tagsLimit = pq($value)->attr('data-limit');
            $str = self::getBlogTags($tagsApp, $tagsCat, $tagsLimit);
            pq($value)->find('.ba-button-wrapper')->html($str);
        }
        foreach (pq('.ba-item-categories') as $value) {
            $id = pq($value)->attr('id');
            $catApp = pq($value)->attr('data-app');
            self::$editItem = $obj->{$id};
            $items = self::getBlogCategories($catApp);
            $str = self::getBlogCategoriesHtml($items, $obj->{$id}->maximum);
            if (!empty($obj->{$id}->layout->layout) && !pq($value)->find('.ba-categories-wrapper')->hasClass($obj->{$id}->layout->layout)) {
                pq($value)->find('.ba-categories-wrapper')->addClass($obj->{$id}->layout->layout);
            }
            pq($value)->find('.ba-categories-wrapper')->html($str);
        }
        pq('.ba-search-result-modal')->remove();
        foreach (pq('.ba-item-search, .ba-item-store-search') as $value) {
            $id = pq($value)->attr('id');
            $system = self::getSystemParamsByType($obj->{$id}->type);
            $url = 'index.php?option=com_gridbox&view=system&id='.$system->id;
            $itemId = self::getDefaultMenuItem();
            if (!empty($itemId)) {
                $url .= '&Itemid='.$itemId;
            }
            $url .= '&query=';
            $url = JRoute::_($url);
            pq($value)->find('.ba-search-wrapper > input')->attr('data-search-url', $url);
        }
        foreach (pq('.ba-item-search-result') as $value) {
            $id = pq($value)->attr('id');
            $search = $input->get('query', '', 'string');
            $search = trim($search);
            self::$editItem = $obj->{$id};
            $start = $input->get('page', 1, 'int');
            $str = self::getSearchResult($search, $obj->{$id}->limit, $start - 1, $obj->{$id}->maximum);
            if (empty($str)) {
                $str = '<p>'.JText::_('NO_MATCHING_SEARCH_RESULTS').'</p>';
            }
            pq($value)->find('.ba-blog-posts-wrapper')->html($str);
            $str = self::getSearchResultPaginator($search, $obj->{$id}->limit, $start - 1, $obj->{$id}->maximum);
            pq($value)->find('.ba-blog-posts-pagination-wrapper')->remove();
            pq($value)->find('.ba-blog-posts-wrapper')->after($str);
        }
        foreach (pq('.ba-item-store-search-result') as $value) {
            $id = pq($value)->attr('id');
            $search = $input->get('query', '', 'string');
            $search = trim($search);
            self::$editItem = $obj->{$id};
            $start = $input->get('page', 1, 'int');
            $str = self::getStoreSearchResult($search, $obj->{$id}->limit, $start - 1, $obj->{$id}->maximum);
            if (empty($str)) {
                $str = '<p>'.JText::_('NO_MATCHING_SEARCH_RESULTS').'</p>';
            }
            pq($value)->find('.ba-blog-posts-wrapper')->html($str);
            $str = self::getStoreSearchResultPaginator($search, $obj->{$id}->limit, $start - 1, $obj->{$id}->maximum);
            pq($value)->find('.ba-blog-posts-pagination-wrapper')->remove();
            pq($value)->find('.ba-blog-posts-wrapper')->after($str);
        }
        if ($view != 'gridbox') {
            foreach (pq('.ba-item-search-result-headline .search-result-headline-wrapper > *') as $value) {
                $text = pq($value)->text();
                $search = $input->get('query', '', 'string');
                $search = trim($search);
                pq($value)->text($text.' '.$search);
            }
        }
        if ($view == 'page') {
            $fields = self::getPageFieldData();
            if (!empty($fields)) {
                $desktopFiles = self::getDesktopFieldFiles();
            }
            foreach ($fields as $key => $value) {
                if (empty($value->value) || $value->value == '[]') {
                    pq('#'.$value->field_key)->remove();
                    pq('.ba-field-wrapper[data-id="'.$value->field_key.'"]')->remove();
                    continue;
                }
                if ($value->field_type == 'field-google-maps') {
                    continue;
                } else if ($value->field_type == 'radio' || $value->field_type == 'select') {
                    $str = '';
                    $fieldOptions = json_decode($value->options);
                    foreach ($fieldOptions->items as $fieldOption) {
                        if ($fieldOption->key == $value->value) {
                            $str = $fieldOption->title;
                            break;
                        }
                    }
                } else if ($value->field_type == 'checkbox') {
                    $str = '';
                    $fieldOptions = json_decode($value->options);
                    $valueOptions = json_decode($value->value);
                    foreach ($valueOptions as $valueOption) {
                        foreach ($fieldOptions->items as $fieldOption) {
                            if ($fieldOption->key == $valueOption) {
                                $str .= '<span>'.$fieldOption->title.'</span>';
                            }
                        }
                    }
                } else if ($value->field_type == 'file') {
                    $fieldOptions = json_decode($value->options);
                    if (is_numeric($value->value) && isset($desktopFiles->{$value->value})) {
                        $desktopFile = $desktopFiles->{$value->value};
                        $src = 'components/com_gridbox/assets/uploads/app-'.$desktopFile->app_id.'/'.$desktopFile->filename;
                    } else {
                        $src = $value->value;
                    }
                    $str = '<a href="'.JUri::root().$src.'" download>'.$fieldOptions->title.'</a>';
                } else if ($value->field_type == 'url') {
                    $fieldOptions = json_decode($value->options);
                    $valueOptions = json_decode($value->value);
                    $link = self::prepareGridboxLinks($valueOptions->link);
                    if (empty($link)) {
                        pq('#'.$value->field_key)->remove();
                        pq('.ba-field-wrapper[data-id="'.$value->field_key.'"]')->remove();
                        continue;
                    }
                    $str = '<a href="'.$link.'" '.$fieldOptions->download.' target="'.$fieldOptions->target;
                    $str .= '">'.$valueOptions->label.'</a>';
                } else if ($value->field_type == 'image-field') {
                    $valueOptions = json_decode($value->value);
                    $src = $valueOptions->src;
                    if (is_numeric($valueOptions->src) && isset($desktopFiles->{$valueOptions->src})) {
                        $desktopFile = $desktopFiles->{$valueOptions->src};
                        $src = 'components/com_gridbox/assets/uploads/app-'.$desktopFile->app_id.'/'.$desktopFile->filename;
                    } else if (is_numeric($valueOptions->src)) {
                        $src = '';
                    }
                    if (empty($src)) {
                        pq('#'.$value->field_key)->remove();
                        pq('.ba-field-wrapper[data-id="'.$value->field_key.'"]')->remove();
                        continue;
                    }
                    if (strpos($src, 'balbooa.com') === false) {
                        $src = JUri::root().$src;
                    }
                    $str = '<img src="'.$src.'" alt="'.$valueOptions->alt.'">';
                } else if ($value->field_type == 'tag') {
                    $str = self::getPostTags($page->id);
                } else if ($value->field_type == 'field-simple-gallery' || $value->field_type == 'product-gallery') {
                    $valueOptions = json_decode($value->value);
                    $str = '';
                    foreach ($valueOptions as $key => $valueOption) {
                        if (is_numeric($valueOption->img) && isset($desktopFiles->{$valueOption->img})) {
                            $desktopFile = $desktopFiles->{$valueOption->img};
                            $img = 'components/com_gridbox/assets/uploads/app-'.$desktopFile->app_id.'/'.$desktopFile->filename;
                        } else {
                            $img = $valueOption->img;
                        }
                        if (strpos($img, 'balbooa.com') === false) {
                            $img = JUri::root().$img;
                        }
                        $str .= '<div class="ba-instagram-image" style="background-image: url('.$img.');">';
                        $str .= '<img src="'.$img.'" data-src="'.$valueOption->img.'" alt="'.$valueOption->alt;
                        $str .= '"><div class="ba-simple-gallery-image"></div></div>';
                    }
                } else if ($value->field_type == 'field-slideshow' || $value->field_type == 'product-slideshow') {
                    $valueOptions = json_decode($value->value);
                    $str = '';
                    $slideshowStyle = '';
                    foreach ($valueOptions as $key => $valueOption) {
                        if (is_numeric($valueOption->img) && isset($desktopFiles->{$valueOption->img})) {
                            $desktopFile = $desktopFiles->{$valueOption->img};
                            $img = 'components/com_gridbox/assets/uploads/app-'.$desktopFile->app_id.'/'.$desktopFile->filename;
                        } else {
                            $img = $valueOption->img;
                        }
                        if (strpos($img, 'balbooa.com') === false) {
                            $img = JUri::root().$img;
                        }
                        $slideshowStyle .= '--thumbnails-dots-image-'.$key.': url('.$img.');';
                        $str .= '<li class="item'.($key == 0 ? ' active' : '');
                        $str .= '"><div class="ba-slideshow-img" data-src="'.$img.'" style="background-image: url('.$img.');"></div></li>';
                    }
                    pq('#'.$value->field_key.' .ba-slideshow-dots.thumbnails-dots')->attr('style', $slideshowStyle);
                    pq('.ba-field-wrapper[data-id="'.$value->field_key.'"] .ba-slideshow-dots.thumbnails-dots')
                        ->attr('style', $slideshowStyle);
                } else if ($value->field_type == 'field-video') {
                    $valueOptions = json_decode($value->value);
                    if ($valueOptions->type == 'youtube') {
                        $str = '<iframe src="https://www.youtube.com/embed/'.$valueOptions->id.'?showinfo=1&controls=1&autoplay=0"';
                        $str .= ' frameborder="0" allowfullscreen></iframe>';
                    } else if ($valueOptions->type == 'vimeo') {
                        $str = '<iframe src="https://player.vimeo.com/video/'.$valueOptions->id.'?autoplay=0&loop=0"';
                        $str .= ' frameborder="0" allowfullscreen></iframe>';
                    } else {
                        if (is_numeric($valueOptions->file) && isset($desktopFiles->{$valueOptions->file})) {
                            $desktopFile = $desktopFiles->{$valueOptions->file};
                            $img = 'components/com_gridbox/assets/uploads/app-'.$desktopFile->app_id.'/'.$desktopFile->filename;
                        } else {
                            $img = $valueOptions->file;
                        }
                        if (strpos($img, 'balbooa.com') === false) {
                            $img = JUri::root().$img;
                        }
                        $str = '<video controls><source src="'.$img.'" type="video/mp4"></video>';
                    }
                } else if ($value->field_type == 'time') {
                    $valueOptions = json_decode($value->value);
                    $str = $valueOptions->hours.':'.$valueOptions->minutes.' '.$valueOptions->format;
                } else if ($value->field_type == 'date' || $value->field_type == 'event-date') {
                    $str = self::getPostDate($value->value);
                } else if ($value->field_type == 'price') {
                    $fieldOptions = json_decode($value->options);
                    $thousand = $fieldOptions->thousand;
                    $separator = $fieldOptions->separator;
                    $decimals = $fieldOptions->decimals;
                    $price = self::preparePrice($value->value, $thousand, $separator, $decimals);
                    pq('#'.$value->field_key.' .ba-field-content .field-price-value')->text($price);
                    pq('.ba-field-wrapper[data-id="'.$value->field_key.'"] .ba-field-content .field-price-value')->text($price);
                    continue;
                } else if ($value->field_type == 'text') {
                    $str = htmlspecialchars($value->value);
                } else {
                    $str = $value->value;
                }
                pq('#'.$value->field_key.' .ba-field-content')->html($str);
                pq('.ba-field-wrapper[data-id="'.$value->field_key.'"] .ba-field-content')->html($str);
            }
            foreach (pq('.ba-item-field-group') as $value) {
                $removeFlag = true;
                foreach (pq($value)->find('.ba-field-wrapper') as $fieldW) {
                    $removeFlag = false;
                    break;
                }
                if ($removeFlag) {
                    pq($value)->remove();
                }
            }
        }
        foreach (pq('.ba-field-label') as $value) {
            $text = pq($value)->text();
            if (empty($text)) {
                pq($value)->addClass('empty-content');
            }
        }
        foreach (pq('.ba-item-recent-comments') as $value) {
            $id = pq($value)->attr('id');
            self::$editItem = $obj->{$id};
            $application = self::$editItem->app;
            $sorting = self::$editItem->sorting;
            $limit = self::$editItem->limit;
            $maximum = self::$editItem->maximum;
            $categories = self::$editItem->categories;
            $category = '';
            if (!empty($category)) {
                $cats = array();
                foreach ($category as $cat) {
                    $cats[] = $cat->id;
                }
                $category = implode(', ', $cats);
            }
            $str = self::getRecentComments($application, $sorting, $limit, $maximum, $category);
            pq($value)->find('.ba-blog-posts-wrapper')->html($str);
        }
        foreach (pq('.ba-item-recent-reviews') as $value) {
            $id = pq($value)->attr('id');
            self::$editItem = $obj->{$id};
            $application = self::$editItem->app;
            $sorting = self::$editItem->sorting;
            $limit = self::$editItem->limit;
            $maximum = self::$editItem->maximum;
            $categories = self::$editItem->categories;
            $category = '';
            if (!empty($categories)) {
                $cats = array();
                foreach ($categories as $cat) {
                    $cats[] = $cat->id;
                }
                $category = implode(', ', $cats);
            }
            $str = self::getRecentReviews($application, $sorting, $limit, $maximum, $category);
            pq($value)->find('.ba-blog-posts-wrapper')->html($str);
        }
        foreach (pq('.ba-item-recent-posts') as $value) {
            $application = pq($value)->attr('data-app');
            $sorting = pq($value)->attr('data-sorting');
            $limit = pq($value)->attr('data-count');
            $maximum = pq($value)->attr('data-maximum');
            $category = pq($value)->attr('data-category');
            $id = pq($value)->attr('id');
            if (isset($obj->{$id}->featured)) {
                $featured = $obj->{$id}->featured;
            } else {
                $featured = false;
            }
            $pagination = $obj->{$id}->layout->pagination;
            self::$editItem = $obj->{$id};
            $paginationStr = self::getRecentPostsPagination($application, $limit, $category, $featured, 0, $pagination);
            $str = self::getRecentPosts($application, $sorting, $limit, $maximum, $category, $featured);
            pq($value)->find('.ba-blog-posts-wrapper')->html($str);
            pq($value)->find('.ba-blog-posts-pagination')->remove();
            if ($paginationStr) {
                pq($value)->find('.ba-blog-posts-wrapper')->after($paginationStr);
            }
        }
        foreach (pq('.ba-item-fields-filter') as $value) {
            $id = pq($value)->attr('id');
            $app_id = $input->get('app', 0, 'input');
            $category_id = $input->get('id', 0, 'input');
            $tag_id = $input->get('tag', 0, 'input');
            $author_id = $input->get('author', 0, 'input');
            if (empty($app_id) || $obj->{$id}->app != $app_id) {
                $category_id = $tag_id = $author_id = 0;
                $app_id = $obj->{$id}->app;
            }
            if (!empty($tag_id)) {
                $url = self::getGridboxTagLinks($tag_id, $app_id);
            } else if (!empty($author_id)) {
                $url = self::getGridboxAuthorLinks($author_id, $app_id);
            } else {
                $url = self::getGridboxCategoryLinks($category_id, $app_id);
            }
            $order = $input->get('sort-by', '', 'string');
            $url = JRoute::_($url);
            $symbol = strpos($url, '?') === false ? '?' : '&';
            if (!empty($order)) {
                $url .= $symbol.'sort-by='.$order;
                $symbol = '&';
            }
            $url .= $symbol.'query=';
            pq($value)->find('.open-responsive-filters span')->text(JText::_('FILTERS'));
            pq($value)->find('.ba-fields-filter-wrapper')->attr('data-query', $url);
            pq($value)->find('.ba-fields-filter-wrapper')->attr('data-category', $category_id);
            pq($value)->find('.ba-fields-filter-wrapper')->attr('data-tag', $tag_id);
            pq($value)->find('.ba-fields-filter-wrapper')->attr('data-author', $author_id);
            $appFields = self::getAppFields($obj->{$id}->app);
            $str = self::getItemsFilter($obj->{$id}->app);
            pq($value)->find('.ba-fields-filter-wrapper')->html($str);
            if (isset($obj->{$id}->collapsible) && $obj->{$id}->collapsible) {
                $first = pq($value)->find('.ba-field-filter')->get(0);
                if (!empty($obj->{$id}->fields)) {
                    $firstOrder = pq($value)->find('.ba-field-filter[data-id="'.$obj->{$id}->fields[0].'"]')->get(0);
                    if ($firstOrder) {
                        $first = $firstOrder;
                    }
                }
                pq($value)->find('.ba-field-filter')->addClass('ba-filter-collapsed ba-filter-icon-rotated');
                pq($first)->removeClass('ba-filter-collapsed')->removeClass('ba-filter-icon-rotated');
            }
            if (isset($obj->{$id}->auto) && $obj->{$id}->auto) {
                pq($value)->find('.ba-items-filter-search-button')->remove();
            }
        }
        foreach (pq('.ba-item-author') as $key => $value) {
            $id = $input->get('id', 0, 'int');
            $str = self::getPostAuthor($id);
            pq($value)->find('.ba-posts-author-wrapper')->html($str);
        }
        foreach (pq('.ba-item-related-posts') as $value) {
            $itemId = pq($value)->attr('id');
            self::$editItem = $obj->{$itemId};
            $related = pq($value)->attr('data-related');
            $limit = pq($value)->attr('data-count');
            $maximum = pq($value)->attr('data-maximum');
            $str = self::getRelatedPosts(0, $related, $limit, $maximum);
            pq($value)->find('.ba-blog-posts-wrapper')->html($str);
        }
        foreach (pq('.ba-item-related-posts-slider') as $value) {
            $itemId = pq($value)->attr('id');
            self::$editItem = $obj->{$itemId};
            $related = self::$editItem->related;
            $limit = self::$editItem->limit;
            $maximum = self::$editItem->maximum;
            $str = self::getRelatedPosts(0, $related, $limit, $maximum);
            pq($value)->find('.slideshow-content')->html($str);
        }
        foreach (pq('.ba-item-recently-viewed-products') as $value) {
            $itemId = pq($value)->attr('id');
            self::$editItem = $obj->{$itemId};
            $limit = self::$editItem->limit;
            $maximum = self::$editItem->maximum;
            $str = self::getRecentlyViewedProducts($limit, $maximum);
            pq($value)->find('.slideshow-content')->html($str);
        }
        foreach (pq('.ba-item-post-navigation') as $value) {
            $itemId = pq($value)->attr('id');
            self::$editItem = $obj->{$itemId};
            $maximum = pq($value)->attr('data-maximum');
            $str = self::getPostNavigation($maximum);
            pq($value)->find('.ba-blog-posts-wrapper')->html($str);
            pq($value)->find('.ba-blog-posts-wrapper > i')->remove();
            $posts = pq($value)->find('.ba-blog-posts-wrapper .ba-blog-post');
            foreach (pq($posts) as $key => $post) {
                if ($key == 0) {
                    $title = JText::_('PREVIOUS');
                } else {
                    $title = JText::_('NEXT');
                }
                $href = pq($post)->find('.ba-blog-post-title-wrapper .ba-blog-post-title a')->attr('href');
                $str = '<div class="ba-post-navigation-info"><a href="'.$href.'">'.$title.'</a></div>';
                pq($post)->find('.ba-blog-post-title-wrapper')->before($str);
            }
        }
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        foreach (pq('.ba-edit-item') as $value) {
            $parent = pq($value)->parent();
            $itemId = pq($parent)->attr('id');
            if (isset($obj->{$itemId}) && isset($obj->{$itemId}->access) && !in_array($obj->{$itemId}->access, $groups)) {
                pq($parent)->addClass('ba-user-level-edit-denied');
            } else {
                pq($parent)->removeClass('ba-user-level-edit-denied');
            }
            pq($value)->attr('style', '');
        }
        foreach (pq('.ba-item-blog-posts') as $value) {
            $flag = false;
            foreach (pq('.ba-item-category-intro') as $intro) {
                $flag = true;
            }
            if (!$flag){
                include(JPATH_ROOT.'/components/com_gridbox/views/layout/category-intro.php');
                $str = $out;
                pq('.ba-item-blog-posts')->before($str);
                $app = $input->getCmd('id', 0);
                if ($view != 'gridbox') {
                    $app = $input->getCmd('app', 0);
                    pq('.ba-edit-item, .ba-box-model')->remove();
                }
                $file = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/app-'.$app.'.css';
                if (JFile::exists($file)) {
                    JFile::delete($file);
                }
            }
        }
        foreach (pq('.ba-item-category-intro') as $key => $intro) {
            $tag = $input->get('tag', '');
            $author = $input->get('author', '');
            $postContent = self::getCategoryIntro();
            $postHTML = pq($intro)->find('.intro-post-wrapper')->html();
            if (trim($postHTML) == '') {
                $postHTML = '[intro-post-image]'.
                    '<div class="intro-post-title-wrapper"><h1 class="intro-post-title"></h1></div>'.
                    '<div class="intro-post-info"><div class="intro-category-description"></div></div>';
            }
            $postHTML = str_replace('[intro-post-image]', $postContent->image, $postHTML);
            pq($intro)->find('.intro-post-wrapper')->html($postHTML);
            pq($intro)->find('.intro-post-wrapper .intro-post-image-wrapper')->replaceWith($postContent->image);
            pq($intro)->find('.intro-post-wrapper .intro-category-description')->html($postContent->description);
            if (isset($postContent->social)) {
                pq($intro)->find('.intro-post-wrapper .intro-post-info')->after($postContent->social);
            }
            pq($intro)->find('.intro-post-wrapper .intro-post-title')->html($postContent->title);
        }
        foreach (pq('.ba-item-post-intro') as $value) {
            $postContent = self::getBlogPostIntro();
            pq($value)->find('.intro-post-wrapper .intro-post-info')->html($postContent->info);
            pq($value)->find('.intro-post-wrapper .intro-post-title')->text($postContent->title);
            pq($value)->find('.intro-post-wrapper .intro-post-title')->removeAttr('contenteditable');
            $postHTML = pq($value)->find('.intro-post-wrapper')->html();
            $postHTML = str_replace('[intro-post-image]', $postContent->image, $postHTML);
            pq($value)->find('.intro-post-wrapper')->html($postHTML);
        }
        pq('.ba-item-instagram')->remove();
        foreach (pq('.ba-item-weather') as $value) {
            $openWeatherMapKey = self::getOpenWeatherKey();
            break;
        }
        foreach (pq('.ba-item-weather') as $key => $value) {
            if (empty($openWeatherMapKey)) {
                break;
            }
            $id = pq($value)->attr('id');
            $item = $obj->{$id};
            if (empty($item->weather->location)) {
                continue;
            }
            $weather = self::getWeather($item, $id, $openWeatherMapKey);
            if ($weather) {
                pq($value)->find('.ba-weather')->html($weather);
            }
        }
        foreach (pq('.ba-item-error-message') as $value) {
            $code = '{gridbox_error_code}';
            $message = '{gridbox_error_message}';
            if ($view == 'gridbox') {
                $code = '404';
                $message = JText::_('NOT_FOUND');
            }
            pq($value)->find('.ba-error-code')->text($code);
            pq($value)->find('.ba-error-message')->text($message);
        }
        foreach (pq('.ba-item-recent-posts-slider') as $value) {
            $id = pq($value)->attr('id');
            $application = $obj->{$id}->app;
            $sorting = $obj->{$id}->sorting;
            $limit = $obj->{$id}->limit;
            $maximum = $obj->{$id}->maximum;
            $categories = $obj->{$id}->categories;
            if (isset($obj->{$id}->featured)) {
                $featured = $obj->{$id}->featured;
            } else {
                $featured = false;
            }
            $array = array();
            foreach ($categories as $catId => $cat) {
                $array[] = $catId;
            }
            $category = implode(',', $array);
            self::$editItem = $obj->{$id};
            $str = self::getRecentPosts($application, $sorting, $limit, $maximum, $category, $featured);
            pq($value)->find('.slideshow-content')->html($str);
            foreach (pq($value)->find('li.item') as $key => $postLi) {
                if ($key == $obj->{$id}->desktop->slideset->count) {
                    break;
                }
                pq($postLi)->addClass('active');
            }
        }
        foreach (pq('.ba-item-blog-posts') as $value) {
            $itemId = pq($value)->attr('id');
            self::$editItem = $obj->{$itemId};
            $id = $input->get('id', 0, 'int');
            $category = $input->get('category', 0, 'int');
            $application = $input->get('app', 0, 'int');
            if (!empty($application)) {
                $category = $id;
                $id = $application;
            }
            $start = $input->get('page', 1, 'int');
            $max = $obj->{$itemId}->maximum;
            $limit = $obj->{$itemId}->limit;
            $order = isset($obj->{$itemId}->order) ? $obj->{$itemId}->order : 'created';
            $isStore = self::$storeHelper->checkAppType($id);
            if ($isStore) {
                $order = $input->get('sort-by', $order, 'string');
            }
            $str = self::getBlogPosts($id, $max, $limit, $start - 1, $category, $order);
            if (empty($str)) {
                $str = self::getEmptyList();
            }
            pq($value)->find('.ba-blog-posts-wrapper')->html($str);
            $str = self::getBlogPostsHeader($isStore, $id, $category, $order);
            pq($value)->find('.ba-blog-posts-header')->html($str);
            $str = self::getBlogPagination($id, $start - 1, $limit, $category);
            pq($value)->find('.ba-blog-posts-pagination-wrapper')->html($str);
        }
        foreach (pq('.ba-item-google-maps-places') as $value) {
            $menus = $app->getMenu('site');
            $menu = $menus->getActive();
            $itemId = pq($value)->attr('id');
            $pages = self::getMapsPlacesPostsList($obj->{$itemId}->app);
            pq($value)->find('.ba-map-wrapper')->attr('data-menuitem', $menu->id);
            pq($value)->find('.ba-map-wrapper')->attr('data-pages', $pages);
        }
        foreach (pq('.ba-item-add-to-cart') as $value) {
            pq($value)->find('.ba-add-to-cart-price-currency')->text(self::$store->currency->symbol);
            pq($value)->find('.ba-add-to-cart-price-wrapper')->removeClass('right-currency-position');
            pq($value)->find('.ba-add-to-cart-sale-price-wrapper')->removeClass('right-currency-position');
            pq($value)->find('.ba-add-to-cart-price-wrapper')->addClass(self::$store->currency->position);
            pq($value)->find('.ba-add-to-cart-sale-price-wrapper')->addClass(self::$store->currency->position);
            pq($value)->find('.ba-add-to-wishlist .ba-tooltip')->text(JText::_('ADD_TO_WISHLIST'));
            pq($value)->find('.ba-add-to-cart-sku .ba-add-to-cart-row-label')->text(JText::_('SKU'));
            pq($value)->find('.ba-add-to-cart-stock .ba-add-to-cart-row-label')->text(JText::_('IN_STOCK'));
            if ($view != 'gridbox') {
                $data = self::getProductData($page->id);
                $extra_options = $data->extra_options;
                $variationsMap = self::getProductVariationsMap($page->id);
                $variations = self::getProductVariations($data->variations, $variationsMap);
                $variationImages = new stdClass();
                foreach ($variationsMap as $variation) {
                    $variationImages->{$variation->option_key} = json_decode($variation->images);
                }
                $enabledVariation = null;
                $images = array();
                $get = $input->get->getArray(array());
                if (!empty($get)) {
                    foreach ($variations as $ind => $variation) {
                        $flag = true;
                        foreach ($variation->urls as $key => $url) {
                            $key = urldecode($key);
                            $url = urldecode($url);
                            if (!isset($get[$key]) || $get[$key] != $url) {
                                $flag = false;
                                break;
                            }
                        }
                        if ($flag) {
                            $data = $variation;
                            $enabledVariation = $variation;
                            $vars = explode('+', $ind);
                            foreach ($vars as $var) {
                                if (!empty($variationImages->{$var})) {
                                    $images = $variationImages->{$var};
                                }
                            }
                        }
                    }
                }
                $str = self::getProductVariationsHTML($variationsMap, $enabledVariation);
                $extra = self::getExtraOptionsHTML($extra_options);
                pq($value)->find('.ba-add-to-cart-sku .ba-add-to-cart-row-value')->text($data->sku);
                $stock = $data->stock;
                if ($stock !== '' && $stock == 0) {
                    $stock = JText::_('OUT_OF_STOCK');
                }
                pq($value)->find('.ba-add-to-cart-stock .ba-add-to-cart-row-value')->text($stock);
                $data->price += $extra->price;
                if ($data->sale_price != '') {
                    $data->sale_price += $extra->price;
                }
                $prices = self::prepareProductPrices($page->id, $data->price, $data->sale_price);
                pq($value)->find('.ba-add-to-cart-price-wrapper .ba-add-to-cart-price-value')->text($prices->regular);
                if ($prices->sale === '') {
                    pq($value)->find('.ba-add-to-cart-sale-price-wrapper')->remove();
                } else {
                    pq($value)->find('.ba-add-to-cart-sale-price-wrapper .ba-add-to-cart-price-value')->text($prices->sale);
                }
                pq($value)->find('.ba-add-to-cart-variations')->html($str);
                pq($value)->find('.ba-add-to-cart-variations')->after($extra->html);
                if (!empty($variationsMap) || $data->stock == 0) {
                    pq($value)->find('.ba-add-to-cart-quantity input')->val(1);
                }
                if (isset($data->product_type) && $data->product_type == 'digital') {
                    pq($value)->find('.ba-add-to-cart-quantity')->remove();
                }
                if ($data->stock !== '' && $data->stock == 0) {
                    pq($value)->find('.ba-add-to-cart-buttons-wrapper a')->text(JText::_('OUT_OF_STOCK'));
                    pq($value)->find('.ba-add-to-cart-button-wrapper')->addClass('disabled');
                } else if ((!empty($variationsMap) && !$enabledVariation) || $extra->required) {
                    pq($value)->find('.ba-add-to-cart-buttons-wrapper a')->text(JText::_('SELECT_AN_OPTION'));
                }
                if ($enabledVariation && !empty($images)) {
                    $galleryImages = $slideshowImages = $slideshowDots = '';
                    foreach ($images as $i => $image) {
                        if (strpos($image, 'balbooa.com') === false) {
                            $image = JUri::root().$image;
                        }
                        $galleryImages .= '<div class="ba-instagram-image" style="background-image: url('.$image.');">';
                        $galleryImages .= '<img data-src="'.$image.'" alt="" class="" src="'.$image.'">';
                        $galleryImages .= '<div class="ba-simple-gallery-image"></div></div>';
                        $slideshowImages .= '<li class="item"><div class="ba-slideshow-img" style="background-image: url(';
                        $slideshowImages .= $image.');" data-src="'.$image.'"></div></li>';
                        $slideshowDots .= '--thumbnails-dots-image-'.$i.': url('.$image.');';
                    }
                    foreach (pq('.ba-item-product-gallery') as $gallery) {
                        $original = array();
                        foreach (pq($gallery)->find('.ba-instagram-image img') as $img) {
                            $image = pq($img)->attr('data-src');
                            if (strpos($image, 'balbooa.com') === false) {
                                $image = JUri::root().$image;
                            }
                            $original[] = $image;
                        }
                        $str = json_encode($original);
                        pq($gallery)->find('.instagram-wrapper')->attr('data-original', $str);
                        pq($gallery)->find('.instagram-wrapper')->attr('data-variation', $enabledVariation->variation);
                        pq($gallery)->find('.instagram-wrapper')->html($galleryImages);
                    }
                    foreach (pq('.ba-item-product-slideshow') as $slideshow) {
                        $original = array();
                        foreach (pq($slideshow)->find('li.item .ba-slideshow-img') as $img) {
                            $original[] = 'url('.pq($img)->attr('data-src').')';
                        }
                        $str = json_encode($original);
                        pq($slideshow)->find('ul.ba-slideshow')->attr('data-original', $str);
                        pq($slideshow)->find('ul.ba-slideshow')->attr('data-variation', $enabledVariation->variation);
                        pq($slideshow)->find('.slideshow-content')->html($slideshowImages);
                        pq($slideshow)->find('.ba-slideshow-dots')->attr('style', $slideshowDots);
                    }
                }
            }
        }
        foreach (pq('.ba-item-wishlist') as $value) {
            if ($view != 'gridbox') {
                $wishId = self::getWishlistId();
                $wishlist = self::getStoreWishlist($wishId);
                pq($value)->find('a i')->attr('data-products-count', $wishlist->quantity);
            }
        }
        $cartId = $input->cookie->get('gridbox_store_cart', 0, 'int');
        $cart = null;
        foreach (pq('.ba-item-cart') as $value) {
            pq($value)->find('.store-currency-symbol')->text(self::$store->currency->symbol);
            pq($value)->find('.ba-cart-subtotal')->removeClass('right-currency-position');
            pq($value)->find('.ba-cart-subtotal')->addClass(self::$store->currency->position);
            $url = self::getStoreSystemUrl('checkout');
            pq($valid)->find('a')->attr('data-url', $url);
            if ($view != 'gridbox') {
                $cart = !empty($cart) ? $cart : self::getStoreCart($cartId);
                pq($value)->find('a i')->attr('data-products-count', $cart->quantity);
                $currency = self::$store->currency;
                $total = gridboxHelper::preparePrice($cart->total, $currency->thousand, $currency->separator, $currency->decimals);
                pq($value)->find('.store-currency-price')->text($total);
            }
        }
        foreach (pq('.ba-item-checkout-order-form') as $value) {
            $cart = self::getStoreCart($cartId);
            if ($view == 'gridbox') {
                self::prepareCartForEditor($cart);
            }
            include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/store/checkout-order.php';
            pq($value)->find('.ba-checkout-order-form-wrapper')->html($out);
        }
        foreach (pq('.ba-item-checkout-form') as $value) {
            $html = self::getCustomerInfoHTML($cart);
            pq($value)->find('.ba-checkout-form-wrapper')->html($html);
        }
        $str = $dom->htmlOuter();
        
        return $str;
    }

    public static function getStoreSystemUrl($type)
    {
        $app = JFactory::getApplication();
        $system = self::getSystemParamsByType($type);
        $menus = JFactory::getApplication()->getMenu('site');
        $menu = $menus->getDefault();
        $url = 'index.php?option=com_gridbox&view=system&id='.$system->id.'&Itemid='.$menu->id;
        $router = $app::getRouter();
        $uri = $router->build($url);
        $uri2 = JUri::getInstance();
        $host_port = array($uri2->getHost(), $uri2->getPort());
        $scheme = array('path', 'query', 'fragment');
        $uri->setScheme(($uri->isSsl()) ? 'https' : 'http');
        $uri->setHost($host_port[0]);
        $uri->setPort($host_port[1]);
        $scheme = array_merge($scheme, array('host', 'port', 'scheme'));
        $url = $uri->toString($scheme);
        $url = preg_replace('/\s/u', '%20', $url);
        $url = htmlspecialchars($url, ENT_COMPAT, 'UTF-8');

        return $url;
    }

    public static function getDefaultMenuItem()
    {
        $id = 0;
        $menus = JFactory::getApplication()->getMenu('site');
        $menu = $menus->getDefault();
        if ($menu && $menu->component == 'com_gridbox') {
            $id = $menu->id;
        }

        return $id;
    }

    public static function getPublishedPromoCode()
    {
        $db = JFactory::getDBO();
        $query = gridboxHelper::getPromoCodeQuery()
            ->select('COUNT(p.id)');
        $db->setQuery($query);
        $count = $db->loadResult();

        return $count > 0;
    }

    public static function prepareCartForEditor($cart)
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
        $product->data->single =  new stdClass();
        $product->data->single->price = $productData->price;
        $product->data->single->sale = $productData->sale_price;
        $product->prices = new stdClass();
        $product->prices->sale_price = '';
        $product->prices->regular = gridboxHelper::preparePrice(36.99, $currency->thousand, $currency->separator, $currency->decimals);
        $product->variations = array();
        $product->link = JUri::root();
        $product->extra_options = new stdClass();
        $product->extra_options->items = new stdClass();
        $product->extra_options->count = 0;
        $cart->products = array($product);
        $cart->total = $cart->subtotal = $product->data->price;
        $cart->discount = 0;
        $cart->validPromo = false;
        $cart->quantity = 1;
        if (!empty($cart->tax)) {
            $cart->taxes = new stdClass();
            $cart->taxes->count = 0;
        }
    }

    public static function getStoreCheckoutProductsHTML($cart)
    {
        $html = '';
        $currency = self::$store->currency;
        foreach ($cart->products as $product) {
            $image = !empty($product->images) ? $product->images[0] : $product->intro_image;
            if (!empty($image) && strpos($image, 'balbooa.com') === false) {
                $image = JUri::root().$image;
            }
            $price = $product->prices->sale_price !== '' ? $product->prices->sale : $product->prices->regular;
            $info = array();
            foreach ($product->variations as $variation) {
                $info[] = '<span>'.$variation->title.' '.$variation->value.'</span>';
            }
            $infoStr = implode('/', $info);
            include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/store/order-products-row.php';
            $html .= $out;
        }

        return $html;
    }

    public static function getStorePaymentsHTML($cart)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_payment_methods')
            ->where('published = 1')
            ->order('order_list ASC');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $html = '';
        $query = $db->getQuery(true)
            ->select('payment_id')
            ->from('#__gridbox_store_orders_payment')
            ->where('cart_id = '.$cart->id);
        $db->setQuery($query);
        $payment_id = $db->loadResult();
        $count = count($items);
        foreach ($items as $item) {
            $item->default = $item->id == $payment_id;
            $settings = json_decode($item->settings);
            include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/store/order-payment-row.php';
            $html .= $out;
        }

        return $html;
    }

    public static function getStoreShippingTax($cart, $country = true, $region = true)
    {
        $obj = null;
        foreach (self::$store->tax->rates as $key => $rate) {
            $count = $country ? $rate->country_id == $cart->country : true;
            $reg = $region ? self::getTaxRegion($rate->regions, $cart->region) : true;
            if ($rate->shipping && $count && $reg) {
                $obj = new stdClass();
                $obj->key = $key;
                $obj->title = $rate->title;
                $obj->rate = $rate->rate;
                $obj->amount = $rate->rate / 100;
                break;
            }
        }
        if (!$obj && $country && $region) {
            $obj = self::getStoreShippingTax($cart, true, false);
        } else if (!$obj && $country && !$region) {
            $obj = self::getStoreShippingTax($cart, false, false);
        }

        return $obj;
    }

    public static function getStoreShippingItem($item, $total, $tax, $cart)
    {
        $mode = self::$store->tax->mode;
        $item->params = json_decode($item->options);
        $type = $item->params->type;
        $object = isset($item->params->{$type}) ? $item->params->{$type} : null;
        if ($type == 'free' || $type == 'pickup') {
            $item->price = 0;
        } else if ($type == 'flat') {
            $item->price = $object->price;
        } else if ($type == 'weight-unit') {
            $weight = 0;
            foreach ($cart->products as $product) {
                if (!empty($product->dimensions->weight)) {
                    $weight += $product->dimensions->weight * $product->quantity;
                }
            }
            $item->price = $weight * $object->price;
        } else if ($type == 'product') {
            $item->price = $cart->quantity * $item->params->product->price;
        } else if ($type == 'prices' || $type == 'weight') {
            $range = array();
            $unlimited = null;
            foreach ($object->range as $value) {
                if ($value->rate === '') {
                    $unlimited = $value;
                } else {
                    $value->rate *= 1;
                    $range[] = $value;
                }
            }
            usort($range, function($a, $b){
                if ($a->rate == $b->rate) {
                    return 0;
                }

                return ($a->rate < $b->rate) ? -1 : 1;
            });
            $price = null;
            if ($type == 'weight') {
                $netValue = 0;
                foreach ($cart->products as $product) {
                    if (!empty($product->dimensions->weight)) {
                        $netValue += $product->dimensions->weight * $product->quantity;
                    }
                }
            } else {
                $netValue = $cart->total;
            }
            foreach ($range as $value) {
                if ($netValue <= $value->rate) {
                    $price = $value;
                    break;
                }
            }
            if ($price === null && $unlimited) {
                $price = $unlimited;
            }
            if ($price) {
                $item->price = $price->price;
            } else {
                $item->price = 0;
            }
        } else if ($type == 'category') {
            $item->price = 0;
            foreach ($cart->products as $product) {
                $categories = self::getCategoryId($product->product_id);
                $price = null;
                foreach ($item->params->category->range as $range) {
                    foreach ($range->rate as $id) {
                        if (in_array($id, $categories)) {
                            $price = $range->price;
                            break;
                        }
                    }
                    if ($price !== null) {
                        break;
                    }
                }
                if ($price !== null) {
                    $item->price += $price * $product->quantity;
                    continue;
                }
            }
        }
        if ($object && isset($object->enabled) && $object->enabled && $total > $object->free * 1) {
            $item->price = 0;
        }
        $amount = $tax ? $tax->amount : 0;
        $item->tax = $mode == 'excl' ? $item->price * $amount : $item->price - $item->price / ($amount + 1);
        $item->total = $total + $item->price + ($mode == 'excl' ? $item->tax : 0);

        return $item;
    }

    public static function getStoreShippingItems($cart)
    {
        $digital = true;
        foreach ($cart->products as $product) {
            if (!isset($product->data->product_type) || $product->data->product_type != 'digital') {
                $digital = false;
                break;
            }
        }
        $shipping = array();
        if (!$digital) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_shipping')
                ->where('published = 1')
                ->order('order_list ASC');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            $query = $db->getQuery(true)
                ->select('shipping_id')
                ->from('#__gridbox_store_orders_shipping')
                ->where('cart_id = '.$cart->id);
            $db->setQuery($query);
            $shipping_id = $db->loadResult();
            $total = $cart->total + (self::$store->tax->mode == 'excl' ? $cart->tax : 0);
            $mode = self::$store->tax->mode;
            $tax = self::getStoreShippingTax($cart);
            self::$storeHelper->checkShippingOptions($items);
            foreach ($items as $item) {
                $item->default = $item->id == $shipping_id;
                $item = self::getStoreShippingItem($item, $total, $tax, $cart);
                $available = true;
                $object = $item->params->regions->available;
                $countries = self::getTaxCountries(true);
                $vars = get_object_vars($object);
                $count = count($vars);
                if ($count != 0 && (empty($cart->country) || !isset($object->{$cart->country}) || !isset($countries->{$cart->country}))) {
                    $available = false;
                } else if ($count != 0) {
                    $regions = $object->{$cart->country};
                    if (count($countries->{$cart->country}->states) != 0 &&
                        (empty($cart->region) || !isset($regions->{$cart->region}) || !$regions->{$cart->region})) {
                        $available = false;
                    }
                }
                $object = $item->params->regions->restricted;
                $vars = get_object_vars($object);
                $count = count($vars);
                if ($count != 0 && !empty($cart->country) && isset($object->{$cart->country})
                    && count($countries->{$cart->country}->states) == 0) {
                    $available = false;
                } else if ($count != 0 && !empty($cart->country) && isset($object->{$cart->country})) {
                    $regions = $object->{$cart->country};
                    if (!empty($cart->region) && isset($regions->{$cart->region}) && $regions->{$cart->region}) {
                        $available = false;
                    }
                }
                if ($available) {
                    $shipping[] = $item;
                }
            }
        }

        return $shipping;
    }

    public static function getStoreShippingHTML($cart, $items)
    {
        $html = '';
        $currency = self::$store->currency;
        $count = count($items);
        foreach ($items as $item) {
            $price = self::preparePrice($item->price, $currency->thousand, $currency->separator, $currency->decimals);
            $taxPrice = self::preparePrice($item->tax, $currency->thousand, $currency->separator, $currency->decimals);
            $total = self::preparePrice($item->total, $currency->thousand, $currency->separator, $currency->decimals);
            $totalTax = self::preparePrice($cart->tax + $item->tax, $currency->thousand, $currency->separator, $currency->decimals);
            include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/store/order-shipping-row.php';
            $html .= $out;
        }

        return $html;
    }

    public static function checkProductCategory($id, $array)
    {
        $flag = in_array($id, $array);
        if (!$flag && !empty($id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('parent')
                ->from('#__gridbox_categories')
                ->where('id = '.$id);
            $db->setQuery($query);
            $category = $db->loadResult();
            $flag = self::checkProductCategory($category, $array);
        }

        return $flag;
    }

    public static function prepareProductPrices($id, $price, $sale_price)
    {
        $currency = self::$store->currency;
        $sales = self::$store->sales;
        if (!isset($sales->products)) {
            $sales->products = new stdClass();
        }
        $prices = new stdClass();
        $prices->price = $price;
        $prices->regular = self::preparePrice($price, $currency->thousand, $currency->separator, $currency->decimals);
        $prices->sale = '';
        $prices->sale_price = $sale_price;
        if ($sale_price === '' && !empty($sales->amount)) {
            if (!isset($sales->products->{$id})) {
                $config = JFactory::getConfig();
                $offset = $config->get('offset');
                date_default_timezone_set($offset);
                $date = date('Y-m-d H:i:s');
                $publish_up = $sales->publish_up == '' || $date >= $sales->publish_up;
                $publish_down = $sales->publish_down == '' || $date <= $sales->publish_down;
                if ($sales->applies_to != '*') {
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true)
                        ->select('page_category')
                        ->from('#__gridbox_pages')
                        ->where('id = '.$id);
                    $db->setQuery($query);
                    $category = $db->loadResult();
                    $applies = self::checkProductCategory($category, self::$store->sales->map);
                }
                $sales->products->{$id} = $publish_up && $publish_down && ($sales->applies_to == '*' || $applies);
            }
            if ($sales->products->{$id}) {
                $sale_price = $price - $price * ($sales->amount / 100);
            }
        }
        if ($sale_price !== '') {
            $prices->sale_price = $sale_price;
            $prices->sale = self::preparePrice($sale_price, $currency->thousand, $currency->separator, $currency->decimals);
        }

        return $prices;
    }

    public static function getExtraOptionsHTML($options)
    {
        $extra = new stdClass();
        $extra->required = false;
        $extra->price = 0;
        include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/store/add-to-cart-extra-options.php';
        $extra->html = $out;
        
        return $extra;
    }

    public static function getProductVariationsHTML($variations_map, $enabled)
    {
        $variations = new stdClass();
        $str = '';
        $active = array();
        if ($enabled) {
            $active = explode('+', $enabled->variation);
        }
        foreach ($variations_map as $variation) {
            if (!isset($variations->{$variation->field_id})) {
                $variations->{$variation->field_id} = new stdClass();
                $variations->{$variation->field_id}->title = $variation->title;
                $variations->{$variation->field_id}->type = $variation->field_type;
                $variations->{$variation->field_id}->items = array();
            }
            $variations->{$variation->field_id}->items[] = $variation;
        }
        foreach ($variations as $variation) {
            usort($variation->items, function($a, $b){
                return ($a->order_list < $b->order_list) ? -1 : 1;
            });
            include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/store/add-to-cart-variations.php';
            $str .= $out;
        }

        return $str;
    }

    public static function getProductVariationsMap($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('vm.*, fd.value, fd.color, fd.image, f.title, f.field_type, f.field_key')
            ->from('#__gridbox_store_product_variations_map AS vm')
            ->where('vm.product_id = '.$id)
            ->order('vm.order_group ASC, vm.order_list ASC')
            ->leftJoin('#__gridbox_store_products_fields_data AS fd ON fd.option_key = vm.option_key')
            ->leftJoin('#__gridbox_store_products_fields AS f ON f.id = vm.field_id');
        $db->setQuery($query);
        $variations_map = $db->loadObjectList();
        
        return $variations_map;
    }

    public static function getProductExtraOptions($options)
    {
        $options = !empty($options) ? $options : '{}';
        $options = json_decode($options);
        $db = JFactory::getDbo();
        $extra_options = new stdClass();
        foreach ($options as $id => $option) {
            $query =  $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_products_fields')
                ->where('id = '.$option->id);
            $db->setQuery($query);
            $field = $db->loadObject();
            if (!$field) {
                continue;
            }
            $obj = new stdClass();
            $obj->title = $field->title;
            $obj->type = $field->field_type;
            $obj->required = $field->required;
            $obj->items = new stdClass();
            $items = json_decode($field->options);
            foreach ($items as $key => $item) {
                if (isset($option->items->{$item->key})) {
                    $option->items->{$item->key}->title = $item->title;
                    $item->price = $option->items->{$item->key}->price;
                    $item->default = $option->items->{$item->key}->default;
                    $obj->items->{$item->key} = $item;
                }
            }
            $extra_options->{$field->id} = $obj;
        }

        return $extra_options;
    }

    public static function getProductData($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('d.*, p.intro_image AS image, p.title')
            ->from('#__gridbox_store_product_data AS d')
            ->where('d.product_id = '.$id)
            ->leftJoin('#__gridbox_pages AS p ON d.product_id = p.id');
        $db->setQuery($query);
        $data = $db->loadObject();
        $data->variations = json_decode($data->variations);
        $data->extra_options = self::getProductExtraOptions($data->extra_options);
        $data->dimensions = !empty($data->dimensions) ? json_decode($data->dimensions) : new stdClass();

        return $data;
    }

    public static function getProductBadges($id, $data)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('b.*')
            ->from('#__gridbox_store_badges_map AS bm')
            ->where('bm.product_id = '.$id)
            ->order('bm.order_list ASC')
            ->leftJoin('#__gridbox_store_badges AS b ON b.id = bm.badge_id');
        $db->setQuery($query);
        $badges = $db->loadObjectList();
        foreach ($badges as $badge) {
            if ($badge->type == 'sale') {
                $price = $data->price;
                $sale = $data->sale_price;
                $badge->title = '- '.($price == 0 ? 0 : round(100 - (($sale === '' ? $price : $sale) * 100 / $price))).'%';
            }
        }
        
        return $badges;
    }

    public static function getProductVariations($variations, $variationsMap = array())
    {
        $variationsURL = new stdClass();
        foreach ($variationsMap as $variation) {
            $variationsURL->{$variation->option_key} = new stdClass();
            $variationsURL->{$variation->option_key}->key = urlencode($variation->title);
            $variationsURL->{$variation->option_key}->value = urlencode($variation->value);
        }
        foreach ($variations as $key => $variation) {
            if (!empty($variationsMap)) {
                $vars = explode('+', $key);
                $urls = array();
                $variation->urls = array();
                $variation->variation = $key;
                foreach ($vars as $var) {
                    $urls[] = $variationsURL->{$var}->key.'='.$variationsURL->{$var}->value;
                    $variation->urls[$variationsURL->{$var}->key] = $variationsURL->{$var}->value;
                }
                $variation->url = implode('&', $urls);
            }
        }

        return $variations;
    }

    public static function getMapsPlacesPostsList($id)
    {
        $input = JFactory::getApplication()->input;
        $view = $input->get('view', '', 'string');
        $category = 0;
        $app = $input->get('app', 0, 'int');
        if ($view == 'blog' && $app == $id) {
            $category = $input->get('id', 0, 'int');
        }
        $db = JFactory::getDbo();
        $query = self::getBlogPostsQuery($id, $category)
            ->select('p.id');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $array = array();
        foreach ($items as $item) {
            $array[] = $item->id;
        }
        $str = implode(', ', $array);

        return $str;
    }

    public static function prepareGridboxLinks($link)
    {
        if (strpos($link, 'option=com_gridbox')) {
            parse_str($link, $array);
            if (!isset($array['app']) && isset($array['blog'])) {
                $array['app'] = $array['blog'];
            }
            if ($array['view'] == 'page') {
                $type = !empty($array['app']) ? 'blog' : 'single';
                $app_id = !empty($array['app']) ? $array['app'] : 0;
                $category = !empty($array['category']) ? $array['category'] : 0;
                $link = self::getGridboxPageLinks($array['id'], $type, $app_id, $category);
            } else if ($array['view'] == 'blog') {
                $link = self::getGridboxCategoryLinks($array['id'], $array['app']);
            }
        }

        return $link;
    }

    public static function getWeatherLanguage()
    {
        $lang = array('wind' => JText::_('WIND'), 'humidity' => JText::_('HUMIDITY'), 'pressure' => JText::_('PRESSURE'),
            'hpa' => JText::_('HPA'), 'Mon' => JText::_('WEATHER_MONDAY'),
            'Tue' => JText::_('WEATHER_TUESDAY'), 'Wed' => JText::_('WEATHER_WEDNESDAY'), 'Thu' => JText::_('WEATHER_THURSDAY'),
            'Fri' => JText::_('WEATHER_FRIDAY'), 'Sat' => JText::_('WEATHER_SATURDAY'), 'Sun' => JText::_('WEATHER_SUNDAY'),
            '0' => JText::_('WEATHER_JANUARY'), '1' => JText::_('WEATHER_FEBRUARY'), '2' => JText::_('WEATHER_MARCH'),
            '3' => JText::_('WEATHER_APRIL'), '4' => JText::_('WEATHER_MAY'), '5' => JText::_('WEATHER_JUNE'),
            '6' => JText::_('WEATHER_JULY'), '7' => JText::_('WEATHER_AUGUST'), '8' => JText::_('WEATHER_SEPTEMBER'),
            '9' => JText::_('WEATHER_OCTOBER'), '10' => JText::_('WEATHER_NOVEMBER'), '11' => JText::_('WEATHER_DECEMBER'),
            'mph' => JText::_('MPH'), 'ms' => JText::_('MS'));
        
        return $lang;
    }

    public static function getWeatherIcons()
    {
        $icons = array("01" => "wi wi-day-sunny", "02" => "wi wi-day-cloudy", "03" => "wi wi-cloud",
            "04" => "wi wi-cloudy", "09" => "wi wi-showers", "10" => "wi wi-sprinkle",
            "11" => "wi wi-thunderstorm", "13" => "wi wi-snow", "50" => "wi wi-fog");
        
        return $icons;
    }

    public static function getWeatherData($url, $id, $location)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('`#__gridbox_weather`')
            ->where('plugin_id = '.$db->quote($id));
        $db->setQuery($query);
        $obj = $db->loadObject();
        $now = strtotime('now');
        if (!$obj || $now - $obj->saved_time >= 3600 || $obj->location != $location) {
            $data = self::getInstagramData($url);
            $weather = json_decode($data);
            if ($weather->cod == 200) {
                $forecast = self::renderWetherData($item->weather, $data);
                $data = json_encode($forecast);
                $object = new stdClass();
                $object->plugin_id = $id;
                $object->saved_time = $now;
                $object->data = $data;
                $object->location = $location;
                if ($obj) {
                    $object->id = $obj->id;
                    $db->updateObject('#__gridbox_weather', $object, 'id');
                } else {
                    $db->insertObject('#__gridbox_weather', $object);
                }

                return $data;
            } else {
                return false;
            }
        } else {
            return $obj->data;
        }
    }

    public static function renderWetherData($weather, $data)
    {
        $lang = self::getWeatherLanguage();
        $icons = self::getWeatherIcons();
        $obj = json_decode($data);
        preg_match('/[-\d]+/', $obj->list[0]->dt_txt, $matches);
        $date = explode('-', $matches[0]);
        $now = date('Y-m-d', strtotime($obj->list[0]->dt_txt));
        $icon = preg_replace('/d|n/', '', $obj->list[0]->weather[0]->icon);
        $speed = $weather->unit == 'c' ? $lang['ms'] : $lang['mph'];
        $object = new stdClass();
        $object->weather = new stdClass();
        $object->weather->city = $weather->location;
        $object->weather->date = ($date[2] * 1).' '.$lang[$date[1] * 1 - 1].' '.$date[0];
        $object->weather->temp = round($obj->list[0]->main->temp);
        $object->weather->icon = $icons[$icon];
        $object->weather->wind = $lang['wind'].': '.$obj->list[0]->wind->speed.' '.($speed);
        $object->weather->humidity = $lang['humidity'].': '.$obj->list[0]->main->humidity.'%';
        $object->weather->pressure = $lang['pressure'].': '.$obj->list[0]->main->pressure.' '.$lang['hpa'];
        $object->forecast = array();
        $array = new stdClass();
        foreach ($obj->list as $list) {
            $listDate = date('Y-m-d', strtotime($list->dt_txt));
            if ($now == $listDate) {
                continue;
            }
            if (!isset($array->{$listDate})) {
                $array->{$listDate} = array();
            }
            $time = explode(' ', $list->dt_txt);
            if ($time[1] <= '12:00:00') {
                $array->{$listDate}[] = $list;
            }
        }
        foreach ($array as $key => $value) {
            $i = count($object->forecast);
            $day = date('D', strtotime($key));
            $dayObj = $value[count($value) - 1];
            $icon = preg_replace('/d|n/', '', $dayObj->weather[0]->icon);
            $object->forecast[$i] = new stdClass();
            $object->forecast[$i]->day = $lang[$day];
            $object->forecast[$i]->nightTemp = round($value[0]->main->temp);
            $object->forecast[$i]->dayTemp = round($dayObj->main->temp);
            $object->forecast[$i]->icon = $icons[$icon];
        }

        return $object;
    }

    public static function renderWetherHTML($weather, $item)
    {
        $name = isset($item->weather->name) && !empty($item->weather->name) ? $item->weather->name : $item->weather->location;
        include(JPATH_COMPONENT.'/views/layout/weather-today.php');
        $str = $out.'<div>';
        foreach ($weather->forecast as $forecast) {
            include(JPATH_COMPONENT.'/views/layout/weather-forecast.php');
            $str .= $out;
        }
        $str .= '</div>';

        return $str;
    }

    public static function getWeather($item, $id, $openWeatherMapKey)
    {
        $units = $item->weather->unit == 'c' ? 'metric' : 'imperial';
        $latLon = explode(',', $item->weather->location);
        if (!empty($latLon) && count($latLon) == 2 && is_numeric($latLon[0])&& is_numeric($latLon[1])) {
            $url = 'http://api.openweathermap.org/data/2.5/forecast?lat='.trim($latLon[0]).'&lon='.trim($latLon[1]);
            $url .= '&units='.$units.'&appid='.$openWeatherMapKey;
        } else if (is_numeric($item->weather->location)) {
            $url = 'http://api.openweathermap.org/data/2.5/forecast?id='.$item->weather->location;
            $url .= '&units='.$units.'&appid='.$openWeatherMapKey;
        } else {
            $location = str_replace(' ', '%20', $item->weather->location);
            $url = 'http://api.openweathermap.org/data/2.5/forecast?q='.$location;
            $url .= '&units='.$units.'&appid='.$openWeatherMapKey;
        }
        $data = self::getWeatherData($url, $id, $item->weather->location);
        if (!$data) {
            return $data;
        }
        $weather = json_decode($data);
        $str = self::renderWetherHTML($weather, $item);

        return $str;
    }

    public static function getInstagramData($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    public static function setCommentsCaptcha()
    {
        if (!empty(self::$website->comments_recaptcha)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('params')
                ->from('#__extensions')
                ->where('element = '.$db->quote(self::$website->comments_recaptcha))
                ->where('folder = '.$db->quote('captcha'))
                ->where('enabled = 1')
                ->where('type = '.$db->quote('plugin'));
            $db->setQuery($query);
            $captcha = $db->loadResult();
        } else {
            $captcha = null;
        }
        $doc = JFactory::getDocument();
        if ($captcha) {
            $obj = new Registry();
            $obj->loadString($captcha);
            $object = new stdClass();
            $object->data = new stdClass();
            $object->public_key = $obj->get('public_key', '');
            $object->private_key = $obj->get('private_key', '');
            $object->type = self::$website->comments_recaptcha;
            $object->theme = $obj->get('theme2', '');
            $object->size = $obj->get('size', '');
            $object->badge = $obj->get('badge', '');
            $data = json_encode($object);
            $opt = array();
            $atr = array('defer' => true, 'async' => true);
            $doc->addScript('https://www.google.com/recaptcha/api.js?onload=recaptchaCommentsOnload&render=explicit', $opt, $atr);
            $doc->addScriptDeclaration('var recaptchaObject = '.$data.';');
        } else {
            $doc->addScriptDeclaration('var recaptchaObject = null;');
        }

        return $captcha;
    }

    public static function setReviewsCaptcha()
    {
        if (!empty(self::$website->reviews_recaptcha)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('params')
                ->from('#__extensions')
                ->where('element = '.$db->quote(self::$website->reviews_recaptcha))
                ->where('folder = '.$db->quote('captcha'))
                ->where('enabled = 1')
                ->where('type = '.$db->quote('plugin'));
            $db->setQuery($query);
            $captcha = $db->loadResult();
        } else {
            $captcha = null;
        }
        $doc = JFactory::getDocument();
        if ($captcha) {
            $obj = new Registry();
            $obj->loadString($captcha);
            $object = new stdClass();
            $object->data = new stdClass();
            $object->public_key = $obj->get('public_key', '');
            $object->private_key = $obj->get('private_key', '');
            $object->type = self::$website->reviews_recaptcha;
            $object->theme = $obj->get('theme2', '');
            $object->size = $obj->get('size', '');
            $object->badge = $obj->get('badge', '');
            $data = json_encode($object);
            $opt = array();
            $atr = array('defer' => true, 'async' => true);
            $doc->addScript('https://www.google.com/recaptcha/api.js?onload=recaptchaCommentsOnload&render=explicit', $opt, $atr);
            $doc->addScriptDeclaration('var recaptchaObject = '.$data.';');
        } else {
            $doc->addScriptDeclaration('var recaptchaObject = null;');
        }

        return $captcha;
    }

    public static function getCategoryIntro()
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $db = JFactory::getDbo();
        $id = $input->get('id', 0, 'int');
        $tag = $input->get('tag', 0, 'int');
        $author = $input->get('author', 0, 'int');
        if (!empty($tag)) {
            $id = $tag;
        } else if (!empty($author)) {
            $id = $author;
        }
        if ($input->getCmd('view') == 'gridbox') {
            $obj = new stdClass();
            $obj->title = 'Category Title';
            $obj->description = 'Category Description';
            $obj->image = '';
        } else {
            $query = $db->getQuery(true)
                ->select('title, description, image');
            if (!empty($tag)) {
                $query->from('#__gridbox_tags');
            } else if (!empty($author)) {
                $query->select('avatar, author_social');
                $query->from('#__gridbox_authors');
            } else if ($id != 0) {
                $query->from('#__gridbox_categories');
            } else {
                $id = $input->get('app', 0, 'int');
                $query->from('#__gridbox_app');
            }
            $query->where('id = '.$id);
            $db->setQuery($query);
            $obj = $db->loadObject();
        }
        if (isset($obj->avatar) && empty($obj->avatar)) {
            $obj->avatar = 'components/com_gridbox/assets/images/default-user.png';
        }
        if (empty($obj->image)) {
            $obj->image = 'components/com_gridbox/assets/images/default-theme.png';
        }
        if (strpos($obj->image, 'balbooa.com') === false) {
            $obj->image = JUri::root().$obj->image;
        }
        $image = '<div class="intro-post-image-wrapper"><div class="ba-overlay"></div><div class="intro-post-image"';
        $image .= ' style="background-image: url('.$obj->image;
        $image .= ');">';
        $image .= '</div></div>';
        $title = $obj->title;
        if (isset($obj->avatar)) {
            if (strpos($obj->avatar, 'balbooa.com') === false) {
                $obj->avatar = JUri::root().$obj->avatar;
            }
            $title = '<span class="ba-author-avatar" style="background-image: url('.$obj->avatar.')"></span>'.$title;
        }
        $object = new stdClass();
        $object->image = $image;
        $object->title = $title;
        $object->description = $obj->description;
        if (isset($obj->author_social)) {
            $socialHTML = '';
            $socials = json_decode($obj->author_social);
            foreach ($socials as $key => $social) {
                if (!empty($social->link)) {
                    $socialHTML .= '<a target="_blank" href="'.$social->link.'" class="'.$social->icon.'"></a>';
                }
            }
            if (!empty($socialHTML)) {
                $socialHTML = '<div class="intro-category-author-social-wrapper">'.$socialHTML.'</div>';
                $object->social = $socialHTML;
            }
        }

        return $object;
    }

    public static function renderModules($body)
    {
        $app = JFactory::getApplication();
        $view = $app->input->getCmd('view', '');
        $regex = '/\[modules ID=+(.*?)\]/i';
        preg_match_all($regex, $body, $matches, PREG_SET_ORDER);
        if ($matches) {
            foreach ($matches as $index => $match) {
                $id = (int)$match[1];
                if ($id) {
                    $db = JFactory::getDBO();
                    $date = JDate::getInstance()->format('Y-m-d H:i:s');
                    $date = $db->quote($date);
                    $nullDate = $db->quote($db->getNullDate());
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__modules')
                        ->where('client_id = 0')
                        ->where('published = 1')
                        ->where('(publish_down = '.$nullDate.' OR publish_down >= '.$date.')')
                        ->where('(publish_up = '.$nullDate.' OR publish_up <= '.$date.')')
                        ->where('language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
                        ->where('id = '.$id);
                    $query->order('ordering');
                    $db->setQuery($query);
                    $module = $db->loadObject();
                    $access = self::checkModuleAccess($module);
                    if ($access) {
                        $document = JFactory::getDocument();
                        $document->_type = 'html';
                        $renderer = $document->loadRenderer('module');
                        $html = $renderer->render($module);
                        if ($module->module == 'mod_custom') {
                            $html = JHtml::_('content.prepare', $html);
                        }
                        if ($module->showtitle) {
                            $moduleParams = new Registry;
                            $moduleParams->loadString($module->params);
                            $headerTag = htmlspecialchars($moduleParams->get('header_tag', 'h3'), ENT_COMPAT, 'UTF-8');
                            $headerClass = htmlspecialchars($moduleParams->get('header_class', 'page-header'), ENT_COMPAT, 'UTF-8');
                            $html = '<'.$headerTag.' class="'.$headerClass.'">'.$module->title.'</'.$headerTag.'>'.$html;
                        }
                    } else {
                        $html = '';
                    }
                    if (!empty($html) || $view != 'gridbox') {
                        $html = str_replace('\\\\', '\\\\\\\\', $html);
                        $html = str_replace('\\/', '\\\/', $html);
                        $html = str_replace('\\.', '\\\.', $html);
                        $html = str_replace('\\u', '\\\u', $html);
                        $html = str_replace('\\(', '\\\(', $html);
                        $html = str_replace('\\)', '\\\)', $html);
                        $html = str_replace('\\[', '\\\[', $html);
                        $html = str_replace('\\]', '\\\]', $html);
                        $html = str_replace('\\?', '\\\?', $html);
                        $html = str_replace('\\n', '\\\n', $html);
                        $html = str_replace('\\r', '\\\r', $html);
                        $html = str_replace('\\s', '\\\s', $html);
                        $html = str_replace('\\t', '\\\t', $html);
                        $body = str_replace($match[0], $html, $body);
                    }
                }
            }
        }
        
        return $body;
    }

    public static function checkModules($body, $items)
    {
        if (!is_object($items)) {
            $obj = json_decode($items);
        } else {
            $obj = $items;
        }
        $body = self::checkGlobalItem($body, $obj);
        $app = JFactory::getApplication();
        $view = $app->input->getCmd('view', '');
        $option = $app->input->getCmd('option', '');
        if ($option != 'com_gridbox' || ($view != 'gridbox' && !empty($view))) {
            $body = self::clearDOM($body, $obj);
        }
        $body = self::checkMainMenu($body);
        $body = self::checkDOM($body, $obj);
        $body = self::checkPostTags($body, $app->input->getCmd('id', 0));
        $body = self::renderModules($body);
        
        return $body;
    }

    public static function getStarRatings($id, $page)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_star_ratings')
            ->where('`plugin_id` = '.$db->quote($id))
            ->where('`option` = '.$db->quote($page->option))
            ->where('`view` = '.$db->quote($page->view))
            ->where('`page_id` = '.$db->quote($page->id));
        $db->setQuery($query);
        $obj = $db->loadObject();
        if (!isset($obj->rating)) {
            $obj = new stdClass();
            $obj->rating = '0.00';
            $obj->count = 0;
        }
        $str = '<div class="info-wrapper" id="star-ratings-'.$id;
        $str .= '" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
        $str .= '<span class="rating-wrapper"><span class="rating-title">'.JText::_('RATING');
        $str .= ' </span><span class="rating-value" itemprop="ratingValue">'.$obj->rating.'</span></span>';
        $str .= '<span class="votes-wrapper"> (<span class="votes-count" itemprop="reviewCount">'.$obj->count;
        $str .= '</span><span class="votes-title"> '.JText::_('VOTES').'</span>)</span></span>';
        $array = array($str, $obj->rating);

        return $array;
    }

    public static function getEmptyList()
    {
        $input = JFactory::getApplication()->input;
        $task = $input->get->get('task', 'gridbox', 'string');
        if (strpos($task, 'editor.') !== false) {
            $input->set('view', 'gridbox');
        }
        $view = $input->get('view', 'gridbox', 'string');
        $option = $input->getCmd('option', '');
        $html = '<div class="empty-list"><i class="zmdi zmdi-alert-polygon"></i><p>';
        $html .= JText::_('NO_ITEMS_HERE').'</p></div>';
        if ($option != 'com_gridbox' || ($view != 'gridbox' && !empty($view))) {
            $html = '';
        }

        return $html;
    }

    public static function getBlogPostsHeader($isStore, $id, $category, $order)
    {
        $app = JFactory::getApplication();
        $input = JFactory::getApplication()->input;
        $tag = $input->get('tag', 0, 'int');
        $author = $input->get('author', 0, 'int');
        if (!empty($tag)) {
            $url = self::getGridboxTagLinks($tag, $id);
        } else if (!empty($author)) {
            $url = self::getGridboxAuthorLinks($author, $id);
        } else {
            $url = self::getGridboxCategoryLinks($category, $id);
        }
        $query = $input->get('query', '', 'raw');
        if (!empty($query)) {
            $url .= '&query='.$query;
        }
        $url .= '&sort-by=';
        $url = JRoute::_($url);
        include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/store/blog-posts-header.php';

        return $header;
    }

    public static function getBlogPagination($id, $active, $limit, $category)
    {
        $app = JFactory::getApplication();
        $input = JFactory::getApplication()->input;
        $tag = $input->get('tag', 0, 'int');
        $author = $input->get('author', 0, 'int');
        if (!empty($tag)) {
            $url = self::getGridboxTagLinks($tag, $id);
        } else if (!empty($author)) {
            $url = self::getGridboxAuthorLinks($author, $id);
        } else {
            $url = self::getGridboxCategoryLinks($category, $id);
        }
        $queryStr = $input->get('query', '', 'raw');
        $order = $input->get('sort-by', '', 'raw');
        if (!empty($queryStr)) {
            $url .= '&query='.$queryStr;
        }
        if (!empty($order)) {
            $url .= '&sort-by='.$order;
        }
        $active = $active * 1;
        $db = JFactory::getDbo();
        $query = self::getBlogPostsQuery($id, $category)
            ->select('COUNT(*)');
        $db->setQuery($query);
        $count = $db->loadResult();
        if ($count == 0) {
            return '';
        }
        if ($limit == 0) {
            $limit = 1;
        }
        $pages = ceil($count / $limit);
        if ($pages == 1) {
            return '';
        }
        $start = 0;
        $max = $pages;
        if ($active > 2 && $pages > 4) {
            $start = $active - 2;
        }
        if ($pages > 4 && ($pages - $active) < 3) {
            $start = $pages - 5;
        }
        if ($pages > $active + 2) {
            $max = $active + 3;
            if ($pages > 3 && $active < 2) {
                $max = 4;
            }
            if ($pages > 4 && $active < 2) {
                $max = 5;
            }
        }
        include JPATH_ROOT.'/components/com_gridbox/views/layout/blog-posts-pagination.php';
        
        return $out;
    }

    public static function getBlogPostsChildCategories($id)
    {
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $date = date("Y-m-d H:i:s");
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__gridbox_categories')
            ->where('published = 1')
            ->where('parent = '.$db->quote($id))
            ->where('language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('access in ('.$groups.')')
            ->order('order_list ASC');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as $item) {
            $childs = self::getBlogPostsChildCategories($item->id);
            $items = array_merge($items, $childs);
        }

        return $items;
    }

    public static function getItemsFilterWheres($app, $object)
    {
        $db = JFactory::getDbo();
        $wheres = array();
        foreach ($object as $key => $array) {
            if (empty($array)) {
                continue;
            }
            $sub = array();
            if ($key != 'rating') {
                $query = $db->getQuery(true)
                    ->select('type')
                    ->from('#__gridbox_app')
                    ->where('id = '.$app);
                $db->setQuery($query);
                $type = $db->loadResult();
                $query = $db->getQuery(true)
                    ->select('id')
                    ->from('#__gridbox_store_products_fields')
                    ->where('title = '.$db->quote($key));
                $db->setQuery($query);
                $field = $db->loadObject();
                if ($type == 'products' && JText::_('PRICE') == $key) {
                    $field = new stdClass();
                    $field->product = true;
                    $field->field_type = 'price';
                } else if ($field) {
                    $field->product = true;
                    $field->field_type = 'tag';
                } else {
                    $query = $db->getQuery(true)
                        ->select('id, field_type')
                        ->from('#__gridbox_fields')
                        ->where('app_id = '.$app)
                        ->where('label = '.$db->quote($key));
                    $db->setQuery($query);
                    $field = $db->loadObject();
                }
                if (isset($field->product) && $field->field_type == 'price') {
                    $query = $db->getQuery(true)
                        ->select('d.product_id, d.price, d.sale_price')
                        ->from('#__gridbox_store_product_data AS d')
                        ->where('a.id = '.$app)
                        ->leftJoin('#__gridbox_pages AS p ON p.id = d.product_id')
                        ->leftJoin('#__gridbox_app AS a ON a.id = p.app_id');
                    $db->setQuery($query);
                    $list = $db->loadObjectList();
                    foreach ($list as $obj) {
                        $prices = self::prepareProductPrices($obj->product_id, $obj->price, $obj->sale_price);
                        $value = $prices->sale_price != '' ? $prices->sale_price : $prices->price;
                        if ($value * 1 >= $array[0] * 1 && $value * 1 <= $array[1] * 1 && !in_array($obj->product_id, $sub)) {
                            $sub[] = $obj->product_id;
                        }
                    }
                } else if (isset($field->product)) {
                    foreach ($array as $value) {
                        $query = $db->getQuery(true)
                            ->select('DISTINCT vm.product_id')
                            ->from('#__gridbox_store_products_fields_data AS fd')
                            ->where('fd.value = '.$db->quote($value))
                            ->leftJoin('#__gridbox_store_product_variations_map AS vm ON vm.option_key = fd.option_key');
                        $db->setQuery($query);
                        $list = $db->loadObjectList();
                        foreach ($list as $obj) {
                            if (!in_array($obj->product_id, $sub)) {
                                $sub[] = $obj->product_id;
                            }
                        }
                    }
                } else if ($field->field_type == 'price') {
                    $query = $db->getQuery(true)
                        ->select('page_id, value')
                        ->from('#__gridbox_page_fields')
                        ->where('field_id = '.$field->id);
                    $db->setQuery($query);
                    $list = $db->loadObjectList();
                    foreach ($list as $obj) {
                        if ($obj->value * 1 >= $array[0] * 1 && $obj->value * 1 <= $array[1] * 1 && !in_array($obj->page_id, $sub)) {
                            $sub[] = $obj->page_id;
                        }
                    }
                } else {
                    foreach ($array as $value) {
                        $query = $db->getQuery(true)
                            ->select('fd.option_key')
                            ->from('#__gridbox_fields_data AS fd')
                            ->where('fd.field_id = '.$field->id)
                            ->where('fd.value = '.$db->quote($value));
                        $db->setQuery($query);
                        $option_key = $db->loadResult();
                        $query = $db->getQuery(true)
                            ->select('page_id')
                            ->from('#__gridbox_page_fields')
                            ->where('field_id = '.$field->id);
                        if ($field->field_type != 'checkbox') {
                            $query->where('value = '.$db->quote($option_key));
                        } else {
                            $query->where('value LIKE '.$db->quote('%'.$option_key.'%'));
                        }
                        $db->setQuery($query);
                        $list = $db->loadObjectList();
                        foreach ($list as $obj) {
                            if (!in_array($obj->page_id, $sub)) {
                                $sub[] = $obj->page_id;
                            }
                        }
                    }
                }
            } else {
                $rating = implode(', ', $array);
                self::setCommentUser();
                self::setReviewsModerators();
                $query = $db->getQuery(true)
                    ->select('id')
                    ->from('#__gridbox_pages')
                    ->where('app_id = '.$app);
                $db->setQuery($query);
                $pages = $db->loadObjectList();
                foreach ($pages as $page) {
                    $reviews = self::getReviewsCount($page->id);
                    if ($reviews->count > 0) {
                        foreach ($array as $rating) {
                            if ($reviews->rating >= $rating && $reviews->rating < $rating * 1 + 1) {
                                $sub[] = $page->id;
                                break;
                            }
                        }
                    }
                }
            }
            if (!empty($sub)) {
                $str = implode(', ', $sub);
                $wheres[] = 'p.id in ('.$str.')';
            } else {
                $wheres[] = 'p.id in (0)';
            }
        }

        return $wheres;
    }

    public static function getItemsFilterCount($app, $object)
    {
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $db = JFactory::getDbo();
        $input = JFactory::getApplication()->input;
        $category = $input->get('id', 0, 'int');
        $tag = $input->get('tag', 0, 'int');
        $author = $input->get('author', 0, 'int');
        $wheres = self::getItemsFilterWheres($app, $object);
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('COUNT(p.id)')
            ->from('#__gridbox_pages AS p')
            ->where('p.app_id = '.$app);
        if ($category > 0 && empty($tag) && empty($author)) {
            $categories = self::getBlogPostsChildCategories($category);
            $catStr = (string)$category;
            foreach ($categories as $value) {
                $catStr .= ','.$value->id;
            }
            $query->where('p.page_category in ('.$catStr.')');
        }
        $query->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->leftJoin('`#__gridbox_categories` AS c'
                . ' ON '
                . $db->quoteName('p.page_category')
                . ' = ' 
                . $db->quoteName('c.id')
            )
            ->where('c.published = 1')
            ->where('c.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('c.access in ('.$groups.')');
        if (!empty($tag)) {
            $query->where('t.tag_id = '.$tag)
                ->leftJoin('`#__gridbox_tags_map` AS t'
                    . ' ON '
                    . $db->quoteName('p.id')
                    . ' = ' 
                    . $db->quoteName('t.page_id')
                );
        } else if (!empty($author)) {
            $query->where('t.author_id = '.$author)
                ->leftJoin('`#__gridbox_authors_map` AS t'
                    . ' ON '
                    . $db->quoteName('p.id')
                    . ' = ' 
                    . $db->quoteName('t.page_id')
                );
        }
        if (!empty($wheres)) {
            $query->where(implode(' AND ', $wheres));
        }
        $db->setQuery($query);
        $count = $db->loadResult();

        return $count;
    }

    public static function getBlogPostsQuery($id, $category, $order = 'p.id ASC')
    {
        $input = JFactory::getApplication()->input;
        $tag = $input->get('tag', 0, 'int');
        $author = $input->get('author', 0, 'int');
        $queryStr = $input->get('query', '', 'raw');
        $db = JFactory::getDbo();
        if (!empty($queryStr)) {
            $array = explode('__', $queryStr);
            $object = new stdClass();
            $values = array();
            $keys = array();
            foreach ($array as $k => $v) {
                if ($k % 2 == 0) {
                    $keys[] = $v;
                }
                else {
                    $values[] = $v;
                }
            }
            foreach ($keys as $i => $key) {
                $object->{$key} = explode('--', $values[$i]);
            }
            $wheres = self::getItemsFilterWheres($id, $object);
        }
        if ($order == 'newest') {
            $order = 'p.created DESC';
        } else if ($order == 'popular') {
            $order = 'p.hits DESC';
        } else if ($order == 'price-low-high' || $order == 'price-high-low' || $order == 'highest-rated' || $order == 'most-reviewed') {
            if ($order == 'highest-rated' || $order == 'most-reviewed') {
                self::setCommentUser();
                self::setReviewsModerators();
            }
            $query = $db->getQuery(true)
                ->select('d.product_id, d.price, d.sale_price')
                ->from('#__gridbox_store_product_data AS d')
                ->where('a.id = '.$id)
                ->leftJoin('#__gridbox_pages AS p ON p.id = d.product_id')
                ->leftJoin('#__gridbox_app AS a ON a.id = p.app_id');
            if (!empty($queryStr) && !empty($wheres)) {
                $query->where(implode(' AND ', $wheres));
            }
            $db->setQuery($query);
            $list = $db->loadObjectList();
            foreach ($list as $obj) {
                if ($order == 'price-low-high' || $order == 'price-high-low') {
                    $prices = self::prepareProductPrices($obj->product_id, $obj->price, $obj->sale_price);
                    $obj->price_value = $prices->sale_price != '' ? $prices->sale_price * 1 : $prices->price * 1;
                } else {
                    $obj->reviews = self::getReviewsCount($obj->product_id);
                }
            }
            if ($order == 'price-low-high') {
                usort($list, function($a, $b){
                    return ($a->price_value < $b->price_value) ? -1 : 1;
                });
            } else if ($order == 'price-high-low') {
                usort($list, function($a, $b){
                    return ($a->price_value < $b->price_value) ? 1 : -1;
                });
            } else if ($order == 'highest-rated') {
                usort($list, function($a, $b){
                    return ($a->reviews->rating < $b->reviews->rating) ? 1 : -1;
                });
            } else {
                usort($list, function($a, $b){
                    return ($a->reviews->count < $b->reviews->count) ? 1 : -1;
                });
            }
            $pks = array();
            foreach ($list as $obj) {
                $pks[] = $obj->product_id;
            }
            if (!empty($pks)) {
                $order = 'FIELD(p.id, '.implode(',', $pks).')';
            } else {
                $order = 'p.id ASC';
            }
        }
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $query = $db->getQuery(true)
            ->from('#__gridbox_pages AS p')
            ->where('p.app_id = '.$id);
        if ($category > 0 && empty($tag) && empty($author)) {
            $categories = self::getBlogPostsChildCategories($category);
            $catStr = (string)$category;
            foreach ($categories as $value) {
                $catStr .= ','.$value->id;
            }
            $query->where('p.page_category in ('.$catStr.')');
        }
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->order($order)
            ->leftJoin('`#__gridbox_categories` AS c ON p.page_category = c.id')
            ->where('c.published = 1')
            ->where('c.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('c.access in ('.$groups.')')
            ->leftJoin('`#__gridbox_app` AS a ON p.app_id = a.id');
        if (!empty($tag)) {
            $query->where('t.tag_id = '.$tag)
                ->leftJoin('`#__gridbox_tags_map` AS t'
                    . ' ON '
                    . $db->quoteName('p.id')
                    . ' = ' 
                    . $db->quoteName('t.page_id')
                );
        } else if (!empty($author)) {
            $query->where('t.author_id = '.$author)
                ->leftJoin('`#__gridbox_authors_map` AS t ON p.id = t.page_id');
        }
        if (!empty($queryStr) && !empty($wheres)) {
            $query->where(implode(' AND ', $wheres));
        }


        return $query;
    }

    public static function getBlogPosts($id, $max, $limit, $start, $category, $order)
    {
        $start *= $limit;
        $list = self::getBlogPostsSortingList();
        if (isset($list[$order])) {
            $dir = '';
        } else if ($order == 'order_list') {
            $dir = ' ASC';
            if ($category == 0) {
                $order = 'root_order_list';
            }
        } else {
            $dir = ' DESC';
        }
        if ($order == 'random') {
            $order = 'RAND()';
        } else if (!isset($list[$order])) {
            $order = 'p.'.$order;
        }
        $html = '';
        $db = JFactory::getDbo();
        $query = self::getBlogPostsQuery($id, $category, $order.$dir)
            ->select('p.id, p.title, p.intro_text, p.created, p.hits, p.intro_image, p.page_category,
                p.app_id, p.meta_title, c.title as category, a.title as blog, a.type');
        $db->setQuery($query, $start, $limit);
        $pages = $db->loadObjectList();
        include JPATH_ROOT.'/components/com_gridbox/views/layout/blog-posts.php';
        foreach ($pages as $key => $page) {
            $html .= self::getRecentPostsHTML($page, $out, $max);
        }

        return $html;
    }

    public static function checkGlobalItem($body, $items)
    {
        $regex = '/\[global item=+(.*?)\]/i';
        preg_match_all($regex, $body, $matches, PREG_SET_ORDER);
        $db = JFactory::getDBO();
        foreach ($matches as $index => $match) {
            $query = $db->getQuery(true)
                ->select('item')
                ->from('#__gridbox_library')
                ->where('`global_item` = ' . $db->quote($match[1]));
            $db->setQuery($query);
            $obj = $db->loadResult();
            $html = '';
            if (!empty($obj)) {
                $obj = json_decode($obj);
                $html = $obj->html;
                foreach ($obj->items as $key => $value) {
                    $items->{$key} = $obj->items->{$key};
                }
            }
            $body = @preg_replace("|\[global item=".$match[1]."\]|", $html, $body, 1);
        }
        preg_match_all($regex, $body, $matches, PREG_SET_ORDER);
        if (!empty($matches)) {
            $body = self::checkGlobalItem($body, $items);
        }

        return $body;
    }

    public static function getCategoryBreadcrumb($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('title, id, parent, app_id')
            ->from('#__gridbox_categories')
            ->where('`id` = '.$id);
        $db->setQuery($query);
        $obj = $db->loadObject();
        self::getGridboxMenuItems();
        $itemId = self::getGridboxMenuItemidByCategory($obj->app_id, $id);
        if (!empty($itemId)) {
            return array();
        } else {
            $url = self::getGridboxCategoryLinks($id, $obj->app_id);
        }
        $result = array(array('title' => $obj->title, 'link' => JRoute::_($url)));
        if ($obj->parent != 0) {
            $array = self::getCategoryBreadcrumb($obj->parent);
            $result = array_merge($result, $array);
        }

        return $result;
        
    }

    public static function getCategoryId($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('page_category')
            ->from('#__gridbox_pages')
            ->where('id = '.$id);
        $db->setQuery($query);
        $category = $db->loadResult();
        $array = array($category);
        $array2 = self::getCategoryIdPath($category);
        $result = array_merge($array, $array2);
        
        return $result;
    }

    public static function getCategoryIdPath($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('parent')
            ->from('#__gridbox_categories')
            ->where('`id` = '.$id * 1);
        $db->setQuery($query);
        $obj = $db->loadObject();
        $array1 = array($obj->parent);
        if ($obj->parent != 0) {
            $array2 = self::getCategoryIdPath($obj->parent);
        } else {
            $array2 = array();
        }
        $result = array_merge($array1, $array2);
        
        return $result;
    }

    public static function getCategoryPath($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('alias, app_id, parent')
            ->from('#__gridbox_categories')
            ->where('`id` = '.$id);
        $db->setQuery($query);
        $obj = $db->loadObject();
        self::getGridboxMenuItems();
        $itemId = self::getGridboxMenuItemidByCategory($obj->app_id, $id);
        if (!empty($itemId)) {
            return array();
        }
        $result = array($obj->alias);
        if ($obj->parent != 0) {
            $array = self::getCategoryPath($obj->parent);
            $result = array_merge($result, $array);
        }
        
        return $result;
    }

    public static function getAuthorsHtml($authors, $className, $app_id)
    {
        $str = '';
        foreach ($authors as $author) {
            $url = self::getGridboxAuthorLinks($author->id, $app_id);
            if ($className == 'event-calendar-event-item-author') {
                $authorUrl = JRoute::_($authorUrl);
            }
            if (empty($author->avatar)) {
                $author->avatar = 'components/com_gridbox/assets/images/default-user.png';
            }
            if (strpos($author->avatar, 'balbooa.com') === false) {
                $author->avatar= JUri::root().$author->avatar;
            }
            $str .= '<span class="'.$className.'"><a href="'.$url.'"><span class="ba-author-avatar"';
            $str .= ' style="background-image: url('.$author->avatar.')"></span>';
            $str .= $author->title.'</a></span>';
        }

        return $str;
    }

    public static function getBlogPostIntro()
    {
        $input = JFactory::getApplication()->input;
        $db = JFactory::getDbo();
        $id = $input->get('id', 0, 'int');
        $edit_type = $input->get('edit_type', '', 'string');
        if ($edit_type == 'post-layout') {
            $page = self::getPostLayoutPage($id);
            if ($page) {
                $id = $page;
            } else {
                $id = 0;
            }
        }
        $query = $db->getQuery(true)
            ->select('p.*')
            ->from('`#__gridbox_pages` as p')
            ->where('p.id = '.$id)
            ->where('p.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')')
            ->select('a.type as app_type')
            ->leftJoin('`#__gridbox_app` AS a'
                . ' ON '
                . $db->quoteName('p.app_id')
                . ' = ' 
                . $db->quoteName('a.id')
            )
            ->select('c.title AS category_title')
            ->leftJoin('`#__gridbox_categories` AS c'
                . ' ON '
                . $db->quoteName('p.page_category')
                . ' = ' 
                . $db->quoteName('c.id')
            )
            ->order('p.id ASC');
        $db->setQuery($query);
        $item = $db->loadObject();
        if (!$item) {
            $item = new stdClass();
            $item->id = 0;
            $item->intro_image = 'components/com_gridbox/assets/images/default-theme.png';
            $item->category_title = 'category';
            $item->created = date('Y-m-d');
            $item->hits = $item->page_category = 0;
            $item->title = JText::_('PAGE_TITLE');
            $item->app_id = $id;
        }
        $query = $db->getQuery(true)
            ->select('au.title, au.avatar, au.id')
            ->from('`#__gridbox_authors_map` AS au_m')
            ->where('au_m.page_id = '.$id)
            ->leftJoin('`#__gridbox_authors` AS au ON au.id = au_m.author_id')
            ->where('au.published = 1')
            ->order('au_m.id ASC');
        $db->setQuery($query);
        $item->authors = $db->loadObjectList();
        $url = self::getGridboxCategoryLinks($item->page_category, $item->app_id);
        $category = '<a href="'.JRoute::_($url).'">'.$item->category_title.'</a>';
        $date = self::getPostDate($item->created);
        $views = $item->hits.' '.JText::_('VIEWS');
        $intro_image = $item->intro_image;
        if (empty($item->intro_image)) {
            $item->intro_image = 'components/com_gridbox/assets/images/default-theme.png';
        }
        if (strpos($item->intro_image, 'balbooa.com') === false) {
            $item->intro_image = JUri::root().$item->intro_image;
        }
        $app = JFactory::getApplication();
        $view = $app->input->get('view', 'gridbox', 'string');
        $obj = new stdClass();
        if (!empty($intro_image) || $view == 'gridbox') {
            $obj->image = '<div class="intro-post-image-wrapper"><div class="ba-overlay"></div><div class="intro-post-image"';
            $obj->image .= ' style="background-image: url('.$item->intro_image;
            $obj->image .= ');"></div></div>';
        } else {
            $obj->image = '';
        }
        $obj->title = $item->title;
        $author = self::getAuthorsHtml($item->authors, 'intro-post-author', $item->app_id);
        if ($item->id == 0) {
            $author = '<span class="intro-post-author"><a href="#"><span class="ba-author-avatar" ';
            $author .= 'style="background-image: url('.JUri::root().'components/com_gridbox/assets/images/default-user.png';
            $author .= ')"></span>admin</a></span>';
        }
        $comments = self::getCommentsCount($item->id);
        if ($comments == 0) {
            $commentsStr = JText::_('LEAVE_COMMENT');
        } else {
            $commentsStr = $comments.' '.JText::_('COMMENTS');
        }
        $reviews = self::getReviewsCount($item->id);
        if ($reviews->count == 0) {
            $reviewsStr = JText::_('LEAVE_REVIEW');
        } else {
            $reviewsStr = $reviews->count.' '.JText::_('REVIEWS');
        }
        include JPATH_ROOT.'/components/com_gridbox/views/layout/intro-post-content.php';
        $obj->info = $out;

        return $obj;
    }

    public static function getPostAuthor($id)
    {
        $db = JFactory::getDbo();
        $input = JFactory::getApplication()->input;
        $edit_type = $input->get('edit_type', '', 'string');
        if ($edit_type == 'post-layout') {
            $page = self::getPostLayoutPage($id);
            if ($page) {
                $id = $page;
            } else {
                $id = 0;
            }
        }
        $query = $db->getQuery(true)
            ->select('DISTINCT p.app_id, a.title, a.id, a.avatar, a.description, a.author_social')
            ->from('#__gridbox_pages AS p')
            ->where('p.id = '.$id)
            ->leftJoin('`#__gridbox_authors_map` AS m ON m.page_id = p.id')
            ->leftJoin('`#__gridbox_authors` AS a ON m.author_id = a.id')
            ->where('a.published = 1');
        $db->setQuery($query);
        $authors = $db->loadObjectList();
        $html = '';
        foreach ($authors as $author) {
            $url = self::getGridboxAuthorLinks($author->id, $author->app_id);
            if (empty($author->avatar)) {
                $author->avatar = 'components/com_gridbox/assets/images/default-user.png';
            }
            $html .= '<div class="ba-post-author"><div class="ba-post-author-image"><div class="ba-overlay"></div><a href="'.
                $url.'" style="background-image: url('.JUri::root().$author->avatar.');"></a></div>'.
                '<div class="ba-post-author-content"><a href="'.$url.'"></a><div class="ba-post-author-title-wrapper">'.
                '<h3 class="ba-post-author-title"><a href="'.$url.'">'.$author->title.'</a></h3></div>'.
                '<div class="ba-post-author-description">'.$author->description.'</div>';

            $socials = json_decode($author->author_social);
            $socialHTML = '';
            foreach ($socials as $key => $social) {
                if (!empty($social->link)) {
                    $socialHTML .= '<a target="_blank" href="'.$social->link.'" class="'.$social->icon.'"></a>';
                }
            }
            if (!empty($socialHTML)) {
                $html .= '<div class="ba-post-author-social-wrapper">'.$socialHTML.'</div>';
            }
            $html .= '</div></div>';
        }
        if (empty($html)) {
            $html = self::getEmptyList();
        }

        return $html;
    }

    public static function getPostTagsData($id)
    {
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $input = JFactory::getApplication()->input;
        $edit_type = $input->get('edit_type', '', 'string');
        if ($edit_type == 'post-layout') {
            $page = self::getPostLayoutPage($id);
            if ($page) {
                $id = $page;
            } else {
                $id = 0;
            }
        }
        $query = $db->getQuery(true)
            ->select('m.tag_id as id')
            ->from('#__gridbox_tags_map AS m')
            ->where('m.page_id = '.$id)
            ->select('t.title')
            ->leftJoin('`#__gridbox_tags` AS t'
                . ' ON '
                . $db->quoteName('m.tag_id')
                . ' = ' 
                . $db->quoteName('t.id')
            )
            ->where('t.published = 1')
            ->where('t.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('t.access in ('.$groups.')')
            ->select('p.app_id, p.page_category')
            ->leftJoin('`#__gridbox_pages` AS p'
                . ' ON '
                . $db->quoteName('m.page_id')
                . ' = ' 
                . $db->quoteName('p.id')
            );
        $db->setQuery($query);
        $tags = $db->loadObjectList();

        return $tags;
    }

    public static function getPostTags($id)
    {
        $tags = self::getPostTagsData($id);
        $html = '';
        foreach ($tags as $tag) {
            $url = self::getGridboxTagLinks($tag->id, $tag->app_id);
            $html .= '<a href="'.JRoute::_($url).'" class="ba-btn-transition fields-post-tags"><span>'.$tag->title.'</span></a>';
        }

        return $html;
    }

    public static function prepareBlogCategories($obj)
    {
        $array = array('desktop', 'tablet', 'phone', 'laptop', 'tablet-portrait', 'phone-portrait');
        $object = self::getOptions('categories');
        $object = self::object_extend($object, $obj);
        $obj = $object;
        $obj->desktop->view->image = false;
        $obj->desktop->view->intro = false;
        foreach ($array as $view) {
            if (!isset($obj->{$view}) || (!isset($obj->{$view}->{'nav-typography'}) && !isset($obj->{$view}->{'nav-hover'}))) {
                continue;
            }
            if (isset($obj->{$view}->title)) {
                $obj->{$view}->title->margin->bottom = $obj->{$view}->title->margin->top = 0;
                $obj->{$view}->info->margin->bottom = $obj->{$view}->info->margin->top = 0;
            }
            if (!isset($obj->{$view}->title) && (isset($obj->{$view}->{'nav-typography'}) || isset($obj->{$view}->{'nav-hover'}))) {
                $obj->{$view}->title = new stdClass();
                $obj->{$view}->info = new stdClass();
            }
            if (isset($obj->{$view}->{'nav-typography'})) {
                $obj->{$view}->title->typography = $obj->{$view}->{'nav-typography'};
                $obj->{$view}->info->typography = $obj->{$view}->{'nav-typography'};
                unset($obj->{$view}->{'nav-typography'});
            }
            if (isset($obj->{$view}->{'nav-hover'})) {
                $obj->{$view}->title->hover = $obj->{$view}->{'nav-hover'};
                $obj->{$view}->info->hover = $obj->{$view}->{'nav-hover'};
                unset($obj->{$view}->{'nav-hover'});
            }
        }

        return $obj;
    }

    public static function checkPostTags($body, $id)
    {
        $regex = '/\[blog_post_tags\]/i';
        preg_match_all($regex, $body, $matches, PREG_SET_ORDER);
        foreach ($matches as $key => $value) {
            $str = self::getPostTags($id);
            $body = @preg_replace("|\[blog_post_tags\]|", $str, $body, 1);
        }

        return $body;
    }

    public static function getAppId($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('app_id')
            ->from('#__gridbox_pages')
            ->where('id = '.$id);
        $db->setQuery($query);
        $app = $db->loadResult();
        if (empty($app)) {
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_app')
                ->where('type <> '.$db->quote('single'))
                ->where('type <> '.$db->quote('system_apps'))
                ->order('id desc');
            $db->setQuery($query);
            $app = $db->loadResult();
        }

        return $app;
    }

    public static function getCustomerInfo()
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

    public static function getCustomerInfoHTML($cart = null)
    {
        $info = self::getCustomerInfo();
        $html  = '';
        $db = JFactory::getDbo();
        $user_id = JFactory::getUser()->id;
        foreach ($info as $obj) {
            if (!empty($user_id)) {
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_store_user_info')
                    ->where('user_id = '.$user_id)
                    ->where('customer_id = '.$obj->id);
                $db->setQuery($query);
                $customer = $db->loadObject();
            } else if ($cart) {
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_store_order_customer_info')
                    ->where('cart_id = '.$cart->id)
                    ->where('customer_id = '.$obj->id);
                $db->setQuery($query);
                $customer = $db->loadObject();
            } else {
                $customer = null;
            }
            include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/store/checkout-form-fields.php';
            $html .= $out;
        }

        return $html;
    }

    public static function getBlogTags($id, $category = '', $limit = 0)
    {
        $html = '';
        if (!empty($id)) {
            $db = JFactory::getDbo();
            $user = JFactory::getUser();
            $groups = $user->getAuthorisedViewLevels();
            $groups = implode(',', $groups);
            $query = $db->getQuery(true)
                ->select('DISTINCT t.title, t.id')
                ->from('`#__gridbox_tags` AS t')
                ->where('t.published = 1')
                ->where('t.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
                ->where('t.access in ('.$groups.')')
                ->order('t.hits desc')
                ->leftJoin('`#__gridbox_tags_map` AS m'
                    . ' ON '
                    . $db->quoteName('m.tag_id')
                    . ' = ' 
                    . $db->quoteName('t.id')
                )
                ->leftJoin('`#__gridbox_pages` AS p'
                    . ' ON '
                    . $db->quoteName('m.page_id')
                    . ' = ' 
                    . $db->quoteName('p.id')
                )
                ->where('p.app_id = '.$id)
                ->where('p.page_category <> '.$db->quote('trashed'));
            if (!empty($category)) {
                $query->where('p.page_category in ('.$category.')');
            }
            $db->setQuery($query, 0, $limit);
            $tags = $db->loadObjectList();
            foreach ($tags as $tag) {
                $url = self::getGridboxTagLinks($tag->id, $id);
                $html .= '<a href="'.JRoute::_($url).'" class="ba-btn-transition"><span>'.$tag->title.'</span></a>';
            }
        }
        if (empty($html)) {
            $html = self::getEmptyList();
        }

        return $html;
    }

    public static function getBlogCategories($id, $parent = 0)
    {
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('id, title, app_id, image, description')
            ->from('#__gridbox_categories')
            ->where('published = 1')
            ->where('app_id = '.$db->quote($id))
            ->where('parent = '.$db->quote($parent))
            ->where('language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('access in ('.$groups.')')
            ->order('order_list ASC');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as $item) {
            $query = $db->getQuery(true)
                ->select('COUNT(id)')
                ->from('`#__gridbox_pages`')
                ->where('page_category = '.$item->id)
                ->where('published = 1')
                ->where('created <= '.$db->quote($date))
                ->where('(end_publishing = '.$nullDate.' OR end_publishing >= '.$db->quote($date).')')
                ->where('language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
                ->where('page_access in ('.$groups.')');
            $db->setQuery($query);
            $item->count = $db->loadResult();
            $item->childs = self::getBlogCategories($id, $item->id);
            foreach ($item->childs as $child) {
                $item->count += $child->count;
            }
        }

        return $items;
    }

    public static function getBlogCategoriesChilds($categories, $level = 0)
    {
        $html = '';
        $input = JFactory::getApplication()->input;
        $option = $input->get('option', '', 'string');
        $view = $input->get('view', '', 'string');
        $app = $input->get('app', '', 'string');
        $id = $input->get('id', '', 'string');
        foreach ($categories as $category) {
            $url = self::getGridboxCategoryLinks($category->id, $category->app_id);
            $url = JRoute::_($url);
            $className = '';
            if ($option == 'com_gridbox' && $view == 'blog' && $app == $category->app_id && $id == $category->id) {
                $className .= ' active';
            }
            $html .= '<a class="ba-app-sub-category'.$className.'" style="--sub-category-level: '.$level;
            $html .= '" href="'.$url.'" data-level="'.$level.'"><span>'.$category->title;
            $html .= '</span><span class="ba-app-category-counter">('.$category->count.')</span></a>';
            if (!empty($category->childs)) {
                $html .= self::getBlogCategoriesChilds($category->childs, $level + 1);
            }
        }

        return $html;
    }

    public static function getBlogCategoriesHtml($categories, $max = 75)
    {
        $html = '';
        $input = JFactory::getApplication()->input;
        $option = $input->get('option', '', 'string');
        $view = $input->get('view', '', 'string');
        $app = $input->get('app', '', 'string');
        $id = $input->get('id', '', 'string');
        foreach ($categories as $category) {
            $url = self::getGridboxCategoryLinks($category->id, $category->app_id);
            $url = JRoute::_($url);
            $className = '';
            if ($option == 'com_gridbox' && $view == 'blog' && $app == $category->app_id && $id == $category->id) {
                $className .= ' active';
            }
            $html .= '<div class="ba-blog-post'.$className.'">';
            if (!empty($category->image)) {
                $image = (strpos($category->image, 'balbooa.com') === false ? JUri::root() : '').$category->image;
                $html .= '<div class="ba-blog-post-image"><img src="'.$image.'" alt="'.$category->title;
                $html .= '"><div class="ba-overlay"></div><a href="'.$url.'" style="background-image: url(';
                $html .= $image.');"></a></div>';
            }
            $htmlTag = isset(self::$editItem->tag) ? self::$editItem->tag : 'h3';
            $html .= '<div class="ba-blog-post-content"><a href="'.$url.'"></a><div class="ba-blog-post-title-wrapper">';
            $html .= '<'.$htmlTag.' class="ba-blog-post-title"><a href="'.$url.'"><span>'.$category->title;
            $html .= '</span><span class="ba-app-category-counter">('.$category->count.')</span></a></'.$htmlTag.'></div>';
            if (!empty($category->childs)) {
                $childs = self::getBlogCategoriesChilds($category->childs);
                $html .= '<div class="ba-blog-post-info-wrapper">'.$childs.'</div>';
            }
            $html .= '</div></div>';
        }
        if (empty($html)) {
            $html = self::getEmptyList();
        }

        return $html;
    }

    public static function getRecentComments($id, $sorting, $limit, $max, $category = '')
    {
        $order = 'c.date desc';
        if ($sorting == 'popular') {
            $order = 'c.likes desc';
        } else if ($sorting == 'random') {
            $order = 'RAND() desc';
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('c.*, p.title, u.email AS user_email')
            ->from('#__gridbox_comments AS c')
            ->where('c.status = '.$db->quote('approved'))
            ->where('p.app_id = '.$id)
            ->leftJoin('`#__gridbox_pages` AS p ON '.$db->quoteName('p.id').' = '.$db->quoteName('c.page_id'))
            ->leftJoin('#__users AS u ON u.id = c.user_id')
            ->order($order);
        if (!empty($category)) {
            $query->where('p.page_category in ('.$category.')');
        }
        $db->setQuery($query, 0, $limit);
        $comments = $db->loadObjectList();
        $html = '';
        foreach ($comments as $comment) {
            $url = JUri::root().'index.php/commentID-'.$comment->id;
            if (empty($comment->avatar)) {
                if ($comment->user_type == 'user' && !empty($comment->user_email)) {
                    $comment->email = $comment->user_email;
                }
                $avatar = self::getCommentsUserAvatar($comment->email);
            } else {
                $avatar = $comment->avatar;
            }
            $message = $comment->message;
            if ($message && mb_strlen($message) != 0 && $max != 0) {
                $text = mb_substr($message, 0, $max);
                if (mb_strlen($message) > $max) {
                    $text .= '...';
                }
                $introStr = '<div class="ba-blog-post-intro-wrapper">'.$text.'</div>';
            } else {
                $introStr = '';
            }
            $time = time() - strtotime($comment->date);
            $hour = 60 * 60;
            if ($time < 60) {
                $comment->date = '1 '.JText::_('MINUTES_AGO');
            } else if ($time < $hour) {
                $comment->date = floor($time / 60).' '.JText::_('MINUTES_AGO');
            } else if ($time < 86400) {
                $comment->date = floor($time / $hour).' '.JText::_('HOURS_AGO');
            } else {
                $comment->date = self::getPostDate($comment->date);
            }
            $htmlTag = isset(self::$editItem->tag) ? self::$editItem->tag : 'h3';
            $titleStr = '<a href="'.$url.'"></a><div class="ba-blog-post-title-wrapper"><';
            $titleStr .= $htmlTag.' class="ba-blog-post-title">'.$comment->name.' '.JText::_('COMMENTS_ON').' <a href="'.$url;
            $titleStr .= '">'.$comment->title.'</a></'.$htmlTag.'></div>';
            $html .= '<div class="ba-blog-post"><div class="ba-blog-post-image"><img src="'.$avatar.'" alt="'.$comment->name;
            $html .= '" onerror="this.src = JUri+\'components/com_gridbox/assets/images/default-user.png\'; ';
            $html .= 'this.parentNode.querySelector(\'a\').style.backgroundImage = \'url(\'+this.src+\')\';">';
            $html .= '<div class="ba-overlay"></div><a href="'.$url.'" style="background-image: url('.$avatar;
            $html .= ');"></a></div><div class="ba-blog-post-content">';
            $html .= $titleStr.'<div class="ba-blog-post-info-wrapper"><span class="ba-blog-post-date">'.$comment->date;
            $html .= '</span></div>'.$introStr.'</div></div>';
        }
        if (empty($html)) {
            $html = self::getEmptyList();
        }

        return $html;
    }

    public static function getRecentReviews($id, $sorting, $limit, $max, $category = '')
    {
        $order = 'c.date desc';
        if ($sorting == 'popular') {
            $order = 'c.likes desc';
        } else if ($sorting == 'random') {
            $order = 'RAND() desc';
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('c.*, p.title, u.email AS user_email')
            ->from('#__gridbox_reviews AS c')
            ->where('c.status = '.$db->quote('approved'))
            ->where('parent = 0')
            ->where('p.app_id = '.$id)
            ->leftJoin('`#__gridbox_pages` AS p ON '.$db->quoteName('p.id').' = '.$db->quoteName('c.page_id'))
            ->leftJoin('#__users AS u ON u.id = c.user_id')
            ->order($order);
        if (!empty($category)) {
            $query->where('p.page_category in ('.$category.')');
        }
        $db->setQuery($query, 0, $limit);
        $reviews = $db->loadObjectList();
        $html = '';
        foreach ($reviews as $review) {
            $url = JUri::root().'index.php/reviewID-'.$review->id;
            if (empty($review->avatar)) {
                if ($review->user_type == 'user' && !empty($review->user_email)) {
                    $review->email = $review->user_email;
                }
                $avatar = self::getReviewsUserAvatar($review->email);
            } else {
                $avatar = $review->avatar;
            }
            $message = $review->message;
            if ($message && mb_strlen($message) != 0 && $max != 0) {
                $text = mb_substr($message, 0, $max);
                if (mb_strlen($message) > $max) {
                    $text .= '...';
                }
                $introStr = '<div class="ba-blog-post-intro-wrapper">'.$text.'</div>';
            } else {
                $introStr = '';
            }
            $time = time() - strtotime($review->date);
            $hour = 60 * 60;
            if ($time < 60) {
                $review->date = '1 '.JText::_('MINUTES_AGO');
            } else if ($time < $hour) {
                $review->date = floor($time / 60).' '.JText::_('MINUTES_AGO');
            } else if ($time < 86400) {
                $review->date = floor($time / $hour).' '.JText::_('HOURS_AGO');
            } else {
                $review->date = self::getPostDate($review->date);
            }
            $htmlTag = isset(self::$editItem->tag) ? self::$editItem->tag : 'h3';
            $titleStr = '<a href="'.$url.'"></a><div class="ba-blog-post-title-wrapper"><';
            $titleStr .= $htmlTag.' class="ba-blog-post-title"><span class="ba-reviews-name">'.$review->name;
            $titleStr .= '</span> <span class="ba-reviews-source">';
            $titleStr .= JText::_('COMMENTS_ON').'</span> <a class="ba-reviews-source" href="'.$url;
            $titleStr .= '">'.$review->title.'</a></'.$htmlTag.'></div>';
            $html .= '<div class="ba-blog-post"><div class="ba-blog-post-image"><img src="'.$avatar.'" alt="'.$review->name;
            $html .= '" onerror="this.src = JUri+\'components/com_gridbox/assets/images/default-user.png\'; ';
            $html .= 'this.parentNode.querySelector(\'a\').style.backgroundImage = \'url(\'+this.src+\')\';">';
            $html .= '<div class="ba-overlay"></div><a href="'.$url.'" style="background-image: url('.$avatar;
            $html .= ');"></a></div><div class="ba-blog-post-content">';
            $html .= $titleStr.'<div class="ba-blog-post-info-wrapper"><span class="ba-blog-post-date">'.$review->date;
            $html .= '</span></div><div class="ba-review-stars-wrapper">';
            for ($i = 1; $i < 6; $i++) {
                $html .= '<i class="zmdi zmdi-star'.($i <= $review->rating ? ' active' : '');
                $html .= '" data-rating="'.$i.'" style="width:'.($i <= $review->rating ? 'auto' : '0').';"></i>';
            }
            $html .= '</div>'.$introStr.'</div></div>';
        }
        if (empty($html)) {
            $html = self::getEmptyList();
        }

        return $html;
    }

    public static function getRecentPostsPagination($id, $limit, $category = '', $featured = false, $active, $type)
    {
        if (!$id || empty($type)) {
            return '';
        }
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('COUNT(p.id)')
            ->from('#__gridbox_pages AS p')
            ->where('p.app_id = '.$id)
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->leftJoin('`#__gridbox_categories` AS c'
                . ' ON '
                . $db->quoteName('p.page_category')
                . ' = ' 
                . $db->quoteName('c.id')
            )
            ->where('c.published = 1')
            ->where('c.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('c.access in ('.$groups.')');
        if ($featured) {
            $query->where('p.featured = 1');
        }
        if (!empty($category)) {
            $query->where('p.page_category in ('.$category.')');
        }
        $db->setQuery($query);
        $count = $db->loadResult();
        if ($count == 0) {
            return '';
        }
        if ($limit == 0) {
            $limit = 1;
        }
        $pages = ceil($count / $limit);
        if ($pages == 1) {
            return '';
        }
        $html = '';
        if ($active != $pages - 1) {
            $next = $active == $pages - 1 ? $pages : $active + 2;
            $style = ($type == 'infinity' || ($type == 'load-more-infinity' && $active > 0) ? 'style="display:none !important"' : '');
            $html .= '<div class="ba-blog-posts-pagination"'.$style;
            $html .= '><span><a href="'.JRoute::_('&page='.$next).'" class="ba-btn-transition">';
            $html .= JText::_('LOAD_MORE').'</a></span></div>';
        }

        return $html;
    }

    public static function getRecentPostsQuery($id, $category = '', $featured = false, $order = '')
    {
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $levels = $user->getAuthorisedViewLevels();
        $groups = implode(',', $levels);
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $languages = $db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*');
        $query = $db->getQuery(true)
            ->from('#__gridbox_pages AS p')
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$languages.')')
            ->where('p.page_access in ('.$groups.')')
            ->leftJoin('`#__gridbox_categories` AS c ON p.page_category = c.id')
            ->leftJoin('`#__gridbox_app` AS a ON p.app_id = a.id')
            ->where('c.published = 1')
            ->where('c.language in ('.$languages.')')
            ->where('c.access in ('.$groups.')');
        if ($order != 'top_selling') {
            $query->where('p.app_id = '.$id);
        }
        if ($featured && $order != 'top_selling') {
            $query->where('p.featured = 1');
        }
        if (!empty($category) && $order != 'top_selling') {
            $query->where('p.page_category in ('.$category.')');
        }

        return $query;
    }

    public static function getRecentPosts($id, $order, $limit, $max, $category = '', $featured = false, $start = 0, $not = '')
    {
        if (!$id || is_object($id)) {
            return self::getEmptyList();
        }
        $db = JFactory::getDbo();
        $pks = array();
        if ($order == 'top_selling') {
            $query = self::getRecentPostsQuery($id, $category, $featured, $order)
                ->select('op.product_id, COUNT(op.product_id) AS count')
                ->leftJoin('#__gridbox_store_order_products AS op ON op.product_id = p.id')
                ->where('o.published = 1')
                ->where('o.status = '.$db->quote('completed'))
                ->leftJoin('#__gridbox_store_orders AS o ON o.id = op.order_id')
                ->group('op.product_id')
                ->order('count DESC');
            if (!empty($not)) {
                $query->where('p.id NOT IN('.$not.')');
            }
            $db->setQuery($query);
            $products = $db->loadObjectList();
            foreach ($products as $product) {
                $pks[] = $product->product_id;
            }
            $length = count($pks);
            if ($length != 0 && ($length < $limit || !empty($not))) {
                $query = self::getRecentPostsQuery($id, $category, $featured, $order)
                    ->select('p.id')
                    ->where('p.id NOT IN('.implode(', ', $pks).')')
                    ->where('a.type = '.$db->quote('products'))
                    ->order('p.id ASC');
                if (!empty($not)) {
                    $query->where('p.id NOT IN('.$not.')');
                }
                $l = !empty($not) ? 0 : $limit - count($pks);
                $db->setQuery($query, 0, $l);
                $products = $db->loadObjectList();
                foreach ($products as $product) {
                    $pks[] = $product->id;
                }
            }
            if ($length == 0) {
                $order = 'id';
            }
            $dir = ' ASC';
        } else if ($order == 'order_list') {
            $dir = ' ASC';
            if (empty($category)) {
                $order = 'root_order_list';
            }
        } else {
            $dir = ' DESC';
        }
        if ($order == 'random') {
            $order = 'RAND()';
        } else if ($order != 'top_selling') {
            $order = 'p.'.$order;
        }
        if (!empty($not) && $order != 'top_selling') {
            $order = 'p.id';
        }
        $query = self::getRecentPostsQuery($id, $category, $featured, $order)
            ->select('p.id, p.title, p.intro_text, p.created, p.intro_image, p.page_category, p.app_id,
                p.meta_title, c.title as category, a.type');
        if ($order != 'top_selling') {
            $query->order($order.$dir);
        } else if (!empty($pks)) {
            $query->where('p.id IN ('.implode(', ', $pks).')')
                ->order('FIELD(p.id, '.implode(', ', $pks).')');
        }
        if (empty($not)) {
            $db->setQuery($query, $start, $limit);
            $pages = $db->loadObjectList();
        } else {
            $db->setQuery($query);
            $data = $db->loadObjectList();
            $notArray = explode(',', $not);
            $result = array();
            foreach ($data as $key => $value) {
                if (!in_array($value->id, $notArray)) {
                    $result[] = $value;
                }
            }
            if (count($result) <= $limit) {
                $pages = $result;
            } else {
                $keys = array_rand($result, $limit);
                $pages = array();
                foreach ($keys as $key) {
                    $pages[] = $result[$key];
                }
            }
        }
        $html = '';
        if (is_object(self::$editItem) && self::$editItem->type == 'recent-posts-slider') {
            include JPATH_ROOT.'/components/com_gridbox/views/layout/blog-posts-slider.php';
        } else {
            include JPATH_ROOT.'/components/com_gridbox/views/layout/blog-posts.php';
        }
        foreach ($pages as $key => $page) {
            $html .= self::getRecentPostsHTML($page, $out, $max, true, false);
        }
        if (empty($html)) {
            $html = self::getEmptyList();
        }

        return $html;
    }

    public static function getRecentlyViewedProducts($limit, $max)
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $edit_type = $input->get('edit_type', '', 'string');
        $id = $input->get('id', 0, 'int');
        $option = $app->input->getCmd('option', '');
        $view = $app->input->getCmd('view', '');
        if ($option != 'com_gridbox' || $view == 'blog') {
            return '';
        }
        if ($edit_type == 'post-layout') {
            $id = self::getPostLayoutPage($id);
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('a.type')
            ->from('#__gridbox_pages AS p')
            ->where('p.id = '.$id)
            ->leftJoin('#__gridbox_app AS a ON a.id = p.app_id');
        $db->setQuery($query);
        $type = $db->loadResult();
        if ($type != 'products') {
            return '';
        }
        $array = $input->cookie->get('gridbox_viewed_products', array(), 'array');
        $time = time() + 604800;
        $viewed = array($id);
        foreach ($array as $value) {
            if ($value != $id) {
                $viewed[] = $value;
            }
        }
        foreach ($viewed as $key => $value) {
            self::setcookie('gridbox_viewed_products['.$key.']', $value, $time);
        }
        if (count($viewed) != 1) {
            unset($viewed[0]);
        }
        $pks = implode(', ', $viewed);
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('DISTINCT p.id, p.title, p.intro_text, p.created, p.intro_image, p.page_category,
                p.app_id, p.meta_title, a.type, c.title as category')
            ->from('#__gridbox_pages AS p')
            ->where('p.id IN ('.$pks.')')
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->leftJoin('`#__gridbox_categories` AS c ON p.page_category = c.id')
            ->leftJoin('`#__gridbox_app` AS a ON p.app_id = a.id')
            ->where('c.published = 1')
            ->where('c.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('c.access in ('.$groups.')')
            ->order('FIELD(p.id, '.$pks.')');
        $db->setQuery($query, 0, $limit);
        $pages = $db->loadObjectList();
        $html = '';
        include JPATH_ROOT.'/components/com_gridbox/views/layout/blog-posts-slider.php';
        foreach ($pages as $key => $page) {
            $html .= self::getRecentPostsHTML($page, $out, $max, true, false);
        }
        if (empty($html)) {
            $html = self::getEmptyList();
        }

        return $html;


    }

    public static function getRelatedPosts($id, $relate, $limit, $max, $pageId = null)
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $edit_type = $input->get('edit_type', '', 'string');
        if ($edit_type == 'post-layout') {
            if (!$pageId) {
                $pageId = $app->input->get('id', 0, 'int');
            }
            $page = self::getPostLayoutPage($pageId);
            if ($page) {
                $pageId = $page;
            } else {
                $pageId = 0;
            }
        }
        if (!$pageId) {
            $pageId = $app->input->get('id', 0, 'int');
            $option = $app->input->getCmd('option', '');
            $view = $app->input->getCmd('view', '');
            if ($option != 'com_gridbox' || $view == 'blog') {
                return '';
            }
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('app_id')
            ->from('#__gridbox_pages')
            ->where('id = '.$pageId);
        $db->setQuery($query);
        $id = $db->loadResult();
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $date = date("Y-m-d H:i:s");
        if ($relate == 'tags') {
            $query = $db->getQuery(true)
                ->select('tag_id')
                ->from('#__gridbox_tags_map')
                ->where('page_id = '.$pageId);
            $db->setQuery($query);
            $tags = $db->loadObjectList();
            $array = array();
            if (empty($tags)) {
                return self::getEmptyList();
            }
            foreach ($tags as $tag) {
                $array[] = $tag->tag_id;
            }
            $array = implode(',', $array);
        } else if ($relate == 'categories') {
            $query = $db->getQuery(true)
                ->select('page_category')
                ->from('#__gridbox_pages')
                ->where('id = '.$pageId);
            $db->setQuery($query);
            $category = $db->loadResult();
            if (empty($category)) {
                return self::getEmptyList();
            }
        } else if ($relate == 'custom') {
            $query = $db->getQuery(true)
                ->select('p.id')
                ->from('#__gridbox_pages AS p')
                ->where('r.product_id = '.$pageId)
                ->leftJoin('#__gridbox_store_related_products AS r ON r.related_id = p.id')
                ->order('r.order_list ASC');
            $db->setQuery($query);
            $custom = $db->loadObjectList();
            if (empty($custom)) {
                return self::getEmptyList();
            }
        }
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('DISTINCT p.id, p.title, p.intro_text, p.created, p.intro_image, p.page_category,
                p.app_id, p.meta_title, a.type, c.title as category')
            ->from('#__gridbox_pages AS p')
            ->where('p.id <> '.$pageId)
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->order('p.hits desc')
            ->leftJoin('`#__gridbox_categories` AS c ON p.page_category = c.id')
            ->leftJoin('`#__gridbox_app` AS a ON p.app_id = a.id')
            ->where('c.published = 1')
            ->where('c.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('c.access in ('.$groups.')');
        if ($relate != 'custom') {
            $query->where('p.app_id = '.$id);
        }
        if ($relate == 'tags') {
            $query->leftJoin('`#__gridbox_tags_map` AS m ON p.id = m.page_id')
                ->where('m.tag_id in('.$array.')')
                ->leftJoin('`#__gridbox_tags` AS t ON t.id = m.tag_id')
                ->where('t.published = 1')
                ->where('t.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
                ->where('t.access in ('.$groups.')');
        } else if ($relate == 'categories') {
            $query->where('p.page_category = '.$category);
        } else if ($relate == 'custom') {
            $pks = array();
            foreach ($custom as $value) {
                $pks[] = $value->id;
            }
            $query->where('p.id IN ('.implode(', ', $pks).')');
        }
        $db->setQuery($query, 0, $limit);
        $pages = $db->loadObjectList();
        $html = '';
        if (is_object(self::$editItem) && self::$editItem->type == 'related-posts-slider') {
            include JPATH_ROOT.'/components/com_gridbox/views/layout/blog-posts-slider.php';
        } else {
            include JPATH_ROOT.'/components/com_gridbox/views/layout/blog-posts.php';
        }
        foreach ($pages as $key => $page) {
            $html .= self::getRecentPostsHTML($page, $out, $max, true, false);
        }
        if (empty($html)) {
            $html = self::getEmptyList();
        }

        return $html;
    }

    public static function getPostNavigation($max, $id = null)
    {
        if (!$id) {
            $app = JFactory::getApplication();
            $id = $app->input->get('id', 0, 'int');
            $option = $app->input->getCmd('option', '');
            $view = $app->input->getCmd('view', '');
            if ($option != 'com_gridbox' || $view == 'blog') {
                return '';
            }
        }
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $date = date("Y-m-d H:i:s");
        $db = JFactory::getDbo();
        $input = JFactory::getApplication()->input;
        $edit_type = $input->get('edit_type', '', 'string');
        if ($edit_type == 'post-layout') {
            $page = self::getPostLayoutPage($id);
            if ($page) {
                $id = $page;
            } else {
                $id = 0;
            }
        }
        $query = $db->getQuery(true)
            ->select('created, app_id')
            ->from('#__gridbox_pages')
            ->where('id = '.$id);
        $db->setQuery($query);
        $obj = $db->loadObject();
        if (empty($obj->app_id)) {
            return self::getEmptyList();
        }
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('p.id, p.title, p.intro_text, p.created, p.intro_image, p.page_category, p.app_id, c.title as category, a.type')
            ->from('#__gridbox_pages AS p')
            ->where('p.id <> '.$id)
            ->where('p.app_id = '.$obj->app_id)
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('p.created <= '.$db->quote($obj->created))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->order('p.created desc')
            ->leftJoin('`#__gridbox_categories` AS c ON p.page_category = c.id')
            ->leftJoin('`#__gridbox_app` AS a ON p.app_id = a.id')
            ->where('c.published = 1')
            ->where('c.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('c.access in ('.$groups.')');
        $db->setQuery($query);
        $prev = $db->loadObject();
        $query = $db->getQuery(true)
            ->select('p.id, p.title, p.intro_text, p.created, p.intro_image, p.page_category, p.app_id, a.type')
            ->from('#__gridbox_pages AS p')
            ->where('p.id <> '.$id)
            ->where('p.app_id = '.$obj->app_id)
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('p.created >= '.$db->quote($obj->created))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->order('p.created asc')
            ->select('c.title as category')
            ->leftJoin('`#__gridbox_categories` AS c ON p.page_category = c.id')
            ->leftJoin('`#__gridbox_app` AS a ON p.app_id = a.id')
            ->where('c.published = 1')
            ->where('c.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('c.access in ('.$groups.')');
        $db->setQuery($query);
        $next = $db->loadObject();
        if (!$next) {
            $query = $db->getQuery(true)
                ->select('p.id, p.title, p.intro_text, p.created, p.intro_image, p.page_category, p.app_id, p.meta_title, a.type')
                ->from('#__gridbox_pages AS p')
                ->where('p.app_id = '.$obj->app_id)
                ->where('p.page_category <> '.$db->quote('trashed'))
                ->where('p.published = 1')
                ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
                ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
                ->where('p.page_access in ('.$groups.')')
                ->order('p.created asc')
                ->select('c.title as category')
                ->leftJoin('`#__gridbox_categories` AS c ON p.page_category = c.id')
                ->leftJoin('`#__gridbox_app` AS a ON p.app_id = a.id')
                ->where('c.published = 1')
                ->where('c.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
                ->where('c.access in ('.$groups.')');
            $db->setQuery($query);
            $next = $db->loadObject();
        }
        if (!$prev) {
            $query = $db->getQuery(true)
                ->select('p.id, p.title, p.intro_text, p.created, p.intro_image, p.page_category, p.app_id, p.meta_title, a.type')
                ->from('#__gridbox_pages AS p')
                ->where('p.app_id = '.$obj->app_id)
                ->where('p.page_category <> '.$db->quote('trashed'))
                ->where('p.published = 1')
                ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
                ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
                ->where('p.page_access in ('.$groups.')')
                ->order('p.created desc')
                ->select('c.title as category')
                ->leftJoin('`#__gridbox_categories` AS c ON p.page_category = c.id')
                ->leftJoin('`#__gridbox_app` AS a ON p.app_id = a.id')
                ->where('c.published = 1')
                ->where('c.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
                ->where('c.access in ('.$groups.')');
            $db->setQuery($query);
            $prev = $db->loadObject();
        }
        $html = '';
        include JPATH_ROOT.'/components/com_gridbox/views/layout/blog-posts.php';
        if (isset($prev->id)) {
            $html .= self::getRecentPostsHTML($prev, $out, $max, true, false);
        }
        if (isset($next->id)) {
            $html .= self::getRecentPostsHTML($next, $out, $max, true, false);
        }
        if (empty($html)) {
            $html = self::getEmptyList();
        }
        
        return $html;
    }

    public static function translateMonth($n)
    {
        $month = array(1 => JText::_('JANUARY'), 2 => JText::_('FEBRUARY'), 3 => JText::_('MARCH'),
            4 => JText::_('APRIL'), 5 => JText::_('MAY'), 6 => JText::_('JUNE'),
            7 => JText::_('JULY'), 8 => JText::_('AUGUST'), 9 => JText::_('SEPTEMBER'),
            10 => JText::_('OCTOBER'), 11 => JText::_('NOVEMBER'), 12 =>JText::_('DECEMBER'));

        return $month[$n];
    }

    public static function getPostDate($created)
    {
        /*
        if (self::$dateFormat == 'l, d F Y') {
            $date = JHtml::date($created, self::$dateFormat);
        } else {
            $timestamp = strtotime($created);
            $date = date(self::$dateFormat, $timestamp);
            if (strpos(self::$dateFormat, 'F') !== false || strpos(self::$dateFormat, 'M') !== false) {
                $month  = date('n', $timestamp);
                $replace = self::translateMonth($month);
                $search = date('F', $timestamp);
                if (self::$dateFormat == 'M') {
                    $replace = substr($replace, 0, 3);
                }
                $date = str_replace($search, $replace, $date);
            }
        }
        */
        $date = JHtml::date($created, self::$dateFormat);

        return $date;
    }

    public static function getRecentPostsHTML($page, $out, $max, $cat = true, $view = true, $intro = true, $btn = true)
    {
        $type = $page->app_id == 0 || $page->page_category == '' ? 'single' : 'blog';
        $url = self::getGridboxPageLinks($page->id, $type, $page->app_id, $page->page_category);
        $input = JFactory::getApplication()->input;
        $pageView = $input->get('view', 'gridbox', 'string');
        $className = '';
        $fields = self::getCategoryListFields($page->id, $page->app_id);
        if (!empty($fields) && (empty(self::$editItem) ||
            (!empty(self::$editItem) && self::$editItem->type != 'search-result' && self::$editItem->type != 'store-search-result'))) {
            $desktopFiles = self::getDesktopFieldFiles($page->id);
            $fieldsStr = '<div class="ba-blog-post-fields"><div class="ba-blog-post-field-row-wrapper">';
            foreach ($fields as $field) {
                if (!isset($field->value)) {
                    $field->value = '';
                }
                if ($pageView != 'gridbox' && (empty($field->value) || $field->value == '[]')) {
                    continue;
                }
                $options = json_decode($field->options);
                $label = $field->label;
                $value = '';
                if (empty($field->value)) {
                    $value = $field->value;
                } else if ($field->field_type == 'select' || $field->field_type == 'radio') {
                    foreach ($options->items as $option) {
                        if ($option->key == $field->value) {
                            if (!empty($value)) {
                                $value .= ', ';
                            }
                            $value .= $option->title;
                        }
                    }
                } else if ($field->field_type == 'checkbox') {
                    $fieldValue = json_decode($field->value);
                    foreach ($options->items as $option) {
                        if (in_array($option->key, $fieldValue)) {
                            $value .= '<span class="ba-blog-post-field-checkbox-value">'.$option->title.'</span>';
                        }
                    }
                } else if ($field->field_type == 'url') {
                    $fieldOptions = json_decode($field->options);
                    $valueOptions = json_decode($field->value);
                    $link = self::prepareGridboxLinks($valueOptions->link);
                    if (empty($link)) {
                        continue;
                    }
                    $value = '<a href="'.$link.'" '.$fieldOptions->download.' target="'.$fieldOptions->target;
                    $value .= '">'.$valueOptions->label.'</a>';
                } else if ($field->field_type == 'tag') {
                    $value = self::getPostTags($page->id);
                } else if ($field->field_type == 'time') {
                    if (!empty($field->value)) {
                        $valueOptions = json_decode($field->value);
                        $value = $valueOptions->hours.':'.$valueOptions->minutes.' '.$valueOptions->format;
                    }
                } else if ($field->field_type == 'date' || $field->field_type == 'event-date') {
                    if (!empty($field->value)) {
                        $value = self::getPostDate($field->value);
                    }
                } else if ($field->field_type == 'price' && !empty($field->value)) {
                    $fieldOptions = json_decode($field->options);
                    $thousand = $fieldOptions->thousand;
                    $separator = $fieldOptions->separator;
                    $decimals = $fieldOptions->decimals;
                    $value = self::preparePrice($field->value, $thousand, $separator, $decimals);
                    if ($fieldOptions->position == '') {
                        $value = $fieldOptions->symbol.$value;
                    } else {
                        $value .= $fieldOptions->symbol;
                    }
                } else if ($field->field_type == 'file') {
                    if (!empty($field->value)) {
                        $fieldOptions = json_decode($field->options);
                        if (is_numeric($field->value) && isset($desktopFiles->{$field->value})) {
                            $desktopFile = $desktopFiles->{$field->value};
                            $src = 'components/com_gridbox/assets/uploads/app-'.$desktopFile->app_id.'/'.$desktopFile->filename;
                        } else {
                            $src = $field->value;
                        }
                        $value = '<a href="'.JUri::root().$src.'" download>'.$fieldOptions->title.'</a>';
                    }
                } else if ($field->field_type == 'text') {
                    $value = htmlspecialchars($field->value);
                } else {
                    $value = $field->value;
                }
                $fieldsStr .= '<div class="ba-blog-post-field-row" data-id="'.$field->field_key.'"><div class="ba-blog-post-field-title">';
                $fieldsStr .= $label.'</div><div class="ba-blog-post-field-value">'.$value.'</div></div>';
            }
            $fieldsStr .= '</div></div>';
        } else {
            $fieldsStr = '';
        }
        $url = JRoute::_($url);
        $productImages = self::getProductImages($page->id, $page->app_id);
        if (isset($page->type) && $page->type == 'products') {
            $className .= ' ba-store-app-product';
            $currency = self::$store->currency;
            $data = self::getProductData($page->id);
            foreach ($data->variations as $key => $variation) {
                if (isset($variation->default) && $variation->default) {
                    $data->default = $variation;
                    $data->default->variation = $key;
                    $data->default->images = array();
                    break;
                }
            }
            include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/store/blog-post-add-to-cart.php';
            include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/store/blog-post-badge-wishlist.php';
            include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/store/blog-post-product-options.php';
        }
        if (isset($page->type) && $page->type == 'products' && isset($data->default) && !empty($data->default->images)) {
            $productImages = array();
            foreach ($data->default->images as $i => $image) {
                $img = new stdClass();
                $img->img = $image;
                $productImages[] = $img;
                if ($i == 1) {
                    break;
                }
            }
        }
        if (!empty($productImages)) {
            $page->intro_image = $productImages[0]->img;
        }
        $imageUrl = empty($page->intro_image) ? 'components/com_gridbox/assets/images/default-theme.png' : $page->intro_image;
        if (strpos($imageUrl, 'balbooa.com') === false) {
            $imageUrl = JUri::root().$imageUrl;
        }
        $date = self::getPostDate($page->created);
        $str = $out;
        if (is_object(self::$editItem) &&
            (self::$editItem->type == 'recent-posts-slider' || self::$editItem->type == 'related-posts-slider'
                || self::$editItem->type == 'recently-viewed-products')) {
            $image = '<div class="ba-slideshow-img" style="';
            if (isset($page->type) && $page->type == 'products' && !empty($productImages)) {
                foreach ($productImages as $key => $imgObj) {
                    $imgObj->img = strpos($imgObj->img, 'balbooa.com') === false ? JUri::root().$imgObj->img : $imgObj->img;
                    $image .= '--product-image-'.$key.': url('.$imgObj->img.'); ';
                }
            } else if (!empty($page->intro_image)) {
                $image .= 'background-image: url('.$imageUrl.');';
            }
            $image .= '">';
            if (isset($page->type) && $page->type == 'products') {
                $image .= $badges;
            }
            $image .= '<a href="'.$url.'"></a></div>';
        } else if (!empty($page->intro_image)) {
            $alt = !empty($page->meta_title) ? $page->meta_title : $page->title;
            $image = '<div class="ba-blog-post-image"><img src="'.$imageUrl.'" alt="'.$alt;
            $image .= '"><div class="ba-overlay"></div><a href="'.$url.'" style="';
            if (isset($page->type) && $page->type == 'products' && !empty($productImages)) {
                foreach ($productImages as $key => $imgObj) {
                    $imgObj->img = strpos($imgObj->img, 'balbooa.com') === false ? JUri::root().$imgObj->img : $imgObj->img;
                    $image .= '--product-image-'.$key.': url('.$imgObj->img.'); ';
                }
            } else {
                $image .= 'background-image: url('.$imageUrl.');';
            }
            $image .= '"></a>';
            if (isset($page->type) && $page->type == 'products') {
                $image .= $badges;
            }
            $image .= '</div>';
        } else {
            $image = '';
        }
        $dateStr = '<span class="ba-blog-post-date">'.$date.'</span>';
        if ($cat && $page->page_category != '') {
            $catUrl = self::getGridboxCategoryLinks($page->page_category, $page->app_id);
            $catStr = '<span class="ba-blog-post-category"><a href="';
            $catStr .= JRoute::_($catUrl).'">'.$page->category.'</a></span>';
        } else {
            $catStr = '';
        }
        if ($view) {
            $viewStr = '<span class="ba-blog-post-hits">'.$page->hits.' '.JText::_('VIEWS').'</span>';
        } else {
            $viewStr = '';
        }
        $comments = self::getCommentsCount($page->id);
        $viewStr .= '<span class="ba-blog-post-comments"><a href="'.$url.'#total-count-wrapper">';
        if ($comments == 0) {
            $viewStr .= JText::_('LEAVE_COMMENT');
        } else {
            $viewStr .= $comments.' '.JText::_('COMMENTS');
        }
        $viewStr .= '</a></span>';
        $reviews = self::getReviewsCount($page->id);
        if ($reviews->count == 0) {
            $reviews->rating = 0;
        }
        $reviewsStr = '<div class="ba-blog-post-reviews"><span class="ba-blog-post-rating-stars">';
        $floorRating = floor($reviews->rating);
        for ($i = 1; $i < 6; $i++) {
            $width = 'auto';
            if ($i == $floorRating + 1) {
                $width = (($reviews->rating - $floorRating) * 100).'%';
            }
            $reviewsStr .= '<i class="zmdi zmdi-star'.($i <= $floorRating ? ' active' : '').'" style="width: '.$width.'"></i>';
        }
        $reviewsStr .= '</span><a class="ba-blog-post-rating-count" href="'.$url.'#total-reviews-count-wrapper">';
        if ($reviews->count == 0) {
            $reviewsStr .= JText::_('LEAVE_REVIEW');
        } else {
            $reviewsStr .= $reviews->count.' '.JText::_('REVIEWS');
        }
        $reviewsStr .= '</a></div>';
        if ($intro && $mblen = mb_strlen($page->intro_text) != 0 && $max != 0) {
            if (strpos($page->intro_text, 'ba-search-highlighted-word') === false) {
                $text = mb_substr($page->intro_text, 0, $max);
                if ($mblen > $max) {
                    $text .= '...';
                }
            } else {
                $text = $page->intro_text;
            }
            $introStr = '<div class="ba-blog-post-intro-wrapper">'.$text.'</div>';
        } else {
            $introStr = '';
        }
        $introStr = $reviewsStr.$introStr;
        if ($btn) {
            $btnStr = '<div class="ba-blog-post-button-wrapper"><a class="ba-btn-transition" href="';
            $btnStr .= $url.'">'.(isset(self::$editItem->buttonLabel) ? self::$editItem->buttonLabel : JText::_('READ_MORE'));
            $btnStr .= '</a></div>';
        } else {
            $btnStr = '';
        }
        if (isset($page->type) && $page->type == 'products') {
            $fieldsStr = $addToCart.$fieldsStr;
        }
        $btnStr = $fieldsStr.$btnStr;
        $htmlTag = isset(self::$editItem->tag) ? self::$editItem->tag : 'h3';
        $titleStr = '<a href="'.$url.'"></a>';
        if (isset($page->type) && $page->type == 'products') {
            $titleStr .= $productOptions;
        }
        $titleStr .= '<div class="ba-blog-post-title-wrapper"><';
        $titleStr .= $htmlTag.' class="ba-blog-post-title"><a href="'.$url;
        $titleStr .= '">'.$page->title.'</a></'.$htmlTag.'></div>';
        $page->authors = self::getRecentPostAuthor($page->id);
        $authorsHtml = self::getAuthorsHtml($page->authors, 'ba-blog-post-author', $page->app_id);
        $str = str_replace('data-id="0"', 'data-id="'.$page->id.'"', $str);
        $str = str_replace('[ba-blog-post-date]', $authorsHtml.$dateStr, $str);
        $str = str_replace('[ba-blog-post-category]', $catStr, $str);
        $str = str_replace('[ba-blog-post-views]', $viewStr, $str);
        $str = str_replace('[ba-blog-post-intro]', $introStr, $str);
        $str = str_replace('[ba-blog-post-title]', $titleStr, $str);
        $str = str_replace('[ba-blog-post-image]', $image, $str);
        $str = str_replace('[ba-blog-post-btn]', $btnStr, $str);
        $str = str_replace('[classname]', $className, $str);

        return $str;
    }

    public static function getRecentPostAuthor($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('au.title, au.avatar, au.id')
            ->from('`#__gridbox_authors_map` AS au_m')
            ->where('au_m.page_id = '.$id)
            ->leftJoin('`#__gridbox_authors` AS au ON au.id = au_m.author_id')
            ->where('au.published = 1')
            ->order('au_m.id ASC');
        $db->setQuery($query);
        $authors = $db->loadObjectList();

        return $authors;
    }

    public static function addCacheData($data, $key, $subkey)
    {
        if (empty(self::$cacheData)) {
            self::$cacheData = new stdClass();
        }
        if (!isset(self::$cacheData->{$key})) {
            self::$cacheData->{$key} = new stdClass();
        }
        self::$cacheData->{$key}->{$subkey} = $data;
    }

    public static function getEditorSearchResult()
    {
        $str = '';
        $date = self::getPostDate(date('Y-m-d'));
        for ($i = 0; $i < 6; $i++) {
            $str .= '<div class="ba-blog-post'.(self::$editItem->type == ' store-search-result' ? 'ba-store-app-product' : '');
            $str .= '"><div class="ba-blog-post-image"><img src="'.JUri::root();
            $str .= 'components/com_gridbox/assets/images/default-theme.png" alt="'.JText::_('TITLE');
            $str .= '"><div class="ba-overlay"></div><a href="'.JUri::root();
            $str .= '" style="background-image: url('.JUri::root().'components/com_gridbox/assets/images/default-theme.png);"></a>';
            if (self::$editItem->type == 'store-search-result') {
                $str .= '<div class="ba-blog-post-wishlist-wrapper"><i class="zmdi zmdi-favorite"></i>';
                $str .= '<span class="ba-tooltip ba-left">'.JText::_('ADD_TO_WISHLIST').'</span></div>';
            }
            $str .= '</div><div class="ba-blog-post-content"><div class="ba-blog-post-title-wrapper">';
            $str .= '<'.self::$editItem->tag.' class="ba-blog-post-title"><a href="'.JUri::root().'">'.JText::_('PAGE_TITLE');
            $str .= '</a></'.self::$editItem->tag.'></div><div class="ba-blog-post-info-wrapper">';
            $str .= '<span class="ba-blog-post-author"><a href="#"><span class="ba-author-avatar"';
            $str .= ' style="background-image: url('.JUri::root();
            $str .= 'components/com_gridbox/assets/images/default-user.png)"></span>';
            $str .= JText::_('AUTHOR').'</a></span>';
            $str .= '<span class="ba-blog-post-date">';
            $str .= $date.'</span>';
            $str .= '<span class="ba-blog-post-category"><a href="'.JUri::root().'">';
            $str .= JText::_('CATEGORY').'</a></span><span class="ba-blog-post-comments"><a href="';
            $str .= JUri::root().'#total-count-wrapper">0 '.JText::_('COMMENTS').'</a></span>';
            $str .= '</div>';
            $str .= '<div class="ba-blog-post-reviews"><span class="ba-blog-post-rating-stars">';
            for ($j = 1; $j < 6; $j++) {
                $str .= '<i class="zmdi zmdi-star"></i>';
            }
            $str .= '</span><a class="ba-blog-post-rating-count" href="#total-reviews-count-wrapper">';
            $str .= JText::_('LEAVE_REVIEW');
            $str .= '</a></div>';
            $str .= '<div class="ba-blog-post-intro-wrapper">';
            $str .= JText::_('INTRO_TEXT').'</div>';
            if (self::$editItem->type == 'store-search-result') {
                $currency = self::$store->currency;
                $total = gridboxHelper::preparePrice(36.99, $currency->thousand, $currency->separator, $currency->decimals);
                $str .= '<div class="ba-blog-post-add-to-cart-wrapper"><div class="ba-blog-post-add-to-cart-price">';
                $str .= '<span class="ba-blog-post-add-to-cart-price-wrapper '.self::$store->currency->position;
                $str .= '"><span class="ba-blog-post-add-to-cart-price-currency">'.self::$store->currency->symbol;
                $str .= '</span><span class="ba-blog-post-add-to-cart-price-value">'.$total.'</span></span></div>';
                $str .= '<div class="ba-blog-post-add-to-cart-button"><span class="ba-blog-post-add-to-cart">';
                $str .= JText::_('ADD_TO_CART').'</span></div></div>';
            }
            $str .= '<div class="ba-blog-post-button-wrapper">';
            $str .= '<a class="ba-btn-transition" href="'.JUri::root().'">';
            $str .= (isset(self::$editItem->buttonLabel) ? self::$editItem->buttonLabel : JText::_('READ_MORE'));
            $str .= '</a></div></div></div>';
        }

        return $str;
    }

    public static function getSearchResult($search, $limit, $start, $max)
    {
        $view = JFactory::getApplication()->input->get('view', '');
        if ($view == 'gridbox') {

            return self::getEditorSearchResult();
        } else if (empty($search)) {
            return '';
        }
        $active = $start;
        $start *= $limit;
        $html = '';
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $wheres = array();
        $wheres[] = 'p.title LIKE '.$db->quote('%'.$db->escape($search, true).'%', false);
        $wheres[] = 'p.params LIKE '.$db->quote('%'.$db->escape($search, true).'%', false);
        $query = $db->getQuery(true)
            ->select('distinct option_key')
            ->from('#__gridbox_fields_data')
            ->where('value LIKE '.$db->quote('%'.$db->escape($search, true).'%'));
        $db->setQuery($query);
        $result = $db->loadObjectList();
        $array = array();
        foreach ($result as $value) {
            $array[] = $value->option_key;
        }
        $where = '(pf.value LIKE '.$db->quote('%'.$db->escape($search, true).'%');
        if (!empty($array)) {
            $subStr = implode(', ', $array);
            $where .= ' OR pf.value in ('.$subStr.')';
        }
        if ($time = strtotime($search)) {
            $dateStr = date('Y-m-d', $time);
            $where .= ' OR pf.value LIKE '.$db->quote('%'.$db->escape($dateStr, true).'%', false);
        }
        $where .= ')';
        $query = $db->getQuery(true)
            ->select('distinct p.id')
            ->from('`#__gridbox_pages` AS p')
            ->leftJoin('`#__gridbox_page_fields` AS pf ON pf.page_id = p.id')
            ->where($where)
            ->where('pf.field_type <> '.$db->quote('field-google-maps'))
            ->where('pf.field_type <> '.$db->quote('field-simple-gallery'))
            ->where('pf.field_type <> '.$db->quote('product-gallery'))
            ->where('pf.field_type <> '.$db->quote('field-slideshow'))
            ->where('pf.field_type <> '.$db->quote('product-slideshow'))
            ->where('pf.field_type <> '.$db->quote('field-video'))
            ->where('pf.field_type <> '.$db->quote('image-field'))
            ->where('pf.field_type <> '.$db->quote('file'));
        $db->setQuery($query);
        $result = $db->loadObjectList();
        $array = array();
        foreach ($result as $value) {
            $array[] = $value->id;
        }
        $subStr = implode(', ', $array);
        self::addCacheData($subStr, 'search', 'fields');
        if (!empty($subStr)) {
            $wheres[] = 'p.id in ('.$subStr.')';
        }
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('p.id, p.title, p.created, p.intro_image, p.page_category, p.app_id, p.intro_text, p.meta_title, p.params')
            ->from('#__gridbox_pages AS p')
            ->where('('.implode(' OR ', $wheres).')')
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->order('p.created desc');
        $db->setQuery($query, $start, $limit);
        $pages = $db->loadObjectList();
        include JPATH_ROOT.'/components/com_gridbox/views/layout/blog-posts.php';
        JLoader::register('SearchHelper', JPATH_ROOT.'/administrator/components/com_search/helpers/search.php');
        foreach ($pages as $key => $page) {
            $word = preg_replace('#\xE3\x80\x80#', ' ', $search);
            $words = preg_split("/\s+/u", $word);
            $needle = $words[0];
            if (empty($page->intro_text)) {
                $text = self::highLight($page->params, $search, $words, $max);
            } else {
                $text1 = self::highLight($page->intro_text, $search, $words, $max);
                $text2 = self::highLight($page->params, $search, $words, $max);
                $pos1 = strpos($text1, 'ba-search-highlighted-word');
                $pos2 = strpos($text2, 'ba-search-highlighted-word');
                if ($pos1 !== false) {
                    $text = $text1;
                } else if ($pos2 !== false) {
                    $text = $text2;
                } else if (!empty($page->intro_text)) {
                    $text = $text1;
                } else {
                    $text = $text2;
                }
            }
            $page->intro_text = $text;
            if ($page->app_id != 0) {
                $query = $db->getQuery(true)
                    ->select('c.title')
                    ->from('#__gridbox_categories AS c')
                    ->leftJoin('`#__gridbox_pages` AS p ON p.page_category = c.id')
                    ->where('p.id = '.$page->id);
                $db->setQuery($query);
                $page->category = $db->loadResult();
            }
            $html .= self::getRecentPostsHTML($page, $out, $max, true, false);
        }

        return $html;
    }

    public static function prepareSearchContent($text, $searchword, $max)
    {
        $text = preg_replace("'<script[^>]*>.*?</script>'si", '', $text);
        $text = preg_replace("'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text);
        $text = preg_replace('/[ \t\n\r\f\v]/', " ", $text);
        $text = preg_replace('/\s{2,}/', ' ', $text);
        $text = preg_replace('/{.+?}/', '', $text);
        $text = strip_tags($text);

        return self::smartSubstr($text, $searchword, $max);
    }

    public static function smartSubstr($text, $searchword, $max)
    {
        $ltext = mb_strtolower(SearchHelper::remove_accents($text));
        $length = mb_strlen($ltext);
        $lsearchword = mb_strtolower(SearchHelper::remove_accents($searchword));
        $pos = mb_strpos($ltext, $lsearchword);
        if ($pos !== false) {
            $end = $pos + $max < $length ? $max : $length - $pos;
            $text = (($pos > 0) ? '...' : '').(mb_substr($text, $pos, $end)).'...';
        }
        
        return $text;
    }

    public static function highLight($string, $needle, $words, $max)
    {
        $hl1 = '<span class="ba-search-highlighted-word">';
        $hl2 = '</span>';
        $highlighterLen = strlen($hl1.$hl2);
        $quoteStyle = version_compare(PHP_VERSION, '5.4', '>=') ? ENT_NOQUOTES | ENT_HTML401 : ENT_NOQUOTES;
        $row = html_entity_decode($string, $quoteStyle, 'UTF-8');
        $row = self::prepareSearchContent($row, $needle, $max);
        $lowerCaseRow = mb_strtolower($row);
        $transliteratedLowerCaseRow = SearchHelper::remove_accents($lowerCaseRow);
        $posCollector = array();
        $found = false;
        $lowerCaseHighlightWord = mb_strtolower($needle);
        if (($pos = mb_strpos($lowerCaseRow, $lowerCaseHighlightWord)) !== false) {
            $found = true;
        } else if (($pos = mb_strpos($transliteratedLowerCaseRow, $lowerCaseHighlightWord)) !== false) {
            $found = true;
        }
        if ($found === true) {
            $posCollector[$pos] = $needle;
        }
        if (count($posCollector)) {
            ksort($posCollector);
            $cnt = 0;
            $lastHighlighterEnd = -1;
            foreach ($posCollector as $pos => $highlightWord) {
                $pos += $cnt * $highlighterLen;
                $chkOverlap = $pos - $lastHighlighterEnd;
                if ($chkOverlap >= 0) {
                    $highlightWordLen = mb_strlen($highlightWord);
                    $row = mb_substr($row, 0, $pos) . $hl1 . mb_substr($row, $pos, $highlightWordLen)
                        .$hl2.mb_substr($row, $pos + $highlightWordLen);
                    $cnt++;
                    $lastHighlighterEnd = $pos+$highlightWordLen+$highlighterLen;
                }
            }
        }

        return $row;
    }

    public static function getStoreSearchResult($search, $limit, $start, $max)
    {
        $view = JFactory::getApplication()->input->get('view', '');
        if ($view == 'gridbox') {

            return self::getEditorSearchResult();
        } else if (empty($search)) {
            return '';
        }
        $active = $start;
        $start *= $limit;
        $html = '';
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $wheres = array();
        $wheres[] = 'p.title LIKE '.$db->quote('%'.$db->escape($search, true).'%', false);
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('p.id, p.title, p.created, p.intro_image, p.page_category, p.app_id, p.intro_text,
                p.meta_title, c.title AS category, a.type')
            ->from('#__gridbox_pages AS p')
            ->where('('.implode(' OR ', $wheres).')')
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->where('a.type ='.$db->quote('products'))
            ->leftJoin('#__gridbox_categories AS c ON p.page_category = c.id')
            ->leftJoin('#__gridbox_app AS a ON p.app_id = a.id')
            ->order('p.created desc');
        $db->setQuery($query, $start, $limit);
        $pages = $db->loadObjectList();
        include JPATH_ROOT.'/components/com_gridbox/views/layout/blog-posts.php';
        foreach ($pages as $key => $page) {
            $html .= self::getRecentPostsHTML($page, $out, $max, true, false);
        }

        return $html;
    }

    public static function getEditorSearchResultPaginator()
    {
        $str = '';
        for ($i = 0; $i < 6; $i++) {
            $str = '<div class="ba-blog-posts-pagination-wrapper"><div class="ba-blog-posts-pagination">';
            $str .= '<span class="disabled ba-search-first-page"><a href="#"><i class="zmdi zmdi-skip-previous"></i>';
            $str .= '</a></span><span class="disabled ba-search-prev-page"><a href="#"><i class="zmdi zmdi-fast-rewind">';
            $str .= '</i></a></span><span class="active ba-search-pages"><a href="#">1</a></span>';
            $str .= '<span class="ba-search-pages"><a href="#">2</a></span><span class="ba-search-next-page">';
            $str .= '<a href="#"><i class="zmdi zmdi-fast-forward"></i></a></span><span class="ba-search-last-page">';
            $str .= '<a href="#"><i class="zmdi zmdi-skip-next"></i></a></span></div></div>';
        }

        return $str;
    }

    public static function getSearchResultPaginator($search, $limit, $start, $max)
    {
        $view = JFactory::getApplication()->input->get('view', '');
        if ($view == 'gridbox') {
            return self::getEditorSearchResultPaginator();
        } else if (empty($search)) {
            return '';
        }
        $active = $start;
        $start *= $limit;
        $html = '';
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $wheres = array();
        $wheres[] = 'p.title LIKE '.$db->quote('%'.$db->escape($search, true).'%', false);
        $wheres[] = 'p.params LIKE '.$db->quote('%'.$db->escape($search, true).'%', false);
        $fieldsStr = self::$cacheData->search->fields;
        if (!empty($fieldsStr)) {
            $wheres[] = 'p.id in ('.$fieldsStr.')';
        }
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('COUNT(p.id)')
            ->from('#__gridbox_pages AS p')
            ->where('('.implode(' OR ', $wheres).')')
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->order('p.created desc');
        $db->setQuery($query);
        $count = $db->loadResult();
        if ($limit == 0) {
            $limit = 1;
        }
        $allPages = ceil($count / $limit);
        if ($count != 0 && $allPages != 1) {
            $start = 0;
            $max = $allPages;
            if ($active > 2 && $allPages > 4) {
                $start = $active - 2;
            }
            if ($allPages > 4 && ($allPages - $active) < 3) {
                $start = $allPages - 5;
            }
            if ($allPages > $active + 2) {
                $max = $active + 3;
                if ($allPages > 3 && $active < 2) {
                    $max = 4;
                }
                if ($allPages > 4 && $active < 2) {
                    $max = 5;
                }
            }
            $prev = $active == 0 ? 1 : $active;
            $next = $active == $allPages - 1 ? $allPages : $active + 2;
            $system = self::getSystemParamsByType('search');
            $url = 'index.php?option=com_gridbox&view=system&id='.$system->id;
            $itemId = self::getDefaultMenuItem();
            if (!empty($itemId)) {
                $url .= '&Itemid='.$itemId;
            }
            $url .= '&query='.$search;
            include JPATH_ROOT.'/components/com_gridbox/views/layout/search-result-pagination.php';
            $html .= $out;
        }

        return $html;
    }

    public static function getStoreSearchResultPaginator($search, $limit, $start, $max)
    {
        $view = JFactory::getApplication()->input->get('view', '');
        if ($view == 'gridbox') {
            return self::getEditorSearchResultPaginator();
        } else if (empty($search)) {
            return '';
        }
        $active = $start;
        $start *= $limit;
        $html = '';
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $groups);
        $wheres = array();
        $wheres[] = 'p.title LIKE '.$db->quote('%'.$db->escape($search, true).'%', false);
        $date = date("Y-m-d H:i:s");
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('COUNT(p.id)')
            ->from('#__gridbox_pages AS p')
            ->where('('.implode(' OR ', $wheres).')')
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$db->quote($date))
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$db->quote($date).')')
            ->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')')
            ->where('p.page_access in ('.$groups.')')
            ->where('a.type ='.$db->quote('products'))
            ->leftJoin('#__gridbox_app AS a ON p.app_id = a.id')
            ->order('p.created desc');
        $db->setQuery($query);
        $count = $db->loadResult();
        if ($limit == 0) {
            $limit = 1;
        }
        $allPages = ceil($count / $limit);
        if ($count != 0 && $allPages != 1) {
            $start = 0;
            $max = $allPages;
            if ($active > 2 && $allPages > 4) {
                $start = $active - 2;
            }
            if ($allPages > 4 && ($allPages - $active) < 3) {
                $start = $allPages - 5;
            }
            if ($allPages > $active + 2) {
                $max = $active + 3;
                if ($allPages > 3 && $active < 2) {
                    $max = 4;
                }
                if ($allPages > 4 && $active < 2) {
                    $max = 5;
                }
            }
            $prev = $active == 0 ? 1 : $active;
            $next = $active == $allPages - 1 ? $allPages : $active + 2;
            $system = self::getSystemParamsByType('store-search');
            $url = 'index.php?option=com_gridbox&view=system&id='.$system->id;
            $itemId = self::getDefaultMenuItem();
            if (!empty($itemId)) {
                $url .= '&Itemid='.$itemId;
            }
            $url .= '&query='.$search;
            include JPATH_ROOT.'/components/com_gridbox/views/layout/search-result-pagination.php';
            $html .= $out;
        }

        return $html;
    }

    public static function checkMenuItems($menuItems, $itemId)
    {
        $flag = true;
        foreach ($menuItems as $menu) {
            if ($menu->id == $itemId) {
                $flag = false;
                break;
            }
        }

        return $flag;
    }

    public static function getAppFilterFields($id)
    {
        $db = JFactory::getDbo();
        $obj = new stdClass();
        $query = $db->getQuery(true)
            ->select('type')
            ->from('#__gridbox_app')
            ->where('id = '.$id);
        $db->setQuery($query);
        $type = $db->loadResult();
        if ($type == 'products') {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_products_fields');
            $db->setQuery($query);
            $fields = $db->loadObjectList();
            $item = new stdClass();
            $item->label = JText::_('PRODUCT').': '.JText::_('PRICE');
            $item->product = true;
            $item->title = JText::_('PRICE');
            $item->field_type = 'price';
            $item->field_key = 'price';
            $item->options = '{}';
            $obj->{$item->field_key} = $item;
            foreach ($fields as $item) {
                $item->product = true;
                $item->label = JText::_('PRODUCT').': '.$item->title;
                $obj->{$item->field_key} = $item;
            }
        }
        $query = $db->getQuery(true)
            ->select('options, label, id, field_key, field_type')
            ->from('#__gridbox_fields')
            ->where('app_id = '.$id)
            ->where('field_type <> '.$db->quote('field-simple-gallery'))
            ->where('field_type <> '.$db->quote('field-slideshow'))
            ->where('field_type <> '.$db->quote('product-gallery'))
            ->where('field_type <> '.$db->quote('product-slideshow'))
            ->where('field_type <> '.$db->quote('field-google-maps'))
            ->where('field_type <> '.$db->quote('field-video'))
            ->where('field_type <> '.$db->quote('image-field'));
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as $item) {
            $item->product = false;
            $obj->{$item->field_key} = $item;
        }

        return $obj;
    }

    public static function getItemsFilter($id)
    {
        $str = '';
        $input = JFactory::getApplication()->input;
        $queryStr = $input->get('query', '', 'raw');
        $object = new stdClass();
        $db = JFactory::getDbo();
        $appFields = self::getAppFilterFields($id);
        if (!empty($queryStr)) {
            $url .= '&query='.$queryStr;
            $array = explode('__', $queryStr);
            $values = array();
            $keys = array();
            foreach ($array as $k => $v) {
                if ($k % 2 == 0) {
                    $keys[] = $v;
                }
                else {
                    $values[] = $v;
                }
            }
            foreach ($keys as $i => $key) {
                $object->{$key} = explode('--', $values[$i]);
            }
        }
        $selectedArea = '<div class="ba-selected-filter-values-wrapper"><div class="ba-selected-filter-values-header">';
        $selectedArea .= '<span class="ba-selected-filter-values-title">'.JText::_('SELECTED').'</span>';
        $selectedArea .= '</div>';
        $selectedArea .= '<div class="ba-selected-filter-values-body">';
        $totalChecked = 0;
        foreach ($appFields as $appField) {
            if (($appField->field_type != 'checkbox' && $appField->field_type != 'radio' && $appField->field_type != 'select'
                    && $appField->field_type != 'price' && !$appField->product) || empty($appField->label)) {
                continue;
            }
            if ($appField->product) {
                $label = $appField->title;
            } else {
                $label = $appField->label;
            }
            $str .= '<div class="ba-field-filter" data-id="'.$appField->field_key.'">';
            $str .= '<div class="ba-field-filter-label"><span>'.$label.'</span><i class="zmdi zmdi-caret-down"></i></div>';
            $str .= '<div class="ba-field-filter-value-wrapper"><div class="ba-field-filter-value">';
            if (!$appField->product || ($appField->product && $appField->field_key != 'price')) {
                $options = json_decode($appField->options);
            }
            if ($appField->field_type == 'price') {
                if ($appField->field_key == 'price') {
                    $options = self::$store->currency;
                    $query = $db->getQuery(true)
                        ->select('d.product_id, d.price, d.sale_price')
                        ->from('#__gridbox_store_product_data AS d')
                        ->where('a.id = '.$id)
                        ->leftJoin('#__gridbox_pages AS p ON p.id = d.product_id')
                        ->leftJoin('#__gridbox_app AS a ON a.id = p.app_id');
                    $db->setQuery($query);
                    $data = $db->loadObjectList();
                    $array = array();
                    foreach ($data as $value) {
                        $prices = self::prepareProductPrices($value->product_id, $value->price, $value->sale_price);
                        $obj = new stdClass();
                        $obj->value = $prices->sale_price != '' ? $prices->sale_price : $prices->price;
                        $array[] = $obj;
                    }
                } else {
                    $query = $db->getQuery(true)
                        ->select('value')
                        ->from('#__gridbox_page_fields')
                        ->where('field_id = '.$appField->id);
                    $db->setQuery($query);
                    $array = $db->loadObjectList();
                }
                $minMax = new stdClass();
                foreach ($array as $value) {
                    $minMax->max = !isset($minMax->max) || $value->value * 1 > $minMax->max ? $value->value * 1 : $minMax->max;
                    $minMax->min = !isset($minMax->min) || $value->value * 1 < $minMax->min ? $value->value * 1 : $minMax->min;
                }
                if (!isset($minMax->min)) {
                    $minMax->min = 0;
                }
                if (!isset($minMax->max)) {
                    $minMax->max = 0;
                }
                $minMax->min = floor($minMax->min);
                $minMax->max = ceil($minMax->max);
                if (isset($object->{$label})) {
                    $minMax->minValue = $object->{$label}[0];
                    $minMax->maxValue = $object->{$label}[1];
                    if (empty($minMax->minValue)) {
                        $minMax->minValue = $minMax->min;
                    }
                    if (empty($minMax->maxValue)) {
                        $minMax->maxValue = $minMax->max;
                    }
                    $totalChecked++;
                    $selectedArea .= '<span class="ba-selected-filter-values" data-name="'.$label;
                    $selectedArea .= '" data-value="'.$value->title.'"><span class="ba-selected-filter-value">';
                    $selectedArea .= $options->symbol.' '.$minMax->minValue.' - '.$options->symbol.' '.$minMax->maxValue;
                    $selectedArea .= '</span><i class="zmdi zmdi-close"></i></span>';
                } else {
                    $minMax->minValue = $minMax->min;
                    $minMax->maxValue = $minMax->max;
                }
                $diff = $minMax->max - $minMax->min;
                if ($diff == 0) {
                    $diff = 1;
                }
                $percentage = array(
                    ($minMax->minValue - $minMax->min) * 100 / $diff,
                    ($minMax->maxValue - $minMax->min) * 100 / $diff);
                $str .= '<div class="ba-field-filter-range-wrapper">';
                $str .= '<div class="price-range-track" data-min="'.$minMax->min.'" data-max="'.$minMax->max;
                $str .= '" data-min-value="'.$minMax->minValue.'" data-max-value="'.$minMax->maxValue.'">';
                $str .= '<div class="price-range-selection" style="left: '.$percentage[0];
                $str .= '%; width: '.($percentage[1] - $percentage[0]).'%;"></div>';
                $str .= '<div class="price-range-handle" style="left: '.$percentage[0].'%;"></div>';
                $str .= '<div class="price-range-handle" style="left: '.$percentage[1].'%;"></div></div>';
                $str .= '</div>';
                $str .= '<div class="ba-field-filter-input-wrapper">';
                $str .= '<span class="ba-field-filter-price-symbol">'.$options->symbol.'</span>';
                $str .= '<input type="number" name="'.$label.'" value="'.$minMax->minValue.'" data-min="'.$minMax->min.'">';
                $str .= '<span class="ba-field-filter-price-delimiter">-</span>';
                $str .= '<span class="ba-field-filter-price-symbol">'.$options->symbol.'</span>';
                $str .= '<input type="number" name="'.$label.'" value="'.$minMax->maxValue.'" data-max="'.$minMax->max.'">';
                $str .= '</div>';
            } else if (isset($options->items) || $appField->product) {
                $count = 0;
                $items = isset($options->items) ? $options->items : $options;
                foreach ($items as $value) {
                    $exist = true;
                    if ($appField->product) {
                        $query = $db->getQuery(true)
                            ->select('app_id')
                            ->from('#__gridbox_store_product_variations_map AS vm')
                            ->where('vm.option_key = '.$db->quote($value->key))
                            ->where('a.id = '.$id)
                            ->leftJoin('#__gridbox_pages AS p ON p.id = vm.product_id')
                            ->leftJoin('#__gridbox_app AS a ON a.id = p.app_id');
                        $db->setQuery($query);
                        $exist = $db->loadResult();
                    }
                    if (!$exist) {
                        continue;
                    }
                    $count++;
                    if ($appField->product && $appField->field_type == 'color') {
                        $str .= '<div class="ba-filter-color-value" style="--variation-color-value: '.$value->color.';">';
                        $str .= '<span class="ba-tooltip ba-top">'.$value->title.'</span>';
                    } else if ($appField->product && $appField->field_type == 'image') {
                        if (strpos($value->image, 'balbooa.com') === false) {
                            $value->image = 'url('.JUri::root().$value->image.')';
                        }
                        $str .= '<div class="ba-filter-image-value" style="--variation-image-value: '.$value->image.';">';
                        $str .= '<span class="ba-tooltip ba-top">'.$value->title.'</span>';
                    } else {
                        $str .= '<div class="ba-checkbox-wrapper"><span>'.$value->title.'</span>';
                    }
                    $str .= '<label class="ba-checkbox">';
                    $str .= '<input type="checkbox" name="'.$label.'" value="'.$value->title.'"';
                    if (isset($object->{$label}) && in_array($value->title, $object->{$label})) {
                        $totalChecked++;
                        $str .= ' checked';
                        $selectedArea .= '<span class="ba-selected-filter-values" data-name="'.$label;
                        $selectedArea .= '" data-value="'.$value->title.'"><span class="ba-selected-filter-value">'.$value->title;
                        $selectedArea .= '</span><i class="zmdi zmdi-close"></i></span>';
                    }
                    $str .= '><span></span></label></div>';
                }
                if ($count > 10) {
                    $str .= '<span class="ba-show-all-filters">'.JText::_('SHOW_ALL').'</span>';
                    $str .= '<span class="ba-hide-filters">'.JText::_('HIDE').'</span>';
                }
            }
            $str .= '</div></div></div>';
        }
        $str .= '<div class="ba-field-filter" data-id="rating">';
        $str .= '<div class="ba-field-filter-label"><span>'.JText::_('RATING').'</span><i class="zmdi zmdi-caret-down"></i></div>';
        $str .= '<div class="ba-field-filter-value-wrapper"><div class="ba-field-filter-value">';
        for ($i = 5; $i > 0; $i--) {
            $str .= '<div class="ba-checkbox-wrapper"><span class="ba-filter-rating">';
            $stars = '';
            for ($j = 1; $j < 6; $j++) {
                $stars .= '<i class="zmdi zmdi-star'.($j <= $i ? ' active' : '').'"></i>';
            }
            $str .= $stars.'</span>';
            $str .= '<label class="ba-checkbox">';
            $str .= '<input type="checkbox" name="rating" value="'.$i.'"';
            if (isset($object->{'rating'}) && in_array($i, $object->{'rating'})) {
                $totalChecked++;
                $str .= ' checked';
                $selectedArea .= '<span class="ba-selected-filter-values" data-name="rating';
                $selectedArea .= '" data-value="'.$i.'"><span class="ba-selected-filter-value">'.$stars;
                $selectedArea .= '</span><i class="zmdi zmdi-close"></i></span>';
            }
            $str .= '><span></span></label></div>';
        }
        $str .= '</div></div></div>';
        $selectedArea .= '</div><div class="ba-selected-filter-values-footer">';
        $selectedArea .= '<span class="ba-selected-filter-values-remove-all"><span>'.JText::_('CANCEL_ALL').'</span></span>';
        $selectedArea .= '</div></div>';
        if (empty($str)) {
            $str = self::getEmptyList();
        } else {
            $str .= '<span class="ba-items-filter-search-button">'.JText::_('SEARCH').'</span>';
        }
        $selectedAreaWrapper = '<div class="ba-selected-values-wrapper">';
        if ($totalChecked > 0) {
            $selectedAreaWrapper .= $selectedArea;
        }
        $selectedAreaWrapper .= '</div>';
        $str = $selectedAreaWrapper.$str;

        return $str;
    }

    public static function getFieldsGroups($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('fields_groups')
            ->from('#__gridbox_app')
            ->where('id = '.$id);
        $db->setQuery($query);
        $obj = $db->loadObject();
        $groups = !empty($obj->fields_groups) ? json_decode($obj->fields_groups) : new stdClass();
        $exists = false;
        $productsGroups = array('ba-group-product-pricing', 'ba-group-product-variations', 'ba-group-related-product',
            'ba-group-digital-product');
        foreach ($groups as $key => $value) {
            if (!in_array($key, $productsGroups)) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $groups->{'ba-group-1552307734035'} = new stdClass();
            $groups->{'ba-group-1552307734035'}->title = 'Group';
            $groups->{'ba-group-1552307734035'}->fields = array();
            $obj->fields_groups = json_encode($groups);
        }

        return $obj->fields_groups;
    }

    public static function getAppFields($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_fields')
            ->where('app_id = '.$id * 1)
            ->order('order_list DESC');
        $db->setQuery($query);
        $fields = $db->loadObjectList();

        return $fields;
    }

    public static function getFieldsData($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_page_fields')
            ->where('page_id = '.$id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $fields = new stdClass();
        foreach ($items as $item) {
            $fields->{$item->field_id} = $item;
        }

        return $fields;
    }

    public static function getProductImages($id, $app_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('f.*, pf.value')
            ->from('#__gridbox_fields as f')
            ->where('f.app_id = '.$app_id)
            ->where('(f.field_type = '.$db->quote('product-slideshow').' OR '.'f.field_type = '.$db->quote('product-gallery').')')
            ->where('pf.page_id = '.$id)
            ->leftJoin('`#__gridbox_page_fields` AS pf ON pf.field_id = f.id')
            ->order('f.id ASC');
        $db->setQuery($query);
        $data = $db->loadObjectList();
        $images = array();
        $files = self::getDesktopFieldFiles($id);
        foreach ($data as $field) {
            $value = json_decode($field->value);
            if (!empty($value)) {
                $images = array_slice($value, 0, 2);
                break;
            }
        }
        foreach ($images as $key => $image) {
            if (is_numeric($image->img) && isset($files->{$image->img})) {
                $file = $files->{$image->img};
                $image->img = 'components/com_gridbox/assets/uploads/app-'.$file->app_id.'/'.$file->filename;
                $images[$key] = $image;
            }
        }

        return $images;
    }

    public static function getCategoryListFields($id, $app_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('f.*, pf.value')
            ->from('#__gridbox_fields as f')
            ->where('f.app_id = '.$app_id)
            ->where('f.field_type <> '.$db->quote('field-simple-gallery'))
            ->where('f.field_type <> '.$db->quote('product-gallery'))
            ->where('f.field_type <> '.$db->quote('field-slideshow'))
            ->where('f.field_type <> '.$db->quote('product-slideshow'))
            ->where('f.field_type <> '.$db->quote('field-google-maps'))
            ->where('f.field_type <> '.$db->quote('field-video'))
            ->where('f.field_type <> '.$db->quote('image-field'))
            ->where('pf.page_id = '.$id)
            ->leftJoin('`#__gridbox_page_fields` AS pf ON pf.field_id = f.id');
        $db->setQuery($query);
        $data = $db->loadObjectList();
        $not = '0';
        foreach ($data as $field) {
            $not .= ','.$field->id;
        }
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_fields')
            ->where('id NOT IN('.$not.')')
            ->where('app_id = '.$app_id)
            ->where('field_type <> '.$db->quote('field-simple-gallery'))
            ->where('field_type <> '.$db->quote('product-gallery'))
            ->where('field_type <> '.$db->quote('field-slideshow'))
            ->where('field_type <> '.$db->quote('product-slideshow'))
            ->where('field_type <> '.$db->quote('field-google-maps'))
            ->where('field_type <> '.$db->quote('field-video'))
            ->where('field_type <> '.$db->quote('image-field'));
        $db->setQuery($query);
        $array = $db->loadObjectList();
        $data = array_merge($data, $array);

        return $data;
    }

    public static function getPageFieldData()
    {
        $db = JFactory::getDbo();
        $app = JFactory::getApplication();
        $input = $app->input;
        $view = $input->get('view', '', 'string');
        $option = $input->get('option', '', 'string');
        $id = $input->get('id', 0, 'int');
        $data = array();
        if ($option == 'com_gridbox' && $view == 'page') {
            $query = $db->getQuery(true)
                ->select('app_id')
                ->from('#__gridbox_pages')
                ->where('id = '.$id);
            $db->setQuery($query);
            $app_id = $db->loadResult();
            if ($app_id) {
                $query = $db->getQuery(true)
                    ->select('f.*')
                    ->from('#__gridbox_fields as f')
                    ->where('f.app_id = '.$app_id)
                    ->select('pf.value')
                    ->where('pf.page_id = '.$id)
                    ->leftJoin('`#__gridbox_page_fields` AS pf ON pf.field_id = f.id');
                $db->setQuery($query);
                $data = $db->loadObjectList();
                $not = '0';
                foreach ($data as $field) {
                    $not .= ','.$field->id;
                }
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_fields')
                    ->where('id NOT IN('.$not.')')
                    ->where('app_id = '.$app_id);
                $db->setQuery($query);
                $array = $db->loadObjectList();
                $data = array_merge($data, $array);
            }
        }

        return $data;
    }

    public static function preparePrice($price, $thousand, $separator, $decimals)
    {
        $price = round($price * 1, $decimals);
        $price = number_format($price, $decimals, $separator, $thousand);

        return $price;
    }

    public static function getBlogPostsSortingList()
    {
        $list = array(
            'price-low-high' => JText::_('PRICE_LOW_TO_HIGH'), 'price-high-low' => JText::_('PRICE_HIGH_TO_LOW'),
            'newest' => JText::_('NEWEST'), 'highest-rated' => JText::_('HIGHEST_RATED'),
            'most-reviewed' => JText::_('MOST_REVIEWED'), 'popular' => JText::_('MOST_POPULAR'));

        return $list;
    }

    public static function getTaxCountries($setObject = null)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_countries')
            ->order('title ASC');
        $db->setQuery($query);
        $countries = $db->loadObjectList();
        if ($setObject) {
            $data = new stdClass();
            foreach ($countries as $country) {
                $data->{$country->id} = $country;
            }
            $countries = $data;
        }
        foreach ($countries as $country) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_country_states')
                ->where('country_id = '.$country->id)
                ->order('title ASC');
            $db->setQuery($query);
            $country->states = $db->loadObjectList();
        }
        
        return $countries;
    }

    public static function getDigitalFolder($id)
    {
        $dir = JPATH_ROOT.'/components/com_gridbox/assets/uploads/digital/';
        if (!JFolder::exists($dir)) {
            JFolder::create($dir);
        }
        $folder = hash('md5', 'product-'.$id);
        $dir = JPATH_ROOT.'/components/com_gridbox/assets/uploads/digital/'.$folder.'/';

        return $dir;
    }
}