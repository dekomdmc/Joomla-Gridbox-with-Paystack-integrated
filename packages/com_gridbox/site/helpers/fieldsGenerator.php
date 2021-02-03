<?php

/**
 * @package   Gridbox
 * @author    Balbooa http://www.balbooa.com/
 * @copyright Copyright @ Balbooa
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */


class fieldsGenerator
{
    public $jce;
    public $jceIndex;
    public $form;
    public $pageTags;
    public $tags;
    public $desktopFiles;
    public $type;

    public function __construct($jce, $form, $pageTags, $tags, $type)
    {
        $this->jce = !empty($jce) && $jce * 1 === 1;
        $this->jceIndex = 1;
        $this->form = $form;
        $this->pageTags = $pageTags;
        $this->tags = $tags;
        $this->desktopFiles = gridboxHelper::getDesktopFieldFiles();
        $this->type = $type;
    }

    public function getGroupsHTML($fields, $groups, $fields_data, $productData)
    {
        $object = json_decode($groups);
        $array = array();
        $lastKey = '';
        $html = '';
        $data = new stdClass();
        $productsGroups = array(
            'ba-group-product-pricing', 'ba-group-product-variations', 'ba-group-related-product',
            'ba-group-digital-product'
        );
        if ($this->type == 'products' && !isset($object->{'ba-group-product-pricing'})) {
            $object->{'ba-group-product-pricing'} = new stdClass();
            $object->{'ba-group-product-pricing'}->title = 'Pricing and Inventory';
            $object->{'ba-group-product-pricing'}->fields = array();
            $object->{'ba-group-product-variations'} = new stdClass();
            $object->{'ba-group-product-variations'}->title = 'Options and Variations';
            $object->{'ba-group-product-variations'}->fields = array();
        }
        if ($this->type == 'products' && !isset($object->{'ba-group-related-product'})) {
            $object->{'ba-group-related-product'} = new stdClass();
            $object->{'ba-group-related-product'}->title = 'Related Products';
            $object->{'ba-group-related-product'}->fields = array();
        }
        if ($this->type == 'products' && !isset($object->{'ba-group-digital-product'})) {
            $object->{'ba-group-digital-product'} = new stdClass();
            $object->{'ba-group-digital-product'}->title = 'Add Digital Product';
            $object->{'ba-group-digital-product'}->fields = array();
        }
        if ($productData && !$productData->relatedFlag) {
            unset($object->{'ba-group-related-product'});
        }
        foreach ($fields as $value) {
            $data->{$value->field_key} = $value;
        }
        foreach ($object as $key => $group) {
            if (!in_array($key, $productsGroups)) {
                $lastKey = $key;
            }
            $group->str = '';
            if ($key == 'ba-group-product-pricing') {
                $group->str = $this->getProductPricingFields($productData);
            } else if ($key == 'ba-group-product-variations') {
                $group->str = $this->getProductVariations($productData);
            } else if ($key == 'ba-group-related-product') {
                $group->str = $this->getProductRelated($productData);
            } else if ($key == 'ba-group-digital-product') {
                $group->str = $this->getGigitalProduct($productData);
            }
            foreach ($group->fields as $id) {
                if (!isset($data->{$id})) {
                    continue;
                }
                $value = $data->{$id};
                $group->str .= $this->getFieldHTML($fields_data, $value);
                if ($value->field_type == 'textarea') {
                    $options = json_decode($value->options);
                    if ($options->texteditor) {
                        $group->texteditor = true;
                    }
                }
                $array[] = $value->id;
            }
        }
        foreach ($fields as $value) {
            if (!in_array($value->id, $array)) {
                if ($value->field_type == 'textarea') {
                    $options = json_decode($value->options);
                    if ($options->texteditor) {
                        $object->{$lastKey}->texteditor = true;
                    }
                }
                $object->{$lastKey}->str .= $this->getFieldHTML($fields_data, $value);
            }
        }
        foreach ($object as $key => $group) {
            if (empty($group->str)) {
                continue;
            }
            $html .= '<div class="ba-fields-group-wrapper';
            if (($key == 'ba-group-product-variations' || $key == 'ba-group-product-pricing')
                && $productData->data->product_type == 'digital'
            ) {
                $html .= ' digital-product-type';
            } else if ($key == 'ba-group-digital-product' && $productData->data->product_type != 'digital') {
                $html .= ' physical-product-type';
            }
            $html .= '" id="' . $key;
            $html .= '"><div class="ba-fields-group-title"><input type="text" placeholder="';
            $html .= JText::_('NEW_GROUP') . '" value="' . $group->title;
            $html .= '"><div class="ba-fields-group-icons">';
            if (!in_array($key, $productsGroups)) {
                $html .= '<i class="zmdi zmdi-delete"></i>';
            }
            if (!isset($group->texteditor)) {
                $html .= '<i class="zmdi zmdi-apps"></i>';
            }
            $html .= '</div></div><div class="ba-fields-group"';
            if ($key == 'ba-group-product-pricing' || $key == 'ba-group-product-variations') {
                $html .= ' data-disable-sorting="disable"';
            }
            $html .= '>';
            $html .= $group->str;
            $html .= '</div></div>';
        }

        return $html;
    }

    protected function getGigitalProduct($product)
    {
        $digital = !empty($product->data->digital_file) ? json_decode($product->data->digital_file) : new stdClass();
        $filename = isset($digital->file) ? $digital->file->filename : '';
        $name = isset($digital->file) ? $digital->file->name : '';
        $value = isset($digital->file) ? $digital->expires->value : '';
        $format = isset($digital->file) ? $digital->expires->format : '';
        $max = isset($digital->file) ? $digital->max : '';
        $required = $product->data->product_type == 'digital';
        $html = $this->getHTMLHeader('0', 'digital-product-file', '0', $required, JText::_('UPLOAD_PRODUCT_FILE'), true);
        $html .= '<div><input type="text" readonly="" onfocus="this.blur()" class="trigger-upload-digital-file"';
        $html .= ' data-value="' . $filename . '" value="' . $name . '" placeholder="' . JText::_('SELECT');
        $html .= '"><i class="zmdi zmdi-attachment-alt"></i>';
        $html .= '<div class="reset disabled-reset reset-digital-file"><i class="zmdi zmdi-close"></i></div></div>';
        $html .= '</div></div>';
        $expires = array('h' => JText::_('HOURS'), 'd' => JText::_('DAYS'), 'm' => JText::_('MONTHS'), 'y' => JText::_('YEARS'));
        $html .= $this->getHTMLHeader('0', 'digital-link-expires', '0', false, JText::_('DOWNLOAD_LINK_EXPIRES'), true);
        $html .= '<input type="number" value="' . $value . '">';
        $html .= '<select>';
        foreach ($expires as $key => $value) {
            $html .= '<option value="' . $key . '"' . ($format == $key ? ' selected' : '') . '>' . $value . '</option>';
        }
        $html .= '</select>';
        $html .= '</div></div>';
        $html .= $this->getHTMLHeader('0', 'digital-max-downloads', '0', false, JText::_('MAXIMUM_DOWNLOADS'), true);
        $html .= '<input type="number" value="' . $max . '">';
        $html .= '</div></div>';

        return $html;
    }

    protected function getProductRelated($product)
    {
        $html = $this->getHTMLHeader('0', 'related-product', '0', false, JText::_('PRODUCTS'), true);
        $html .= '<div class="field-sorting-wrapper related-product"><div class="sorting-container">';
        $html .= '<div class="sorting-item"><div class="related-product-title-wrapper"><i class="zmdi zmdi-plus"></i></div>';
        $html .= '<div class="selected-items-wrapper" style="--placeholder-text:\'' . JText::_('ADD_NEW_ITEM') . '\';">';
        foreach ($product->related as $related) {
            $img = $related->image;
            $html .= '<span class="selected-items" data-id="' . $related->id . '">';
            $html .= '<span class="ba-item-thumbnail"' . (!empty($img) ? ' style="background-image:url(' . $img . ')"' : '') . '>';
            if (empty($img)) {
                $html .= '<i class="zmdi zmdi-label"></i>';
            }
            $html .= '</span><span class="selected-items-name">' . $related->title;
            $html .= '</span><i class="zmdi zmdi-close remove-selected-items"></i><span class="grid-sorting-handle"></span></span>';
        }
        $html .= '</div></div>';
        $html .= '</div></div></div></div>';

        return $html;
    }

    protected function getProductVariations($product)
    {
        $html = $this->getHTMLHeader('0', 'product-options', '0', false, JText::_('OPTIONS'), true);
        $html .= '<div class="field-sorting-wrapper product-options"><div class="sorting-container">';
        foreach ($product->fields as $key => $field) {
            $html .= '<div class="sorting-item" data-id="' . $key . '" data-type="' . $field->type . '">';
            $html .= '<div class="product-options-title-wrapper">';
            $html .= $field->title . '</div><div class="selected-items-wrapper">';
            usort($field->map, function ($a, $b) {
                return ($a->order_list < $b->order_list) ? -1 : 1;
            });
            foreach ($field->map as $option) {
                $images = json_decode($option->images);
                $count = count($images);
                $html .= '<span class="selected-items" data-key="' . $option->option_key . '" data-id="' . $option->id;
                $html .= '"><span class="ba-item-thumbnail" data-image-count="' . $count . '"';
                if ($count > 0) {
                    $image = strpos($images[0], 'balbooa.com') === false ? JUri::root() . $images[0] : $images[0];
                    $html .= ' style="background-image: url(' . $image . ');"';
                    foreach ($images as $key => $image) {
                        $html .= ' data-image-' . $key . '="' . $image . '"';
                    }
                }
                $html .= '><i class="zmdi zmdi-camera"></i></span>';
                $html .= '<span class="selected-items-name">' . $product->fields_data->{$option->option_key};
                $html .= '</span><i class="zmdi zmdi-close remove-selected-items"></i><span class="grid-sorting-handle"></span></span>';
            }
            $html .= '</div><div class="product-options-icons-wrapper"><span class="add-new-product-options-value">';
            $html .= '<i class="zmdi zmdi-plus"></i></span><span class="sorting-handle"><i class="zmdi zmdi-apps"></i>';
            $html .= '</span><span class=""><i class="zmdi zmdi-delete"></i></span></div></div>';
        }
        $html .= '</div><div class="add-new-item"><span><input type="text" value="' . JText::_('ADD_NEW_ITEM');
        $html .= '" readonly="" onfocus="this.blur()"><i class="zmdi zmdi-plus"></i></span></div></div>';
        $html .= '</div></div>';
        $html .= $this->getHTMLHeader('1', 'product-variations', '1', false, JText::_('VARIATIONS'), true);
        $html .= '<div class="product-variations-table">';
        $html .= '<div class="variations-table-header"><div class="variations-table-row">';
        $html .= '<div class="variations-table-cell variation-cell"></div>';
        $html .= '<div class="variations-table-cell price-cell">' . JText::_('PRICE') . '</div>';
        $html .= '<div class="variations-table-cell sele-price-cell">' . JText::_('SALE_PRICE') . '</div>';
        $html .= '<div class="variations-table-cell sku-cell">' . JText::_('SKU') . '</div>';
        $html .= '<div class="variations-table-cell stock-cell">' . JText::_('IN_STOCK') . '</div>';
        $html .= '<div class="variations-table-cell default-cell">' . JText::_('DEFAULT') . '</div>';
        $html .= '</div></div>';
        $html .= '<div class="variations-table-body">';
        foreach ($product->data->variations as $key => $obj) {
            $html .= '<div class="variations-table-row" data-key="' . $key . '">';
            $html .= '<div class="variations-table-cell variation-cell">';
            $array = explode('+', $key);
            foreach ($array as $value) {
                $html .= '<span>' . $product->fields_data->{$value} . '</span>';
            }
            $html .= '</div>';
            $html .= '<div class="variations-table-cell price-cell" data-field-type="price">';
            $html .= '<input type="text" data-key="price" data-decimals="10';
            $html .= '" value="' . $obj->price . '"></div>';
            $html .= '<div class="variations-table-cell sale-price-cell" data-field-type="price">';
            $html .= '<input type="text" data-key="sale_price" data-decimals="10';
            $html .= '" value="' . $obj->sale_price . '"></div>';
            $html .= '<div class="variations-table-cell sku-cell"><input type="text" data-key="sku" value="' . $obj->sku;
            $html .= '"></div><div class="variations-table-cell stock-cell" data-field-type="price">';
            $html .= '<input type="text" data-key="stock" value="' . $obj->stock . '"></div>';
            $html .= '<div class="variations-table-cell default-cell" data-default="';
            $html .= (isset($obj->default) && $obj->default ? 1 : 0) . '"><i class="zmdi zmdi-star"></i></div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '</div></div></div>';
        $html .= $this->getHTMLHeader('0', 'product-extra-options', '0', false, JText::_('EXTRA_OPTIONS'), true);
        $html .= '<div class="field-sorting-wrapper product-extra-options"><div class="sorting-container">';
        foreach ($product->data->extra_options as $id => $obj) {
            $html .= '<div class="sorting-item" data-id="' . $id . '">';
            $html .= '<div class="extra-product-options-table"><div class="extra-product-options-thead">';
            $html .= '<div class="extra-product-options-row"><div class="extra-product-option-title">' . $obj->title . '</div>';
            $html .= '<div class="extra-product-option-price">' . JText::_('PRICE') . '</div>';
            $html .= '<div class="extra-product-option-default">' . JText::_('DEFAULT') . '</div>';
            $html .= '<div class="extra-product-option-icons"><span class="add-new-extra-product-options">';
            $html .= '<i class="zmdi zmdi-plus"></i></span><span class="sorting-handle"><i class="zmdi zmdi-apps"></i></span>';
            $html .= '<span><i class="zmdi zmdi-delete"></i></span></div></div></div>';
            $html .= '<div class="extra-product-options-tbody">';
            foreach ($obj->items as $key => $item) {
                $html .= '<div class="extra-product-options-row" data-key="' . $key . '">';
                $html .= '<div class="extra-product-option-title">' . $item->title . '</div>';
                $html .= '<div class="extra-product-option-price" data-field-type="price">';
                $html .= '<input type="text" data-decimals="10" value="' . $item->price . '"></div>';
                $html .= '<div class="extra-product-option-default"><i class="zmdi zmdi-star" data-default="';
                $html .= ((int)$item->default) . '"></i></div><div class="extra-product-option-icons">';
                $html .= '<span class="delete-extra-product-option"><i class="zmdi zmdi-delete"></i></span></div></div>';
            }
            $html .= '</div></div></div>';
        }
        $html .= '</div><div class="add-new-item"><span><input type="text" value="' . JText::_('ADD_NEW_ITEM');
        $html .= '" readonly="" onfocus="this.blur()"><i class="zmdi zmdi-plus"></i></span></div></div>';
        $html .= '</div></div>';

        return $html;
    }

    protected function getProductPricingFields($product)
    {
        $keys = array(
            'price' => array(
                'type' => 'price',
                'required' => true,
                'title' => JText::_('REGULAR_PRICE'),
                'value' => !empty($product->data) ? $product->data->price : '',
                'class' => ''
            ),
            'sale_price' => array(
                'type' => 'price',
                'required' => false,
                'title' => JText::_('SALE_PRICE'),
                'value' => !empty($product->data) ? $product->data->sale_price : '',
                'class' => ''
            ),
            'sku' => array(
                'type' => 'text',
                'required' => false,
                'title' => JText::_('SKU'),
                'value' => !empty($product->data) ? $product->data->sku : '',
                'class' => 'one-third-width'
            ),
            'stock' => array(
                'type' => 'text',
                'required' => false,
                'title' => JText::_('IN_STOCK'),
                'value' => !empty($product->data) ? $product->data->stock : '',
                'class' => 'one-third-width'
            ),
        );
        $options = new stdClass();
        $options->symbol = gridboxHelper::$store->currency->symbol;
        $options->decimals = 10;
        $options->position = gridboxHelper::$store->currency->position;
        $obj = new stdClass();
        $obj->id = !empty($product->data) ? $product->data->id : 0;
        $html = '';
        foreach ($keys as $key => $data) {
            $options->type = $data['type'];
            $className = $data['class'] . ' product-data';
            $html .= $this->getHTMLHeader($key, $data['type'], $obj->id, $data['required'], $data['title'], true, $className);
            if ($data['type'] == 'price') {
                $html .= $this->renderPrice($obj, $options, $data['value']);
            } else {
                $html .= $this->renderText($obj, $options, $data['value']);
            }
            $html .= '</div></div>';
        }
        $html .= $this->getHTMLHeader('dimensions', 'price', 'dimensions', false, JText::_('WEIGHT'), true, 'one-third-width');
        $value = isset($product->data->dimensions->weight) ? $product->data->dimensions->weight : '';
        $html .= '<div class="field-editor-price-wrapper right-currency-position';
        $html .= '"><span class="field-editor-price-currency">' . gridboxHelper::$store->units->weight;
        $html .= '</span><input type="text" name="weight"';
        $html .= ' value="' . $value . '" data-decimals="2"></div>';




        $html .= '</div></div>';



        $html .= $this->getHTMLHeader('badges', 'product-badges', 'badges', false, JText::_('PRODUCT_BADGES'), true);
        $html .= '<div class="field-sorting-wrapper product-badges"><div class="sorting-container">';
        $html .= '<div class="sorting-item"><div class="product-badges-title-wrapper"><i class="zmdi zmdi-plus"></i></div>';
        $html .= '<div class="selected-items-wrapper" style="--placeholder-text:\'' . JText::_('ADD_NEW_ITEM') . '\';">';
        foreach ($product->badges as $badge) {
            $html .= '<span class="selected-items" data-id="' . $badge->id;
            $html .= '"><span class="selected-items-color" style="--badge-color: ' . $badge->color;
            $html .= ';"></span><span class="selected-items-name">' . $badge->title;
            $html .= '</span><i class="zmdi zmdi-close remove-selected-items"></i><span class="grid-sorting-handle"></span></span>';
        }
        $html .= '</div></div>';
        $html .= '</div></div></div></div>';

        return $html;
    }

    protected function getFieldHTML($fields_data, $value)
    {
        if (isset($fields_data->{$value->id})) {
            $fieldValue = $fields_data->{$value->id}->value;
        } else {
            $fieldValue = '';
        }
        $str = $this->getHTML($value, $fieldValue);

        return $str;
    }

    public function getHTMLHeader($key, $type, $id, $required, $label, $texteditor = false, $className = '')
    {
        $html = '<div class="blog-post-editor-options-group ' . $className . '" data-field-key="' . $key . '" data-field-type="' . $type . '"';
        $html .= ' data-id="' . $id . '" ' . ($required ? 'data-required' : '') . '>';
        $html .= '<div class="blog-post-editor-group-element">';
        if (!$texteditor) {
            $html .= '<div class="sorting-handle"><i class="zmdi zmdi-apps"></i></div>';
        }
        $html .= '<label class="ba-field-editor-label">';
        $html .= $label . ($required ? '<span class="required-fields-star">*</span>' : '') . '</label>';

        return $html;
    }

    public function getHTML($field, $value = '')
    {
        $options = json_decode($field->options);
        $label = isset($options->label) && !empty($options->label) ? $options->label : $field->label;
        $texteditor = $options->type == 'textarea' ? $options->texteditor : false;
        $html = $this->getHTMLHeader($field->field_key, $options->type, $field->id, $field->required, $label, $texteditor);
        if (isset($options->description) && !empty($options->description)) {
            $html .= '<span class="ba-field-editor-admin-description">' . $options->description . '</span>';
        }
        switch ($options->type) {
            case 'text':
            case 'email':
            case 'number':
                $html .= $this->renderText($field, $options, $value);
                break;
            case 'price':
                $html .= $this->renderPrice($field, $options, $value);
                break;
            case 'textarea':
                $html .= $this->renderTextarea($field, $options, $value);
                break;
            case 'select':
                $html .= $this->renderSelect($field, $options, $value);
                break;
            case 'checkbox':
                $html .= $this->renderCheckbox($field, $options, $value);
                break;
            case 'radio':
                $html .= $this->renderRadio($field, $options, $value);
                break;
            case 'range':
                $html .= $this->renderRange($field, $options, $value);
                break;
            case 'url':
                $html .= $this->renderUrl($field, $options, $value);
                break;
            case 'file':
                $html .= $this->renderFile($field, $options, $value);
                break;
            case 'date':
            case 'event-date':
                $html .= $this->renderDate($field, $options, $value);
                break;
            case 'tag':
                $html .= $this->renderTags($field, $options, $value);
                break;
            case 'image-field':
                $html .= $this->renderImage($field, $options, $value);
                break;
            case 'field-simple-gallery':
            case 'product-gallery':
            case 'field-slideshow':
            case 'product-slideshow':
                $html .= $this->renderGallery($field, $options, $value);
                break;
            case 'field-google-maps':
                $html .= $this->renderGoogleMaps($field, $options, $value);
                break;
            case 'field-video':
                $html .= $this->renderVideo($field, $options, $value);
                break;
            case 'time':
                $html .= $this->renderTime($field, $options, $value);
                break;
        }
        $html .= '</div></div>';

        return $html;
    }

    protected function renderText($field, $options, $value)
    {
        return '<input type="' . $options->type . '" name="' . $field->id . '" value="' . $value . '">';
    }

    protected function renderPrice($field, $options, $value)
    {
        $str = '<div class="field-editor-price-wrapper ' . $options->position;
        $str .= '"><span class="field-editor-price-currency">' . $options->symbol . '</span><input type="text" name="';
        $str .= $field->id . '" value="' . $value . '" data-decimals="' . $options->decimals . '"></div>';

        return $str;
    }

    protected function renderTextarea($field, $options, $value)
    {
        $str = '';
        if ($options->texteditor && $this->jce) {
            $str .= '<div class="ba-editor-wrapper jce-editor-enabled">';
            $str .= $this->form->getInput('editor' . $this->jceIndex);
            $str .= '</div>';
        }
        $str .= '<textarea name="' . $field->id . '" style="' . ($options->texteditor && $this->jce ? 'display: none;' : '') . '"';
        $str .= ($options->texteditor ? ' data-texteditor="texteditor"' : '');
        $str .= ($options->texteditor && $this->jce ? ' data-jce="' . ($this->jceIndex++) . '"' : '');
        $str .= '>' . $value . '</textarea>';

        return $str;
    }

    protected function renderSelect($field, $options, $value)
    {
        $str = '<select name="' . $field->id . '" value="' . $value . '">';
        foreach ($options->items as $item) {
            if ($value == $item->key) {
                $selected = ' selected';
            } else {
                $selected = '';
            }
            $str .= '<option value="' . $item->key . '"' . $selected . '>' . $item->title . '</option>';
        }
        $str .= '</select>';

        return $str;
    }

    protected function renderCheckbox($field, $options, $value)
    {
        if (!empty($value)) {
            $data = json_decode($value);
        } else {
            $data = array();
        }
        $str = '';
        foreach ($options->items as $item) {
            $checked = in_array($item->key, $data);
            $str .= '<div class="ba-checkbox-wrapper"><span>' . $item->title . '</span><label class="ba-checkbox">';
            $str .= '<input type="checkbox" name="' . $field->id . '"' . ($checked ? ' checked' : '') . ' value="' . $item->key . '">';
            $str .= '<span></span></label></div>';
        }

        return $str;
    }

    protected function renderRadio($field, $options, $value)
    {
        $str = '';
        foreach ($options->items as $item) {
            $checked = $value == $item->key;
            $str .= '<div class="ba-checkbox-wrapper"><span>' . $item->title . '</span><label class="ba-radio">';
            $str .= '<input type="radio" name="' . $field->id . '"' . ($checked ? ' checked' : '') . ' value="' . $item->key . '">';
            $str .= '<span></span></label></div>';
        }

        return $str;
    }

    protected function renderRange($field, $options, $value)
    {
        $rangeValue = !empty($value) ? $value : 0;
        $str = '<div class="ba-range-wrapper"><span class="ba-range-liner"></span>';
        $str .= '<input type="range" class="ba-range" name="' . $field->id . '" min="' . $options->min;
        $str .= '" max="' . $options->max . '" value="' . $rangeValue . '"><input type="number" ';
        $str .= 'data-callback="emptyCallback" value="' . $rangeValue . '"></div>';

        return $str;
    }

    protected function renderUrl($field, $options, $value)
    {
        if (!empty($value)) {
            $data = json_decode($value);
        } else {
            $data = new stdClass();
            $data->label = $data->link = '';
        }
        $str = '<div><div class="ba-url-field-label-wrapper"><label>' . JText::_('LABEL') . '</label><input type="text" name="' . $field->id;
        $str .= '" data-name="label" value="' . $data->label . '"></div><div class="link-picker-container"><label>';
        $str .= JText::_('LINK') . '</label><input type="text" name="' . $field->id . '"  data-name="link" value="' . $data->link . '">';
        $str .= '<div class="select-link"><i class="zmdi zmdi-attachment-alt"></i><span class="ba-tooltip">';
        $str .= JText::_('LINK_PICKER') . '</span></div><div class="select-file"><i class="zmdi zmdi-file"></i>';
        $str .= '<span class="ba-tooltip">' . JText::_('FILE_PICKER') . '</span></div></div></div>';

        return $str;
    }

    protected function renderFile($field, $options, $value)
    {
        if (is_numeric($value) && isset($this->desktopFiles->{$value})) {
            $desktopFile = $this->desktopFiles->{$value};
            $filename = $desktopFile->name;
        } else if (is_numeric($value)) {
            $value = $filename = '';
        } else {
            $filename = basename($value);
        }
        $str = '<div><input type="text" readonly="" onfocus="this.blur()" class="trigger-attachment-file-field"';
        if ($options->source == 'desktop') {
            $str .= ' data-source="desktop"';
        }
        $str .= ' data-value="' . $value . '" value="' . $filename . '" placeholder="' . JText::_('SELECT');
        $str .= '" name="' . $field->id . '" data-size="' . $options->size;
        $str .= '" data-types="' . $options->types . '"><i class="zmdi zmdi-attachment-alt"></i>';
        $str .= '<div class="reset disabled-reset reset-attachment-file-field"><i class="zmdi zmdi-close"></i></div></div>';

        return $str;
    }

    protected function renderDate($field, $options, $value)
    {
        $str = '<div class="container-icon"><input type="text" name="' . $field->id . '" readonly value="' . $value . '">';
        $str .= '<div class="icons-cell"><i class="zmdi zmdi-calendar-alt"></i></div>';
        $str .= '<div class="reset disabled-reset reset-date-field"><i class="zmdi zmdi-close"></i></div></div>';

        return $str;
    }

    protected function renderTags($field, $options, $value)
    {
        $str = '<div class="meta-tags" data-name="' . $field->id . '">';
        $str .= '<select style="display: none;" name="meta_tags[]" class="meta_tags" multiple>';
        foreach ($this->pageTags as $key => $pageTag) {
            $str .= '<option value="' . $key . '" selected>' . $pageTag . '</option>';
        }
        $str .= '</select><ul class="picked-tags">';
        foreach ($this->pageTags as $key => $pageTag) {
            $str .= '<li class="tags-chosen"><span>';
            $str .= $pageTag . '</span><i class="zmdi zmdi-close" data-remove="' . $key . '"></i></li>';
        }
        $str .= '<li class="search-tag"><input type="text" placeholder="' . JText::_('TAGS') . '">';
        $str .= '</li></ul><ul class="all-tags">';
        foreach ($this->tags as $tag) {
            $str .= '<li data-id="' . $tag->id . '" style="display:none;"';
            if (isset($this->pageTags[$tag->id])) {
                $str .= ' class="selected-tag"';
            }
            $str .= '>' . $tag->title . '</li>';
        }
        $str .= '</ul></div>';

        return $str;
    }

    protected function renderImage($field, $options, $value)
    {
        if (!empty($value)) {
            $data = json_decode($value);
        } else {
            $data = new stdClass();
            $data->src = $data->alt = '';
        }
        $img = !empty($data->src) ? 'background-image:url(' . JUri::root() . $data->src . ')"' : '';
        if (is_numeric($data->src) && isset($this->desktopFiles->{$data->src})) {
            $desktopFile = $this->desktopFiles->{$data->src};
            $filename = $desktopFile->name;
            $path = 'components/com_gridbox/assets/uploads/app-' . $desktopFile->app_id . '/' . $desktopFile->filename;
            $img = 'background-image:url(' . JUri::root() . $path . ')"';
        } else if (is_numeric($data->src)) {
            $data->src = $filename = '';
        } else {
            $filename = basename($data->src);
        }
        $str = '<div class="select-image-field-wrapper"><div class="ba-image-field-label-wrapper"><label>' . JText::_('SELECT') . '</label><div>';
        $str .= '<input type="text" name="' . $field->id . '" data-name="src" placeholder="' . JText::_('SELECT');
        $str .= '" value="' . $filename . '" readonly onfocus="this.blur()" class="select-image-field"';
        if ($options->source == 'desktop') {
            $str .= ' data-source="desktop" data-size="' . $options->size . '"';
        }
        $str .= ' data-value="' . $data->src . '"><i class="zmdi zmdi-camera"></i><div class="image-field-tooltip" style="' . $img . '"></div>';
        $str .= '<div class="reset disabled-reset reset-image-field"><i class="zmdi zmdi-close"></i></div></div></div>';
        $str .= '<div class="link-picker-container"><label>' . JText::_('IMAGE_ALT') . '</label>';
        $str .= '<input type="text" name="' . $field->id . '"  data-name="alt" value="' . $data->alt . '"></div></div>';

        return $str;
    }

    protected function renderGallery($field, $options, $value)
    {
        if (!empty($value)) {
            $data = json_decode($value);
        } else {
            $data = array();
        }
        $str = '<div class="field-sorting-wrapper"';
        if ($options->source == 'desktop') {
            $str .= ' data-source="desktop" data-size="' . $options->size . '"';
        }
        $str .= '><div class="sorting-container">';
        foreach ($data as $key => $obj) {
            if (is_numeric($obj->img) && isset($this->desktopFiles->{$obj->img})) {
                $desktopFile = $this->desktopFiles->{$obj->img};
                $path = 'components/com_gridbox/assets/uploads/app-' . $desktopFile->app_id . '/' . $desktopFile->filename;
                $filename = $desktopFile->name;
            } else {
                $path = $obj->img;
                $filename = basename($obj->img);
            }
            $img =  strpos($path, 'balbooa.com') === false ? JUri::root() . $path : $path;
            $str .= '<div class="sorting-item" data-img="' . $obj->img . '" data-path="' . $path . '" data-alt="' . $obj->alt . '">';
            $str .= '<div class="sorting-handle"><i class="zmdi zmdi-apps"></i></div>';
            $str .= '<div class="sorting-image"><img src="' . $img . '"></div><div class="sorting-title">';
            $str .= $filename . '</div><div class="sorting-icons"><span><i class="zmdi zmdi-edit"></i></span>';
            $str .= '<span><i class="zmdi zmdi-copy"></i></span><span><i class="zmdi zmdi-delete"></i></span></div></div>';
        }
        $str .= '</div><div class="add-new-item"><span><input type="text" value="' . JText::_('ADD_NEW_ITEM');
        $str .= '" readonly="" onfocus="this.blur()"><i class="zmdi zmdi-camera"></i></span></div></div>';

        return $str;
    }

    protected function renderGoogleMaps($field, $options, $value)
    {
        if (!empty($value)) {
            $obj = json_decode($value);
        } else {
            $value = '{}';
            $obj = new stdClass();
        }
        $str = '<div class="field-sorting-wrapper"><input type="text" name="' . $field->id;
        $str .= '" data-autocomplete="" placeholder="' . JText::_('ENTER_LOCATION');
        $str .= '" value="' . (isset($obj->marker) ? $obj->marker->place : '') . '">';
        $str .= '<div style="display: none !important;">' . $value . '</div>';
        $str .= '<div class="field-google-map-wrapper" data-id="' . $field->id . '"></div></div>';

        return $str;
    }

    protected function renderVideo($field, $options, $value)
    {
        if (!empty($value)) {
            $obj = json_decode($value);
        } else {
            $obj = new stdClass();
            $obj->type = 'source';
            $obj->id = $obj->file = '';
        }
        if ($obj->type == 'source' && is_numeric($obj->file) && isset($this->desktopFiles->{$obj->file})) {
            $desktopFile = $this->desktopFiles->{$obj->file};
            $filename = $desktopFile->name;
        } else {
            $filename = basename($obj->file);
        }
        $str = '<div class="field-sorting-wrapper"><div class="ba-field-video-source-wrapper"><label>' . JText::_('VIDEO_SOURCE') . '</label>';
        $str .= '<select class="select-field-video-type" name="' . $field->id . '" data-name="type" value="' . $obj->type . '">';
        $str .= '<option value="" style="display: none;">' . JText::_('SELECT') . '</option>';
        $youtube = !isset($options->youtube) || $options->youtube;
        $vimeo = !isset($options->vimeo) || $options->vimeo;
        $file = !isset($options->file) || $options->file;
        if ($youtube) {
            $str .= '<option value="youtube"' . ($obj->type == 'youtube' ? ' selected' : '') . '>Youtube</option>';
        }
        if ($vimeo) {
            $str .= '<option value="vimeo"' . ($obj->type == 'vimeo' ? ' selected' : '') . '>Vimeo</option>';
        }
        if ($file) {
            $str .= '<option value="source"' . ($obj->type == 'source' ? ' selected' : '') . '>' . JText::_('SOURCE_FILE') . '</option>';
        }
        $str .= '</select>';
        $str .= '</div><div class="field-video-id" style="' . ($obj->type != 'source' && ($youtube || $vimeo) ? '' : 'display: none;') . '">';
        $str .= '<label>' . JText::_('VIDEO_ID') . '</label><input type="text" name="' . $field->id;
        $str .= '" data-name="id" value="' . $obj->id . '" placeholder="' . JText::_('VIDEO_ID') . '"></div>';
        $str .= '<div class="field-video-file" style="' . ($obj->type == 'source' && $file ? '' : 'display: none;') . '">';
        $str .= '<label>' . JText::_('SOURCE_FILE') . '</label><div><input type="text" class="select-input';
        $str .= ' disable-webkit-placeholder" readonly onfocus="this.blur()" name="' . $field->id;
        $str .= '" data-name="file" data-value="' . $obj->file . '" value="' . $filename . '"';
        if ($options->source == 'desktop') {
            $str .= ' data-source="desktop" data-size="' . $options->size . '"';
        }
        $str .= ' placeholder="' . JText::_('SELECT');
        $str .= '"><i class="zmdi zmdi-attachment-alt"></i></div></div></div>';

        return $str;
    }

    protected function renderTime($field, $options, $value)
    {
        if (!empty($value)) {
            $obj = json_decode($value);
        } else {
            $obj = new stdClass();
            $obj->format = '';
            $obj->hours = $obj->minutes = '';
        }
        $str = '<div class="field-sorting-wrapper"><div class="ba-select-secondary"><label>' . JText::_('HOURS') . '</label>';
        $str .= '<select name="' . $field->id . '" data-name="hours" value="' . $obj->hours . '">';
        for ($i = 0; $i < 24; $i++) {
            $j = $i < 10 ? '0' . $i : $i;
            $str .=  '<option value="' . $j . '"' . ($j ==  $obj->hours ? ' selected' : '') . '>' . $j . '</option>';
        }
        $str .= '</select></div><span>:</span><div class="ba-select-secondary"><label>' . JText::_('MINUTES') . '</label>';
        $str .= '<select name="' . $field->id . '" data-name="minutes" value="' . $obj->minutes . '">';
        for ($i = 0; $i < 60; $i++) {
            $j = $i < 10 ? '0' . $i : $i;
            $str .= '<option value="' . $j . '"' . ($j ==  $obj->minutes ? ' selected' : '') . '>' . $j . '</option>';
        }
        $str .= '</select></div><div class="ba-select-secondary"><label>' . JText::_('FORMAT') . '</label>';
        $str .= '<select name="' . $field->id . '" data-name="format" value="' . $obj->format . '">';
        $str .= '<option value=""' . ($obj->format == '' ? ' selected' : '') . '>-</option>';
        $str .= '<option value="AM"' . ($obj->format == 'AM' ? ' selected' : '') . '>AM</option>';
        $str .= '<option value="PM"' . ($obj->format == 'PM' ? ' selected' : '') . '>PM</option></select></div></div>';

        return $str;
    }
}
