<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;
jimport('joomla.filter.output');
jimport('joomla.filesystem.file');
if (!function_exists('mb_strtolower')) {
    function mb_strtolower($str, $encoding = 'utf-8')
    {
        return strtolower($str);
    }
}

abstract class gridboxHelper 
{
    public static $website;
    public static $installComments;
    public static $installReviews;
    public static $store;
    public static $storeHelper;

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

    public static function getShopStatistic($date, $type)
    {
        $start = array();
        $end = array();
        $dates = array();
        if ($type == 'd') {
            $start[] = $date;
            $end[] = $date;
            $dates[] = JDate::getInstance(strtotime($date))->format('M d, Y');
        } else if ($type == 'w') {
            $i = 7;
            while ($i > 0) {
                $d = date('Y-m-d', strtotime($date.' -'.$i.' days'));
                $start[] = $d;
                $end[] = $d;
                $dates[] = JDate::getInstance(strtotime($d))->format('D');
                $i--;
            }
        } else if ($type == 'm') {
            $i = 1;
            while ($i <= 12) {
                $d = date('Y-m-d', strtotime($date.'-'.($i < 10 ? '0'.$i : $i).'-01'));
                $start[] = $d;
                $end[] = date('Y-m-t', strtotime($d));
                $dates[] = JDate::getInstance($d)->format('M');
                $i++;
            }
        } else if ($type == 'y') {
            $date = self::getFirstOrderDate();
            $i = date('Y', strtotime($date));
            $current = date('Y');
            for ($i; $i <= $current; $i++) {
                $d = date('Y-m-d', strtotime($i.'-01-01'));
                $start[] = $d;
                $end[] = date('Y-m-t', strtotime($i));
                $dates[] = JDate::getInstance($d)->format('Y');
                $i++;
            }
        } else if ($type == 'c') {
            $array = explode(' - ', $date);
            $start[] = $array[0];
            $end[] = $array[1];
            $d = JDate::getInstance(strtotime($start[0]))->format('M d, Y');
            $dates[] = $d.' - '.JDate::getInstance(strtotime($end[0]))->format('M d, Y');
        }
        $data = new stdClass();
        $data->total = 0;
        $data->counts = array('orders' => 0, 'completed' => 0, 'refunded' => 0);
        $data->products = array();
        $data->chart = array();
        foreach ($start as $key => $value) {
            self::getStatisticData($start[$key], $end[$key], $dates[$key], $data);
        }

        return $data;
    }

    public static function getFirstOrderDate()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('date')
            ->from('#__gridbox_store_orders')
            ->where('published = 1')
            ->order('date ASC');
        $db->setQuery($query);
        $date = $db->loadResult();

        return $date;
    }

    public static function getStatisticData($start, $end, $date, $data)
    {
        $db = JFactory::getDbo();
        $start = $db->quote($start.' 00:00:01');
        $end = $db->quote($end.' 23:59:59');
        $query = $db->getQuery(true)
            ->select('total, tax, status, id')
            ->from('#__gridbox_store_orders')
            ->where('published = 1')
            ->where('date > '.$start)
            ->where('date < '.$end);
        $db->setQuery($query);
        $orders = $db->loadObjectList();
        $chart = new stdClass();
        $chart->label = $date;
        $chart->value = 0;
        $pks = array();
        $products = array();
        foreach ($orders as $order) {
            if (isset($data->counts[$order->status])) {
                $data->counts[$order->status]++;
            }
            if ($order->status == 'completed') {
                $pks[] = $order->id;
            }
            $data->counts['orders']++;
        }
        if (!empty($pks)) {
            $str = implode(', ', $pks);
            $products = self::getStatisticProducts($db, $str);
            foreach ($products as $product) {
                $data->products[] = $product;
                $data->total += $product->price;
                $chart->value += $product->price;
            }
            foreach ($pks as $pk) {
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_store_orders_discount')
                    ->where('order_id = '.$pk);
                $db->setQuery($query);
                $promo = $db->loadObject();
                if ($promo) {
                    $data->total -= $promo->value;
                    $chart->value -= $promo->value;
                }
            }
        }
        $data->chart[] = $chart;
    }

    public static function getStatisticProducts($db, $order_id, $i = 0, $pks = array())
    {
        $user = JFactory::getUser();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_order_products')
            ->where('order_id IN ('.$order_id.')');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $object = new stdClass();
        foreach ($items as $item) {
            $id = !empty($item->variation) ? $item->variation : $item->product_id;
            $item->quantity = $item->quantity * 1;
            if ($item->sale_price != '') {
                $item->price = $item->sale_price;
            }
            if (!isset($object->{$id})) {
                $object->{$id} = $item;
            } else {
                $object->{$id}->quantity += $item->quantity;
                $object->{$id}->price += $item->price;
            }
        }
        $products = array();
        foreach ($object as $obj) {
            $products[] = $obj;
        }
        uasort($products, function($a, $b){
            if ($a->price == $b->price) {
                return 0;
            }
            return ($a->price < $b->price) ? 1 : -1;
        });
        if (count($products) > 10) {
            $products = array_slice($products, 0, 10);
        }
        foreach ($products as $product) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_order_product_variations')
                ->where('product_id = '.$product->id);
            $db->setQuery($query);
            $variations = $db->loadObjectList();
            $info = array();
            foreach ($variations as $variation) {
                $info[] = '<span>'.$variation->title.' '.$variation->value.'</span>';
            }
            $product->info = implode('/', $info);
            if (!empty($product->image) && strpos($product->image, 'balbooa.com') === false) {
                $product->image = JUri::root().$product->image;
            }
            $query = $db->getQuery(true)
                ->select('p.id')
                ->from('#__gridbox_pages AS p')
                ->where('d.product_id = '.$product->product_id)
                ->leftJoin('#__gridbox_store_product_data AS d ON d.product_id = p.id');
            $db->setQuery($query);
            $product->product_id = $db->loadResult();
            $product->link = '';
            if ($product->product_id && $user->authorise('core.edit', 'com_gridbox.page.'.$product->product_id)) {
                $product->link = 'index.php?option=com_gridbox&task=gridbox.edit&id='.$product->product_id;
            }
        }

        return $products;
    }

    public static function getStatuses()
    {
        $data = new stdClass();
        $data->undefined = new stdClass();
        $data->undefined->title = 'Undefined';
        $data->undefined->color = '#f10000';
        foreach (self::$store->statuses as $status) {
            $data->{$status->key} = $status;
        }

        return $data;
    }

    public static function getProductCategoryId($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('page_category')
            ->from('#__gridbox_pages')
            ->where('id = '.$id);
        $db->setQuery($query);
        $category = $db->loadResult();
        $array = array($category);
        $array2 = self::getProductCategoryIdPath($category);
        $result = array_merge($array, $array2);
        
        return $result;
    }

    public static function getProductCategoryIdPath($id)
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
            $array2 = self::getProductCategoryIdPath($obj->parent);
        } else {
            $array2 = array();
        }
        $result = array_merge($array1, $array2);
        
        return $result;
    }

    public static function getUserGroups()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__usergroups'))
            ->order('lft ASC');
        $db->setQuery($query);
        $groups = $db->loadObjectList();
        foreach ($groups as $group) {
            $group->level = self::getUserGroupLevel($group->parent_id);
        }

        return $groups;
    }

    public static function getUserGroupLevel($parent, $level = 0)
    {
        if (!empty($parent)) {
            ++$level;
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('parent_id')
                ->from($db->quoteName('#__usergroups'))
                ->where('id = '.$parent);
            $db->setQuery($query);
            $id = $db->loadResult();
            $level = self::getUserGroupLevel($id, $level);
        }

        return $level;
    }

    public static function checkCommentUserBanStatus($value, $table, $key)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from($table)
            ->where($key.' = '.$db->quote($value));
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result;
    }

    public static function checkSystemApp($type)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(id)')
            ->from('#__gridbox_app')
            ->where('type = '.$db->quote('system_apps'))
            ->where('title = '.$db->quote($type));
        $db->setQuery($query);
        $count = $db->loadResult();

        return $count > 0;
    }

    public static function deleteComment($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__gridbox_comments')
            ->where('id = '.$id);
        $db->setQuery($query)
            ->execute();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_comments_attachments')
            ->where('comment_id = '.$id);
        $db->setQuery($query);
        $files = $db->loadObjectList();
        foreach ($files as $file) {
            self::removeTmpAttachment($file->id, $file->filename);
        }
        $query = $db->getQuery(true)
            ->delete('#__gridbox_comments_likes_map')
            ->where('comment_id = '.$id);
        $db->setQuery($query)
            ->execute();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__gridbox_comments')
            ->where('parent = '.$id);
        $db->setQuery($query);
        $childs = $db->loadObjectList();
        foreach ($childs as $key => $child) {
            self::deleteComment($child->id);
        }
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

    public static function deleteReview($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__gridbox_reviews')
            ->where('id = '.$id);
        $db->setQuery($query)
            ->execute();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_reviews_attachments')
            ->where('comment_id = '.$id);
        $db->setQuery($query);
        $files = $db->loadObjectList();
        foreach ($files as $file) {
            self::removeTmpReviewsAttacment($file->id, $file->filename);
        }
        $query = $db->getQuery(true)
            ->delete('#__gridbox_reviews_likes_map')
            ->where('comment_id = '.$id);
        $db->setQuery($query)
            ->execute();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__gridbox_reviews')
            ->where('parent = '.$id);
        $db->setQuery($query);
        $childs = $db->loadObjectList();
        foreach ($childs as $key => $child) {
            self::deleteReview($child->id);
        }
    }

    public static function removeTmpReviewsAttacment($id, $filename)
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
        foreach ($files as $key => $file) {
            $file->link = $dir.$file->filename;
        }

        return $files;
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
        foreach ($files as $key => $file) {
            $file->link = $dir.$file->filename;
        }

        return $files;
    }

    public static function getGravatarImage($email)
    {
        $avatar = JUri::root().'components/com_gridbox/assets/images/default-user.png';
        if (self::$website->enable_gravatar == 1) {
            $hash = md5(strtolower(trim($email)));
            $avatar = "https://www.gravatar.com/avatar/".$hash."?d=".$avatar."&s=50";
        }

        return $avatar;
    }

    public static function getReviewsGravatarImage($email)
    {
        $avatar = JUri::root().'components/com_gridbox/assets/images/default-user.png';
        if (self::$website->reviews_enable_gravatar == 1) {
            $hash = md5(strtolower(trim($email)));
            $avatar = "https://www.gravatar.com/avatar/".$hash."?d=".$avatar."&s=50";
        }

        return $avatar;
    }

    public static function getUnreadCount($table, $where = '')
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(id)')
            ->from($table)
            ->where('unread = 1');
        if (!empty($where)) {
            $query->where($where);
        }
        $db->setQuery($query);
        $count  = $db->loadResult();

        return $count;
    }

    public static function getEditorLink($type = '')
    {
        $user = JFactory::getUser();
        $link = JUri::root().'index.php?option=com_gridbox&view=editor&tmpl=component&name=';
        $link .= urlencode($user->username).'&pwd='.urlencode($user->password);
        if ($type == 'products') {
            $link .= '&product_type={product_type}';
        }

        return $link;
    }

    public static function assetsCheckPermission($id, $type, $action, $name = '')
    {
        $assets = new gridboxAssetsHelper($id, $type);

        return $assets->checkPermission($action, $name);
    }

    public static function checkUserEditLevel($id = '', $type = '')
    {
        $action = 'com_gridbox';
        if (!empty($id)) {
            $action .= '.'.$type.'.'.$id;
        }
        if (!JFactory::getUser()->authorise('core.edit', $action)) {
            exit;
        }
    }

    public static function movePageFields($id, $app_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('app_id')
            ->from('#__gridbox_pages')
            ->where('id = '.$id);
        $db->setQuery($query);
        $result = $db->loadResult();
        if ($result != $app_id) {
            $array = array($id);
            self::deletePageFields($array);
        }
    }

    public static function afterDeleteAction($cid)
    {
        gridboxHelper::deletePageCss($cid);
        gridboxHelper::deleteTagsLink($cid);
        gridboxHelper::deletePageFields($cid);
        gridboxHelper::deleteProductData($cid);
    }

    public static function deleteProductData($cid)
    {
        $db = JFactory::getDbo();
        foreach ($cid as $id) {
            $query = $db->getQuery(true)
                ->delete('#__gridbox_store_product_variations_map')
                ->where('product_id = '.$id);
            $db->setQuery($query)
                ->execute();
            $query = $db->getQuery(true)
                ->delete('#__gridbox_store_product_data')
                ->where('product_id = '.$id);
            $db->setQuery($query)
                ->execute();
        }
    }

    public static function deletePageFields($cid)
    {
        $db = JFactory::getDbo();
        foreach ($cid as $id) {
            $query = $db->getQuery(true)
                ->delete('#__gridbox_page_fields')
                ->where('page_id = '.$id);
            $db->setQuery($query)
                ->execute();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_fields_desktop_files')
                ->where('page_id = '.$id);
            $db->setQuery($query);
            $files = $db->loadObjectList();
            $desktopArray = array();
            foreach ($files as $file) {
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
    }

    public static function getOptions($type)
    {
        $json = JFile::read(JPATH_ROOT.'/components/com_gridbox/libraries/json/'.$type.'.json');
        
        return json_decode($json);
    }

    public static function checkInstalledBlog($type = '')
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(id)')
            ->from('#__gridbox_app')
            ->where('type <> '.$db->quote('system_apps'))
            ->where('type <> '.$db->quote('single'));
        if (!empty($type)) {
            $query->where('type = '.$db->quote($type));
        }
        $db->setQuery($query);
        $count = $db->loadResult();

        return $count > 0;
    }

    public static function setGridboxFilters($ordering, $direction, $context)
    {
        if ($ordering == 'order_list') {
            $direction = 'ASC';
        }
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $query = $db->getQuery(true)
            ->select('id, name')
            ->from('#__gridbox_filter_state')
            ->where('name = '.$db->quote($context.'.list.ordering').' OR name = '.$db->quote($context.'.list.direction'))
            ->where('user = '.$user->id);
        $db->setQuery($query);
        $array = $db->loadObjectList();
        if (!empty($array)) {
            foreach ($array as $obj) {
                if ($obj->name == $context.'.list.ordering') {
                    $obj->value = $ordering;
                } else {
                    $obj->value = $direction;
                }
                $db->updateObject('#__gridbox_filter_state', $obj, 'id');
            }
        } else {
            $obj = new stdClass();
            $obj->user = $user->id;
            $obj->name = $context.'.list.ordering';
            $obj->value = $ordering;
            $db->insertObject('#__gridbox_filter_state', $obj);
            $obj->name = $context.'.list.direction';
            $obj->value = $direction;
            $db->insertObject('#__gridbox_filter_state', $obj);
        }
    }

    public static function getGridboxFilters($context)
    {
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $query = $db->getQuery(true)
            ->select('id, name, value')
            ->from('#__gridbox_filter_state')
            ->where('name = '.$db->quote($context.'.list.ordering').' OR name = '.$db->quote($context.'.list.direction'))
            ->where('user = '.$user->id);
        $db->setQuery($query);
        $array = $db->loadObjectList();

        return $array;
    }

    public static function getGridboxLanguage()
    {
        $language = JFactory::getLanguage();
        $result = array('EDIT_NOT_PERMITTED' => JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'),
            'CREATE_NOT_PERMITTED' => JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'),
            'SAVE_SUCCESS' => JText::_('JLIB_APPLICATION_SAVE_SUCCESS'), 'TITLE' => JText::_('JGLOBAL_TITLE')
        );
        $shortDate = array('SHORT_M1' => 'January ', 'SHORT_M2' => 'February', 'SHORT_M3' => 'March', 'SHORT_M4' => 'April',
            'SHORT_M5' => 'May', 'SHORT_M6' => 'June', 'SHORT_M7' => 'July', 'SHORT_M8' => 'August', 'SHORT_M9' => 'September',
            'SHORT_M10' => 'October', 'SHORT_M11' => 'November', 'SHORT_M12' => 'December 2019');
        foreach ($shortDate as $key => $value) {
            $result[$key] = JHtml::date(strtotime($value), 'M');
        }
        $path = JPATH_ROOT.'/administrator/components/com_gridbox/language/admin/en-GB/en-GB.com_gridbox.ini';
        if (JFile::exists($path)) {
            $contents = JFile::read($path);
            $contents = str_replace('_QQ_', '"\""', $contents);
            $data = parse_ini_string($contents);
            foreach ($data as $ind => $value) {
                $result[$ind] = JText::_($ind);
            }
        }
        
        $data = 'var gridboxLanguage = '.json_encode($result).';';

        return $data;
    }
    
    public static function getThemes()
    {
        $url = 'http://www.balbooa.com/updates/gridbox/themes/themes.xml';
        $curl = self::getContentsCurl($url);
        $xml = simplexml_load_string($curl);
        $themes = array();
        foreach ($xml->themes->theme as $theme) {
            $obj = new stdClass();
            $obj->id = trim((string)$theme->id);
            $obj->title = trim((string)$theme->title);
            $obj->image = trim((string)$theme->image);
            $themes[] = $obj;
        }

        return $themes;
    }

    public static function getTemplate()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from('#__template_styles')
            ->where('`template` = '.$db->Quote('gridbox'))
            ->where('`client_id` = 0');
        $db->setQuery($query);
        $id = $db->loadResult();

        return $id;
    }

    public static function deleteTagsLink($pages)
    {
        $db = JFactory::getDbo();
        foreach ($pages as $value) {
            $query = $db->getQuery(true)
                ->select('tag_id')
                ->from('#__gridbox_tags_map')
                ->where('`page_id` = '. $value);
            $db->setQuery($query);
            $tags = $db->loadObjectList();
            $query = $db->getQuery(true)
                ->delete('#__gridbox_tags_map')
                ->where('`page_id` = '. $value);
            $db->setQuery($query)
                ->execute();
            if (!empty($tags) && is_array($tags)) {
                foreach ($tags as $tag) {
                    $query = $db->getQuery(true)
                        ->select('COUNT(id)')
                        ->from('#__gridbox_tags_map')
                        ->where('`tag_id` = '. $tag->tag_id);
                    $db->setQuery($query);
                    $count = $db->loadResult();
                    if (empty($count)) {
                        $query = $db->getQuery(true)
                            ->delete('#__gridbox_tags')
                            ->where('`id` = '. $tag->tag_id);
                        $db->setQuery($query)
                            ->execute();
                    }
                }
            }
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_comments')
                ->where('`page_id` = '. $value);
            $db->setQuery($query);
            $comments = $db->loadObjectList();
            foreach ($comments as $comment) {
                self::deleteComment($comment->id);
            }
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_reviews')
                ->where('`page_id` = '. $value);
            $db->setQuery($query);
            $reviews = $db->loadObjectList();
            foreach ($reviews as $review) {
                self::deleteReview($review->id);
            }
        }
    }

    public static function findGridboxLinks($html, $items, $apps, $categories, $pages)
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        foreach ($items as $key => $item) {
            if ($item->type == 'logo' || $item->type == 'image' || $item->type == 'icon' || $item->type == 'button') {
                $item->link->link = self::importGridboxLinks($item->link->link, $apps, $categories, $pages);
            } else if ($item->type == 'column' && isset($item->link)) {
                $item->link->link = self::importGridboxLinks($item->link->link, $apps, $categories, $pages);
            } else if ($item->type == 'slideshow' || $item->type == 'slideset' || $item->type == 'carousel') {
                foreach ($item->desktop->slides as $slide) {
                    if (isset($slide->link) && !empty($slide->link)) {
                        $slide->link = self::importGridboxLinks($slide->link, $apps, $categories, $pages);
                    }
                }
            } else if ($item->type == 'content-slider') {
                foreach ($item->slides as $slide) {
                    $slide->link->href = self::importGridboxLinks($slide->link->href, $apps, $categories, $pages);
                }
            } else if ($item->type == 'icon-list') {
                foreach ($item->list as $listValue) {
                    $listValue->link = self::importGridboxLinks($listValue->link, $apps, $categories, $pages);
                }
            }
        }
        include_once JPATH_ROOT.'/components/com_gridbox/libraries/php/phpQuery/phpQuery.php';
        $dom = phpQuery::newDocument($html);
        foreach (pq('.ba-item-text .content-text a[href]') as $value) {
            $link = pq($value)->attr('href');
            $link = self::importGridboxLinks($link, $apps, $categories, $pages);
            pq($value)->attr('href', $link);
        }
        $obj = new stdClass();
        $obj->html = $dom->htmlOuter();
        $obj->items = $items;

        return $obj;
    }

    public static function importGridboxLinks($link, $apps, $categories, $pages)
    {
        if (strpos($link, 'option=com_gridbox')) {
            $link = str_replace('index.php?', '', $link);
            parse_str($link, $array);
            if (isset($array['app']) && isset($apps[$array['app']])) {
                $array['app'] = $apps[$array['app']];
            }
            if (isset($array['blog']) && isset($apps[$array['blog']])) {
                $array['blog'] = $apps[$array['blog']];
            }
            if ($array['view'] == 'page') {
                if (isset($array['category']) && isset($categories[$array['category']])) {
                    $array['category'] = $categories[$array['category']];
                }
                if (isset($array['id']) && isset($pages[$array['id']])) {
                    $array['id'] = $pages[$array['id']];
                }
            } else if ($array['view'] == 'blog') {
                if (isset($array['id']) && isset($categories[$array['id']])) {
                    $array['id'] = $categories[$array['id']];
                }
            }
            $data = array();
            foreach ($array as $key => $value) {
                $data[] = $key.'='.$value;
            }
            $link = implode('&', $data);
            $link = 'index.php?'.$link;
        }

        return $link;
    }

    public static function importBlogContent($obj, $apps, $categories)
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        include_once JPATH_ROOT.'/components/com_gridbox/libraries/php/phpQuery/phpQuery.php';
        $dom = phpQuery::newDocument($obj->html);
        foreach (pq('.ba-item-event-calendar, .ba-item-fields-filter, .ba-item-google-maps-places') as $value) {
            $id = pq($value)->attr('id');
            if (!empty($obj->items->{$id}->app) && isset($apps[$obj->items->{$id}->app])) {
                $obj->items->{$id}->app = $apps[$obj->items->{$id}->app];
            }
        }
        if (!self::$installComments) {
            foreach (pq('.ba-item-comments-box') as $key => $value) {
                self::$installComments = true;
                break;
            }
        }
        if (!self::$installReviews) {
            foreach (pq('.ba-item-reviews') as $key => $value) {
                self::$installReviews = true;
                break;
            }
        }
        $tags = pq('.ba-item-tags');
        foreach ($tags as $value) {
            $app = pq($value)->attr('data-app');
            $cat = pq($value)->attr('data-category');
            $id = pq($value)->attr('id');
            pq($value)->attr('data-app', $apps[$app]);
            $item = $obj->items->{$id};
            $item->app = $apps[$app];
            if (!empty($cat)) {
                $catList = explode(',', $cat);
                $object = new stdClass();
                foreach ($catList as $category) {
                    if (!isset($categories[$category])) {
                        continue;
                    }
                    $catObj = new stdClass();
                    $catObj->id = $categories[$category];
                    $catObj->title = $item->categories->{$category}->title;
                    $object->{$catObj->id} = $catObj;
                    $category = $categories[$category];
                }
                $item->categories = $object;
                $cat = implode(',', $catList);
                pq($value)->attr('data-category', $cat);
            }
        }
        $itemCategories = pq('.ba-item-categories');
        foreach ($itemCategories as $value) {
            $app = pq($value)->attr('data-app');
            $id = pq($value)->attr('id');
            pq($value)->attr('data-app', $apps[$app]);
            $obj->items->{$id}->app = $apps[$app];
        }
        $recent = pq('.ba-item-recent-posts');
        foreach ($recent as $value) {
            $app = pq($value)->attr('data-app');
            $cat = pq($value)->attr('data-category');
            $id = pq($value)->attr('id');
            pq($value)->attr('data-app', $apps[$app]);
            $obj->items->{$id}->app = $apps[$app];
            $item = $obj->items->{$id};
            $item->app = $apps[$app];
            if (!empty($cat)) {
                $catList = explode(',', $cat);
                $object = new stdClass();
                $newCats = array();
                foreach ($catList as $category) {
                    if (!isset($categories[$category])) {
                        continue;
                    }
                    $catObj = new stdClass();
                    $catObj->id = $categories[$category];
                    $catObj->title = $item->categories->{$category}->title;
                    $object->{$catObj->id} = $catObj;
                    $newCats[] = $categories[$category];
                }
                $item->categories = $object;
                $cat = implode(',', $newCats);
                pq($value)->attr('data-category', $cat);
            }
        }
        foreach (pq('.ba-item-recent-posts-slider') as $value) {
            $id = pq($value)->attr('id');
            $item = $obj->items->{$id};
            $item->app = $apps[$item->app];
            $object = new stdClass();
            foreach ($item->categories as $key => $category) {
                if (!isset($categories[$key])) {
                    continue;
                }
                $category->id = $categories[$key];
                $object->{$key} = $category;
            }
            $item->categories = $object;
        }
        $related = pq('.ba-item-related-posts');
        foreach ($related as $value) {
            $app = pq($value)->attr('data-app');
            $id = pq($value)->attr('id');
            pq($value)->attr('data-app', $apps[$app]);
        }
        $obj->html = $dom->htmlOuter();

        return $obj;

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
    
    public static function getLanguagesList()
    {
        $url = 'http://www.balbooa.com/updates/gridbox/language/language.xml';
        $curl = self::getContentsCurl($url);
        $xml = simplexml_load_string($curl);
        $array = array();
        if (isset($xml->languages)) {
            foreach ($xml->languages->language as $language) {
                $obj = new StdClass();
                $obj->flag = 'http://www.balbooa.com/updates/gridbox/language/flags/'.trim((string)$language->flag);
                $obj->title = trim((string)$language->title);
                $obj->code = trim((string)$language->tag);
                $obj->url = trim((string)$language->url);
                $array[] = $obj;
            }
        }

        return $array;
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

    public static function deletePageCss($cid)
    {
        foreach ($cid as $id) {
            $file = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/style-'.$id.'.css';
            JFile::delete($file);
        }
    }

    public static function deleteThemeCss($cid)
    {
        foreach ($cid as $id) {
            $file = JPATH_ROOT. '/templates/gridbox/css/storage/code-editor-'.$id.'.css';
            self::deleteFile($file);
            $file = JPATH_ROOT. '/templates/gridbox/css/storage/style-'.$id.'.css';
            self::deleteFile($file);
            $file = JPATH_ROOT. '/templates/gridbox/js/storage/code-editor-'.$id.'.js';
            self::deleteFile($file);
        }
    }

    public static function deleteFile($file)
    {
        if (JFile::exists($file)) {
            JFile::delete($file);
        }
    }

    public static function getApps()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title, alias, theme, type, published, access, language, image, meta_title,
            share_image, share_title, share_description, meta_description, meta_keywords, description, robots,
            sitemap_include, changefreq, priority')
            ->from('#__gridbox_app')
            ->where('type <> '.$db->quote('system_apps'))
            ->order('id ASC');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        
        return $items;
    }

    public static function saveCodeEditor($obj, $id)
    {
        $file = JPATH_ROOT. '/templates/gridbox/css/storage/code-editor-'.$id.'.css';
        JFile::write($file, (string)$obj->css);
        $file = JPATH_ROOT. '/templates/gridbox/js/storage/code-editor-'.$id.'.js';
        JFile::write($file, (string)$obj->js);
    }

    public static function copyThemeFiles($pk, $id)
    {
        $file = JPATH_ROOT. '/templates/gridbox/css/storage/code-editor-'.$pk.'.css';
        if (JFile::exists($file)) {
            $target = JPATH_ROOT. '/templates/gridbox/css/storage/code-editor-'.$id.'.css';
            JFile::copy($file, $target);
        }
        $file = JPATH_ROOT. '/templates/gridbox/js/storage/code-editor-'.$pk.'.js';
        if (JFile::exists($file)) {
            $target = JPATH_ROOT. '/templates/gridbox/js/storage/code-editor-'.$id.'.js';
            JFile::copy($file, $target);
        }
    }

    public static function copyCss($pk, $id)
    {
        $file = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/style-'.$pk.'.css';
        $target = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/style-'.$id.'.css';
        JFile::copy($file, $target);
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
    
    public static function checkActive($app)
    {
        $active = '';
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $view = $input->get('view', 'pages', 'string');
        $type = gettype($app);
        $appslist = array('pages', 'apps', 'single');
        $store = array('paymentmethods', 'shipping', 'storesettings', 'promocodes', 'productoptions', 'orders');
        $viewFlag = $type == 'string' && ($app == $view || ($app == 'appslist' && in_array($view, $appslist)));
        $storeFlag = $type == 'string' && $app == 'store' && in_array($view, $store);
        if ($viewFlag || $storeFlag || ($type != 'string' && $app->id == $id)) {
            $active = 'active';
        }

        return $active;
    }

    public static function getUrl($app)
    {
        $view = $app->type == 'single' ? 'single' : 'apps';

        return 'index.php?option=com_gridbox&view='.$view.'&id='.$app->id;
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
        $balbooa->key = json_encode($balbooa->key);
        $db->updateObject('#__gridbox_api', $balbooa, 'id');
    }

    public static function getIcon($app)
    {
        $type = $app->type != 'system_apps' ? $app->type : $app->title;
        switch ($type) {
            case 'blog':
                return 'zmdi zmdi-format-color-text';
                break;
            case 'blank':
                return 'zmdi zmdi-crop-free';
                break;
            case 'products':
                return 'zmdi zmdi-shopping-basket';
                break;
            case 'portfolio':
                return 'zmdi zmdi-camera';
                break;
            case 'hotel-rooms':
                return 'zmdi zmdi-hotel';
                break;
            case 'comments':
                return 'zmdi zmdi-comment-more';
                break;
            case 'reviews':
                return 'zmdi zmdi-ticket-star';
                break;
            case 'photo-editor':
                return 'zmdi zmdi-camera-alt';
                break;
            case 'code-editor':
                return 'zmdi zmdi-code-setting';
                break;
            case 'performance':
                return 'zmdi zmdi-time-restore-setting';
                break;
            case 'preloader':
                return 'zmdi zmdi-spinner';
                break;
            case 'canonical':
                return 'zmdi zmdi-link';
                break;
            case 'sitemap':
                return 'zmdi zmdi-device-hub';
                break;
            default:
                return 'zmdi zmdi-file';
                break;
        }
    }
    
    public static function ajaxReload($text, $type = '')
    {
        echo $type.JText::_($text);
        exit;
    }

    public static function stringURLSafe($string, $language = '')
    {
        if (JFactory::getConfig()->get('unicodeslugs') == 1) {
            $output = JFilterOutput::stringURLUnicodeSlug($string);
        } else {
            if ($language === '*' || $language === '') {
                $languageParams = JComponentHelper::getParams('com_languages');
                $language = $languageParams->get('site');
            }
            $output = JFilterOutput::stringURLSafe($string, $language);
        }

        return $output;
    }

    public static function getAlias($alias, $table, $app = 0)
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
            ->where('`alias` = ' .$db->Quote($alias))
            ->where('`id` <> ' .$db->Quote($app));
        $db->setQuery($query);
        $id = $db->loadResult();
        if (!empty($id)) {
            $alias = JString::increment($alias);
            $alias = self::getAlias($alias, $table);
        }
        
        return $alias;
    }

    public static function getCategories($map)
    {
        $array = array();
        if (!empty($map)) {
            $db = JFactory::getDbo();
            $pks = implode(', ', $map);
            $query = $db->getQuery(true)
                ->select('c.id, c.title, c.image')
                ->from('#__gridbox_categories AS c')
                ->leftJoin('#__gridbox_app AS a ON c.app_id = a.id')
                ->where('a.type = '.$db->quote('products'))
                ->where('c.id IN ('.$pks.')');
            $db->setQuery($query);
            $array = $db->loadObjectList();
        }

        return $array;
    }

    public static function preparePrice($price, $symbol = null, $position = null)
    {
        if ($symbol == null) {
            $symbol = self::$store->currency->symbol;
            $position = self::$store->currency->position;
        }
        $decimals = self::$store->currency->decimals;
        $separator = self::$store->currency->separator;
        $thousand = self::$store->currency->thousand;
        $price = round($price * 1, $decimals);
        $price = number_format($price, $decimals, $separator, $thousand);
        if ($position == '') {
            $value = $symbol.' '.$price;
        } else {
            $value = $price.' '.$symbol;
        }

        return $value;
    }

    public static function prepareGridbox()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_api')
            ->where('service = '.$db->quote('balbooa'));
        $db->setQuery($query);
        $balbooa = $db->loadObject();
        if (!$balbooa) {
            $obj = new stdClass();
            $obj->key = '{}';
            $obj->service = 'balbooa';
            $db->insertObject('#__gridbox_api', $obj);
        }
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
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_website')
            ->where('1');
        $db->setQuery($query);
        $website = $db->loadObject();
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
        include JPATH_ROOT.'/components/com_gridbox/helpers/store.php';
        self::$storeHelper = new store();
        self::$store = self::$storeHelper->getSettings();
    }

    public static function getNewPageAlias($type, $orig)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from('#__gridbox_pages')
            ->where('`page_alias` = '.$db->quote($type));
        $db->setQuery($query);
        $id = $db->loadResult();
        if (!empty($id)) {
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
            $type = self::getNewPageAlias($type, $orig);
        }

        return $type;
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
        $balbooa->key = json_encode($balbooa->key);
        $db->updateObject('#__gridbox_api', $balbooa, 'id');
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
    
    public static function getContentsCurl($url)
    {
        $http = JHttpFactory::getHttp();
        $body = '';
        $host = 'balbooa.com';
        if($socket =@ fsockopen($host, 80, $errno, $errstr, 30)) {
            $data = $http->get($url);
            $body = $data->body;
            fclose($socket);
        }
        
        return $body;
    }
    
    public static function getSystemPlugin()
    {
        $flag = JPluginHelper::isEnabled('system', 'gridbox');
        
        return $flag;
    }
    
    public static function getGlobal($body, $array)
    {
        $regex = '/\[global item=+(.*?)\]/i';
        preg_match_all($regex, $body, $matches, PREG_SET_ORDER);
        $db = JFactory::getDBO();
        foreach ($matches as $index => $match) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_library')
                ->where('`global_item` = ' . $db->quote($match[1]));
            $db->setQuery($query);
            $result = $db->loadObject();
            $array[] = $result;
        }
        
        return $array;
    }

    public static function getBaforms($body, $array)
    {
        $regex = '/\[forms ID=+(.*?)\]/i';
        preg_match_all($regex, $body, $matches, PREG_SET_ORDER);
        $db = JFactory::getDbo();
        $query = 'SHOW TABLES LIKE '.$db->quote('%baforms_forms');
        $db->setQuery($query);
        $result = $db->loadResult();
        if (!empty($result)) {
            foreach ($matches as $match) {
                if (!array_key_exists($match[1], $array)) {
                    $id = $match[1];
                    $obj = new StdClass();
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__baforms_forms')
                        ->where('`id` = ' .$db->quote($id));
                    $db->setQuery($query);
                    $obj->forms = $db->loadObject();
                    if (empty($obj)) {
                        continue;
                    }
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__baforms_items')
                        ->where('`form_id` = ' .$db->quote($id));
                    $db->setQuery($query);
                    $obj->items = $db->loadObjectList();
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__baforms_columns')
                        ->where('`form_id` = ' .$db->quote($id));
                    $db->setQuery($query);
                    $obj->columns = $db->loadObjectList();
                    $query = 'SHOW TABLES LIKE '.$db->quote('%baforms_forms_settings');
                    $db->setQuery($query);
                    $settings = $db->loadResult();
                    if (!empty($settings)) {
                        $query = $db->getQuery(true)
                            ->select('*')
                            ->from('#__baforms_forms_settings')
                            ->where('`form_id` = ' .$db->quote($id));
                        $db->setQuery($query);
                        $obj->settings = $db->loadObjectList();
                        $query = $db->getQuery(true)
                            ->select('*')
                            ->from('#__baforms_pages')
                            ->where('`form_id` = ' .$db->quote($id));
                        $db->setQuery($query);
                        $obj->pages = $db->loadObjectList();
                    }
                    $array[$id] = $obj;
                }
            }
        }
        
        return $array;
    }

    public static function getMainMenu($body, $array)
    {
        $regex = '/\[main_menu=+(.*?)\]/i';
        preg_match_all($regex, $body, $matches, PREG_SET_ORDER);
        if ($matches) {
            foreach ($matches as $match) {
                if (!array_key_exists($match[1], $array)) {
                    $id = $match[1];
                    $obj = new StdClass();
                    $db = JFactory::getDBO();
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__modules')
                        ->where('`id` = ' .$db->quote($id));
                    $db->setQuery($query);
                    $obj->module = $db->loadObject();
                    if (empty($obj->module)) {
                        $query = $db->getQuery(true)
                            ->select('*')
                            ->from('#__modules')
                            ->where('client_id = 0')
                            ->where('published = 1')
                            ->where('position = '.$db->quote('main-menu'))
                            ->where('module = '.$db->quote('mod_menu'));
                        $db->setQuery($query);
                        $obj->module = $db->loadObject();
                        if (empty($obj->module)) {
                            $query = $db->getQuery(true)
                                ->select('*')
                                ->from('#__modules')
                                ->where('client_id = 0')
                                ->where('published = 1')
                                ->where('module = '.$db->quote('mod_menu'));
                            $db->setQuery($query);
                            $obj->module = $db->loadObject();
                        }
                    }
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__assets')
                        ->where('`id` = ' .$db->quote($obj->module->asset_id));
                    $db->setQuery($query);
                    $obj->asset = $db->loadObject();
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__modules_menu')
                        ->where('`moduleid` = ' .$db->quote($obj->module->id));
                    $db->setQuery($query);
                    $obj->module_menu = $db->loadObject();
                    $params = $obj->module->params;
                    $params = json_decode($params);
                    $query = $db->getQuery(true);
                    $query->select("extension_id");
                    $query->from("#__extensions");
                    $query->where("type=" .$db->quote('component'))
                        ->where('element=' .$db->quote('com_gridbox'));
                    $db->setQuery($query);
                    $com_id = $db->loadResult();
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__menu_types')
                        ->where('`menutype` = ' .$db->quote($params->menutype));
                    $db->setQuery($query);
                    $obj->menu = $db->loadObject();
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__menu')
                        ->where('`menutype` = ' .$db->quote($params->menutype))
                        ->where('`component_id` = ' .$db->quote($com_id))
                        ->order('`id` DESC');
                    $db->setQuery($query);
                    $obj->menu_items = $db->loadObjectList();
                    $array[$id] = $obj;
                }
            }
        }
        
        return $array;
    }

    public static function getTags()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__gridbox_tags');
        $db->setQuery($query);
        $tags = $db->loadObjectList();

        return $tags;
    }

    public static function getTaxCountries()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_countries')
            ->order('title ASC');
        $db->setQuery($query);
        $countries = $db->loadObjectList();
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
}