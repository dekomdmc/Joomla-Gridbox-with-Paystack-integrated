<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;
?>
<div class="ba-context-menu options-context-menu" data-source="gridbox-options" style="display: none">
    <span class="export-gridbox">
        <i class="zmdi zmdi-upload"></i>
        <span class="ba-context-menu-title">
            <?php echo JText::_('EXPORT'); ?>
        </span>
    </span>
    <span class="import-gridbox">
        <i class="zmdi zmdi-download "></i>
        <span class="ba-context-menu-title">
            <?php echo JText::_('IMPORT'); ?>
        </span>
    </span>
    <span class="import-joomla-content">
        <i class="zmdi zmdi-inbox"></i>
        <span class="ba-context-menu-title">
            <?php echo JText::_('IMPORT_JOOMLA_CONTENT'); ?>
        </span>
    </span>
    <span class="context-menu-item-link ba-group-element">
        <a href="<?php echo $this->preferences(); ?>" class="default-action">
            <i class="zmdi zmdi-accounts"></i>
            <span class="ba-context-menu-title">
                <?php echo JText::_('JCONFIG_PERMISSIONS_LABEL'); ?>
            </span>
        </a>
    </span>
</div>
<div class="ba-context-menu store-context-menu" data-source="gridbox-store" style="display: none">
    <span class="context-menu-item-link">
        <a href="index.php?option=com_gridbox&view=orders" class="default-action">
            <i class="zmdi zmdi-shopping-cart"></i>
            <span class="ba-context-menu-title">
                <?php echo JText::_('ORDERS'); ?>
            </span>
<?php
        if ($ordersCount > 0) {
?>
            <span class="unread-comments-count" data-type="orders"><?php echo $ordersCount; ?></span>
<?php
        }
?>            
        </a>
    </span>
    <span class="context-menu-item-link">
        <a href="index.php?option=com_gridbox&view=paymentmethods" class="default-action">
            <i class="zmdi zmdi-card"></i>
            <span class="ba-context-menu-title">
                <?php echo JText::_('PAYMENT_METHODS'); ?>
            </span>
        </a>
    </span>
    <span class="context-menu-item-link">
        <a href="index.php?option=com_gridbox&view=shipping" class="default-action">
            <i class="zmdi zmdi-truck"></i>
            <span class="ba-context-menu-title">
                <?php echo JText::_('SHIPPING'); ?>
            </span>
        </a>
    </span>
    <span class="context-menu-item-link">
        <a href="index.php?option=com_gridbox&view=promocodes" class="default-action">
            <i class="zmdi zmdi-card-giftcard"></i>
            <span class="ba-context-menu-title">
                <?php echo JText::_('PROMO_CODES'); ?>
            </span>
        </a>
    </span>
    <span class="context-menu-item-link">
        <a href="index.php?option=com_gridbox&view=productoptions" class="default-action">
            <i class="zmdi zmdi-invert-colors"></i>
            <span class="ba-context-menu-title">
                <?php echo JText::_('PRODUCT_OPTIONS'); ?>
            </span>
        </a>
    </span>
    <span class="context-menu-item-link ba-group-element">
        <a href="index.php?option=com_gridbox&view=storesettings" class="default-action">
            <i class="zmdi zmdi-settings"></i>
            <span class="ba-context-menu-title">
                <?php echo JText::_('SETTINGS'); ?>
            </span>
        </a>
    </span>
</div>
<div id="languages-dialog" class="ba-modal-sm modal hide" style="display:none">
    <div class="modal-body">
        <div class="languages-wrapper">

        </div>
    </div>
</div>
<div id="import-joomla-content-modal" class="ba-modal-md modal hide" style="display:none">
    <div class="modal-body">
        <div class="ba-modal-header">
            <h3><?php echo JText::_('IMPORT'); ?></h3>
            <i data-dismiss="modal" class="zmdi zmdi-close"></i>
        </div>
        <div class="availible-folders">
            <ul class="root-list">
                
            </ul>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="ba-btn" data-dismiss="modal">
            <?php echo JText::_('CANCEL') ?>
        </a>
        <a href="#" class="ba-btn-primary apply-import-joomla-content">
            <?php echo JText::_('IMPORT') ?>
        </a>
    </div>
</div>
<div id="import-dialog" class="ba-modal-sm modal hide" style="display:none">
    <div class="modal-header">
        <h3><?php echo JText::_('IMPORT'); ?></h3>
        <label class="ba-help-icon">
            <i class="zmdi zmdi-help"></i>
            <span class="ba-tooltip ba-help ba-hide-element">
                <?php echo JText::_('IMPORT_PAGES_THEMES_TOOLTIP'); ?> 
            </span>
        </label>
    </div>
    <div class="modal-body">
        <div class="ba-input-lg">
            <input id="theme-import-trigger" class="theme-import-trigger" readonly
                type="text" placeholder="<?php echo JText::_('SELECT'); ?>">
            <i class="zmdi zmdi-attachment-alt theme-import-trigger"></i>
            <input type="file" id="theme-import-file" name="ba-files[]" style="display: none;">
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="ba-btn" data-dismiss="modal">
            <?php echo JText::_('CANCEL') ?>
        </a>
        <a href="#" class="ba-btn-primary apply-import">
            <?php echo JText::_('INSTALL') ?>
        </a>
    </div>
</div>
<input type="hidden" id="installing-const" value="<?php echo JText::_('INSTALLING'); ?>">