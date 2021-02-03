<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;
$badges = self::getProductBadges($page->id, $data);
ob_start();
?>
<div class="ba-blog-post-badge-wrapper">
<?php
if (!empty($variations_map) || (empty($variations_map) && ($data->stock === '' || $data->stock != 0))) {
	foreach ($badges as $badge) {
?>
	<span class="ba-blog-post-badge" style="--badge-color:<?php echo $badge->color; ?>;"><?php echo $badge->title; ?></span>
<?php
	}
} else {
?>
	<span class="ba-blog-post-badge out-of-stock-badge"><?php echo JText::_('OUT_OF_STOCK'); ?></span>
<?php
}
?>
</div>
<div class="ba-blog-post-wishlist-wrapper">
    <i class="zmdi zmdi-favorite"></i>
    <span class="ba-tooltip ba-left"><?php echo JText::_('ADD_TO_WISHLIST'); ?></span>
</div>
<?php
$badges = ob_get_contents();
ob_end_clean();