<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class gridboxControllerEditor extends JControllerForm
{
    public function getModel($name = 'Editor', $prefix = 'gridboxModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

    public function checkAppFields()
    {
        $id = $this->input->get('id', 0, 'int');
        $model = $this->getModel();
        $type = $model->checkAppFields($id);
        echo $type;exit();
    }

    public function getProductOptions()
    {
        $model = $this->getModel();
        $array = $model->getProductOptions();
        $str = json_encode($array);
        echo $str;
        exit();
    }

    public function generateNewApp()
    {
        $input = JFactory::getApplication()->input;
        $name = $input->get('name', 'test', 'string');
        $path = JPATH_ROOT.'/tmp/'.$name;
        $xml = simplexml_load_file(JPATH_ROOT.'/tmp/'.$name.'.xml');
        if (JFolder::exists($path)) {
            JFolder::delete($path);
        }
        JFolder::create($path);
        foreach ($xml->apps->app as $app) {
            $obj = json_decode($app);
            $file = 'app.html';
            JFile::write($path.'/'.$file, $obj->app_layout);
            $file = 'app.json';
            JFile::write($path.'/'.$file, $obj->app_items);
            $file = 'default.html';
            JFile::write($path.'/'.$file, $obj->page_layout);
            $file = 'default.json';
            JFile::write($path.'/'.$file, $obj->page_items);
            $file = 'fields-groups.json';
            JFile::write($path.'/'.$file, $obj->fields_groups);
        }
        $obj = new stdClass();
        $obj->fields = array();
        $obj->fields_data = array();
        foreach ($xml->fields->field as $field) {
            $object = json_decode($field);
            $obj->fields[] = $object;
        }
        foreach ($xml->fields_data->field_data as $field_data) {
            $object = json_decode($field_data);
            $obj->fields_data[] = $object;
        }
        $str = json_encode($obj);
        $file = 'fields.json';
        JFile::write($path.'/'.$file, $str);
        echo 'created';
        exit;
    }

    public function getAppFields()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->post->get('id', 0, 'int');
        $type = $input->post->get('type', '', 'string');
        $edit_type = $input->post->get('edit_type', '', 'string');
        if (($type == 'post-navigation'  || $type == 'related-posts') && $edit_type != 'post-layout') {
            $id = gridboxHelper::getAppId($id);
        }
        $model = $this->getModel();
        $items = $model->getAppFields($id);
        $str = json_encode($items);
        echo $str;
        exit;
    }

    public function getItemsFilter()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->post->get('id', 0, 'int');
        $type = $input->post->get('type', '', 'string');
        $edit_type = $input->post->get('edit_type', '', 'string');
        if (($type == 'post-navigation'  || $type == 'related-posts') && $edit_type != 'post-layout') {
            $id = gridboxHelper::getAppId($id);
        }
        $str = gridboxHelper::getItemsFilter($id);
        echo $str;exit;
    }

    public function uploadDesktopFieldFile()
    {
        $input = JFactory::getApplication()->input;
        $file = $input->files->get('file', array(), 'array');
        $id = $input->post->get('id', 0, 'int');
        $model = $this->getModel();
        $obj = $model->uploadDesktopFieldFile($file, $id);
        $str = json_encode($obj);
        echo $str;
        exit();
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
        print_r($balbooa);exit();
    }

    public function checkSitemap()
    {
        if (isset(gridboxHelper::$systemApps->sitemap)) {
            gridboxHelper::checkSitemap();
        }
        exit;
    }

    public function generateSitemap()
    {
        $input = JFactory::getApplication()->input;
        gridboxHelper::$website->sitemap_domain = $input->get('sitemap_domain', '', 'string');
        gridboxHelper::$website->sitemap_slash = $input->get('sitemap_slash', 0, 'int');
        gridboxHelper::createSitemap();
    }

    public function getMapsPlaces()
    {
        $input = JFactory::getApplication()->input;
        $app = $input->get('app', 0, 'int');
        $menuItem = $input->post->get('menuitem', 0, 'int');
        $pages = $input->post->get('pages', '', 'string');
        $obj = gridboxHelper::getMapsPlaces($app, $menuItem, $pages);
        $str = json_encode($obj);
        header('Content-Type: text/javascript');
        echo $str;
        exit;
    }

    public function renderEventCalendar()
    {
        $input = JFactory::getApplication()->input;
        $year = $input->get('year', '0', 'string');
        $month = $input->get('month', '0', 'string');
        $app = $input->get('app', 0, 'int');
        $start = $input->get('start', 0, 'int');
        $menuItem = $input->post->get('menuitem', 0, 'int');
        $time = mktime(0, 0, 0, $month, 1, $year);
        $obj = gridboxHelper::renderEventCalendarData($time, $app, $menuItem, $start);
        $str = json_encode($obj);
        header('Content-Type: text/javascript');
        echo $str;
        exit;
    }

    public function renderWeather()
    {
        $openWeatherMapKey = gridboxHelper::getOpenWeatherKey();
        $input = JFactory::getApplication()->input;
        $view = $input->get('view', 'page', 'string');
        $placeholder = '';
        if ($view == 'gridbox') {
            $placeholder = '<div class="empty-list"><i class="zmdi zmdi-alert-polygon"></i><p>';
            $placeholder .= JText::_('ENTER_VALID_API_KEY_LOCATION').'</p></div>';
        }
        if (empty($openWeatherMapKey)) {
            print_r($placeholder);exit;
        }
        $string = $input->get('weather', '{}', 'string');
        $weather = json_decode($string);
        if (empty($weather->location)) {
            print_r($placeholder);exit;
        }
        $item = new stdClass();
        $item->weather = $weather;
        $units = $weather->unit == 'c' ? 'metric' : 'imperial';
        $latLon = explode(',', $weather->location);
        if (!empty($latLon) && count($latLon) == 2 && is_numeric($latLon[0])&& is_numeric($latLon[1])) {
            $url = 'http://api.openweathermap.org/data/2.5/forecast?lat='.trim($latLon[0]).'&lon='.trim($latLon[1]);
            $url .= '&units='.$units.'&appid='.$openWeatherMapKey;
        } else if (is_numeric($weather->location)) {
            $url = 'http://api.openweathermap.org/data/2.5/forecast?id='.$weather->location;
            $url .= '&units='.$units.'&appid='.$openWeatherMapKey;
        } else {
            $location = str_replace(' ', '%20', $weather->location);
            $url = 'http://api.openweathermap.org/data/2.5/forecast?q='.$location;
            $url .= '&units='.$units.'&appid='.$openWeatherMapKey;
        }
        $data = gridboxHelper::getInstagramData($url);
        $weather = json_decode($data);
        if (!is_object($weather) || $weather->cod != 200) {
            print_r($placeholder);exit;
        }
        $forecast = gridboxHelper::renderWetherData($item->weather, $data);
        $str = gridboxHelper::renderWetherHTML($forecast, $item);
        print_r($str);exit;
    }

    public function setOpenWeatherMapKey()
    {
        gridboxHelper::checkUserEditLevel();
        $model = $this->getModel();
        $model->setOpenWeatherMapKey();
    }

    public function getOpenWeatherKey()
    {
        header('Content-Type: text/javascript');
        $key = gridboxHelper::getOpenWeatherKey();
        echo 'var openweathermap = "'.$key.'"';exit();
    }

    public function setAppLicense()
    {
        gridboxHelper::setAppLicense('');
        header('Content-Type: text/javascript');
        echo 'var domainResponse = true;';
        exit();
    }

    public function setAppLicenseForm()
    {
        gridboxHelper::setAppLicense('');
        header('Location: https://www.balbooa.com/user/downloads/licenses');
        exit();
    }

    public function setAppLicenseBalbooa()
    {
        gridboxHelper::setAppLicenseBalbooa('');
        header('Content-Type: text/javascript');
        echo 'success';
        exit();
    }

    public function getAppLicense()
    {
        gridboxHelper::checkUserEditLevel();
        $input = JFactory::getApplication()->input;
        $data = $input->get('data', '', 'string');
        gridboxHelper::setAppLicense($data);
        gridboxHelper::setAppLicenseBalbooa($data);
        exit();
    }

    public function setYandexMapsKey()
    {
        gridboxHelper::checkUserEditLevel();
        $model = $this->getModel();
        $model->setYandexMapsKey();
    }

    public function getYandexMapsKey()
    {
        header('Content-Type: text/javascript');
        $key = gridboxHelper::getYandexMapsKey();

        echo 'app.yandexMapsKey = "'.$key.'"';exit();
    }

    public function getDefaultElementsBox()
    {
        $defaultElementsBox = gridboxHelper::getDefaultElementsBox();
        header('Content-Type: text/javascript');
        $data = 'var defaultElementsBox = '.$defaultElementsBox.';';
        echo $data;exit;
    }

    public function reloadModules()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $type = $input->get('type', '', 'string');
        $model = $this->getModel();
        $str = $model->reloadModules($id, $type);
        echo $str;
        exit;
    }

    public function contentSliderAdd()
    {
        $model = $this->getModel();
        $str = $model->contentSliderAdd();
        echo $str;
        exit;
    }

    public function deleteMenuItem()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $model->deleteMenuItem();
        exit;
    }

    public function saveMenuItemTitle()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $model->saveMenuItemTitle();
        exit;
    }

    public function sortMenuItems()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $model->sortMenuItems();
        exit;
    }

    public function getSiteCssObjeck()
    {
        $obj = gridboxHelper::getSiteCssPaterns();
        $str = json_encode($obj);
        echo $str;exit;
    }

    public function setLibraryImage()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $model->setLibraryImage();
    }

    public function getPostNavigation()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $input->set('view', 'gridbox');
        $id = $input->get('id', 0, 'int');
        $maximum = $input->get('maximum', 0, 'int');
        gridboxHelper::$editItem = null;
        $model = $this->getModel();
        $model->setEditorView();
        $str = gridboxHelper::getPostNavigation($maximum, $id);
        echo $str;exit;
    }

    public function getRelatedPosts()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $input->set('view', 'gridbox');
        $id = $input->get('id', 0, 'int');
        $app = $input->get('app', 0, 'int');
        $related = $input->get('related', '', 'string');
        $limit = $input->get('limit', 0, 'int');
        $maximum = $input->get('maximum', 0, 'int');
        $type = $input->get('type', '', 'string');
        gridboxHelper::$editItem = null;
        if ($type == 'slider') {
            gridboxHelper::$editItem = new stdClass();
            gridboxHelper::$editItem->type = 'related-posts-slider';
        }
        $model = $this->getModel();
        $model->setEditorView();
        $str = gridboxHelper::getRelatedPosts($app, $related, $limit, $maximum, $id);
        echo $str;exit;
    }

    public function getRecentlyViewedProducts()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $input->set('view', 'gridbox');
        $limit = $input->get('limit', 0, 'int');
        $maximum = $input->get('maximum', 0, 'int');
        gridboxHelper::$editItem = new stdClass();
        gridboxHelper::$editItem->type = 'recently-viewed-products';
        $model = $this->getModel();
        $model->setEditorView();
        $str = gridboxHelper::getRecentlyViewedProducts($limit, $maximum);
        echo $str;exit;
    }

    public function getRecentPosts()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $input->set('view', 'gridbox');
        $id = $input->get('id', 0, 'int');
        $sorting = $input->get('sorting');
        $limit = $input->get('limit', 0, 'int');
        $maximum = $input->get('maximum', 0, 'int');
        $category = $input->get('category', '', 'string');
        $featured = $input->get('featured', false, 'bool');
        $pagination = $input->get('pagination', '', 'string');
        gridboxHelper::$editItem = null;
        $model = $this->getModel();
        $model->setEditorView();
        $obj = new stdClass();
        $obj->posts = gridboxHelper::getRecentPosts($id, $sorting, $limit, $maximum, $category, $featured);
        $obj->pagination = gridboxHelper::getRecentPostsPagination($id, $limit, $category, $featured, 0, $pagination);
        $str = json_encode($obj);
        echo $str;exit;
    }

    public function getRecentComments()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $sorting = $input->get('sorting');
        $limit = $input->get('limit', 0, 'int');
        $maximum = $input->get('maximum', 0, 'int');
        $category = $input->get('category', '', 'string');
        gridboxHelper::$editItem = null;
        $str = gridboxHelper::getRecentComments($id, $sorting, $limit, $maximum, $category);
        echo $str;exit;
    }

    public function getRecentReviews()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $sorting = $input->get('sorting');
        $limit = $input->get('limit', 0, 'int');
        $maximum = $input->get('maximum', 0, 'int');
        $category = $input->get('category', '', 'string');
        gridboxHelper::$editItem = null;
        $str = gridboxHelper::getRecentReviews($id, $sorting, $limit, $maximum, $category);
        echo $str;exit;
    }

    public function getRecentPostsSlider()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $input->set('view', 'gridbox');
        $id = $input->get('id', 0, 'int');
        $sorting = $input->get('sorting');
        $limit = $input->get('limit', 0, 'int');
        $maximum = $input->get('maximum', 0, 'int');
        $category = $input->get('category', '', 'string');
        $featured = $input->get('featured', false, 'bool');
        gridboxHelper::$editItem = new stdClass();
        gridboxHelper::$editItem->type = 'recent-posts-slider';
        $model = $this->getModel();
        $model->setEditorView();
        $str = gridboxHelper::getRecentPosts($id, $sorting, $limit, $maximum, $category, $featured);
        echo $str;exit;
    }

    public function getBlogCategories()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $maximum = $input->get('maximum', 0, 'int');
        $items = gridboxHelper::getBlogCategories($id);
        $str = gridboxHelper::getBlogCategoriesHtml($items, $maximum);
        echo $str;exit;
    }

    public function getBlogTags()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $category = $input->get('category', 0, 'int');
        $limit = $input->get('limit', 0, 'int');
        $str = gridboxHelper::getBlogTags($id, $category, $limit);
        echo $str;exit;
    }

    public function getProductData()
    {
        $model = $this->getModel();
        $data = $model->getProductData();
        echo json_encode($data);
        exit;
    }

    public function getPageTags()
    {
        $model = $this->getModel();
        $tags = $model->getPageTags();
        echo json_encode($tags);
        exit;
    }

    public function checkProductTour()
    {
        gridboxHelper::checkUserEditLevel();
        $model = $this->getModel();
        $model->checkProductTour();
    }

    public function getUserAuthorisedLevels()
    {
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $obj = json_encode($groups);
        echo $obj;
        exit;
    }

    public function getLibraryItems()
    {
        $model = $this->getModel();
        $obj = $model->getLibraryItems();
        $obj->global = JText::_('GLOBAL_ITEM');
        $obj->delete = JText::_('DELETE');
        $obj = json_encode($obj);
        echo $obj;
        exit;
    }

    public function getBlogPosts()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $max = $input->get('max', 0, 'int');
        $limit = $input->get('limit', 0, 'int');
        $order = $input->get('order', 'created', 'string');
        $model = $this->getModel();
        $model->setEditorView();
        echo gridboxHelper::getBlogPosts($id, $max, $limit, 0, 0, $order);
        exit;
    }
    
    public function getBlogPagination()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $max = $input->get('max', 0, 'int');
        $limit = $input->get('limit', 0, 'int');
        echo gridboxHelper::getBlogPagination($id, 0, $limit, 0);
        exit;
    }

    public function getItems()
    {
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $theme = $input->get('theme', 0, 'int');
        $edit_type = $input->get('edit_type', '', 'string');
        $view = $input->get('view', '', 'string');
        $pageParams = gridboxHelper::getGridboxItems($id, $theme, $edit_type, $view);
        header('Content-Type: text/javascript');
        echo 'var gridboxItems = '.$pageParams;
        exit;
    }

    public function setStarRatings()
    {
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $result = $model->setStarRatings();
        echo json_encode($result);
        exit;
    }

    public function getLibrary()
    {
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $model->getLibrary();
    }

    public function requestAddLibrary()
    {
        gridboxHelper::checkUserEditLevel();
        $model = $this->getModel();
        $model->requestAddLibrary();
    }

    public function addLibrary()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $model->addLibrary();
    }

    public function removeLibrary()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $model->removeLibrary();
    }

    public function gridboxSave()
    {
        $data = file_get_contents('php://input');
        $obj = json_decode($data);
        $this->executeSave($obj);
    }

    public function gridboxAjaxSave()
    {
        $input = JFactory::getApplication()->input;
        $data = $input->get('obj', '', 'raw');
        $obj = json_decode($data);
        $this->executeSave($obj);
    }

    public function executeSave($obj)
    {
        $user = JFactory::getUser();
        if (!isset($obj->edit_type)) {
            $pageAssets = new gridboxAssetsHelper($obj->page->id, 'page');
            $editPage = $pageAssets->checkPermission('core.edit');
            if (!$editPage && !empty($obj->page->page_category)) {
                $editPage = $pageAssets->checkEditOwn($obj->page->page_category);
            }
            $editFlag = $editPage;
        } else if ($obj->edit_type == 'post-layout' || $obj->edit_type == 'blog') {
            $editFlag = $user->authorise('core.edit.layouts', 'com_gridbox.app.'.$obj->page->id);
        } else {
            $editFlag = $user->authorise('core.edit', 'com_gridbox');
        }
        if ($editFlag) {
            $model = $this->getModel();
            $model->gridboxSave($obj);
        } else {
            echo JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED');
            exit;
        }
    }

    public function checkMainMenu()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $model->checkMainMenu();
    }

    public function setMapsKey()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $model->setMapsKey();
    }

    public function getBlocksLicense()
    {
        gridboxHelper::checkUserEditLevel();
        $model = $this->getModel();
        $model->getBlocksLicense();
    }

    public function getPluginLicense()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $model->getPluginLicense();
    }

    public function setNewMenuItem()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        gridboxHelper::setNewMenuItem();
        exit;
    }

    public function setMenuItem()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        gridboxHelper::setMenuItem();
        exit;
    }

    public function getMenu()
    {
        gridboxHelper::checkPostData();
        $menu = gridboxHelper::getMenu();
        echo $menu;
        exit;
    }

    public function getMenuItems()
    {
        gridboxHelper::checkUserEditLevel();
        gridboxHelper::checkPostData();
        $input = JFactory::getApplication()->input;
        $menutype = $input->get('menutype', 0, 'string');
        $menu = gridboxHelper::getMenuItems($menutype);
        echo json_encode($menu);
        exit;
    }

    public function loadModule()
    {
        header('Content-Type: text/javascript');
        echo gridboxHelper::loadModule();
        exit;
    }

    public function loadLayout()
    {
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $model->loadLayout();
    }

    public function loadPlugin()
    {
        gridboxHelper::checkPostData();
        $model = $this->getModel();
        $model->loadPlugin();
    }
}