<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die; 

jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.file');

class gridboxModelgridbox extends JModelAdmin
{
    public $appFields;

    public function getTable($type = 'pages', $prefix = 'gridboxTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function importJoomlaTags()
    {
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $id = $app->input->get('id', 0, 'int');
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__tags')
            ->where('id = '.$id);
        $db->setQuery($query);
        $tag = $db->loadObject();
        $obj = new stdClass();
        $obj->title = $tag->title;
        $obj->alias = $tag->alias;
        $obj->alias = gridboxHelper::getAlias($obj->alias, '#__gridbox_tags');
        $obj->access = $tag->access;
        $obj->language = $tag->language;
        $images = json_decode($tag->images);
        if (isset($images->image_fulltext)) {
            $obj->image = $images->image_fulltext;
        }
        $obj->meta_description = $tag->metadesc;
        $obj->meta_keywords = $tag->metakey;
        $metadata = json_decode($tag->metadata);
        if (isset($metadata->robots)) {
            $obj->robots = $metadata->robots;
        }
        $obj->hits = $tag->hits;
        $db->insertObject('#__gridbox_tags', $obj);
        $id = $db->insertid();

        return $id;
    }

    public function importJoomlaCategories()
    {
        $app = JFactory::getApplication();
        $categories = $app->input->get('categories', array(), 'array');
        $app_id = $app->input->get('app_id', 0, 'int');
        $id = $app->input->get('id', 0, 'int');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where('id = '.$id);
        $db->setQuery($query);
        $category = $db->loadObject();
        $object = new stdClass();
        $object->title = $category->title;
        $object->alias = $category->alias;
        $object->published = $category->published;
        $object->alias = gridboxHelper::getAlias($object->alias, '#__gridbox_categories');
        $object->access = $category->access;
        $metadata = json_decode($category->metadata);
        if (isset($metadata->robots)) {
            $object->robots = $metadata->robots;
        }
        $object->language = $category->language;
        $object->description = $category->description;
        $params = json_decode($category->params);
        if (isset($params->image)) {
            $object->image = $params->image;
        }
        $object->meta_description = $category->metadesc;
        $object->meta_keywords = $category->metakey;
        $object->parent = $categories[$category->parent_id];
        $object->app_id = $app_id;
        $db->insertObject('#__gridbox_categories', $object);
        $id = $db->insertid();

        return $id;
    }

    public function importJoomlaArticles()
    {
        $app = JFactory::getApplication();
        $app_type = $app->input->get('app_type', 'single', 'string');
        $app_id = $app->input->get('app_id', 0, 'int');
        $id = $app->input->get('id', 0, 'int');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__content')
            ->where('id = '.$id);
        $db->setQuery($query);
        $article = $db->loadObject();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__template_styles')
            ->where('template = ' .$db->quote('gridbox'))
            ->where('home = 1');
        $db->setQuery($query);
        $theme = $db->loadResult();
        if (!$theme) {
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__template_styles')
                ->where('template = ' .$db->quote('gridbox'));
            $db->setQuery($query);
            $theme = $db->loadResult();
        }
        $page = new stdClass();
        $page->title = $article->title;
        $page->page_alias = $this->getNewAlias($article->alias);
        $page->theme = $theme;
        $page->published = $article->state;
        $page->created = $article->publish_up;
        $page->meta_keywords = $article->metakey;
        $page->meta_description = $article->metadesc;
        $page->hits = $article->hits;
        $page->language = $article->language;
        $images = json_decode($article->images);
        if (isset($images->image_intro)) {
            $page->intro_image = $images->image_intro;
        }
        $metadata = json_decode($article->metadata);
        if (isset($metadata->robots)) {
            $page->robots = $metadata->robots;
        }
        $page->page_access = $article->access;
        $page->app_id = $app_id;
        $attribs = json_decode($article->attribs);
        if (isset($attribs->article_page_title)) {
            $page->meta_title = $attribs->article_page_title;
        }        
        if (!empty($app_type) && $app_type != 'single') {
            $page->end_publishing = $article->publish_down;
            if (isset($images->image_intro_alt)) {
                $page->image_alt = $images->image_intro_alt;
            }
            $page->featured = $article->featured;
            $categories = $app->input->get('categories', array(), 'array');
            $page->page_category = $categories[$article->catid];
        }
        $text = $article->introtext.$article->fulltext;
        $count = '12';
        $span = explode('+', $count);
        $count = count($span);
        $obj = new stdClass();
        $obj->items = new stdClass();
        $now = strtotime(date('Y-m-d G:i:s')) * 10;
        include JPATH_ROOT.'/components/com_gridbox/views/layout/import/article.php';
        $page->params = $out;
        $page->style = json_encode($obj->items);
        $db->insertObject('#__gridbox_pages', $page);
        if (!empty($app_type) && $app_type != 'single') {
            $pageId = $db->insertid();
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_authors')
                ->where('user_id = '.$article->created_by);
            $db->setQuery($query);
            $author = $db->loadResult();
            if (!empty($author)) {
                $obj = new stdClass();
                $obj->author_id = $author;
                $obj->page_id = $pageId;
                $db->insertObject('#__gridbox_authors_map', $obj);
            }
            $tags = $app->input->get('tags', array(), 'array');
            $query = $db->getQuery(true)
                ->select('tag_id')
                ->from('#__contentitem_tag_map')
                ->where('content_item_id = '.$article->id);
            $db->setQuery($query);
            $map = $db->loadObjectList();
            foreach ($map as $value) {
                $obj = new stdClass();
                $obj->tag_id = $tags[$value->tag_id];
                $obj->page_id = $pageId;
                $db->insertObject('#__gridbox_tags_map', $obj);
            }
        }
    }

    public function getJoomlaCategories($id = 1)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where('published in (0,1)')
            ->where('parent_id = '.$id)
            ->where('extension = '.$db->quote('com_content'));
        $db->setQuery($query);
        $categories = $db->loadObjectList();
        foreach ($categories as $category) {
            $childs = $this->getJoomlaCategories($category->id, $array = array());
            $categories = array_merge($categories, $childs);
        }
        
        return $categories;
    }

    public function checkJoomlaContentCount()
    {
        $app = JFactory::getApplication();
        $type = $app->input->get('type', '', 'string');
        $content = new stdClass();
        $content->count = 0;
        if (empty($type)) {
            return $content;
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__content')
            ->where('state in (0,1)');
        $db->setQuery($query);
        $content->articles = $db->loadObjectList();
        $content->count += count($content->articles);
        if (!empty($type) && $type != 'single') {
            $content->categories = $this->getJoomlaCategories();
            $content->count += count($content->categories);
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__tags')
                ->where('parent_id <> 0')
                ->where('published in (0,1)');
            $db->setQuery($query);
            $content->tags = $db->loadObjectList();
            $content->count += count($content->tags);
        }

        return $content;
    }

    public function setFeatured($id, $featured)
    {
        $db = JFactory::getDbo();
        $obj = new stdClass();
        $obj->id = $id;
        $obj->featured = $featured;
        $db->updateObject('#__gridbox_pages', $obj, 'id');
    }

    public function orderPages()
    {
        $input = JFactory::getApplication()->input;
        $cid = $input->get('cid', array(), 'array');
        $order = $input->get('order', array(), 'array');
        $root_order = $input->get('root_order', array(), 'array');
        $category = $input->get('category', '', 'string');
        $type = $input->get('type', 'pages', 'string');
        $db = JFactory::getDbo();
        $table = '#__gridbox_'.$type;
        foreach ($cid as $key => $id) {
            $obj = new stdClass();
            $obj->id = $id;
            if ($category == 'root') {
                $obj->root_order_list = $root_order[$key];
            } else {
                $obj->order_list = $order[$key];
            }
            $db->updateObject($table, $obj, 'id');
        }
    }

    public function exportXML()
    {
        $input = JFactory::getApplication()->input;
        $export_data = $input->get('export_data', '', 'raw');
        $db = JFactory::getDbo();
        $export = $export_data;
        $export = json_decode($export);
        $themes = array();
        $doc = new DOMDocument('1.0');
        $doc->formatOutput = true;
        $root = $doc->createElement('gridbox');
        $root = $doc->appendChild($root);
        $pages = $doc->createElement('pages');
        $pages = $root->appendChild($pages);
        $themeElement = $doc->createElement('themes');
        $themeElement = $root->appendChild($themeElement);
        $libElement = $doc->createElement('libraries');
        $libElement = $root->appendChild($libElement);
        $menuElement = $doc->createElement('mainmenu');
        $menuElement = $root->appendChild($menuElement);
        $com_baforms = $doc->createElement('com_baforms');
        $com_baforms = $root->appendChild($com_baforms);
        $params = array();
        $library = array();
        $main_menu = array();
        $forms = array();
        $pagesList = array();
        $productsFields = array();
        if ($export->type == 'gridbox') {
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_pages')
                ->where('app_id = 0')
                ->where('page_category <> '.$db->quote('trashed'));
            $db->setQuery($query);
            $obj = $db->loadObjectList();
            foreach ($obj as $object) {
                $pagesList[] = $object->id;
            }
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_app')
                ->where('type <> '.$db->quote('system_apps'));
            $db->setQuery($query);
            $obj = $db->loadObjectList();
            foreach ($obj as $object) {
                $export->id[] = $object->id;
            }
            $export->type = 'app';
        }
        if ($export->type == 'app') {
            $apps = $doc->createElement('apps');
            $apps = $root->appendChild($apps);
            $categories = $doc->createElement('categories');
            $categories = $root->appendChild($categories);
            $tags = $doc->createElement('tags');
            $tags = $root->appendChild($tags);
            $fields = $doc->createElement('fields');
            $fields = $root->appendChild($fields);
            $fields_data = $doc->createElement('fields_data');
            $fields_data = $root->appendChild($fields_data);
            $page_fields = $doc->createElement('page_fields');
            $page_fields = $root->appendChild($page_fields);
            $fields_files = $doc->createElement('fields_files');
            $fields_files = $root->appendChild($fields_files);
            $products_data = $doc->createElement('products_data');
            $products_data = $root->appendChild($products_data);
            $product_variations = $doc->createElement('product_variations');
            $product_variations = $root->appendChild($product_variations);
            $products_fields = $doc->createElement('products_fields');
            $products_fields = $root->appendChild($products_fields);
            $products_fields_data = $doc->createElement('products_fields_data');
            $products_fields_data = $root->appendChild($products_fields_data);
            foreach ($export->id as $id) {
                $query = $db->getQuery(true)
                    ->select('a.*')
                    ->from('#__gridbox_app AS a')
                    ->where('a.id = '.$id)
                    ->where('a.type <> '.$db->quote('system_apps'))
                    ->select('t.title AS themeTitle, t.params as themeParams')
                    ->leftJoin('`#__template_styles` AS t ON a.theme = t.id');
                $db->setQuery($query);
                $app = $db->loadObject();
                $obj = new stdClass();
                $obj->title = $app->themeTitle;
                $obj->params = $app->themeParams;
                unset($app->themeTitle);
                unset($app->themeParams);
                $params[$app->theme] = $obj;
                $themes[] = $app->theme;
                $library = gridboxHelper::getGlobal($app->page_layout, $library);
                $forms = gridboxHelper::getBaforms($app->page_layout, $forms);
                if ($export->menu) {
                    $main_menu = gridboxHelper::getMainMenu($app->page_layout, $main_menu);
                }
                $library = gridboxHelper::getGlobal($app->app_layout, $library);
                $forms = gridboxHelper::getBaforms($app->app_layout, $forms);
                if ($export->menu) {
                    $main_menu = gridboxHelper::getMainMenu($app->app_layout, $main_menu);
                }
                $child = $doc->createElement('app');
                $child = $apps->appendChild($child);
                $data = json_encode($app);
                $data = $doc->createTextNode($data);
                $child->appendChild($data);
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_categories')
                    ->where('app_id = '.$id);
                $db->setQuery($query);
                $cats = $db->loadObjectList();
                foreach ($cats as $cat) {
                    $child = $doc->createElement('category');
                    $child = $categories->appendChild($child);
                    $data = json_encode($cat);
                    $data = $doc->createTextNode($data);
                    $child->appendChild($data);
                }
                $query = $db->getQuery(true)
                    ->select('id')
                    ->from('#__gridbox_pages')
                    ->where('app_id = '.$id)
                    ->where('page_category <> '.$db->quote('trashed'));
                $db->setQuery($query);
                $result = $db->loadObjectList();
                foreach ($result as $value) {
                    $query = $db->getQuery(true)
                        ->select('m.page_id, m.tag_id')
                        ->from('#__gridbox_tags_map AS m')
                        ->where('m.page_id = '.$value->id)
                        ->select('t.*')
                        ->leftJoin('`#__gridbox_tags` AS t ON m.tag_id = t.id');
                    $db->setQuery($query);
                    $pTags = $db->loadObjectList();
                    foreach ($pTags as $tag) {
                        $tag->hits = 0;
                        $child = $doc->createElement('tag');
                        $child = $tags->appendChild($child);
                        $data = json_encode($tag);
                        $data = $doc->createTextNode($data);
                        $child->appendChild($data);
                    }
                    $pagesList[] = $value->id;
                }
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_fields')
                    ->where('app_id = '.$id);
                $db->setQuery($query);
                $AppFields = $db->loadObjectList();
                foreach ($AppFields as $field) {
                    $child = $doc->createElement('field');
                    $child = $fields->appendChild($child);
                    $data = json_encode($field);
                    $data = $doc->createTextNode($data);
                    $child->appendChild($data);
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__gridbox_fields_data')
                        ->where('field_id = '.$field->id);
                    $db->setQuery($query);
                    $fieldsData = $db->loadObjectList();
                    foreach ($fieldsData as $fieldData) {
                        $child = $doc->createElement('field_data');
                        $child = $fields_data->appendChild($child);
                        $data = json_encode($fieldData);
                        $data = $doc->createTextNode($data);
                        $child->appendChild($data);
                    }
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from('#__gridbox_page_fields')
                        ->where('field_id = '.$field->id);
                    $db->setQuery($query);
                    $pageFields = $db->loadObjectList();
                    foreach ($pageFields as $pageField) {
                        $child = $doc->createElement('page_field');
                        $child = $page_fields->appendChild($child);
                        $data = json_encode($pageField);
                        $data = $doc->createTextNode($data);
                        $child->appendChild($data);
                    }
                }
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_fields_desktop_files')
                    ->where('app_id = '.$id);
                $db->setQuery($query);
                $fieldsFiles = $db->loadObjectList();
                foreach ($fieldsFiles as $fieldFiles) {
                    $child = $doc->createElement('field_files');
                    $child = $fields_files->appendChild($child);
                    $data = json_encode($fieldFiles);
                    $data = $doc->createTextNode($data);
                    $child->appendChild($data);
                }
            }
        }
        if ($export->type != 'pages') {
            $export->id = $pagesList;
        }
        foreach ($export->id as $id) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('`#__gridbox_pages`')
                ->where('`id` = '.$id);
            $db->setQuery($query);
            $table = $db->loadObject();
            $table->hits = 0;
            if (!in_array($table->theme, $themes)) {
                $query = $db->getQuery(true);
                $query->select('params, title')
                    ->from('`#__template_styles`')
                    ->where('`id` = ' .$db->quote($table->theme));
                $db->setQuery($query);
                $params[$table->theme] = $db->loadObject();
                $themes[] = $table->theme;
            }
            $library = gridboxHelper::getGlobal($table->params, $library);
            $forms = gridboxHelper::getBaforms($table->params, $forms);
            if ($export->menu) {
                $main_menu = gridboxHelper::getMainMenu($table->params, $main_menu);
            }
            $page = $doc->createElement('page');
            $page = $pages->appendChild($page);
            $data = json_encode($table);
            $data = $doc->createTextNode($data);
            $page->appendChild($data);
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_product_data')
                ->where('product_id = '.$id);
            $db->setQuery($query);
            $product = $db->loadObject();
            if ($product) {
                $data = json_encode($product);
                if (!empty($product->extra_options)) {
                    $extra_options = json_decode($product->extra_options);
                    foreach ($extra_options as $extra_option) {
                        if (!in_array($extra_option->id, $productsFields)) {
                            $productsFields[] = $extra_option->id;
                        }
                    }
                }
                $child = $doc->createElement('product_data');
                $child = $products_data->appendChild($child);
                $data = $doc->createTextNode($data);
                $child->appendChild($data);
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_store_product_variations_map')
                    ->where('product_id = '.$id);
                $db->setQuery($query);
                $map = $db->loadObjectList();
                foreach ($map as $variation) {
                    $data = json_encode($variation);
                    $child = $doc->createElement('variation');
                    $child = $product_variations->appendChild($child);
                    $data = $doc->createTextNode($data);
                    $child->appendChild($data);
                    if (!in_array($variation->field_id, $productsFields)) {
                        $productsFields[] = $variation->field_id;
                    }
                }
            }
        }
        foreach ($productsFields as $id) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_products_fields')
                ->where('id = '.$id);
            $db->setQuery($query);
            $item = $db->loadObject();
            $data = json_encode($item);
            $child = $doc->createElement('field');
            $child = $products_fields->appendChild($child);
            $data = $doc->createTextNode($data);
            $child->appendChild($data);
        }
        foreach ($params as $key => $param) {
            $library = gridboxHelper::getGlobal($param->params, $library);
            if ($export->menu) {
                $main_menu = gridboxHelper::getMainMenu($param->params, $main_menu);
            }
            $forms = gridboxHelper::getBaforms($param->params, $forms);
            $file = JPATH_ROOT. '/templates/gridbox/css/storage/code-editor-'.$key.'.css';
            if (JFile::exists($file)) {
                $customCss = JFile::read($file);
            } else {
                $customCss = '';
            }
            $file = JPATH_ROOT. '/templates/gridbox/js/storage/code-editor-'.$key.'.js';
            if (JFile::exists($file)) {
                $customJs = JFile::read($file);
            } else {
                $customJs = '';
            }
            $theme = $doc->createElement('theme');
            $theme = $themeElement->appendChild($theme);
            $title = $doc->createElement('id');
            $title = $theme->appendChild($title);
            $data = $doc->createTextNode($key);
            $data = $title->appendChild($data);
            $title = $doc->createElement('title');
            $title = $theme->appendChild($title);
            $data = $doc->createTextNode($param->title);
            $data = $title->appendChild($data);
            $title = $doc->createElement('params');
            $title = $theme->appendChild($title);
            $data = $doc->createTextNode($param->params);
            $data = $title->appendChild($data);
            $title = $doc->createElement('css');
            $title = $theme->appendChild($title);
            $data = $doc->createTextNode($customCss);
            $data = $title->appendChild($data);
            $title = $doc->createElement('js');
            $title = $theme->appendChild($title);
            $data = $doc->createTextNode($customJs);
            $data = $title->appendChild($data);
        }
        foreach ($library as $key => $lib) {
            $theme = $doc->createElement('library');
            $theme = $libElement->appendChild($theme);
            $value = json_encode($lib);
            $data = $doc->createTextNode($value);
            $data = $theme->appendChild($data);
        }
        if ($export->menu) {
            foreach ($main_menu as $value) {
                $theme = $doc->createElement('main_menu');
                $theme = $menuElement->appendChild($theme);
                foreach ($value as $key => $menu) {
                    $menu = json_encode($menu);
                    $title = $doc->createElement($key);
                    $title = $theme->appendChild($title);
                    $data = $doc->createTextNode($menu);
                    $data = $title->appendChild($data);
                }
            }
        }
        foreach ($forms as $value) {
            $theme = $doc->createElement('baform');
            $theme = $com_baforms->appendChild($theme);
            foreach ($value as $key => $form) {
                $form = json_encode($form);
                $title = $doc->createElement($key);
                $title = $theme->appendChild($title);
                $data = $doc->createTextNode($form);
                $data = $title->appendChild($data);
            }
        }
        $config = JFactory::getConfig();
        $file =  $config->get('tmp_path').'/gridbox.xml';
        $bytes = $doc->save($file);
        $root = JPATH_ROOT == '/' ? '' : JPATH_ROOT;
        $file = str_replace($root, '', $file);
        if ($bytes) {
            echo new JResponseJson(true, $file);
        } else {
            echo new JResponseJson(false, '', true);
        }
        jexit();
    }

    public function applySingle()
    {
        $input = JFactory::getApplication()->input;
        $db = JFactory::getDbo();
        $obj = new stdClass();
        $obj->title = $input->get('single_title', '', 'string');
        $obj->id = $input->get('blog', 0, 'int');
        $db->updateObject('#__gridbox_app', $obj, 'id');
        gridboxHelper::ajaxReload('JLIB_APPLICATION_SAVE_SUCCESS');
    }

    public function addApp($type)
    {
        $input = JFactory::getApplication()->input;
        $db = JFactory::getDbo();
        $obj = new stdClass();
        $obj->type = $type;
        $apps = array('single' => JText::_('PAGES'), 'blank' => 'Zero App', 'products' => 'Products',
            'portfolio' => 'Portfolio', 'hotel-rooms' => 'Hotel Rooms');
        if (isset($apps[$obj->type])) {
            $obj->title = $apps[$obj->type];
        } else {
            $title = strtoupper($obj->type);
            $obj->title = JText::_($title);
        }
        $obj->order_list = $input->get('app_order_list', 0, 'int');
        $obj->alias = gridboxHelper::getAlias($obj->title, '#__gridbox_app');
        $obj->theme = gridboxHelper::getTemplate();
        $dir = JPATH_ROOT.'/components/com_gridbox/views/layout/apps/'.$obj->type.'/';
        if ($obj->type != 'single' && JFolder::exists($dir)) {
            $obj->app_layout = JFile::read($dir.'app.html');
            $obj->app_items = JFile::read($dir.'app.json');
            $obj->page_layout = JFile::read($dir.'default.html');
            $obj->page_items = JFile::read($dir.'default.json');
            if (JFile::exists($dir.'fields-groups.json')) {
                $obj->fields_groups = JFile::read($dir.'fields-groups.json');
            }
        }
        $db->insertObject('#__gridbox_app', $obj);
        $id = $db->insertid();
        if ($obj->type != 'single' && JFile::exists($dir.'fields.json')) {
            $fieldsStr = JFile::read($dir.'fields.json');
            $object = json_decode($fieldsStr);
            $fieldsList = array();
            foreach ($object->fields as $field) {
                $fieldId = $field->id;
                unset($field->id);
                $field->app_id = $id;
                $db->insertObject('#__gridbox_fields', $field);
                $fieldsList[$fieldId] = $db->insertid();
            }
            foreach ($object->fields_data as $field) {
                unset($field->id);
                $field->field_id = $fieldsList[$field->field_id];
                $db->insertObject('#__gridbox_fields_data', $field);
            }
        }

        return $id;
    }

    public function getCategories($app, $parent)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_categories')
            ->where('app_id = '.$app)
            ->where('parent = '.$parent);
        $db->setQuery($query);
        $categories = $db->loadObjectList();
        foreach ($categories as $category) {
            $category->childs = $this->getCategories($app, $category->id);
        }

        return $categories;
    }

    public function duplicateCategories($categories, $id, $newId, $parent)
    {
        $db = JFactory::getDbo();
        foreach ($categories as $category) {
            $category->app_id = $newId;
            $catId = $category->id;
            $childs = $category->childs;
            $category->alias = gridboxHelper::getAlias($category->alias, '#__gridbox_categories');
            $category->parent = $parent;
            unset($category->id);
            unset($category->childs);
            $db->insertObject('#__gridbox_categories', $category);
            $newCatId = $db->insertid();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_pages')
                ->where('app_id = '.$id)
                ->where('page_category = '.$catId);
            $db->setQuery($query);
            $pages = $db->loadObjectList();
            foreach ($pages as $page) {
                $page->page_category = $newCatId;
                $page->app_id = $newId;
                $page->hits = 0;
                $page->page_alias = gridboxHelper::getNewPageAlias($page->page_alias, '');
                $pageId = $page->id;
                unset($page->id);
                $db->insertObject('#__gridbox_pages', $page);
                $newPageId = $db->insertid();
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_tags_map')
                    ->where('page_id = '.$pageId);
                $db->setQuery($query);
                $tags = $db->loadObjectList();
                foreach ($tags as $tag) {
                    $tag->page_id = $newPageId;
                    unset($tag->id);
                    $db->insertObject('#__gridbox_tags_map', $tag);
                }
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_authors_map')
                    ->where('page_id = '.$pageId);
                $db->setQuery($query);
                $authors = $db->loadObjectList();
                foreach ($authors as $author) {
                    $author->page_id = $newPageId;
                    unset($author->id);
                    $db->insertObject('#__gridbox_authors_map', $author);
                }
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_page_fields')
                    ->where('page_id = '.$pageId);
                $db->setQuery($query);
                $fields = $db->loadObjectList();
                foreach ($fields as $field) {
                    $field->id = 0;
                    $field->page_id = $newPageId;
                    $field->field_id = isset($this->appFields[$field->field_id]) ? $this->appFields[$field->field_id] : $field->field_id;
                    $db->insertObject('#__gridbox_page_fields', $field);
                }
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_store_product_data')
                    ->where('product_id = '.$pageId);
                $db->setQuery($query);
                $data = $db->loadObject();
                $data->id = 0;
                $data->product_id = $newPageId;
                $db->insertObject('#__gridbox_store_product_data', $data);
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_store_product_variations_map')
                    ->where('product_id = '.$pageId);
                $db->setQuery($query);
                $map = $db->loadObjectList();
                foreach ($map as $variation) {
                    $variation->id = 0;
                    $variation->product_id = $newPageId;
                    $db->insertObject('#__gridbox_store_product_variations_map', $variation);
                }
            }
            $this->duplicateCategories($childs, $id, $newId, $newCatId);
        }
    }

    public function duplicateApp()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('blog', 0, 'int');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_app')
            ->where('id = '.$id);
        $db->setQuery($query);
        $app = $db->loadObject();
        $table = $this->getTable('app');
        $title = $app->title;
        while ($table->load(array('title' => $title))) {
            $title = JString::increment($title);
        }
        $app->title = $title;
        $app->alias = gridboxHelper::getAlias($app->alias, '#__gridbox_app');
        unset($app->id);
        $db->insertObject('#__gridbox_app', $app);
        $newId = $db->insertid();
        $this->duplicateAppFields($id, $newId);
        $categories = $this->getCategories($id, 0);
        $this->duplicateCategories($categories, $id, $newId, 0);
    }

    public function duplicateAppFields($pk, $id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_fields')
            ->where('app_id = '.$pk);
        $db->setQuery($query);
        $fields = $db->loadObjectList();
        $this->appFields = array();
        foreach ($fields as $field) {
            $fieldId = $field->id;
            $field->id = 0;
            $field->app_id = $id;
            $db->insertObject('#__gridbox_fields', $field);
            $field->id = $db->insertid();
            $this->appFields[$fieldId] = $field->id;
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_fields_data')
                ->where('field_id = '.$fieldId);
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $item) {
                $item->id = 0;
                $item->field_id = $field->id;
                $db->insertObject('#__gridbox_fields_data', $item);
            }
        }
    }

    public function deleteApp()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('blog', 0, 'int');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__gridbox_app')
            ->where('`id` = '. $id);
        $db->setQuery($query)
            ->execute();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__gridbox_pages')
            ->where('`app_id` = '. $id);
        $db->setQuery($query);
        $pages = $db->loadObjectList();
        $array = array();
        foreach ($pages as $value) {
            $array[] = $value->id;
        }
        gridboxHelper::afterDeleteAction($array);
        $query = $db->getQuery(true)
            ->delete('#__gridbox_fields')
            ->where('`app_id` = '. $id);
        $db->setQuery($query)
            ->execute();
        $query = $db->getQuery(true)
            ->delete('#__gridbox_pages')
            ->where('`app_id` = '. $id);
        $db->setQuery($query)
            ->execute();
        $query = $db->getQuery(true)
            ->delete('#__gridbox_categories')
            ->where('`app_id` = '. $id);
        $db->setQuery($query)
            ->execute();
        $file = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/app-'.$id.'.css';
        gridboxHelper::deleteFile($file);
        $file = JPATH_ROOT. '/components/com_gridbox/assets/css/storage/post-'.$id.'.css';
        gridboxHelper::deleteFile($file);
    }

    public function updateParams()
    {
        $input = JFactory::getApplication()->input;
        $table = $this->getTable();
        $id = $input->get('ba_id', 0, 'int');
        $tags = $input->get('meta_tags', array(), 'array');
        $text = $input->get('intro_text', '', 'raw');
        $theme = $input->get('theme_list', 0, 'int');
        $title = $input->get('page_title', '', 'string');
        $metaTitle = $input->get('page_meta_title', '', 'string');
        $alias = $input->get('page_alias', '', 'string');
        $desc = $input->get('page_meta_description', '', 'raw');
        $keyWords = $input->get('page_meta_keywords', '', 'string');
        $access = $input->get('access', '', 'string');
        $date = $input->get('published_on', '', 'string');
        $endDate = $input->get('published_down', '', 'string');
        if (empty($endDate)) {
            $endDate = '0000-00-00 00:00:00';
        }
        $intro_image = $input->get('intro_image', '', 'string');
        $share_image = $input->get('share_image', '', 'string');
        $share_title = $input->get('share_title', '', 'string');
        $share_description = $input->get('share_description', '', 'string');
        $language = $input->get('language', '', 'string');
        $robots = $input->get('robots', '', 'string');
        $class_suffix = $input->get('class_suffix', '', 'string');
        $sitemap_include = $input->get('sitemap_include', 0, 'int');
        $changefreq = $input->get('changefreq', 'monthly', 'string');
        $priority = $input->get('priority', '0.5', 'string');
        $table->load($id);
        $array = array('title' =>$title, 'meta_title' => $metaTitle, 'created' => $date,
            'meta_description' => $desc, 'meta_keywords' => $keyWords, 'end_publishing' => $endDate,
            'theme' => $theme, 'page_alias' => $alias, 'page_access' => $access, 'class_suffix' => $class_suffix,
            'intro_text' => strip_tags($text), 'intro_image' => $intro_image, 'language' => $language, 'robots' => $robots,
            'share_image' => $share_image, 'share_title' => $share_title, 'share_description' => $share_description,
            'sitemap_include' => $sitemap_include, 'changefreq' => $changefreq, 'priority' => $priority,
            );
        $page_category = $input->get('page_category', 0, 'int');
        if (isset($page_category)) {
            $array['page_category'] = $page_category;
        }
        $table->bind($array);
        if (!$table->check()) {
            gridboxHelper::ajaxReload('ANOTHER_ALIAS');
            return false;
        }
        $table->store();
        $db = JFactory::getDbo();
        $author = $input->get('author', '', 'string');
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
        $gridboxTags = $this->getTable('Tags', 'gridboxTable');
        $map = $this->getTable('TagsMap', 'gridboxTable');
        $query = $db->getQuery(true)
            ->select('id, tag_id')
            ->from('#__gridbox_tags_map')
            ->where('`page_id` = '. $id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as $item) {
            if (!in_array($item->tag_id, $tags)) {
                $map->delete($item->id);
            }
        }
        foreach ($tags as $tag) {
            if (!empty($tag)) {
                if (strpos($tag, 'new$') !== false) {
                    $tag = substr($tag, 4);
                    $alias = gridboxHelper::getAlias($tag, '#__gridbox_tags');
                    $gridboxTags->reset();
                    $gridboxTags->bind(array('id' => 0, 'title' => $tag, 'alias' => $alias));
                    $gridboxTags->store();
                    $tag = $gridboxTags->id;
                    $map->reset();
                    $map->bind(array('id' => 0, 'page_id' => $id, 'tag_id' => $tag));
                    $map->store();
                } else {
                    $query = $db->getQuery(true);
                    $query->select('id')
                        ->from('#__gridbox_tags_map')
                        ->where('`page_id` = '.$id)
                        ->where('`tag_id` = '.$tag);
                    $db->setQuery($query);
                    $item = $db->loadResult();
                    if (empty($item)) {
                        $map->reset();
                        $map->bind(array('id' => 0, 'page_id' => $id, 'tag_id' => $tag));
                        $map->store();
                    }
                }
            }
        }
    }

    public function getPageTags()
    {
        $db = JFactory::getDbo();
        $input = JFactory::getApplication()->input;
        $id = $input->get('page_id', 0, 'int');
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
            $tags[] = $db->loadObject();
        }
        $tags = json_encode($tags);
        echo $tags;
        exit;
    }

    public function updateTags()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__gridbox_tags');
        $db->setQuery($query);
        $tags = $db->loadObjectList();
        $tags = json_encode($tags);
        echo new JResponseJson(true, $tags);
        exit;
    }

    public function applySettings()
    {
        $input = JFactory::getApplication()->input;
        $db = JFactory::getDbo();
        $obj = new stdClass();
        $obj->id = $input->get('blog', 0, 'int');
        $obj->title = $input->get('category_title', '', 'string');
        $obj->alias = $input->get('category_alias', '', 'string');
        if (empty($obj->alias)) {
            $obj->alias = $obj->title;
        }
        $obj->published = $input->get('category_publish', 0, 'int');
        $obj->alias = gridboxHelper::getAlias($obj->alias, '#__gridbox_categories');
        $obj->access = $input->get('category_access', '', 'string');
        $obj->language = $input->get('category_language', '', 'string');
        $obj->robots = $input->get('category_robots', '', 'string');
        $obj->image = $input->get('category_intro_image', '', 'string');
        $obj->meta_title = $input->get('category_meta_title', '', 'string');
        $obj->meta_description = $input->get('category_meta_description', '', 'raw');
        $obj->meta_keywords = $input->get('category_meta_keywords', '', 'string');
        $obj->description = $input->get('category_description', '', 'raw');
        $obj->alias = gridboxHelper::getAlias($obj->alias, '#__gridbox_app', $obj->id);
        $obj->theme = $input->get('blog_theme', 0, 'int');
        $obj->share_image = $input->get('category_share_image', '', 'string');
        $obj->share_title = $input->get('category_share_title', '', 'string');
        $obj->share_description = $input->get('category_share_description', '', 'string');
        $obj->sitemap_include = $input->get('category_sitemap_include', 0, 'int');
        $obj->changefreq = $input->get('category_changefreq', 'monthly', 'string');
        $obj->priority = $input->get('category_priority', '0.5', 'string');
        $db->updateObject('#__gridbox_app', $obj, 'id');
    }
    
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option . '.gridbox', 'gridbox', array('control' => 'jform', 'load_data' => $loadData)
        );
        
        if (empty($form))
        {
            return false;
        }
 
        return $form;
    }
    
    public function getNewTitle($title)
    {
        $table = $this->getTable();
        while ($table->load(array('title' => $title))) {
            $title = JString::increment($title);
        }

        return $title;
    }

    public function getNewAlias($alias)
    {
        $originAlias = $alias;
        $alias = gridboxHelper::stringURLSafe(trim($alias));
        if (empty($alias)) {
            $alias = $originAlias;
            $alias = gridboxHelper::replace($alias);
            $alias = JFilterOutput::stringURLSafe($alias);
        }
        if (empty($alias)) {
            $alias = date('Y-m-d-H-i-s');
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from('#__gridbox_pages')
            ->where('`page_alias` = ' .$db->quote($alias));
        $db->setQuery($query);
        $id = $db->loadResult();
        if (!empty($id)) {
            $alias = JString::increment($alias);
            $alias = $this->getNewAlias($alias);
        }
        
        return $alias;
    }

    public function moveSingle($id, $category)
    {
        $obj = json_decode($category);
        $obj->page_category = '';
        $obj->id = $id;
        $obj->order_list = 0;
        $obj->root_order_list = 0;
        JFactory::getDbo()->updateObject('#__gridbox_pages', $obj, 'id');
    }
    
    public function duplicate(&$pks)
    {
        $db = JFactory::getDbo();
        foreach ($pks as $pk) {
            $table = $this->getTable();
            $table->load($pk, true);
            $table->id = 0;
            $table->hits = 0;
            $table->order_list = 0;
            $table->title = $this->getNewTitle($table->title);
            $table->page_alias = $this->getNewAlias($table->page_alias);
            $table->published = 0;
            $table->order_list = 0;
            $table->root_order_list = 0;
            $table->check();
            $table->store();
            gridboxHelper::copyCss($pk, $table->id);
            $this->duplicatePageFields($pk, $table->id);
            $this->duplicateProductData($pk, $table->id);
            $query = $db->getQuery(true)
                ->select('tag_id')
                ->from('`#__gridbox_tags_map`')
                ->where('`page_id` = '.$pk);
            $db->setQuery($query);
            $tags = $db->loadObjectList();
            foreach ($tags as $tag) {
                $tag->page_id = $table->id;
                $db->insertObject('#__gridbox_tags_map', $tag);
            }
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_authors_map')
                ->where('page_id = '.$pk);
            $db->setQuery($query);
            $authors = $db->loadObjectList();
            foreach ($authors as $author) {
                $author->page_id = $table->id;
                unset($author->id);
                $db->insertObject('#__gridbox_authors_map', $author);
            }
        }
    }

    public function duplicateProductData($pk, $id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_product_variations_map')
            ->where('product_id = '.$pk);
        $db->setQuery($query);
        $variations = $db->loadObjectList();
        foreach ($variations as $variation) {
            unset($variation->id);
            $variation->product_id = $id;
            $db->insertObject('#__gridbox_store_product_variations_map', $variation);
        }
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_product_data')
            ->where('product_id = '.$pk);
        $db->setQuery($query);
        $data = $db->loadObjectList();
        foreach ($data as $value) {
            unset($value->id);
            $value->product_id = $id;
            if (!empty($value->digital_file)) {
                $digital = json_decode($value->digital_file);
                $digital->file->name = $digital->file->filename = '';
                $value->digital_file = json_encode($digital);
            }
            $db->insertObject('#__gridbox_store_product_data', $value);
        }
    }

    public function duplicatePageFields($pk, $id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_page_fields')
            ->where('page_id = '.$pk);
        $db->setQuery($query);
        $fields = $db->loadObjectList();
        foreach ($fields as $field) {
            $field->id = 0;
            $field->page_id = $id;
            $db->insertObject('#__gridbox_page_fields', $field);
        }
    }

    public function trash(&$pks)
    {
        foreach ($pks as $pk) {
            $table = $this->getTable();
            $table->load($pk, true);
            $table->page_category = 'trashed';
            $table->published = 0;
            $table->store();
        }
    }
}