<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class gridboxModelEditor extends JModelItem
{
    public function getTable($type = 'pages', $prefix = 'gridboxTable', $config = array()) 
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function checkAppFields($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('type')
            ->from('#__gridbox_app')
            ->where('id = '.$id);
        $db->setQuery($query);
        $type = $db->loadResult();

        return $type ? $type : '';
    }

    public function getProductData()
    {
        $input = JFactory::getApplication()->input;
        $product = new stdClass();
        $id = $input->get('id', 0, 'int');
        $db = JFactory::getDbo();
        $product->data = gridboxHelper::getProductData($id);
        $product->variations_map = gridboxHelper::getProductVariationsMap($id);
        $product->fields = new stdClass();
        $product->fields_data = new stdClass();
        foreach ($product->variations_map as $variation) {
            if (!isset($product->fields->{$variation->field_id})) {
                $product->fields->{$variation->field_id} = new stdClass();
                $product->fields->{$variation->field_id}->title = $variation->title;
                $product->fields->{$variation->field_id}->map = array();
                $product->fields->{$variation->field_id}->type = $variation->field_type;
            }
            $product->fields->{$variation->field_id}->map[] = $variation;
            $product->fields_data->{$variation->option_key} = $variation->value;
        }
        $product->badges = gridboxHelper::getProductBadges($id, $product->data);
        $query = $db->getQuery(true)
            ->select('p.id, p.title, p.intro_image AS image')
            ->from('#__gridbox_pages AS p')
            ->where('r.product_id = '.$id)
            ->leftJoin('#__gridbox_store_related_products AS r ON r.related_id = p.id')
            ->order('r.order_list ASC');
        $db->setQuery($query);
        $product->related = $db->loadObjectList();
        foreach ($product->related as $value) {
            $value->image = (!empty($value->image) && strpos($value->image, 'balbooa.com') === false ? JUri::root() : '').$value->image;
        }
        $product->relatedFlag = false;
        $query = $db->getQuery(true)
            ->select('a.page_items')
            ->from('#__gridbox_app AS a')
            ->where('p.id = '.$id)
            ->leftJoin('#__gridbox_pages AS p ON p.app_id = a.id');
        $db->setQuery($query);
        $page_items = $db->loadResult();
        if ($page_items) {
            $obj = json_decode($page_items);
            foreach ($obj as $value) {
                if (($value->type == 'related-posts' || $value->type == 'related-posts-slider') && $value->related == 'custom') {
                    $product->relatedFlag = true;
                    break;
                }
            }
        }
        
        return $product;
    }

    public function getProductOptions()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_products_fields');
        $db->setQuery($query);
        $array = $db->loadObjectList();

        return $array;
    }

    public function getAppFields($id)
    {
        $obj = gridboxHelper::getAppFilterFields($id);

        return $obj;
    }

    public function getPageAppId($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('app_id')
            ->from('#__gridbox_pages')
            ->where('id = '.$id);
        $db->setQuery($query);
        $app_id = $db->loadResult();

        return $app_id;
    }

    public function uploadDesktopFieldFile($file, $id)
    {
        $obj = new stdClass();
        if (isset($file['error']) && $file['error'] == 0) {
            $app_id = $this->getPageAppId($id);
            $ext = strtolower(JFile::getExt($file['name']));
            $dir = JPATH_ROOT.'/components/com_gridbox/assets/uploads/app-'.$app_id.'/';
            if (!JFolder::exists($dir)) {
                JFolder::create($dir);
            }
            $name = str_replace('.'.$ext, '', $file['name']);
            $filename = gridboxHelper::replace($name);
            $filename = JFile::makeSafe($filename);
            $name = str_replace('-', '', $filename);
            $name = str_replace('.', '', $name);
            if ($name == '') {
                $filename = date("Y-m-d-H-i-s").'.'.$ext;
            }
            $i = 2;
            $name = $filename;
            while (JFile::exists($dir.$name.'.'.$ext)) {
                $name = $filename.'-'.($i++);
            }
            $filename = $name.'.'.$ext;
            JFile::upload($file['tmp_name'], $dir.$filename);
            $obj = $this->addDesktopFieldFile($file['name'], $filename, $id, $app_id);
            $obj->path = 'components/com_gridbox/assets/uploads/app-'.$app_id.'/'.$filename;
        } else {
            $obj->error = 'ba-alert';
            $obj->msg = JText::_('NOT_ALLOWED_FILE_SIZE');
        }

        return $obj;
    }

    public function addDesktopFieldFile($name, $filename, $id, $app_id)
    {
        $db = JFactory::getDbo();
        $obj = new stdClass();
        $obj->page_id = $id;
        $obj->app_id = $app_id;
        $obj->name = $name;
        $obj->filename = $filename;
        $obj->date = date("Y-m-d-H-i-s");
        $db->insertObject('#__gridbox_fields_desktop_files', $obj);
        $obj->id = $db->insertid();

        return $obj;
    }

    public function setYandexMapsKey()
    {
        $input = JFactory::getApplication()->input;
        $key = $input->get('yandex_maps', '', 'string');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update('`#__gridbox_api`')
            ->set('`key` = '.$db->quote($key))
            ->where('`service` = '.$db->quote('yandex_maps'));
        $db->setQuery($query)
            ->execute();
    }

    public function setOpenWeatherMapKey()
    {
        $input = JFactory::getApplication()->input;
        $key = $input->get('openweathermap', '', 'string');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update('`#__gridbox_api`')
            ->set('`key` = '.$db->quote($key))
            ->where('`service` = '.$db->quote('openweathermap'));
        $db->setQuery($query)
            ->execute();
    }

    public function saveMenuItemTitle()
    {
        $input = JFactory::getApplication()->input;
        $obj = new stdClass();
        $obj->id = $input->get('id', 0, 'int');
        $obj->title = $input->get('title', '', 'string');
        $db = JFactory::getDbo();
        $db->updateObject('#__menu', $obj, 'id');
    }

    public function deleteMenuItem()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $parent_id = $input->get('parent_id', 0, 'int');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__menu')
            ->where("id = " . $id);
        $db->setQuery($query)
            ->execute();
        $query->clear()
            ->update('#__menu')
            ->where('parent_id = '.$id)
            ->set('parent_id = '.$parent_id);
        $db->setQuery($query)
            ->execute();
        JTable::addIncludePath(array(JPATH_ROOT.'/administrator/components/com_menus/tables'));
        $table = JTable::getInstance($type = 'menu', $prefix = 'menusTable', $config = array());
        $table->rebuild();
    }

    public function sortMenuItems()
    {
        $input = JFactory::getApplication()->input;
        $idArray = $input->get('idArray', array(), 'array');
        $pks = array();
        foreach ($idArray as $value) {
            $pks[] = $value['id'];
        }
        $idStr = implode(',', $pks);
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('lft, rgt')
            ->from('#__menu')
            ->where('id in ('.$idStr.')')
            ->order('lft ASC');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        for ($i = 0; $i < count($idArray); $i++) {
            $query->clear()
                ->update('#__menu')
                ->where('id = '.$idArray[$i]['id'])
                ->set('lft = '.$items[$i]->lft)
                ->set('parent_id = '.$idArray[$i]['parent_id'])
                ->set('rgt = '.$items[$i]->rgt);
            $db->setQuery($query)
                ->execute();
        }
        JTable::addIncludePath(array(JPATH_ROOT.'/administrator/components/com_menus/tables'));
        $table = JTable::getInstance($type = 'menu', $prefix = 'menusTable', $config = array());
        $table->rebuild();
    }

    public function setLibraryImage()
    {
        $input = JFactory::getApplication()->input;
        $str = $input->get('object', '', 'string');
        $obj = json_decode($str);
        $db = JFactory::getDbo();
        $db->updateObject('#__gridbox_library', $obj, 'id');
    }

    public function setStarRatings()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', '', 'string');
        $rating = $input->get('rating', 0, 'int');
        $str = $input->get('page', '', 'string');
        $page = json_decode($str);
        if ($page->option == 'com_gridbox' && $page->view == 'gridbox') {
            $page->view = 'page';
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__gridbox_star_ratings_users')
            ->where('`ip` = '.$db->quote($ip))
            ->where('`plugin_id` = '.$db->quote($id))
            ->where('`option` = '.$db->quote($page->option))
            ->where('`view` = '.$db->quote($page->view))
            ->where('`page_id` = '.$db->quote($page->id));
        $db->setQuery($query);
        $flag = $db->loadResult();
        $object = new stdClass();
        if (empty($flag)) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_star_ratings')
                ->where('`plugin_id` = '.$db->quote($id))
                ->where('`option` = '.$db->quote($page->option))
                ->where('`view` = '.$db->quote($page->view))
                ->where('`page_id` = '.$db->quote($page->id));
            $db->setQuery($query);
            $obj = $db->loadObject();
            if (!isset($obj->id)) {
                $obj = new stdClass();
                $obj->plugin_id = $id;
                $obj->rating = $rating;
                $obj->count = 1;
                $obj->option = $page->option;
                $obj->view = $page->view;
                $obj->page_id = $page->id;
                $db->insertObject('#__gridbox_star_ratings', $obj);
                $obj->id = $db->insertid();
            } else {
                $total = ($obj->rating * $obj->count + $rating) / ($obj->count + 1);
                $obj->rating = number_format($total, 2);
                $obj->count++;
                $db->updateObject('#__gridbox_star_ratings', $obj, 'id');
            }
            $user = new stdClass();
            $user->plugin_id = $obj->plugin_id;
            $user->option = $page->option;
            $user->view = $page->view;
            $user->page_id = $page->id;
            $user->ip = $ip;
            $db->insertObject('#__gridbox_star_ratings_users', $user);
            $object->result = '<span>'.JText::_('THANK_YOU_FOR_VOTE').'</span>';
        } else {
            $object->result = '<span>'.JText::_('ALREADY_VOTED').'</span>';
        }
        list($object->str, $object->rating) = gridboxHelper::getStarRatings($id, $page);

        return $object;
    }

    public function getPageTags()
    {
        $db = JFactory::getDbo();
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $query = $db->getQuery(true);
        $query->select('tag_id')
            ->from('#__gridbox_tags_map')
            ->where('`page_id` = '.$id);
        $db->setQuery($query);
        $ids = $db->loadObjectList();
        $tags = array();
        foreach ($ids as $id) {
            $query = $db->getQuery(true);
            $query->select('*')
                ->from('#__gridbox_tags')
                ->where('`id` = '.$id->tag_id);
            $db->setQuery($query);
            $tag = $db->loadObject();
            if (!empty($tag)) {
                $tags[$tag->id] = $tag->title;
            }
        }
        
        return $tags;
    }

    public function getTags()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__gridbox_tags');
        $db->setQuery($query);
        $tags = $db->loadObjectList();

        return $tags;
    }

    public function checkProductTour()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('`key`, `id`')
            ->from('`#__gridbox_api`')
            ->where('`service` = '.$db->quote('editor_tour'));
        $db->setQuery($query);
        $result = $db->loadObject();
        if (!isset($result->key)) {
            $result = new stdClass();
            $result->key = 'true';
            $obj = new stdClass();
            $obj->service = 'editor_tour';
            $obj->key = 'false';
            $db->insertObject('#__gridbox_api', $obj);
        }
        echo $result->key;
        exit;
    }

    public function setEditorView()
    {
        $app = JFactory::getApplication();
        $app->input->set('view', 'gridbox');
    }

    public function getLibrary()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $type = $input->get('type', '', 'string');
        $db = JFactory::getDbo();
        $table = '#__gridbox_library';
        $where = '`id` = '.$db->quote($id);
        if ($type == 'blocks') {
            $id = $input->get('id', '', 'string');
            $table = '#__gridbox_page_blocks';
            $where = '`title` = '.$db->quote($id);
        }
        $query = $db->getQuery(true)
            ->select('item')
            ->from($table)
            ->where($where);
        $db->setQuery($query);
        $string = $db->loadResult();
        $item = json_decode($string);
        $this->setEditorView();
        $item->html = gridboxHelper::checkModules($item->html, $item->items);
        $file = JPATH_ROOT.'/plugins/system/bagallery/bagallery.php';
        if (JFile::exists($file)) {
            include_once $file;
            $subj = JEventDispatcher::getInstance();
            $config = array('type' => 'system', 'name' => 'bagallery', 'params' => '{}');
            $plg = new plgSystemBagallery($subj, $config);
            $item->html = $plg->getContent($item->html);
        }
        $file = JPATH_ROOT.'/plugins/system/baforms/baforms.php';
        if (JFile::exists($file)) {
            include_once $file;
            $subj = JEventDispatcher::getInstance();
            $config = array('type' => 'system', 'name' => 'baforms', 'params' => '{}');
            $plg = new plgSystemBaforms($subj, $config);
            $html = $plg->getContent($item->html);
            if ($html) {
                $item->html = $html;
            }
        }
        $item->html = gridboxHelper::checkMainMenu($item->html);
        $item = json_encode($item);

        echo $item;
        exit;
    }

    public function removeLibrary()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__gridbox_library')
            ->where('`id` = '.$db->quote($id));
        $db->setQuery($query)
            ->execute();
        exit;
    }

    public function insertToLibrary($str)
    {
        $obj = json_decode($str);
        $db = JFactory::getDbo();
        if (!empty($obj->global_item)) {
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_library')
                ->where('`global_item` = '.$db->quote($obj->global_item));
            $db->setQuery($query);
            $id = $db->loadResult();
            if (!empty($id)) {
                $msg = new stdClass();
                $msg->text = JText::_('ALREADY_GLOBAL');
                $msg->type = 'ba-alert';
                $msg = json_encode($msg);
                echo($msg);
                exit;
            }
        }
        $obj->item = json_encode($obj->item);
        $db->insertObject('#__gridbox_library', $obj);
        $msg = new stdClass();
        $msg->text = JText::_('SAVED_TO_LIBRARY');
        $msg->type = '';
        $msg = json_encode($msg);
        echo $msg;
        exit;
    }

    public function requestAddLibrary()
    {
        $data = file_get_contents('php://input');
        $this->insertToLibrary($data);
    }

    public function addLibrary()
    {
        $input = JFactory::getApplication()->input;
        $data = $input->get('object', '', 'raw');
        $json = json_decode($data);
        if (empty($data) || !$json) {
            print_r('empty_data');exit;
        }
        $this->insertToLibrary($data);
    }

    public function savePostFieldsGroups($data, $id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('app_id')
            ->from('#__gridbox_pages')
            ->where('id = '.$id);
        $db->setQuery($query);
        $app_id = $db->loadResult();
        if (!empty($app_id)) {
            $obj = new stdClass();
            $obj->id = $app_id * 1;
            $obj->fields_groups = json_encode($data);
            $db->updateObject('#__gridbox_app', $obj, 'id');
        }
    }

    public function saveProductData($product, $id)
    {
        $db = JFactory::getDbo();
        $product->data->variations = json_encode($product->variations);
        $product->data->extra_options = json_encode($product->extra_options);
        $product->data->dimensions = json_encode($product->dimensions);
        if ($product->data->id != 0) {
            $db->updateObject('#__gridbox_store_product_data', $product->data, 'id');
        } else {
            $db->insertObject('#__gridbox_store_product_data', $product->data);
        }
        $digital = !empty($product->data->digital_file) ? json_decode($product->data->digital_file) : new stdClass();
        if (isset($digital->file)) {
            $dir = gridboxHelper::getDigitalFolder($id);
            if (JFolder::exists($dir)) {
                $files = JFolder::files($dir);
                foreach ($files as $file) {
                    if ($file != $digital->file->filename) {
                        JFile::delete($dir.$file);
                    }
                }
                $files = JFolder::files($dir);
                if (empty($files)) {
                    JFolder::delete($dir);
                }
            }
        }
        $pks = array();
        foreach ($product->variations_map as $obj) {
            if ($obj->id != 0) {
                $db->updateObject('#__gridbox_store_product_variations_map', $obj, 'id');
            } else {
                $db->insertObject('#__gridbox_store_product_variations_map', $obj);
                $obj->id = $db->insertid();
            }
            $pks[] = $obj->id;
        }
        $query = $db->getQuery(true)
            ->delete('#__gridbox_store_product_variations_map')
            ->where('product_id = '.$id);
        if (!empty($pks)) {
            $str = implode(', ', $pks);
            $query->where('id NOT IN ('.$str.')');
        }
        $db->setQuery($query)
            ->execute();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_badges_map')
            ->where('product_id = '.$id);
        $db->setQuery($query);
        $badges = $db->loadObjectList();
        $pks = array();
        foreach ($badges as $badge) {
            if (!isset($product->badges->{$badge->badge_id})) {
                $pks[] = $badge->id;
            } else {
                $product->badges->{$badge->badge_id}->obj = $badge;
            }
        }
        if (!empty($pks)) {
            $str = implode(', ', $pks);
            $query = $db->getQuery(true)
                ->delete('#__gridbox_store_badges_map')
                ->where('id IN ('.$str.')');
            $db->setQuery($query)
                ->execute();
        }
        foreach ($product->badges as $badge_id => $badge) {
            if (isset($badge->obj)) {
                $badge->obj->order_list = $badge->i;
                $db->updateObject('#__gridbox_store_badges_map', $badge->obj, 'id');
            } else {
                $badge->obj = new stdClass();
                $badge->obj->order_list = $badge->i;
                $badge->obj->badge_id = $badge_id;
                $badge->obj->product_id = $id;
                $db->insertObject('#__gridbox_store_badges_map', $badge->obj);
            }
        }
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_related_products')
            ->where('product_id = '.$id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $pks = array();
        foreach ($items as $item) {
            if (!isset($product->related->{$item->related_id})) {
                $pks[] = $item->id;
            } else {
                $product->related->{$item->related_id}->id = $item->id;
            }
        }
        if (!empty($pks)) {
            $str = implode(', ', $pks);
            $query = $db->getQuery(true)
                ->delete('#__gridbox_store_related_products')
                ->where('id IN ('.$str.')');
            $db->setQuery($query)
                ->execute();
        }
        foreach ($product->related as $related) {
            if (isset($related->id)) {
                $db->updateObject('#__gridbox_store_related_products', $related, 'id');
            } else {
                $db->insertObject('#__gridbox_store_related_products', $related);
            }
        }
    }

    public function gridboxSave($obj)
    {
        if (gridboxHelper::$website->compress_images != $obj->website->compress_images ||
            gridboxHelper::$website->images_max_size != $obj->website->images_max_size ||
            gridboxHelper::$website->images_quality != $obj->website->images_quality ||
            gridboxHelper::$website->adaptive_images != $obj->website->adaptive_images ||
            gridboxHelper::$website->adaptive_quality != $obj->website->adaptive_quality) {
            JFolder::delete(JPATH_ROOT.'/'.IMAGE_PATH.'/compressed');
        }
        gridboxHelper::$website = $obj->website;
        gridboxHelper::siteRules($obj->breakpoints);
        gridboxHelper::saveTheme($obj->theme, $obj->page->theme);
        if (!isset($obj->edit_type)) {
            gridboxHelper::savePage($obj->page, $obj->page->id);
            gridboxHelper::savePageFields($obj->fields, $obj->page->id);
            if (isset($obj->product)) {
                $this->saveProductData($obj->product, $obj->page->id);
            }
            $this->savePostFieldsGroups($obj->fieldsGroups, $obj->page->id);
        } else if ($obj->edit_type == 'blog') {
            gridboxHelper::saveAppLayout($obj->page, $obj->page->id);
        } else if ($obj->edit_type == 'system') {
            gridboxHelper::saveSystemPage($obj->page, $obj->page->id);
        } else if ($obj->edit_type == 'post-layout') {
            gridboxHelper::savePostLayout($obj->page, $obj->page->id);
        }
        gridboxHelper::saveCodeEditor($obj->code, $obj->page->theme);
        gridboxHelper::saveWebsite($obj->website);
        gridboxHelper::saveGlobalItems($obj->global);
        $performance = gridboxHelper::getPerformance();
        $options = array(
            'defaultgroup' => 'gridbox',
            'browsercache' => $performance->browser_cache,
            'caching'      => false,
        );
        $cache = JCache::getInstance('page', $options);
        $cacheFolders = JFolder::folders(JPATH_CACHE);
        foreach ($cacheFolders as $group) {
            $cache->clean($group);
        }
        echo JText::_('GRIDBOX_SAVED');
        exit;
    }

    public function checkMainMenu()
    {
        $input = JFactory::getApplication()->input;
        $menu = $input->get('main_menu', 0, 'int');
        $data = $input->get('items', '', 'raw');
        $id = $input->get('id', 0, 'int');
        $items = new stdClass();
        $items->{$id} = json_decode($data);
        $html = '<div class="ba-item-main-menu ba-item" id="'.$id.'">[main_menu='.$menu.']</div>';
        $html = gridboxHelper::checkMainMenu($html);
        $html = gridboxHelper::checkDOM($html, $items);
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        include_once JPATH_ROOT.'/components/com_gridbox/libraries/php/phpQuery/phpQuery.php';
        $dom = phpQuery::newDocument($html);
        $html = pq('.ba-item-main-menu')->html();
        echo $html;
        exit;
    }

    public function setMapsKey()
    {
        $input = JFactory::getApplication()->input;
        $key = $input->get('google_maps_key', '', 'string');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update('`#__gridbox_api`')
            ->set('`key` = '.$db->quote($key))
            ->where('`service` = '.$db->quote('google_maps'));
        $db->setQuery($query)
            ->execute();
    }

    public function getBlocksLicense()
    {
        $str = file_get_contents('php://input');
        $data = json_decode($str);
        $this->installBlocks($data);
        echo JText::_('BLOCKS_INSTALLED');
        exit;
    }

    public function getPluginLicense()
    {
        $input = JFactory::getApplication()->input;
        $str = $input->get('data', '', 'string');
        $data = json_decode($str);
        $this->installPlugin($data);
        echo JText::_('PLUGIN_INSTALLED');
        exit;
    }

    public function loadLayout()
    {
        $input = JFactory::getApplication()->input;
        $layout = $input->get('layout', '', 'raw');
        $count = $input->get('count', '', 'raw');
        $span = explode('+', $count);
        $count = count($span);
        $obj = new stdClass();
        $obj->items = new stdClass();
        $now = strtotime(date('Y-m-d G:i:s')) * 10;
        include JPATH_ROOT.'/components/com_gridbox/views/layout/'.$layout.'.php';
        $obj->html = $out;
        echo json_encode($obj);
        exit;
    }

    public function reloadModules($id, $type)
    {
        $this->setEditorView();
        $out = '['.$type.' ID='.$id.']';
        if ($type == 'modules') {
            $out = gridboxHelper::checkModules($out, '{}');
            $str = $this->returnStyle();
            $out = $str.$out;
        } else if ($type == 'gallery') {
            $file = JPATH_ROOT.'/plugins/system/bagallery/bagallery.php';
            if (JFile::exists($file)) {
                include_once $file;
                $subj = JEventDispatcher::getInstance();
                $config = array('type' => 'system', 'name' => 'bagallery', 'params' => '{}');
                $plg = new plgSystemBagallery($subj, $config);
                $out = $plg->getContent($out);
            }
        } else if ($type == 'forms') {
            $file = JPATH_ROOT.'/plugins/system/baforms/baforms.php';
            if (JFile::exists($file)) {
                include_once $file;
                $subj = JEventDispatcher::getInstance();
                $config = array('type' => 'system', 'name' => 'baforms', 'params' => '{}');
                $plg = new plgSystemBaforms($subj, $config);
                $out = $plg->getContent($out);
            }
        }

        return $out;
    }

    public function contentSliderAdd()
    {
        $input = JFactory::getApplication()->input;
        $data = $input->get('data', array(), 'array');
        $ind = $input->get('ind', 1, 'int');
        $title = $input->get('title', 1, 'int');
        $now = strtotime(date('Y-m-d G:i:s')) * 10;
        $obj = new stdClass();
        $obj->items = new stdClass();
        include JPATH_ROOT.'/components/com_gridbox/views/layout/content-slider-li.php';
        $obj->slides = $slides;
        $obj->html = $out;
        $str = json_encode($obj);

        return $str;
    }

    public function loadPlugin()
    {
        $input = JFactory::getApplication()->input;
        $layout = $input->get('layout', '', 'string');
        $id = $input->get('id', 0, 'int');
        $edit_type = $input->get('edit_type', 'edit_type', 'string');
        if (!gridboxHelper::checkPlugin($layout)) {
            echo '';
            exit;
        }
        if ($layout == 'content-slider') {
            $data = $input->get('data', array(), 'array');
        } else {
            $data = $input->get('data', '', 'string');
        }
        $obj = new stdClass();
        $obj->items = new stdClass();
        $now = strtotime(date('Y-m-d G:i:s')) * 10;
        $this->setEditorView();
        include JPATH_ROOT.'/components/com_gridbox/views/layout/'.$layout.'.php';
        if ($layout == 'modules') {
            $out = gridboxHelper::checkModules($out, $obj->items);
            $str = $this->returnStyle();
            $out = str_replace('<input type="hidden" class="modules-styles">', $str, $out);
        } else if ($layout == 'bagallery') {
            $file = JPATH_ROOT.'/plugins/system/bagallery/bagallery.php';
            if (JFile::exists($file)) {
                include_once $file;
                $subj = JEventDispatcher::getInstance();
                $config = array('type' => 'system', 'name' => 'bagallery', 'params' => '{}');
                $plg = new plgSystemBagallery($subj, $config);
                $str = '[gallery ID='.$data.']';
                $str = $plg->getContent($str);
                $out = str_replace('[gallery ID='.$data.']', $str, $out);
            }
        } else if ($layout == 'baforms') {
            $file = JPATH_ROOT.'/plugins/system/baforms/baforms.php';
            if (JFile::exists($file)) {
                include_once $file;
                $subj = JEventDispatcher::getInstance();
                $config = array('type' => 'system', 'name' => 'baforms', 'params' => '{}');
                $plg = new plgSystemBaforms($subj, $config);
                $str = '[forms ID='.$data.']';
                $str = $plg->getContent($str);
                $out = str_replace('[forms ID='.$data.']', $str, $out);
            }
        } else if ($layout == 'menu') {
            $out = gridboxHelper::checkMainMenu($out);
            $out = gridboxHelper::checkDOM($out, $obj->items);
        } else if ($layout == 'post-tags') {
            $str = gridboxHelper::getPostTags($id);
            $out = str_replace('[blog_post_tags]', $str, $out);
        } else if ($layout == 'tags') {
            if ($edit_type == 'blog') {
                $obj->items->{'item-'.$now}->app = $id;
            } else if (empty($edit_type)) {
                $obj->items->{'item-'.$now}->app = gridboxHelper::getAppId($id);
            }
            if (empty($obj->items->{'item-'.$now}->app)) {
                $obj->items->{'item-'.$now}->app = 0;
            }
            $str = gridboxHelper::getBlogTags($obj->items->{'item-'.$now}->app, '', $obj->items->{'item-'.$now}->count);
            $out = str_replace('[ba_blog_tags]', $str, $out);
        } else if ($layout == 'categories') {
            if ($edit_type == 'blog') {
                $obj->items->{'item-'.$now}->app = $id;
            } else if (empty($edit_type)) {
                $obj->items->{'item-'.$now}->app = gridboxHelper::getAppId($id);
            }
            if (empty($obj->items->{'item-'.$now}->app)) {
                $obj->items->{'item-'.$now}->app = 0;
            }
            $categories = gridboxHelper::getBlogCategories($obj->items->{'item-'.$now}->app);
            $str = gridboxHelper::getBlogCategoriesHtml($categories, $obj->items->{'item-'.$now}->maximum);
            $out = str_replace('[ba_blog_categories]', $str, $out);
        } else if ($layout == 'recent-comments') {
            $id = $obj->items->{'item-'.$now}->app;
            $sort = $obj->items->{'item-'.$now}->sorting;
            $limit = $obj->items->{'item-'.$now}->limit;
            $maximum = $obj->items->{'item-'.$now}->maximum;
            gridboxHelper::$editItem = $obj->items->{'item-'.$now};
            $str = gridboxHelper::getRecentComments($id, $sort, $limit, $maximum, '');
            $out = str_replace('[ba_recent_comments]', $str, $out);
        } else if ($layout == 'recent-reviews') {
            $id = $obj->items->{'item-'.$now}->app;
            $sort = $obj->items->{'item-'.$now}->sorting;
            $limit = $obj->items->{'item-'.$now}->limit;
            $maximum = $obj->items->{'item-'.$now}->maximum;
            gridboxHelper::$editItem = $obj->items->{'item-'.$now};
            $str = gridboxHelper::getRecentReviews($id, $sort, $limit, $maximum, '');
            $out = str_replace('[ba_recent_reviews]', $str, $out);
        } else if ($layout == 'recent-posts') {
            if ($edit_type == 'blog') {
                $obj->items->{'item-'.$now}->app = $id;
            } else if (empty($edit_type)) {
                $obj->items->{'item-'.$now}->app = gridboxHelper::getAppId($id);
            }
            if (empty($obj->items->{'item-'.$now}->app)) {
                $obj->items->{'item-'.$now}->app = 0;
            }
            $id = $obj->items->{'item-'.$now}->app;
            $sort = $obj->items->{'item-'.$now}->sorting;
            $limit = $obj->items->{'item-'.$now}->limit;
            $maximum = $obj->items->{'item-'.$now}->maximum;
            $featured = $obj->items->{'item-'.$now}->featured;
            gridboxHelper::$editItem = $obj->items->{'item-'.$now};
            $str = gridboxHelper::getRecentPosts($id, $sort, $limit, $maximum, '', $featured);
            $out = str_replace('[ba_recent_posts]', $str, $out);
        } else if ($layout == 'author') {
            $str = gridboxHelper::getPostAuthor($id);
            $out = str_replace('[ba_blog_post_author]', $str, $out);
        } else if ($layout == 'post-intro') {
            $out = gridboxHelper::checkDOM($out, $obj->items);
        } else if ($layout == 'comments-box' || $layout == 'reviews') {
            $out = gridboxHelper::checkDOM($out, $obj->items);
        } else if ($layout == 'recent-posts-slider') {
            if ($edit_type == 'blog') {
                $obj->items->{'item-'.$now}->app = $id;
            } else if (empty($edit_type)) {
                $obj->items->{'item-'.$now}->app = gridboxHelper::getAppId($id);
            }
            if (empty($obj->items->{'item-'.$now}->app)) {
                $obj->items->{'item-'.$now}->app = 0;
            }
            $id = $obj->items->{'item-'.$now}->app;
            $sort = $obj->items->{'item-'.$now}->sorting;
            $limit = $obj->items->{'item-'.$now}->limit;
            $maximum = $obj->items->{'item-'.$now}->maximum;
            gridboxHelper::$editItem = $obj->items->{'item-'.$now};
            $str = gridboxHelper::getRecentPosts($id, $sort, $limit, $maximum);
            $out = str_replace('[ba_recent_posts_slider]', $str, $out);
        } else if ($layout == 'related-posts') {
            $obj->items->{'item-'.$now}->app = gridboxHelper::getAppId($id);
            if ($edit_type == 'post-layout') {
                $obj->items->{'item-'.$now}->app = $id;
            }
            $id = $obj->items->{'item-'.$now}->app;
            $related = $obj->items->{'item-'.$now}->related;
            $limit = $obj->items->{'item-'.$now}->limit;
            $maximum = $obj->items->{'item-'.$now}->maximum;
            gridboxHelper::$editItem = $obj->items->{'item-'.$now};
            $str = gridboxHelper::getRelatedPosts($id, $related, $limit, $maximum, $id);
            $out = str_replace('[ba_related_posts]', $str, $out);
        } else if ($layout == 'related-posts-slider' || $layout == 'recently-viewed-products') {
            $out = gridboxHelper::checkDOM($out, $obj->items);
        } else if ($layout == 'post-navigation') {
            $maximum = $obj->items->{'item-'.$now}->maximum;
            gridboxHelper::$editItem = $obj->items->{'item-'.$now};
            $str = gridboxHelper::getPostNavigation($maximum, $id);
            $str .= '<div class="ba-post-navigation-info"><a href="">'.JText::_('PREVIOUS').'</a></div>';
            $str .= '<div class="ba-post-navigation-info"><a href="">'.JText::_('NEXT').'</a></div>';
            $out = str_replace('[ba_post_navigation]', $str, $out);
        }
        $obj->html = $out;
        echo json_encode($obj);
        exit;
    }

    public function returnStyle()
    {
        $str = '';
        $doc = JFactory::getDocument();
        foreach ($doc->_scripts as $key => $script) {
            $str .= '<script src="'.$key.'" type="text/javascript"';
            if (isset($script['defer']) && !empty($script['defer'])) {
                $str .= ' defer';
            }
            if (isset($script['async']) && !empty($script['async'])) {
                $str .= ' async';
            }
            $str .= '></script>';
        }
        foreach ($doc->_script as $key => $script) {
            $str .= '<script type="'.$key.'">'.$script.'</script>';
        }
        foreach ($doc->_styleSheets as $key => $link) {
            $str .= '<link href="'.$key.'" type="text/css"';
            if (isset($script['media']) && !empty($link['media'])) {
                $str .= ' media="'.$link['media'].'"';
            }
            $str .= ' rel="stylesheet">';
        }
        foreach ($doc->_style as $key => $style) {
            $str .= '<style type="'.$key.'">'.$style.'</style>';
        }

        return $str;
    }

    public function getWebsite()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('`#__gridbox_website`')
            ->where('`id` = 1');
        $db->setQuery($query);
        $result = $db->loadObject();

        return $result;
    }

    public function getLibraryItems()
    {
        $obj = new stdClass();
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('title, global_item, id, image')
            ->from('`#__gridbox_library`')
            ->where('`type` = ' .$db->quote('section'));
        $db->setQuery($query);
        $obj->sections = $db->loadObjectList();
        $query = $db->getQuery(true);
        $query->select('title, global_item, id, image')
            ->from('`#__gridbox_library`')
            ->where('`type` = ' .$db->quote('plugin'));
        $db->setQuery($query);
        $obj->plugins = $db->loadObjectList();

        return $obj;
    }

    private function getUsername($name, $pwd)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('username')
            ->from('#__users')
            ->where('username = '.$db->quote($name))
            ->where('password = '.$db->quote($pwd));
        $db->setQuery($query);
        $username = $db->loadResult();

        return $username;
    }

    public function getItem($id = null)
    {
        $input = JFactory::getApplication()->input;
        $name = $input->get('name', '', 'raw');
        $pwd = $input->get('pwd', '', 'raw');
        $username = $this->getUsername($name, $pwd);
        if (!JFactory::getUser()->authorise('core.edit', 'com_gridbox') && !empty($username)) {
            gridboxHelper::userLogin($username);
        }
        if (!empty($name) || !empty($pwd)) {
            $get = $input->get->getArray(array());
            $id = $get['id'];
            unset($get['name']);
            unset($get['pwd']);
            unset($get['id']);
            $get['id'] = $id;
            $url = http_build_query($get);
            header('Location: '.JUri::current().'?'.$url);
            exit;
        }
        $db = $this->getDbo();
        $title = $input->get('ba-title', '', 'string');
        $edit_type = $input->get('edit_type', '', 'string');
        $id = $input->get('id', 0, 'int');
        if ($id != 0) {
            $query = $db->getQuery(true);
            if ($edit_type == 'blog' || $edit_type == 'post-layout') {
                $query->select('b.*')
                    ->from('`#__gridbox_app` AS b')
                    ->where('b.type <> '.$db->quote('system_apps'))
                    ->where('b.id = ' .$id)
                    ->select('t.title as ThemeTitle')
                    ->leftJoin('`#__template_styles` AS t'
                        . ' ON '
                        . $db->quoteName('b.theme')
                        . ' = ' 
                        . $db->quoteName('t.id')
                    );
            } else if (empty($edit_type)) {
                $query->select('b.*')
                    ->from('`#__gridbox_pages` AS b')
                    ->where('b.id = ' .$id)
                    ->select('t.title as ThemeTitle')
                    ->leftJoin('`#__template_styles` AS t'
                        . ' ON '
                        . $db->quoteName('b.theme')
                        . ' = ' 
                        . $db->quoteName('t.id')
                    )
                    ->select('a.type as app_type')
                    ->leftJoin('`#__gridbox_app` AS a'
                        . ' ON '
                        . $db->quoteName('b.app_id')
                        . ' = ' 
                        . $db->quoteName('a.id')
                    );
            } else if ($edit_type == 'system') {
                $query->select('*')
                    ->from('#__gridbox_system_pages')
                    ->where('id = '.$id);
            }
            $db->setQuery($query);
            $item = $db->loadObject();
            if (isset($item->app_type) && $item->app_type != 'single') {
                $query = $db->getQuery(true)
                    ->select('a.id, a.avatar, a.title')
                    ->from('#__gridbox_authors_map AS m')
                    ->where('m.page_id = '.$item->id)
                    ->leftJoin('#__gridbox_authors AS a ON a.id = m.author_id')
                    ->order('m.id ASC');
                $db->setQuery($query);
                $item->authors = $db->loadObjectList();
            } else {
                $item->authors = array();
            }
        } else {
            $item = new stdClass();
        }
        
        return $item;
    }

    public function getPageLayout()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('a.page_layout, a.type')
            ->from('#__gridbox_app AS a')
            ->leftJoin('#__gridbox_pages AS p ON a.id = p.app_id')
            ->where('p.id = '.$id);
        $db->setQuery($query);
        $item = $db->loadObject();
        if (empty($item->page_layout)) {
            $item->page_layout = JFile::read(JPATH_ROOT.'/components/com_gridbox/views/layout/apps/'.$item->type.'/default.html');
        }
        
        return $item->page_layout;
    }

    public function getAuthors()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('a.title, a.id, a.avatar, u.username')
            ->from('`#__gridbox_authors` AS a')
            ->leftJoin('`#__users` AS u ON '.$db->quoteName('u.id').' = '.$db->quoteName('a.user_id'));
        $db->setQuery($query);
        $authors = $db->loadObjectList();

        return $authors;
    }

    public function getThemes()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title, home')
            ->from('#__template_styles')
            ->where('`template`=' .$db->quote('gridbox'))
            ->order('home desc');
        $db->setQuery($query);
        $themes = new stdClass();
        $themes->list = $db->loadObjectList();
        $themes->default = $themes->list[0];
        $app_id = JFactory::getApplication()->input->get('app_id', 0, 'int');
        if (!empty($app_id)) {
            $query = $db->getQuery(true)
                ->select('theme')
                ->from('#__gridbox_app')
                ->where('id = '.$app_id);
            $db->setQuery($query);
            $theme = $db->loadResult();
            foreach ($themes->list as $value) {
                if ($value->id == $theme) {
                    $themes->default = $value;
                    break;
                }
            }
        }
        
        return $themes;
    }

    public function installBlocks($item)
    {
        $db = JFactory::getDbo();
        $obj = new stdClass();
        $obj->type = $item->type;
        $obj->title = $item->title;
        $obj->image = $item->image;
        $object = json_decode($item->data);
        $object->items = json_decode($object->items);
        $obj->item = json_encode($object);
        $db->insertObject('#__gridbox_page_blocks', $obj);
        $array = explode(',', $item->imageData);
        $method = $item->method;
        $content = $method($array[1]);
        JFile::write(JPATH_COMPONENT.'/assets/images/page-blocks/'.$obj->image, $content);
    }

    public function installPlugin($data)
    {
        $db = JFactory::getDbo();
        foreach ($data as $group) {
            foreach ($group as $plugin) {
                $db->insertObject('#__gridbox_plugins', $plugin);
            }
        }
    }

    public function checkBlocks($block) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from('#__gridbox_page_blocks')
            ->where('`title` = ' .$db->quote($block));
        $db->setQuery($query);
        $id = $db->loadResult();
        
        return $id;
    }

    public function checkPlugin($plugin) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from('#__gridbox_plugins')
            ->where('`title` = ' .$db->quote($plugin));
        $db->setQuery($query);
        $id = $db->loadResult();
        
        return $id;
    }

    public function returnObj($id, $plugin)
    {
        $obj = new stdClass();
        $obj->id = $id;
        $obj->title = trim((string)$plugin->title);
        $obj->image = trim((string)$plugin->image);
        $obj->type = trim((string)$plugin->type);
        $obj->joomla_constant = trim((string)$plugin->joomla_constant);

        return $obj;
    }

    public function getBlocks()
    {
        $blocks = array('cover' => array(), 'about-us' => array(), 'services' => array(), 
            'description' => array(), 'steps' => array(), 'schedule' => array(), 'features' => array(),
            'pricing-table' => array(), 'pricing-list' => array(), 'testimonials' => array(), 'team' => array(),
            'counters' => array(), 'faq' => array(), 'call-to-action' => array());
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('type, title, image, id')
            ->from('#__gridbox_page_blocks')
            ->order('id asc');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as $item) {
            if (isset($blocks[$item->type])) {
                $blocks[$item->type][$item->title] = $item;
            }
        }

        return $blocks;
    }

    public function checkInstalledBlog($type = '')
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

    public function getPlugins()
    {
        $input = JFactory::getApplication()->input;
        $edit_type = $input->get('edit_type', '', 'string');
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__gridbox_plugins')
            ->order('joomla_constant asc');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $plugins = array('content' => array(), 'info' => array(), 'navigation' => array(),
            'social' => array(), 'blog' => array(), 'store' => array(), 'fields' => array(), '3rd-party-plugins' => array());
        $blog = $this->checkInstalledBlog();
        if ($blog) {
            $plugins['blog'] = $this->getBlogPlugins();
        } else {
            unset($plugins['blog']);
        }
        $products = $this->checkInstalledBlog('products');
        if ($products) {
            $plugins['store'] = $this->getStorePlugins();
        } else {
            unset($plugins['store']);
        }
        foreach ($items as $item) {
            if ($item->title == 'ba-instagram') {
                continue;
            }
            $plugins[$item->type][$item->title] = $item;
        }
        if (isset(gridboxHelper::$systemApps->comments) && $edit_type != 'blog') {
            $comments = new stdClass();
            $comments->title = 'ba-comments-box';
            $comments->image = 'plugins-comments-box';
            $comments->type = 'social';
            $comments->joomla_constant = 'COMMENTS_BOX';
            $plugins['social'][] = $comments;
        }
        if (isset(gridboxHelper::$systemApps->comments)) {
            $comments = new stdClass();
            $comments->title = 'ba-recent-comments';
            $comments->image = 'plugins-recent-comments';
            $comments->type = 'social';
            $comments->joomla_constant = 'RECENT_COMMENTS';
            $plugins['social'][] = $comments;
        }
        if (isset(gridboxHelper::$systemApps->reviews) && $edit_type != 'blog') {
            $reviews = new stdClass();
            $reviews->title = 'ba-reviews';
            $reviews->image = 'plugins-reviews';
            $reviews->type = 'social';
            $reviews->joomla_constant = 'REVIEWS';
            $plugins['social'][] = $reviews;
        }
        if (isset(gridboxHelper::$systemApps->reviews)) {
            $reviews = new stdClass();
            $reviews->title = 'ba-recent-reviews';
            $reviews->image = 'plugins-recent-reviews';
            $reviews->type = 'social';
            $reviews->joomla_constant = 'RECENT_REVIEWS';
            $plugins['social'][] = $reviews;
        }
        if (isset(gridboxHelper::$systemApps->comments) || isset(gridboxHelper::$systemApps->reviews)) {
            usort($plugins['social'], function($a, $b){
                if ($a->joomla_constant == $b->joomla_constant) {
                    return 0;
                }
                return ($a->joomla_constant < $b->joomla_constant) ? -1 : 1;
            });
        }

        return $plugins;
    }

    public function getStorePlugins()
    {
        $plugins = array('cart' => 'plugins-cart',
            'add-to-cart' => 'plugins-add-to-cart',
            'product-slideshow' => 'plugins-slideshow',
            'product-gallery' => 'flaticon-photo-camera-1',
            'wishlist' => 'flaticon-like-2',
            'store-search' => 'flaticon-search',
            //'recently-viewed-products' => 'flaticon-television'
        );
        $store = array();
        foreach ($plugins as $plugin => $image) {
            $obj = new stdClass();
            $obj->title = 'ba-'.$plugin;
            $obj->image = $image;
            $obj->type = 'store';
            $obj->joomla_constant = strtoupper(str_replace('-', '_', $plugin));
            $store[$obj->title] = $obj;
        }

        return $store;
    }

    public function getBlogPlugins()
    {
        $plugins = array('tags', 'categories', 'recent-posts', 'search', 'recent-posts-slider', 'event-calendar',
            'fields-filter', 'google-maps-places');
        $icons = array('flaticon-bookmark', 'flaticon-folder-13', 'flaticon-calendar-6', 'flaticon-search',
            'flaticon-tabs', 'flaticon-calendar-1', 'flaticon-checked', 'plugins-google-maps');
        $blog = array();
        while ($plugin = array_pop($plugins)) {
            $obj = new stdClass();
            $obj->title = 'ba-'.$plugin;
            $obj->image = array_pop($icons);
            $obj->type = 'blog';
            $obj->joomla_constant = strtoupper(str_replace('-', '_', $plugin));
            if ($plugin == 'recent-posts-slider') {
                $obj->joomla_constant = 'POST_SLIDER';
            } else if ($plugin == 'fields-filter') {
                $obj->joomla_constant = 'CONTENT_FILTERS';
            }
            $blog[$obj->title] = $obj;
        }

        return $blog;
    }

    public function getMenus()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('menutype, title')
            ->from('#__menu_types');
        $db->setQuery($query);
        $menus = $db->loadObjectList();
        
        return $menus;
    }

    public function getAllApps()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('title, id')
            ->from('#__gridbox_app')
            ->where('type <> '.$db->quote('system_apps'));
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $obj = new stdClass();
        $obj->title = JText::_('PAGES');
        $obj->id = 0;
        $array = array($obj);
        $array = array_merge($array, $items);

        return $array;
    }

    public function getApps()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('title, id, type')
            ->from('#__gridbox_app')
            ->where('type <> '.$db->quote('system_apps'))
            ->where('type <> '.$db->quote('single'));
        $db->setQuery($query);
        $items = $db->loadObjectList();

        return $items;
    }

    public function getCategories()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('title, id, app_id')
            ->from('#__gridbox_categories')
            ->order('order_list ASC');
        $db->setQuery($query);
        $items = $db->loadObjectList();

        return $items;
    }
    
    public function getForm()
    {
        $form = JForm::getInstance('gridbox', JPATH_COMPONENT.'/models/forms/gridbox.xml');
        
        return $form;
    }

    public function getJce()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('enabled')
            ->from('`#__extensions`')
            ->where('`element` = '.$db->quote('jce'))
            ->where('`folder` = '.$db->quote('editors'));
        $db->setQuery($query);
        $id = $db->loadResult();

        return $id;
    }
}