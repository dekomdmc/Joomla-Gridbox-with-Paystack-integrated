<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;
$currency = self::$store->currency;
ob_start();
?>
<div class="ba-add-to-cart-extra-options">
<?php
foreach ($options as $id => $option) {
    $required = $option->required * 1 == 1;
?>
<div class="ba-add-to-cart-extra-option" data-ind="<?php echo $id; ?>" data-required="<?php echo $option->required; ?>"
    data-type="<?php echo $option->type; ?>">
    <div class="ba-add-to-cart-row-label"><?php echo $option->title; ?></div>
    <div class="ba-add-to-cart-row-value" data-type="<?php echo $option->type; ?>">
<?php
        if ($option->type == 'dropdown') {
            $li = '';
            $textValue = JText::_('SELECT');
            $value = '';
        }
        foreach ($option->items as $item) {
            if ($item->price != '') {
                $price = self::preparePrice($item->price, $currency->thousand, $currency->separator, $currency->decimals);
                if ($currency->position == '') {
                    $price = $currency->symbol.' '.$price;
                } else {
                    $price = $price.' '.$currency->symbol;
                }
            } else {
                $price = '';
            }
            if ($option->type == 'dropdown' || $option->type == 'radio' || $option->type == 'checkbox') {
                $price = '<span class="extra-option-price">'.$price.'</span>';
            }
            $text = $item->title.' '.$price;
            if ($required && $item->default) {
                $required = false;
            }
            if ($item->default && !empty($item->price)) {
                $extra->price += $item->price * 1;
            }
            if ($option->type == 'dropdown') {
                if ($item->default) {
                    $textValue = strip_tags($text);
                    $value = $item->key;
                }
                $li .= '<li data-value="'.$item->key.'" class="'.($item->default ? 'selected' : '').'">'.$text.'</li>';
            } else if ($option->type == 'tag') {
?>
                <span data-value="<?php echo $item->key; ?>" class="<?php echo $item->default ? 'active' : ''; ?>">
                    <?php echo $text; ?>
                </span>
<?php
            } else if ($option->type == 'color') {
?>
                <span data-value="<?php echo $item->key; ?>" class="<?php echo $item->default ? 'active' : ''; ?>">
                    <span style="--variation-color-value: <?php echo $item->color; ?>;"></span>
                    <span class="ba-tooltip ba-top"><?php echo $text; ?></span>
                </span>
<?php
            }  else if ($option->type == 'image') {
                $image = strpos($item->image, 'balbooa.com') === false ? JUri::root().$item->image : $item->image;
?>
                <span data-value="<?php echo $item->key; ?>" class="<?php echo $item->default ? 'active' : ''; ?>">
                    <span style="--variation-image-value: url(<?php echo $image; ?>);"></span>
                    <span class="ba-tooltip ba-top"><?php echo $text; ?></span>
                </span>
<?php
            } else if ($option->type == 'radio' || $option->type == 'checkbox') {
?>
                <div class="ba-checkbox-wrapper">
                    <span><?php echo $text; ?></span>
                    <label class="ba-<?php echo $option->type; ?>">
                        <input type="<?php echo $option->type; ?>" name="<?php echo $id; ?>"
                            class="<?php echo $item->default ? 'active' : ''; ?>"
                            value="<?php echo $item->key; ?>"<?php echo $item->default ? ' checked' : ''; ?>>
                        <span></span>
                    </label>
                </div>
<?php
            }
        }
        if ($option->type == 'dropdown') {
?>
            <div class="ba-custom-select">
            <input readonly="" onfocus="this.blur()" type="text" value="<?php echo $textValue; ?>">
            <input type="hidden" value="<?php echo $value; ?>">
            <i class="zmdi zmdi-caret-down"></i>
            <ul><?php echo $li; ?></ul>
        </div>
<?php
        }
?>
    </div>
</div>
<?php
    if ($required) {
        $extra->required = $required;
    }
}
?>
</div>
<?php
$out = ob_get_contents();
ob_end_clean();