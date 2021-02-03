<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

?>
<div class="ba-wishlist-products-list" data-quantity="<?php echo $wishlist->quantity; ?>">
<?php
if ($wishlist->empty) {
?>
    <div class="ba-empty-cart-products">
        <i class="zmdi zmdi-favorite"></i>
        <span class="ba-empty-cart-products-message"><?php echo JText::_('EMPTY_WISHLIST'); ?></span>
    </div>
<?php
} else {
    $existsProducts = 0;
    foreach ($wishlist->products as $product) {
        $image = !empty($product->images) ? $product->images[0] : $product->intro_image;
        if (!empty($image) && strpos($image, 'balbooa.com') === false) {
            $image = JUri::root().$image;
        }
        $price = $product->prices->sale_price !== '' ? $product->prices->sale : $product->prices->regular;
        $link = $product->link;
        if (isset($product->variationURL)) {
            $link .= '?'.$product->variationURL;
        }
?>
        <div class="ba-wishlist-product-row row-fluid" data-id="<?php echo $product->id; ?>"
            data-extra-count="<?php echo $product->extra_options->count; ?>">
<?php
        if (!empty($image)) {
?>
            <div class="ba-wishlist-product-image-cell">
                <img src="<?php echo $image; ?>">
                <a href="<?php echo $link; ?>"></a>
            </div>
<?php
        }
?>
            <div class="ba-wishlist-product-content-cell">
                <div class="ba-wishlist-product-content-inner-cell">
                    <div class="ba-wishlist-product-title-cell">
                        <span class="ba-wishlist-product-title">
                            <a href="<?php echo $link; ?>"><?php echo $product->title; ?></a>
                        </span>
                        <span class="ba-wishlist-product-info">
<?php
                        $info = array();
                        foreach ($product->variations as $variation) {
                            $info[] = '<span>'.$variation->title.' '.$variation->value.'</span>';
                        }
                        $infoStr = implode('/', $info);
                        echo $infoStr;
?>
                        </span>
                    </div>
                    <div class="ba-wishlist-product-price-cell">
<?php
                    if ($product->prices->sale_price !== '') {
?>
                        <span class="ba-wishlist-sale-price-wrapper <?php echo $position; ?>">
                            <span class="ba-wishlist-price-currency"><?php echo $symbol; ?></span>
                            <span class="ba-wishlist-price-value"><?php echo $product->prices->regular; ?></span>
                        </span>
<?php
                    }
?>
                        <span class="ba-wishlist-price-wrapper <?php echo $position; ?>">
                            <span class="ba-wishlist-price-currency"><?php echo $symbol; ?></span>
                            <span class="ba-wishlist-price-value"><?php echo $price; ?></span>
                        </span>
                    </div>
                    <div class="ba-wishlist-product-remove-cell">
                        <i class="zmdi zmdi-delete"></i>
                    </div>
                </div>
<?php
                foreach ($product->extra_options->items as $field_id => $item) {
?>
                <div class="ba-wishlist-product-extra-options">
                    <span class="ba-wishlist-product-extra-options-title"><?php echo $item->title; ?></span>
                    <div class="ba-wishlist-product-extra-options-content">
<?php
                    foreach ($item->values as $key => $value) {
                        if ($value->price != '') {
                            $extraPrice = gridboxHelper::preparePrice($value->price, $thousand, $separator, $decimals);
                            if ($position == '') {
                                $extraPrice = $symbol.' '.$extraPrice;
                            } else {
                                $extraPrice = $extraPrice.' '.$symbol;
                            }
                        } else {
                            $extraPrice = '';
                        }
?>
                        <div class="ba-wishlist-product-extra-option" data-key="<?php echo $key ?>"
                            data-field="<?php echo $field_id; ?>">
                            <span class="ba-wishlist-product-extra-option-value"><?php echo $value->value; ?></span>
                            <span class="ba-wishlist-product-extra-option-price"><?php echo $extraPrice; ?></span>
<?php
                        if (!$item->required) {
?>
                            <span class="ba-wishlist-product-remove-extra-option"><i class="zmdi zmdi-delete"></i></span>
<?php
                        }
?>
                        </div>
<?php
                    }
?>
                    </div>
                </div>
<?php
                }
?>
            </div>
            <div class="ba-wishlist-add-to-cart-cell">
<?php
            if ($product->data->stock !== '' && $product->data->stock == 0) {
?>
                <span class="ba-wishlist-empty-stock"><?php echo JText::_('OUT_OF_STOCK'); ?></span>
<?php
            } else {
                $existsProducts++;
?>
                <span class="ba-wishlist-add-to-cart-btn"><?php echo JText::_('ADD_TO_CART'); ?></span>
<?php
            }
?>
            </div>
        </div>
<?php
    }
}
?>
</div>