<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

ob_start();
?>
<div class="ba-add-to-cart-variation" data-type="<?php echo $variation->type; ?>">
    <div class="ba-add-to-cart-row-label"><?php echo $variation->title; ?></div>
    <div class="ba-add-to-cart-row-value" data-type="<?php echo $variation->type; ?>">
<?php
        if ($variation->type == 'dropdown') {
            $li = '';
            $textValue = JText::_('SELECT');
            $value = '';
        }
        foreach ($variation->items as $item) {
            $flag = in_array($item->option_key, $active);
            if ($variation->type == 'dropdown') {
                if ($flag) {
                    $textValue = $item->value;
                    $value = $item->option_key;
                }
                $li .= '<li data-value="'.$item->option_key.'" class="'.($flag ? 'selected' : '').'">'.$item->value.'</li>';
            } else if ($variation->type == 'tag') {
?>
                <span data-value="<?php echo $item->option_key; ?>" class="<?php echo $flag ? 'active' : ''; ?>">
                    <?php echo $item->value; ?>
                </span>
<?php
            } else if ($variation->type == 'color') {
?>
                <span data-value="<?php echo $item->option_key; ?>" class="<?php echo $flag ? 'active' : ''; ?>">
                    <span style="--variation-color-value: <?php echo $item->color; ?>;"></span>
                    <span class="ba-tooltip ba-top"><?php echo $item->value; ?></span>
                </span>
<?php
            }  else if ($variation->type == 'image') {
                $images = json_decode($item->images);
                if (!empty($images)) {
                    $item->image = $images[0];
                }
                $image = strpos($item->image, 'balbooa.com') === false ? JUri::root().$item->image : $item->image;
?>
                <span data-value="<?php echo $item->option_key; ?>" class="<?php echo $flag ? 'active' : ''; ?>">
                    <span style="--variation-image-value: url(<?php echo $image; ?>);"></span>
                    <span class="ba-tooltip ba-top"><?php echo $item->value; ?></span>
                </span>
<?php
            } else if ($variation->type == 'radio') {
?>
                <div class="ba-checkbox-wrapper">
                    <span><?php echo $item->value; ?></span>
                    <label class="ba-radio">
                        <input type="radio" name="variation-<?php echo $item->field_id; ?>"
                            class="<?php echo $flag ? 'active' : ''; ?>"
                            value="<?php echo $item->option_key; ?>"<?php echo $flag ? ' checked' : ''; ?>>
                        <span></span>
                    </label>
                </div>
<?php
            }
        }
        if ($variation->type == 'dropdown') {
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
$out = ob_get_contents();
ob_end_clean();