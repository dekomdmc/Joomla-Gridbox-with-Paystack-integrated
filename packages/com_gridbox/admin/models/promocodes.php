<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.path');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
use Joomla\Registry\Registry;

class gridboxModelPromocodes extends JModelList
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'title', 'published', 'state', 'order_list'
            );
        }
        parent::__construct($config);
    }

    public function updatePromoCode($data)
    {
        $map = json_decode($data->map);
        $db = JFactory::getDbo();
        unset($data->map);
        $db->updateObject('#__gridbox_store_promo_codes', $data, 'id');
        $query = $db->getQuery(true)
            ->delete('#__gridbox_store_promo_codes_map')
            ->where('code_id = '.$data->id)
            ->where('type <> '.$db->quote($data->applies_to));
        $db->setQuery($query)
            ->execute();
        $pks = array();
        foreach ($map as $obj) {
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__gridbox_store_promo_codes_map')
                ->where('code_id = '.$data->id)
                ->where('item_id = '.$obj->id)
                ->where('variation = '.$db->quote($obj->variation));
            $db->setQuery($query);
            $obj->item_id = $db->loadResult();
            if ($obj->item_id) {
                $pks[] = $obj->item_id;
            }
        }
        $query = $db->getQuery(true)
            ->delete('#__gridbox_store_promo_codes_map')
            ->where('code_id = '.$data->id);
        if (!empty($pks)) {
            $str = implode(', ', $pks);
            $query->where('id NOT IN ('.$str.')');
        }
        $db->setQuery($query)
            ->execute();
        foreach ($map as $obj) {
            if (empty($obj->item_id)) {
                $object = new stdClass();
                $object->code_id = $data->id;
                $object->item_id = $obj->id;
                $object->variation = $obj->variation;
                $object->type = $data->applies_to;
                $db->insertObject('#__gridbox_store_promo_codes_map', $object);
            }
        }
    }

    public function getPromoCodes()
    {
        $db = JFactory::getDbo();
        $date = JDate::getInstance()->format('Y-m-d H:i:s');
        $date = $db->quote($date);
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('p.id')
            ->from('#__gridbox_store_promo_codes AS p')
            ->where('p.published = 1')
            ->where('(p.publish_down = '.$nullDate.' OR p.publish_down >= '.$date.')')
            ->where('(p.publish_up = '.$nullDate.' OR p.publish_up <= '.$date.')')
            ->where('(p.limit = 0 OR p.used < pc.limit)')
            ->leftJoin('#__gridbox_store_promo_codes AS pc ON pc.id = p.id');
        $db->setQuery($query);
        $array = $db->loadObjectList();
        $promo = array();
        foreach ($array as$value) {
            $promo[] = $this->getOptions($value->id);
        }

        return $promo;
    }

    public function getProducts($category)
    {
        $db = JFactory::getDbo();
        $date = $db->quote(date("Y-m-d H:i:s"));
        $nullDate = $db->quote($db->getNullDate());
        $query = $db->getQuery(true)
            ->select('d.*, p.title, p.intro_image AS image')
            ->from('#__gridbox_store_product_data AS d')
            ->where('p.page_category <> '.$db->quote('trashed'))
            ->where('p.published = 1')
            ->where('p.created <= '.$date)
            ->where('(p.end_publishing = '.$nullDate.' OR p.end_publishing >= '.$date.')')
            ->leftJoin('#__gridbox_pages AS p ON p.id = d.product_id')
            ->order('d.product_id DESC');
        $db->setQuery($query);
        $pages = $db->loadObjectList();
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
        $products = array();
        foreach ($pages as $page) {
            $variations = json_decode($page->variations);
            $dimensions = !empty($page->dimensions) ? json_decode($page->dimensions) : new stdClass();
            unset($page->variations);
            $c = 0;
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_product_variations_map')
                ->where('product_id = '.$page->product_id);
            $db->setQuery($query);
            $map = $db->loadObjectList();
            $images = new stdClass();
            $extra_options = gridboxHelper::getProductExtraOptions($page->extra_options);
            unset($page->extra_options);
            foreach ($map as $variation) {
                $images->{$variation->option_key} = json_decode($variation->images);
            }
            foreach ($variations as $key => $product) {
                $product->id = $page->product_id;
                $product->product_type = $page->product_type;
                $product->dimensions = $dimensions;
                $product->title = $page->title;
                $product->image = $page->image;
                $product->prices = new stdClass();
                $product->prices->price = gridboxHelper::preparePrice($product->price);
                $product->extra = $extra_options;
                $product->extra_options = new stdClass();
                if (!empty($product->sale_price)) {
                    $product->prices->sale = gridboxHelper::preparePrice($product->sale_price);
                }
                $array = explode('+', $key);
                $info = array();
                $product->variations = array();
                foreach ($array as $var) {
                    if (isset($fieldsData[$var])) {
                        $info[] = '<span>'.$fieldsData[$var]->title.' '.$fieldsData[$var]->value.'</span>';
                        $product->variations[] = $fieldsData[$var];
                    }
                    if (!empty($images->{$var})) {
                        $product->image = $images->{$var}[0];
                    }
                }
                $product->variation = $key;
                $product->info = implode('/', $info);
                if ($category == 1) {
                    $product->categories = gridboxHelper::getProductCategoryId($page->product_id);
                }
                $products[] = $product;
                $c++;
            }
            if ($c == 0) {
                $product = $page;
                $product->prices = new stdClass();
                $product->dimensions = $dimensions;
                $product->prices->price = gridboxHelper::preparePrice($product->price);
                $product->extra = $extra_options;
                $product->extra_options = new stdClass();
                if (!empty($product->sale_price)) {
                    $product->prices->sale = gridboxHelper::preparePrice($product->sale_price);
                }
                if ($category == 1) {
                    $product->categories = gridboxHelper::getProductCategoryId($page->product_id);
                }
                $product->variation = '';
                $product->variations = array();
                $products[] = $product;
            }
        }

        return $products;
    }

    public function getCategories()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('c.id, c.title, c.image')
            ->from('#__gridbox_categories AS c')
            ->leftJoin('#__gridbox_app AS a ON c.app_id = a.id')
            ->where('a.type = '.$db->quote('products'));
        $db->setQuery($query);
        $array = $db->loadObjectList();

        return $array;
    }

    public function getOptions($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_promo_codes')
            ->where('id = '.$id);
        $db->setQuery($query);
        $obj = $db->loadObject();
        if ($obj->applies_to == 'category') {
            $query = $db->getQuery(true)
                ->select('m.item_id AS id, p.title, p.image')
                ->from('#__gridbox_store_promo_codes_map AS m')
                ->where('m.code_id = '.$id)
                ->leftJoin('#__gridbox_categories AS p ON p.id = m.item_id');
            $db->setQuery($query);
            $obj->map = $db->loadObjectList();
        } else if ($obj->applies_to == 'product') {
            $query = $db->getQuery(true)
                ->select('m.item_id AS id, p.title, p.intro_image AS image, d.variations, m.variation')
                ->from('#__gridbox_store_promo_codes_map AS m')
                ->where('m.code_id = '.$id)
                ->leftJoin('#__gridbox_store_product_data AS d ON d.product_id = m.item_id')
                ->leftJoin('#__gridbox_pages AS p ON p.id = d.product_id');
            $db->setQuery($query);
            $obj->map = $db->loadObjectList();
            foreach ($obj->map as $key => $product) {
                $variations = json_decode($product->variations);
                if (empty($product->variation) || !isset($variations->{$product->variation})) {
                    continue;
                }
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__gridbox_store_product_variations_map')
                    ->where('product_id = '.$product->id);
                $db->setQuery($query);
                $variations_map = $db->loadObjectList();
                $images = new stdClass();
                foreach ($variations_map as $variation) {
                    $images->{$variation->option_key} = json_decode($variation->images);
                }
                $vars = explode('+', $product->variation);
                $info = array();
                foreach ($vars as $value) {
                    $query = $db->getQuery(true)
                        ->select('fd.value, f.title')
                        ->from('#__gridbox_store_products_fields_data AS fd')
                        ->where('fd.option_key = '.$db->quote($value))
                        ->leftJoin('#__gridbox_store_products_fields AS f ON f.id = fd.field_id');
                    $db->setQuery($query);
                    $variation = $db->loadObject();
                    $info[] = '<span>'.$variation->title.' '.$variation->value.'</span>';
                    if (!empty($images->{$value})) {
                        $product->image = $images->{$value}[0];
                    }
                }
                $product->info = implode('/', $info);
            }
        } else {
            $obj->map = array();
        }
        $nullDate = $db->getNullDate();
        if ($obj->publish_up == $nullDate) {
            $obj->publish_up = '';
        }
        if ($obj->publish_down == $nullDate) {
            $obj->publish_down = '';
        }
        if ($obj->limit == 0) {
            $obj->limit = '';
        }

        return $obj;
    }

    public function publish($cid, $value)
    {
        $db = JFactory::getDbo();
        foreach ($cid as $id) {
            $obj = new stdClass();
            $obj->id = $id * 1;
            $obj->published = $value * 1;
            $db->updateObject('#__gridbox_store_promo_codes', $obj, 'id');
        }
    }

    public function delete($cid)
    {
        $db = JFactory::getDbo();
        foreach ($cid as $id) {
            $query = $db->getQuery(true)
                ->delete('#__gridbox_store_promo_codes')
                ->where('id = '.$id);
            $db->setQuery($query)
                ->execute();
            $query = $db->getQuery(true)
                ->delete('#__gridbox_store_promo_codes_map')
                ->where('code_id = '.$id);
            $db->setQuery($query)
                ->execute();
        }
    }

    public function duplicate($cid)
    {
        $db = JFactory::getDbo();
        foreach ($cid as $id) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_promo_codes')
                ->where('id = '.$id);
            $db->setQuery($query);
            $code = $db->loadObject();
            $code->published = 0;
            $code->used = 0;
            $id = $code->id;
            unset($code->id);
            $db->insertObject('#__gridbox_store_promo_codes', $code);
            $pk = $db->insertid();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__gridbox_store_promo_codes_map')
                ->where('code_id = '.$id);
            $db->setQuery($query);
            $map = $db->loadObjectList();
            foreach ($map as $value) {
                $value->code_id = $pk;
                unset($value->id);
                $db->insertObject('#__gridbox_store_promo_codes_map', $value);
            }
        }
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

    public function addPromoCode()
    {
        $db = JFactory::getDbo();
        $obj = new stdClass();
        $obj->title = 'Promo Code';
        $db->insertObject('#__gridbox_store_promo_codes', $obj);
    }
    
    protected function getListQuery()
    {
        $this->getGridboxFilters();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__gridbox_store_promo_codes');
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('title LIKE ' . $search);
        }
        $published = $this->getState('filter.state');
        if (is_numeric($published)) {
            $query->where('published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(published IN (0, 1))');
        }
        $orderCol = $this->state->get('list.ordering', 'id');
        $orderDirn = $this->state->get('list.direction', 'desc');
        if ($orderCol == 'order_list') {
            $orderDirn = 'ASC';
        }
        $query->order($db->escape($orderCol . ' ' . $orderDirn));
        
        return $query;
    }
    
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        return parent::getStoreId($id);
    }
    
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);
        parent::populateState('id', 'desc');
    }
}