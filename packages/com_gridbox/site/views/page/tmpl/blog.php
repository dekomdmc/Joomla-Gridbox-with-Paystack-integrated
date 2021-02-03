<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

$str = '<div class="ba-edit-section row-fluid" id="ba-edit-section">'.stripcslashes($this->item->params).'</div>';
?>
<div class="row-fluid">
<?php
if (JFactory::getUser()->authorise('core.edit', 'com_gridbox')) {
?>
    <a class="edit-page-btn" target="_blank"
        href="<?php echo JUri::root().'index.php?option=com_gridbox&view=editor&tmpl=component&id='.$this->item->id; ?>">
        <i class="zmdi zmdi-settings"></i>
        <p class="edit-page"><?php echo JText::_('EDIT_PAGE'); ?></p>
    </a>
<?php
}
?>
    <div class="ba-gridbox-page row-fluid">
<?php
    if (!empty($this->item->app_type) && $this->item->app_type == 'products') {
        $schema = $this->get('ProductSchema');
?>
        <div itemtype="http://schema.org/Product" itemscope>
            <meta itemprop="name" content="<?php echo $schema->title; ?>" />
            <link itemprop="image" href="<?php echo $schema->image; ?>" />
            <meta itemprop="description" content="<?php echo $schema->meta_description; ?>" />
            <div itemprop="offers" itemtype="http://schema.org/Offer" itemscope>
                <link itemprop="url" href="<?php echo $schema->link; ?>" />
                <meta itemprop="availability" content="<?php echo $schema->availability; ?>" />
                <meta itemprop="priceCurrency" content="<?php echo gridboxHelper::$store->currency->code; ?>" />
                <meta itemprop="itemCondition" content="https://schema.org/UsedCondition" />
                <meta itemprop="price" content="<?php echo $schema->price; ?>" />
                <div itemprop="seller" itemtype="http://schema.org/Organization" itemscope>
                    <meta itemprop="name" content="<?php echo gridboxHelper::$store->general->store_name; ?>" />
                </div>
            </div>
            <div itemprop="aggregateRating" itemtype="http://schema.org/AggregateRating" itemscope>
                <meta itemprop="reviewCount" content="<?php echo $schema->count; ?>" />
                <meta itemprop="ratingValue" content="<?php echo $schema->rating; ?>" />
            </div>
            <meta itemprop="sku" content="<?php echo $schema->sku ?>" />
        </div>
<?php
    }
        echo str_replace('[blog_content]', $str, $this->pageLayout);
?>
    </div>
</div>