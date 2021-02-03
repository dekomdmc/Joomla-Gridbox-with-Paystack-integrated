<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

class gridboxViewEditor extends JViewLegacy
{

    public $app;
    public $apps;
    public $category;
    public $categories;
    public $item;
    public $themes;
    public $access;
    public $plugins;
    public $blocks;
    public $blocksIcon;
    public $languages;
    public $menutypes;
    public $mapsKey;
    public $website;
    public $tags;
    public $edit_type;
    public $pageTags;
    public $categoryList;
    public $form;
    public $jce;
    public $authors;
    public $fields;
    public $fieldsCount;
    public $fields_data;
    public $postContent;
    public $fieldsGroups;
    public $allApps;
    public $user;
    public $editFlag;
    public $productData;
    public $product_type;

    public function display($tpl = null)
    {
        $this->item = $this->get('Item');
        $app = JFactory::getApplication();
        $input = $app->input;
        if (!$this->item || (isset($this->item->page_category) && $this->item->page_category == 'trashed')) {
            return JError::raiseError(404, JText::_('NOT_FOUND'));
        }
        $version = gridboxHelper::getVersion();
        $this->app = $input->get('app_id', 0, 'int');
        $this->category = $input->get('category', '', 'string');
        $this->edit_type = $input->get('edit_type', '', 'string');
        $doc = JFactory::getDocument();
        $doc->setTitle('Gridbox Editor');
        $doc->addStyleSheet(JURI::root().'components/com_gridbox/assets/css/ba-style-editor.css?'.$version);
        $doc->addScript(JUri::root().'media/jui/js/jquery.min.js');
        $doc->addScript(JUri::root().'media/jui/js/bootstrap.min.js');
        $doc->setMetaData('cache-control', 'no-cache', true);
        $doc->setMetaData('expires', '0', true);
        $doc->setMetaData('pragma', 'no-cache', true);
        $this->user = JFactory::getUser();
        if (empty($this->item->id)) {
            $this->editFlag = $this->user->authorise('core.create', 'com_gridbox');
        } else if ($this->edit_type == 'post-layout' || $this->edit_type == 'blog') {
            $this->editFlag = $this->user->authorise('core.edit.layouts', 'com_gridbox.app.'.$this->item->id);
        } else if ($this->edit_type == '') {
            $pageAssets = new gridboxAssetsHelper($this->item->id, 'page');
            $editPage = $pageAssets->checkPermission('core.edit');
            if (!$editPage && !empty($this->item->page_category)) {
                $editPage = $pageAssets->checkEditOwn($this->item->page_category);
            }
            $this->editFlag = $editPage;
        } else {
            $this->editFlag = $this->user->authorise('core.edit', 'com_gridbox');
        }
        if (empty($this->item->id) || !$this->editFlag) {
            $this->setLayout('login');
            $this->themes = $this->get('Themes');
            $this->product_type = $input->get('product_type', '', 'string');
            parent::display($tpl);
            return;
        }
        if ($this->edit_type == '') {
            $this->app = $this->item->app_id;
            $this->category = $this->item->page_category;
        } else {
            $this->app = 0;
            $this->category = '';
            $this->item->app_type = '';
        }
        $this->authors = $this->get("Authors");
        $this->website = $this->get('Website');
        $this->access = gridboxHelper::getAccess();
        $this->languages = gridboxHelper::getLanguages();
        $this->menutypes = $this->get('Menus');
        $this->plugins = $this->get('Plugins');
        $this->blocks = $this->get('Blocks');
        $this->blocksIcon = array('cover' => 'zmdi zmdi-tv-list', 'about-us' => 'zmdi zmdi-info',
            'services' => 'zmdi zmdi-cutlery', 'description' => 'zmdi zmdi-assignment',
            'steps' => 'zmdi zmdi-format-list-numbered', 'schedule' => 'zmdi zmdi-calendar-note',
            'features' => 'zmdi zmdi-check-circle', 'pricing-table' => 'zmdi zmdi-mall',
            'pricing-list' => 'zmdi zmdi-money', 'testimonials' => 'zmdi zmdi-comment-more',
            'team' => 'zmdi zmdi-account-circle', 'counters' => 'zmdi zmdi-chart-donut',
            'faq' => 'zmdi zmdi-help', 'call-to-action' => 'zmdi zmdi-mouse');
        if ($this->edit_type != 'post-layout' && !isset($this->item->app_id) && isset($this->plugins['social']['ba-comments-box'])) {
            unset($this->plugins['social']['ba-comments-box']);
        }
        if (isset($this->plugins['store']) && ($this->edit_type != 'post-layout' || $this->item->type != 'products')) {
            unset($this->plugins['store']['ba-add-to-cart']);
            unset($this->plugins['store']['ba-product-slideshow']);
            unset($this->plugins['store']['ba-product-gallery']);
            unset($this->plugins['store']['ba-recently-viewed-products']);
        }
        if ((!empty($this->item->app_type) && $this->item->app_type != 'single')
            || $this->edit_type == 'post-layout' && isset($this->plugins['blog'])) {
            $postPlugins = array(
                'ba-post-tags' => 'flaticon-bookmark-1',
                'ba-related-posts' => 'flaticon-network',
                'ba-post-navigation' => 'flaticon-sign-1',
                'ba-author' => 'flaticon-user-3',
                /*'ba-related-posts-slider' => 'flaticon-share-2'*/
            );
            foreach ($postPlugins as $postPlugin => $postPluginImage) {
                $obj = new stdClass();
                $obj->title = $postPlugin;
                $obj->image = $postPluginImage;
                $obj->type = 'blog';
                $joomla_constant = strtoupper(str_replace('-', '_', $postPlugin));
                if ($joomla_constant == 'BA_AUTHOR') {
                    $joomla_constant = 'BA_AUTHOR_BOX';
                }
                $obj->joomla_constant = substr($joomla_constant, 3);
                $this->plugins['blog'][$postPlugin] = $obj;
            }
            if ($this->edit_type == 'post-layout' && $this->item->type != 'blog') {
                $obj = new stdClass();
                $obj->title = 'ba-post-intro';
                $obj->image = 'plugins-post-intro';
                $obj->type = 'blog';
                $obj->joomla_constant = 'POST_INTRO';
                $this->plugins['blog']['ba-post-intro'] = $obj;
                $obj = new stdClass();
                $obj->title = 'ba-blog-content';
                $obj->image = 'plugins-post-content';
                $obj->type = 'blog';
                $obj->joomla_constant = 'POST_CONTENT';
                $this->plugins['blog']['ba-blog-content'] = $obj;
            }
        }
        if (isset($this->plugins['blog'])) {
            usort($this->plugins['blog'], function($a, $b){
                if ($a->title == $b->title) {
                    return 0;
                }
                return ($a->title < $b->title) ? -1 : 1;
            });
        }
        if ($this->edit_type == 'post-layout' && $this->item->type != 'blog') {
            $postPlugins = array('ba-field', 'ba-image-field', 'ba-field-simple-gallery',
                'ba-field-slideshow', 'ba-field-google-maps', 'ba-field-video', 'ba-field-group');
            $postPluginsIcons = array('flaticon-substract-1', 'flaticon-picture', 'flaticon-photo-camera-1',
                'plugins-slideshow', 'plugins-google-maps', 'flaticon-video-player', 'flaticon-equal-2');
            $postPluginsConts = array('FIELD', 'FIELD_IMAGE', 'FIELD_SIMPLE_GALLERY', 'FIELD_SLIDESHOW',
                'FIELD_GOOGLE_MAPS', 'FIELD_VIDEO', 'FIELD_GROUP');
            while ($postPlugin = array_pop($postPlugins)) {
                $obj = new stdClass();
                $obj->title = $postPlugin;
                $obj->image = array_pop($postPluginsIcons);
                $obj->type = 'fields';
                $obj->joomla_constant = array_pop($postPluginsConts);
                $this->plugins['fields'][$postPlugin] = $obj;
            }
            usort($this->plugins['fields'], function($a, $b){
                if ($a->title == $b->title) {
                    return 0;
                }
                return ($a->title < $b->title) ? -1 : 1;
            });
        } else {
            unset($this->plugins['fields']);
        }
        if ($this->item->app_type != 'blog' && $this->item->app_type != 'single' && $this->item->app_type != '') {
            $pageLayout = $this->get('PageLayout');
            $this->postContent = strpos($pageLayout, 'ba-item-blog-content');
            $this->fields = gridboxHelper::getAppFields($this->item->app_id);
            $this->fieldsCount = count($this->fields);
            $this->fields_data = gridboxHelper::getFieldsData($this->item->id);
            $this->fieldsGroups = gridboxHelper::getFieldsGroups($this->item->app_id);
            if ($this->item->app_type == 'products') {
                $this->productData = $this->get('productData');
                $this->fieldsCount++;
            }
        }
        $fonts = gridboxHelper::getFonts();
        $doc->addScriptDeclaration('var fontsLibrary = '.$fonts.';');
        $doc->addScriptDeclaration("var JUri = '".JUri::root()."', IMAGE_PATH = '".IMAGE_PATH."';");
        $this->mapsKey = gridboxHelper::getMapsKey();
        $doc->addScript(JURI::root().'components/com_gridbox/assets/js/ba-editor.js');
        $doc->addScript(JURI::root().'index.php?option=com_gridbox&task=editor.loadModule&module=gridboxLanguage');
        $doc->addScript(JURI::root().'index.php?option=com_gridbox&task=editor.getOpenWeatherKey');
        $doc->addScript(JURI::root().'index.php?option=com_gridbox&task=editor.getYandexMapsKey');
        $this->tags = $this->get('Tags');
        $this->pageTags = $this->get('PageTags');
        $this->apps = $this->get('Apps');
        $this->allApps = $this->get('AllApps');
        $this->categories = $this->get('Categories');
        $this->categoryList = array();
        if (isset ($this->item->app_id) && !empty($this->item->app_id)) {
            foreach ($this->categories as $category) {
                if ($category->app_id == $this->item->app_id) {
                    $this->categoryList[$category->id] = $category;
                }
            }
        }
        $this->form = $this->get('Form');
        $this->jce = $this->get('Jce');
        if (!empty($this->jce) && $this->jce * 1 === 1) {
            $doc->addScriptDeclaration('var Joomla = {};');
        }

        parent::display($tpl);
    }
}