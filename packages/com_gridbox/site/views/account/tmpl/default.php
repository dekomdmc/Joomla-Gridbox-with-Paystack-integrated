<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

$currency = gridboxHelper::$store->currency;
$thousand = $currency->thousand;
$separator = $currency->separator;
$decimals = $currency->decimals;
$position = $currency->position;
$symbol = $currency->symbol;
$wishlist = $this->wishlist;
$lang = JFactory::getLanguage();
$lang->load('com_users');
?>
<script type="text/javascript">
    let statuses = <?php echo json_encode($this->statuses); ?>,
        currency = <?php echo json_encode($currency); ?>,
        customer = <?php echo json_encode($this->customer); ?>;
</script>
<div class="ba-account-wrapper">
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#ba-my-account-orders" data-toggle="tab">
                <span>
                    <span class="tabs-title"><?php echo JText::_('MY_ORDERS'); ?></span>
                    <i class="zmdi zmdi-shopping-basket"></i>
                </span>
            </a>
        </li>
<?php
    if (!empty($this->digital->products)) {
?>
        <li>
            <a href="#ba-my-account-downloads" data-toggle="tab">
                <span>
                    <span class="tabs-title"><?php echo JText::_('MY_DOWNLOADS'); ?></span>
                    <i class="zmdi zmdi-folder"></i>
                </span>
            </a>
        </li>
<?php
    }
    if (gridboxHelper::$store->wishlist->login) {
?>
        <li>
            <a href="#ba-my-account-wishlist" data-toggle="tab">
                <span>
                    <span class="tabs-title"><?php echo JText::_('MY_WISHLIST'); ?></span>
                    <i class="zmdi zmdi-favorite"></i>
                </span>
            </a>
        </li>
<?php
    }
?>
        <li>
            <a href="#ba-my-account-billing-details" data-toggle="tab">
                <span>
                    <span class="tabs-title"><?php echo JText::_('MY_ADDRESS_BOOK'); ?></span>
                    <i class="zmdi zmdi-truck"></i>
                </span>
            </a>
        </li>
        <li>
            <a href="#ba-my-account-profile" data-toggle="tab">
                <span>
                    <span class="tabs-title"><?php echo JText::_('MY_PROFILE'); ?></span>
                    <i class="zmdi zmdi-account-circle"></i>
                </span>
            </a>
        </li>
        <li>
            <a href="<?php echo JUri::root().'index.php?option=com_gridbox&view=editor&task=store.logout'; ?>">
                <span>
                    <span class="tabs-title"><?php echo JText::_('LOG_OUT'); ?></span>
                    <i class="zmdi zmdi-power"></i>
                </span>
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="ba-my-account-orders">
            <div class="ba-account-title-wrapper">
                <span class="ba-account-title"><?php echo JText::_('MY_ORDERS'); ?></span>
            </div>
            <div class="ba-account-table">
                <div class="ba-account-tbody">
<?php
                if (count($this->orders) == 0) {
?>
                    <div class="ba-empty-cart-products">
                        <span class="ba-empty-cart-products-message"><?php echo JText::_('NO_ORDERS_HAVE_BEEN_FOUND'); ?></span>
                    </div>
<?php
                }
                foreach ($this->orders as $order) {
                    $date = JHtml::date($order->date, gridboxHelper::$dateFormat);
                    $price = gridboxHelper::preparePrice($order->total, $thousand, $separator, $decimals);
                    $status = isset($this->statuses->{$order->status}) ? $this->statuses->{$order->status} : $this->statuses->undefined;
?>
                    <div class="ba-account-tr" data-id="<?php echo $order->id; ?>">
                        <div class="ba-account-td">
                            <span><?php echo $date; ?></span>
                        </div>
                        <div class="ba-account-td">
                            <span><?php echo $order->order_number; ?></span>
                        </div>
                        <div class="ba-account-td">
                            <span style="--status-color: <?php echo $status->color; ?>"><?php echo $status->title; ?></span>
                        </div>
                        <div class="ba-account-td">
                            <span class="ba-account-price-wrapper <?php echo $order->currency_position; ?>">
                                <span class="ba-account-price-currency"><?php echo $order->currency_symbol; ?></span>
                                <span class="ba-account-price-value"><?php echo $price; ?></span>
                            </span>
                        </div>
                    </div>
<?php
                }
?>
                </div>
            </div>
        </div>
<?php
    if (!empty($this->digital->products)) {
?>
        <div class="tab-pane" id="ba-my-account-downloads">
            <div class="ba-account-title-wrapper">
                <span class="ba-account-title"><?php echo JText::_('MY_DOWNLOADS'); ?></span>
            </div>
            <div class="ba-account-table">
                <div class="ba-account-thead">
                    <div class="ba-account-tr">
                        <div class="ba-account-td">
                            <span><?php echo JText::_('PRODUCT'); ?></span>
                        </div>
<?php
                    if (!empty($this->digital->expires)) {
?>
                        <div class="ba-account-td">
                            <span><?php echo JText::_('EXPIRES'); ?></span>
                        </div>
<?php
                    }
                    if (!empty($this->digital->limit)) {
?>
                        <div class="ba-account-td">
                            <span><?php echo JText::_('REMAINING'); ?></span>
                        </div>
<?php
                    }
?>
                        <div class="ba-account-td"></div>
                    </div>
                </div>
                <div class="ba-account-tbody">
<?php
                foreach ($this->digital->products as $product) {
                    if (!empty($product->license->expires)) {
                        $jdate = JDate::getInstance($product->license->expires);
                        $expire = $jdate->format(gridboxHelper::$dateFormat);
                    } else  {
                        $expire = '-';
                    }
                    $remaining = !empty($product->license->limit) ? $product->license->downloads.' / '.$product->license->limit : '-';
                    $link = JUri::root().'index.php?option=com_gridbox&task=store.downloadDigitalFile&file=';
                    $link .= $product->product_token;
?>
                    <div class="ba-account-tr">
                        <div class="ba-account-td">
<?php
                        if (!empty($product->image)) {
                            $image = (strpos($product->image, 'balbooa.com') === false ? JUri::root() : '').$product->image;
?>
                            <span class="ba-account-product-image">
                                <img src="<?php echo $image; ?>">
                            </span>
<?php
                        }
?>
                            <span><?php echo $product->title; ?></span>
                        </div>
<?php
                    if (!empty($this->digital->expires)) {
?>
                        <div class="ba-account-td">
                            <span><?php echo $expire; ?></span>
                        </div>
<?php
                    }
                    if (!empty($this->digital->limit)) {
?>
                        <div class="ba-account-td">
                            <span><?php echo $remaining; ?></span>
                        </div>
<?php
                    }
?>
                        <div class="ba-account-td">
                            <a class="ba-account-btn" href="<?php echo $link; ?>"><?php echo JText::_('DOWNLOAD'); ?></a>
                        </div>
                    </div>
<?php
                }
?>
                </div>
            </div>
        </div>
<?php
    }
    if (gridboxHelper::$store->wishlist->login) {
?>
        <div class="tab-pane" id="ba-my-account-wishlist">
            <div class="ba-account-title-wrapper">
                <span class="ba-account-title"><?php echo JText::_('MY_WISHLIST'); ?></span>
            </div>
            <div class="ba-my-account-wishlist">
<?php
            include JPATH_ROOT.'/components/com_gridbox/views/layout/patterns/store/wishlist-products-list.php';
?>
            </div>
        </div>
<?php
    }
?>
        <div class="tab-pane" id="ba-my-account-billing-details">
            <div class="ba-account-title-wrapper">
                <span class="ba-account-title"><?php echo JText::_('MY_ADDRESS_BOOK'); ?></span>
                <span class="ba-account-btn save-user-customer-info"><?php echo JText::_('SAVE'); ?></span>
            </div>
            <div class="ba-my-account-billing-details">
<?php
                $out = gridboxHelper::getCustomerInfoHTML();
                echo $out;
?>
            </div>
        </div>
        <div class="tab-pane" id="ba-my-account-profile">
            <div class="ba-account-title-wrapper">
                <span class="ba-account-title"><?php echo JText::_('MY_PROFILE'); ?></span>
                <span class="ba-account-btn save-user-profile-data"><?php echo JText::_('SAVE'); ?></span>
            </div>
            <div class="ba-my-account-profile">
                <div class="ba-account-profile-fields" style="--ba-checkout-field-width:50%;">
                    <div class="ba-account-profile-title-wrapper">
                        <span class="ba-account-profile-title"><?php echo JText::_('NAME'); ?></span>
                        <span class="ba-account-profile-required-star">*</span>
                    </div>
                    <div class="ba-account-profile-field-wrapper">
                        <input type="text" name="name" value="<?php echo $this->user->name; ?>">
                    </div>
                </div>
                <div class="ba-account-profile-fields" style="--ba-checkout-field-width:50%;">
                    <div class="ba-account-profile-title-wrapper">
                        <span class="ba-account-profile-title"><?php echo JText::_('COM_USERS_PROFILE_USERNAME_LABEL'); ?></span>
                    </div>
                    <div class="ba-account-profile-field-wrapper">
                        <input type="text" name="username" value="<?php echo $this->user->username; ?>" readonly>
                    </div>
                </div>
                <div class="ba-account-profile-fields" style="--ba-checkout-field-width:50%;">
                    <div class="ba-account-profile-title-wrapper">
                        <span class="ba-account-profile-title">
                            <?php echo JText::_('COM_USERS_PROFILE_PASSWORD1_LABEL'); ?>
                            <?php echo JText::_('COM_USERS_OPTIONAL'); ?>
                        </span>
                    </div>
                    <div class="ba-account-profile-field-wrapper">
                        <input type="password" name="password1" autocomplete="new-password">
                    </div>
                </div>
                <div class="ba-account-profile-fields" style="--ba-checkout-field-width:50%;">
                    <div class="ba-account-profile-title-wrapper">
                        <span class="ba-account-profile-title">
                            <?php echo JText::_('COM_USERS_PROFILE_PASSWORD2_LABEL'); ?>
                            <?php echo JText::_('COM_USERS_OPTIONAL'); ?>
                        </span>
                    </div>
                    <div class="ba-account-profile-field-wrapper">
                        <input type="password" name="password2" autocomplete="new-password">
                    </div>
                </div>
                <div class="ba-account-profile-fields" style="--ba-checkout-field-width:50%;">
                    <div class="ba-account-profile-title-wrapper">
                        <span class="ba-account-profile-title"><?php echo JText::_('COM_USERS_PROFILE_EMAIL1_LABEL'); ?></span>
                        <span class="ba-account-profile-required-star">*</span>
                    </div>
                    <div class="ba-account-profile-field-wrapper">
                        <input type="text" name="email1" value="<?php echo $this->user->email; ?>">
                    </div>
                </div>
                <div class="ba-account-profile-fields" style="--ba-checkout-field-width:50%;">
                    <div class="ba-account-profile-title-wrapper">
                        <span class="ba-account-profile-title"><?php echo JText::_('COM_USERS_PROFILE_EMAIL2_LABEL'); ?></span>
                        <span class="ba-account-profile-required-star">*</span>
                    </div>
                    <div class="ba-account-profile-field-wrapper">
                        <input type="text" name="email2" value="<?php echo $this->user->email; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="ba-account-order-details-backdrop ba-hidden-order-details">
    <div class="ba-account-order-details-wrapper">
        <div class="ba-account-order-details">
            <div class="ba-account-order-header-wrapper">
                <div class="ba-account-order-header">
                    <span class="ba-account-order-number"></span>
                    <span class="ba-account-order-status"></span>
                </div>
                <div class="ba-account-order-header">
                    <span class="ba-account-order-date"></span>
                    <span class="ba-account-order-icons-wrapper">
                        <span class="ba-account-order-icon-wrapper">
                            <i class="zmdi zmdi-download ba-btn-transition" data-layout="pdf"></i>
                            <span class="ba-tooltip ba-bottom"><?php echo JText::_('DOWNLOAD') ?></span>
                        </span>
                        <span class="ba-account-order-icon-wrapper">
                            <i class="zmdi zmdi-print ba-btn-transition" data-layout="print"></i>
                            <span class="ba-tooltip ba-bottom"><?php echo JText::_('PRINT') ?></span>
                        </span>
                    </span>
                    <i class="zmdi zmdi-close ba-btn-transition ba-account-close-order-details"></i>
                </div>
            </div>
            <div class="ba-account-order-customer-info">
                <div class="ba-account-order-body">
                    
                </div>
            </div>
            <div class="ba-account-order-info">
                <div class="ba-account-order-body">

                </div>
            </div>
        </div>
    </div>
</div>
<template data-key="product-row">
    <div class="ba-account-order-product-row row-fluid" data-extra-count="8">
        <div class="ba-account-order-product-image-cell">
            <img src="">
        </div>
        <div class="ba-account-order-product-content-cell">
            <div class="ba-account-order-product-content-inner-cell">
                <div class="ba-account-order-product-title-cell">
                    <span class="ba-account-order-product-title"></span>
                    <span class="ba-account-order-product-info"></span>
                </div>
                <div class="ba-account-order-product-quantity-cell"></div>
                <div class="ba-account-order-product-price-cell">
                    <span class="ba-account-order-price-wrapper">
                        <span class="ba-account-order-price-currency"></span>
                        <span class="ba-cart-price-value"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
<template data-key="extra-options">
    <div class="ba-account-order-product-extra-options">
        <span class="ba-account-order-product-extra-options-title"></span>
        <div class="ba-account-order-product-extra-options-content">
            
        </div>
    </div>
</template>
<template data-key="extra-option">
    <div class="ba-account-order-product-extra-option">
        <span class="ba-account-order-product-extra-option-value"></span>
        <span class="ba-account-order-product-extra-option-price">
            <span class="ba-account-order-price-currency"></span>
            <span class="ba-cart-price-value"></span>
        </span>
    </div>
</template>
<template data-key="order-methods">
    <div class="ba-account-order-methods-wrapper">
        <div class="ba-account-order-shipping-method">
            <span class="ba-account-order-row-title"><?php echo JText::_('SHIPPING'); ?></span>
            <span class="ba-account-order-row-value"></span>
        </div>
        <div class="ba-account-order-payment-method">
            <span class="ba-account-order-row-title"><?php echo JText::_('PAYMENT'); ?></span>
            <span class="ba-account-order-row-value"></span>
        </div>
        <div class="ba-account-order-coupon-code">
            <span class="ba-account-order-row-title"><?php echo JText::_('COUPON_CODE'); ?></span>
            <span class="ba-account-order-row-value"></span>
        </div>
    </div>
</template>
<template data-key="subtotal">
    <div class="ba-account-order-subtotal-wrapper">
        <div class="ba-account-order-subtotal">
            <span class="ba-account-order-row-title"><?php echo JText::_('SUBTOTAL'); ?></span>
            <div class="ba-account-order-product-price-cell">
                <span class="ba-account-order-price-wrapper">
                    <span class="ba-account-order-price-currency"></span>
                    <span class="ba-cart-price-value"></span>
                </span>
            </div>
        </div>
        <div class="ba-account-order-discount">
            <span class="ba-account-order-row-title"><?php echo JText::_('DISCOUNT'); ?></span>
            <div class="ba-account-order-product-price-cell">
                <span class="ba-account-order-price-wrapper">
                    <span class="ba-cart-price-minus">-</span>
                    <span class="ba-account-order-price-currency"></span>
                    <span class="ba-cart-price-value"></span>
                </span>
            </div>
        </div>
        <div class="ba-account-order-shipping">
            <span class="ba-account-order-row-title"><?php echo JText::_('SHIPPING'); ?></span>
            <div class="ba-account-order-product-price-cell">
                <span class="ba-account-order-price-wrapper">
                    <span class="ba-account-order-price-currency"></span>
                    <span class="ba-cart-price-value"></span>
                </span>
            </div>
        </div>
        <div class="ba-account-order-shipping-tax" data-tax="excl">
            <span class="ba-account-order-row-title"><?php echo JText::_('TAX_ON_SHIPPING'); ?></span>
            <div class="ba-account-order-product-price-cell">
                <span class="ba-account-order-price-wrapper">
                    <span class="ba-account-order-price-currency"></span>
                    <span class="ba-cart-price-value"></span>
                </span>
            </div>
        </div>
        <div class="ba-account-order-shipping-tax" data-tax="incl">
            <span class="ba-account-order-row-title">
                <span class="ba-account-tax-title"></span>
                <span class="ba-account-order-price-wrapper">
                    <span class="ba-account-order-price-currency"></span>
                    <span class="ba-cart-price-value"></span>
                </span>
            </span>
        </div>
        <div class="ba-account-order-tax" data-tax="excl">
            <span class="ba-account-order-row-title"></span>
            <div class="ba-account-order-product-price-cell">
                <span class="ba-account-order-price-wrapper">
                    <span class="ba-account-order-price-currency"></span>
                    <span class="ba-cart-price-value"></span>
                </span>
            </div>
        </div>
    </div>
</template>
<template data-key="total">
    <div class="ba-account-order-total">
        <span class="ba-account-order-row-title"><?php echo JText::_('TOTAL'); ?></span>
        <div class="ba-account-order-product-price-cell">
            <span class="ba-account-order-price-wrapper">
                <span class="ba-account-order-price-currency"></span>
                <span class="ba-cart-price-value"></span>
            </span>
        </div>
    </div>
    <div class="ba-account-order-tax" data-tax="incl">
        <span class="ba-account-order-row-title">
            <span class="ba-account-tax-title"></span>
            <span class="ba-account-order-price-wrapper">
                <span class="ba-account-order-price-currency"></span>
                <span class="ba-cart-price-value"></span>
            </span>
        </span>
    </div>
</template>
<template data-key="info-title">
    <div class="ba-account-order-customer-info-title">
        Contact Information
    </div>
</template>
<template data-key="info-row">
    <div class="ba-account-order-customer-info-row">
        First Name: Vladimir
    </div>
</template>