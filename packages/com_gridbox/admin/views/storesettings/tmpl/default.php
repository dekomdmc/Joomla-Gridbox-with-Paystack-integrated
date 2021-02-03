<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

$user = JFactory::getUser();
$store = gridboxHelper::$store;
$map = gridboxHelper::getCategories($store->sales->map);
$countries = gridboxHelper::getTaxCountries();
$countriesList = new stdClass();
foreach ($countries as $country) {
    $countriesList->{$country->id} = $country;
    $country->regions = new stdClass();
    foreach ($country->states as $state) {
        $country->regions->{$state->id} = $state;
    }
}
?>
<script type="text/javascript" src="<?php echo JUri::root(true); ?>/media/system/js/calendar.js"></script>
<script type="text/javascript" src="<?php echo JUri::root(true); ?>/media/system/js/calendar-setup.js"></script>
<script type="text/javascript"><?php echo gridboxHelper::setCalendar(); ?></script>
<link rel="stylesheet" href="<?php echo JUri::root(true); ?>/media/system/css/calendar-jos.css">
<link rel="stylesheet" type="text/css" href="<?php echo JUri::root().'media/jui/css/jquery.minicolors.css'; ?>">
<script src="<?php echo JUri::root(); ?>/administrator/components/com_gridbox/assets/js/sortable.js" type="text/javascript"></script>
<script src="components/com_gridbox/assets/js/ba-admin.js?<?php echo $this->about->version; ?>" type="text/javascript"></script>
<script src="<?php echo JUri::root(); ?>components/com_gridbox/libraries/minicolors/jquery.minicolors.js" type="text/javascript"></script>
<?php
include(JPATH_COMPONENT.'/views/layouts/ckeditor.php');
include(JPATH_COMPONENT.'/views/layouts/notification.php');
?>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_gridbox/libraries/ckeditor/js/dataTags.js"></script>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_gridbox/libraries/ckeditor/js/resizeEditor.js"></script>
<input type="hidden" value="<?php echo JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'); ?>" class="jlib-selection">
<input type="hidden" value="<?php echo JText::_('SUCCESS_UPLOAD'); ?>" id="upload-const">
<form autocomplete="off" action="<?php echo JRoute::_('index.php?option=com_gridbox&view=promocodes'); ?>"
    method="post" name="adminForm" id="adminForm">
    <div class="row-fluid">
        <div id="gridbox-container">
            <div id="gridbox-content">
                <?php include(JPATH_COMPONENT.'/views/layouts/sidebar.php'); ?>
                <div class="ba-main-view">
                    <div id="filter-bar">
                        <div class="app-title-wrapper">
                            <h1><?php echo JText::_('STORE_SETTINGS'); ?></h1>
                        </div>
                        <div class="filter-search-wrapper">
                            
                        </div>
                    </div>
                    <div class="main-table store-settings-table">
                        <div class="store-settings-header">
                            <div class="store-settings-header-left-panel"></div>
                            <div class="store-settings-header-right-panel">
<?php
                            if ($user->authorise('core.edit', 'com_gridbox')) {
?>
                                <span class="apply-store-settings" data-id="<?php echo $this->store->id; ?>">
                                    <i class="zmdi zmdi-check"></i>
                                    <span class="ba-tooltip ba-bottom ba-hide-element"><?php echo JText::_('JAPPLY'); ?></span>
                                </span>
<?php
                            }
?>
                            </div>
                        </div>
                        <div class="store-settings-body">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#store-general-options" data-toggle="tab">
                                        <i class="zmdi zmdi-store"></i>
                                        <?php echo JText::_('GENERAL'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#store-email-options" data-toggle="tab">
                                        <i class="zmdi zmdi-notifications-active"></i>
                                        <?php echo JText::_('EMAIL_NOTIFICATIONS'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#store-currency-options" data-toggle="tab">
                                        <i class="zmdi zmdi-money"></i>
                                        <?php echo JText::_('CURRENCY_UNITS'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#store-tax-options" data-toggle="tab">
                                        <i class="zmdi zmdi-balance-wallet"></i>
                                        <?php echo JText::_('TAX'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#store-sales-options" data-toggle="tab">
                                        <i class="zmdi zmdi-label-heart"></i>
                                        <?php echo JText::_('SALES'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#store-order-statuses-options" data-toggle="tab">
                                        <i class="zmdi zmdi-assignment-check"></i>
                                        <?php echo JText::_('ORDER_STATUSES'); ?>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div id="store-general-options" class="tab-pane active">
                                    <div class="ba-options-group-wrapper">
                                        <div class="ba-options-group-header-wrapper">
                                            <span class="ba-options-group-header"><?php echo JText::_('GENERAL_INFO'); ?></span>
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('STORE_NAME'); ?></label>
                                            <input type="text" data-key="store_name" data-group="general"
                                                value="<?php echo htmlspecialchars($store->general->store_name, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('LEGAL_BUSINESS_NAME'); ?></label>
                                            <input type="text" data-key="business_name" data-group="general"
                                                value="<?php echo htmlspecialchars($store->general->business_name, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-header-wrapper">
                                            <span class="ba-options-group-header"><?php echo JText::_('CONTACT_INFO'); ?></span>
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('PHONE'); ?></label>
                                            <input type="text" data-key="phone" data-group="general"
                                                value="<?php echo htmlspecialchars($store->general->phone, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('EMAIL'); ?></label>
                                            <input type="text" data-key="email" data-group="general"
                                                value="<?php echo htmlspecialchars($store->general->email, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('COUNTRY'); ?></label>
                                            <input type="text" data-key="country" data-group="general"
                                                value="<?php echo htmlspecialchars($store->general->country, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('STATE_PROVINCE'); ?></label>
                                            <input type="text" data-key="region" data-group="general"
                                                value="<?php echo htmlspecialchars($store->general->region, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-element full-width-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('CITY'); ?></label>
                                            <input type="text" data-key="city" data-group="general"
                                                value="<?php echo htmlspecialchars($store->general->city, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('STREET_ADDRESS'); ?></label>
                                            <input type="text" data-key="street" data-group="general"
                                                value="<?php echo htmlspecialchars($store->general->street, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('ZIP_CODE'); ?></label>
                                            <input type="text" data-key="zip_code" data-group="general"
                                                value="<?php echo htmlspecialchars($store->general->zip_code, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-header-wrapper toggle-buttons-header">
                                            <span class="ba-options-group-header"><?php echo JText::_('CHECKOUT'); ?></span>
                                        </div>
                                        <div class="ba-options-group-element toggle-button-wrapper full-width-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('GUEST_CHECKOUT'); ?></label>
                                            <label class="ba-checkbox">
                                                <input type="checkbox" data-key="guest" data-group="checkout"
                                                    <?php echo $store->checkout->guest ? ' checked' : ''; ?>>
                                                <span></span>
                                            </label>
                                        </div>
                                        <div class="ba-options-group-element toggle-button-wrapper full-width-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('CUSTOMER_LOGIN'); ?></label>
                                            <label class="ba-checkbox">
                                                <input type="checkbox" data-key="login" data-group="checkout"
                                                    <?php echo $store->checkout->login ? ' checked' : ''; ?>>
                                                <span></span>
                                            </label>
                                        </div>
<?php
                                        $flag = $store->checkout->login;
?>
                                        <div class="ba-options-group-element toggle-button-wrapper full-width-group-element"
                                            <?php echo !$flag ? 'display: none;' : '' ?>>
                                            <label class="ba-options-group-label"><?php echo JText::_('USER_REGISTRATION_FORM'); ?></label>
                                            <label class="ba-checkbox">
                                                <input type="checkbox" data-key="registration" data-group="checkout"
                                                    <?php echo $store->checkout->registration ? ' checked' : ''; ?>>
                                                <span></span>
                                            </label>
                                        </div>
<?php
                                        $flag = $flag && $store->checkout->registration;
?>
                                        <div class="ba-options-group-element toggle-button-wrapper full-width-group-element"
                                            <?php echo !$flag ? 'display: none;' : '' ?>>
                                            <label class="ba-options-group-label">
                                                <?php echo JText::_('TERMS_AND_CONDITIONS_CHECKBOX'); ?>
                                            </label>
                                            <label class="ba-checkbox">
                                                <input type="checkbox" data-key="terms" data-group="checkout"
                                                    <?php echo $store->checkout->terms ? ' checked' : ''; ?>>
                                                <span></span>
                                            </label>
                                        </div>
<?php
                                        $flag = $flag && $store->checkout->terms;
?>
                                        <div class="ba-options-group-element full-width-group-element terms-text-element"
                                            <?php echo !$flag ? 'display: none;' : '' ?>>
                                            <label class="ba-options-group-label">
                                                <?php echo JText::_('TERMS_AND_CONDITIONS_TEXT'); ?>
                                            </label>
                                            <input type="text" data-key="terms_text" data-group="checkout"
                                                value="<?php echo htmlspecialchars($store->checkout->terms_text, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-header-wrapper toggle-buttons-header">
                                            <span class="ba-options-group-header"><?php echo JText::_('WISHLIST'); ?></span>
                                        </div>
                                        <div class="ba-options-group-element toggle-button-wrapper full-width-group-element">
                                            <label class="ba-options-group-label">
                                                <?php echo JText::_('WISHLIST_ONLY_AUTHENTICATED'); ?>
                                            </label>
                                            <label class="ba-checkbox">
                                                <input type="checkbox" data-key="login" data-group="wishlist"
                                                    <?php echo $store->wishlist->login ? ' checked' : ''; ?>>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div id="store-email-options" class="tab-pane">
                                    <div class="ba-options-group-wrapper">
                                        <div class="ba-options-group-element full-width-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('TYPE'); ?></label>
                                            <select class="store-email-options-filter">
                                                <option value="notification"><?php echo JText::_('ADMIN_NOTIFICATION_NEW_ORDER'); ?></option>
                                                <option value="stock"><?php echo JText::_('ADMIN_NOTIFICATION_OUT_OF_STOCK'); ?></option>
                                                <option value="confirmation">
                                                    <?php echo JText::_('CUSTOMER_NOTIFICATION_ORDER_CONFIRMATION'); ?>
                                                </option>
                                                <option value="completed">
                                                    <?php echo JText::_('CUSTOMER_NOTIFICATION_ORDER_COMPLETED'); ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="notification-email-options">
                                            <div class="ba-options-group-header-wrapper">
                                                <span class="ba-options-group-header">
                                                    <?php echo JText::_('ADMIN_NOTIFICATION_NEW_ORDER'); ?>
                                                </span>
                                            </div>
                                            <div class="ba-options-group-element">
                                                <label class="ba-options-group-label"><?php echo JText::_('ADMIN_EMAIL'); ?></label>
                                                <input type="text" class="ba-add-email-action"
                                                    placeholder="<?php echo JText::_('ADD_EMAIL_AND_PRESS_ENTER'); ?>">
                                                <div class="entered-emails-wrapper selected-items-wrapper" data-group="notification">
<?php
                                                foreach ($store->notification->admins as $value) {
?>
                                                    <span class="entered-emails selected-items" data-email="<?php echo $value; ?>">
                                                        <span class="selected-items-name"><?php echo $value; ?></span>
                                                        <i class="zmdi zmdi-close remove-selected-items"></i>
                                                    </span>
<?php
                                                }
?>
                                                </div>
                                            </div>
                                            <div class="ba-options-group-element ba-options-input-action-element">
                                                <label class="ba-options-group-label"><?php echo JText::_('SUBJECT'); ?></label>
                                                <div class="ba-options-input-action-wrapper">
                                                    <input type="text" data-key="subject" data-group="notification"
                                                        value="<?php echo htmlspecialchars($store->notification->subject, ENT_QUOTES); ?>">
                                                    <div class="select-data-tags input-action-icon">
                                                        <i class="zmdi zmdi-playlist-plus"></i>
                                                        <span class="ba-tooltip ba-top ba-hide-element"
                                                            ><?php echo JText::_('DATA_TAGS'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ba-options-group-element full-width-group-element ckeditor-options-wrapper">
                                                <textarea data-key="body" data-group="notification"
                                                    ><?php echo $store->notification->body; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="stock-email-options" style="display: none;">
                                            <div class="ba-options-group-header-wrapper">
                                                <span class="ba-options-group-header">
                                                    <?php echo JText::_('ADMIN_NOTIFICATION_OUT_OF_STOCK'); ?>
                                                </span>
                                            </div>
                                            <div class="ba-options-group-element">
                                                <label class="ba-options-group-label"><?php echo JText::_('ADMIN_EMAIL'); ?></label>
                                                <input type="text" class="ba-add-email-action"
                                                    placeholder="<?php echo JText::_('ADD_EMAIL_AND_PRESS_ENTER'); ?>">
                                                <div class="entered-emails-wrapper selected-items-wrapper" data-group="stock">
<?php
                                                foreach ($store->stock->admins as $value) {
?>
                                                    <span class="entered-emails selected-items" data-email="<?php echo $value; ?>">
                                                        <span class="selected-items-name"><?php echo $value; ?></span>
                                                        <i class="zmdi zmdi-close remove-selected-items"></i>
                                                    </span>
<?php
                                                }
?>
                                                </div>
                                            </div>
                                            <div class="ba-options-group-element ba-options-input-action-element">
                                                <label class="ba-options-group-label"><?php echo JText::_('SUBJECT'); ?></label>
                                                <div class="ba-options-input-action-wrapper">
                                                    <input type="text" data-key="subject" data-group="stock"
                                                        value="<?php echo htmlspecialchars($store->stock->subject, ENT_QUOTES); ?>">
                                                    <div class="select-data-tags input-action-icon">
                                                        <i class="zmdi zmdi-playlist-plus"></i>
                                                        <span class="ba-tooltip ba-top ba-hide-element"
                                                            ><?php echo JText::_('DATA_TAGS'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ba-options-group-element full-width-group-element ckeditor-options-wrapper">
                                                <textarea data-key="body" data-group="stock"
                                                    ><?php echo $store->stock->body; ?></textarea>
                                            </div>
                                            <div class="ba-options-group-element full-width-group-element">
                                                <label class="ba-options-group-label"><?php echo JText::_('MINIMUM_STOCK_QUANTITY'); ?></label>
                                                <input type="text" class="integer-validation" data-key="quantity" data-group="stock"
                                                    data-decimals="0" value="<?php echo $store->stock->quantity; ?>">
                                            </div>
                                        </div>
                                        <div class="confirmation-email-options" style="display: none;">
                                            <div class="ba-options-group-header-wrapper">
                                                <span class="ba-options-group-header">
                                                    <?php echo JText::_('CUSTOMER_NOTIFICATION_ORDER_CONFIRMATION'); ?>
                                                </span>
                                            </div>
                                            <div class="ba-options-group-element">
                                                <label class="ba-options-group-label"><?php echo JText::_('NAME'); ?></label>
                                                <input type="text" data-key="name" data-group="confirmation"
                                                    value="<?php echo htmlspecialchars($store->confirmation->name, ENT_QUOTES); ?>">
                                            </div>
                                            <div class="ba-options-group-element">
                                                <label class="ba-options-group-label"><?php echo JText::_('EMAIL'); ?></label>
                                                <input type="text" data-key="email" data-group="confirmation"
                                                    value="<?php echo htmlspecialchars($store->confirmation->email, ENT_QUOTES); ?>">
                                            </div>
                                            <div class="ba-options-group-element full-width-group-element ba-options-input-action-element">
                                                <label class="ba-options-group-label"><?php echo JText::_('SUBJECT'); ?></label>
                                                <div class="ba-options-input-action-wrapper">
                                                    <input type="text" data-key="subject" data-group="confirmation"
                                                        value="<?php echo htmlspecialchars($store->confirmation->subject, ENT_QUOTES); ?>">
                                                    <div class="select-data-tags input-action-icon">
                                                        <i class="zmdi zmdi-playlist-plus"></i>
                                                        <span class="ba-tooltip ba-top ba-hide-element"
                                                            ><?php echo JText::_('DATA_TAGS'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ba-options-group-element full-width-group-element ckeditor-options-wrapper">
                                                <textarea data-key="body" data-group="confirmation"
                                                    ><?php echo $store->confirmation->body; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="completed-email-options" style="display: none;">
                                            <div class="ba-options-group-header-wrapper">
                                                <span class="ba-options-group-header">
                                                    <?php echo JText::_('CUSTOMER_NOTIFICATION_ORDER_COMPLETED'); ?>
                                                </span>
                                            </div>
                                            <div class="ba-options-group-element">
                                                <label class="ba-options-group-label"><?php echo JText::_('NAME'); ?></label>
                                                <input type="text" data-key="name" data-group="completed"
                                                    value="<?php echo htmlspecialchars($store->completed->name, ENT_QUOTES); ?>">
                                            </div>
                                            <div class="ba-options-group-element">
                                                <label class="ba-options-group-label"><?php echo JText::_('EMAIL'); ?></label>
                                                <input type="text" data-key="email" data-group="completed"
                                                    value="<?php echo htmlspecialchars($store->completed->email, ENT_QUOTES); ?>">
                                            </div>
                                            <div class="ba-options-group-element full-width-group-element ba-options-input-action-element">
                                                <label class="ba-options-group-label"><?php echo JText::_('SUBJECT'); ?></label>
                                                <div class="ba-options-input-action-wrapper">
                                                    <input type="text" data-key="subject" data-group="completed"
                                                        value="<?php echo htmlspecialchars($store->completed->subject, ENT_QUOTES); ?>">
                                                    <div class="select-data-tags input-action-icon">
                                                        <i class="zmdi zmdi-playlist-plus"></i>
                                                        <span class="ba-tooltip ba-top ba-hide-element"
                                                            ><?php echo JText::_('DATA_TAGS'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ba-options-group-element full-width-group-element ckeditor-options-wrapper">
                                                <textarea data-key="body" data-group="completed"
                                                    ><?php echo $store->completed->body; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="store-currency-options" class="tab-pane">
                                    <div class="ba-options-group-wrapper">
                                        <div class="ba-options-group-header-wrapper">
                                            <span class="ba-options-group-header"><?php echo JText::_('CURRENCY'); ?></span>
                                        </div>
                                        <div class="ba-options-group-element full-width-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('CODE'); ?></label>
                                            <input type="text" data-key="code" data-group="currency"
                                                value="<?php echo htmlspecialchars($store->currency->code, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('SYMBOL'); ?></label>
                                            <input type="text" data-key="symbol" data-group="currency"
                                                value="<?php echo htmlspecialchars($store->currency->symbol, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('POSITION'); ?></label>
                                            <select data-key="position" data-group="currency">
<?php
                                                $selected = $store->currency->position == '' ? ' selected' : '';
?>
                                                <option value=""<?php echo $selected; ?>><?php echo JText::_('LEFT'); ?></option>
<?php
                                                $selected = $store->currency->position != '' ? ' selected' : '';
?>
                                                <option value="right-currency-position"
                                                    <?php echo $selected; ?>><?php echo JText::_('RIGHT'); ?></option>
                                            </select>
                                        </div>
                                        <div class="ba-options-group-header-wrapper">
                                            <span class="ba-options-group-header"><?php echo JText::_('CURRENCY_SEPARATOR'); ?></span>
                                        </div>
                                        <div class="ba-options-group-element full-width-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('THOUSAND_SEPARATOR'); ?></label>
                                            <select data-key="thousand" data-group="currency">
<?php
                                                $selected = $store->currency->thousand == ',' ? ' selected' : '';
?>
                                                <option value=","<?php echo $selected; ?>><?php echo JText::_('COMMA'); ?></option>
<?php
                                                $selected = $store->currency->thousand == '.' ? ' selected' : '';
?>
                                                <option value="."<?php echo $selected; ?>><?php echo JText::_('DOT'); ?></option>
<?php
                                                $selected = $store->currency->thousand == ' ' ? ' selected' : '';
?>
                                                <option value=" "<?php echo $selected; ?>><?php echo JText::_('SPACE'); ?></option>
<?php
                                                $selected = $store->currency->thousand == '' ? ' selected' : '';
?>
                                                <option value=""<?php echo $selected; ?>><?php echo JText::_('NONE_SELECTED'); ?></option>
                                            </select>
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('DECIMAL_SEPARATOR'); ?></label>
                                            <select data-key="separator" data-group="currency">
<?php
                                                $selected = $store->currency->separator == ',' ? ' selected' : '';
?>
                                                <option value=","<?php echo $selected; ?>><?php echo JText::_('COMMA'); ?></option>
<?php
                                                $selected = $store->currency->separator == '.' ? ' selected' : '';
?>
                                                <option value="."<?php echo $selected; ?>><?php echo JText::_('DOT'); ?></option>
                                            </select>
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('NUMBER_OF_DECIMALS'); ?></label>
                                            <input type="number" data-key="decimals" data-group="currency"
                                                value="<?php echo htmlspecialchars($store->currency->decimals, ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ba-options-group-header-wrapper" style="display: none;">
                                            <span class="ba-options-group-header"><?php echo JText::_('UNITS'); ?></span>
                                        </div>
                                        <div class="ba-options-group-element" style="display: none;">
                                            <label class="ba-options-group-label"><?php echo JText::_('DEFAULT_WEIGHT_UNIT'); ?></label>
                                            <select data-key="weight" data-group="units">
<?php
                                                $selected = $store->units->weight == 'kg' ? ' selected' : '';
?>
                                                <option value="kg"<?php echo $selected; ?>><?php echo JText::_('KILOGRAMS'); ?></option>
<?php
                                                $selected = $store->units->weight == 'g' ? ' selected' : '';
?>
                                                <option value="g"<?php echo $selected; ?>><?php echo JText::_('GRAMS'); ?></option>
<?php
                                                $selected = $store->units->weight == 'lb' ? ' selected' : '';
?>
                                                <option value="lb"<?php echo $selected; ?>><?php echo JText::_('POUNDS'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div id="store-tax-options" class="tab-pane">
                                    <div class="ba-options-group-wrapper">
                                        <div class="ba-options-group-header-wrapper">
                                            <span class="ba-options-group-header"><?php echo JText::_('GENERAL'); ?></span>
                                        </div>
                                        <div class="ba-options-group-element full-width-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('TAX_MODE'); ?></label>
<?php
                                            $array = array('excl' => JText::_('TAX_EXCLUSIVE'), 'incl' => JText::_('TAX_INCLUSIVE'));
?>
                                            <select class="store-tax-mode-select" data-key="mode" data-group="tax">
<?php
                                            foreach ($array as $key => $value) {
                                                $attr = $store->tax->mode == $key ? ' selected' : '';
?>
                                                <option value="<?php echo $key; ?>"<?php echo $attr; ?>><?php echo $value; ?></option>
<?php
                                            }
?>
                                            </select>
                                        </div>
                                        <div class="ba-options-group-header-wrapper">
                                            <span class="ba-options-group-header"><?php echo JText::_('TAXES_RATES'); ?></span>
                                        </div>
                                        <div class="ba-options-group-element ba-options-group-sorting-wrapper full-width-group-element">
                                            <div class="ba-options-group-toolbar">
                                                <div>
                                                    <label data-action="add" data-object="taxes">
                                                        <i class="zmdi zmdi-plus"></i>
                                                        <span class="ba-tooltip ba-top ba-hide-element">
                                                            <?php echo JText::_('ADD_NEW_ITEM'); ?>
                                                        </span>
                                                    </label>
                                                    <label data-action="delete" class="disabled">
                                                        <i class="zmdi zmdi-delete"></i>
                                                        <span class="ba-tooltip ba-top ba-hide-element">
                                                            <?php echo JText::_('DELETE'); ?>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="sorting-container">
<?php
                                            foreach ($store->tax->rates as $tax) {
                                                
                                                if (isset($countriesList->{$tax->country_id})) {
                                                    $country = $countriesList->{$tax->country_id};
                                                } else {
                                                    $country = null;
                                                }
?>
                                                <div class="sorting-item">
                                                    <div class="sorting-checkbox">
                                                        <label class="ba-checkbox ba-hide-checkbox">
                                                            <input type="checkbox">
                                                            <span></span>
                                                        </label>
                                                    </div>
                                                    <div class="sorting-title">
                                                        <input type="text" value="<?php echo $tax->title; ?>"
                                                            placeholder="<?php echo JText::_('TITLE'); ?>">
                                                    </div>
                                                    <div class="sorting-tax-rate">
                                                        <input type="text" value="<?php echo $tax->rate; ?>" placeholder="%">
<?php
                                                    foreach ($tax->regions as $region) {
                                                        if ($country && isset($country->regions->{$region->state_id})) {
?>
                                                        <input type="text" value="<?php echo $region->rate; ?>" placeholder="%">
<?php
                                                        }
                                                    }
?>
                                                    </div>
                                                    <div class="sorting-tax-countries-wrapper">
                                                        <div class="sorting-tax-country">
                                                            <div class="tax-rates-items-wrapper">
<?php
                                                            if ($country) {
?>
                                                                <span class="selected-items" data-id="<?php echo $country->id ?>">
                                                                    <span class="selected-items-name"><?php echo $country->title; ?></span>
                                                                    <i class="zmdi zmdi-close delete-tax-country"></i>
                                                                </span>
<?php
                                                                $target = 'region';
                                                                $icon = 'zmdi zmdi-pin';
                                                            } else {
                                                                $target = 'country';
                                                                $icon = 'zmdi zmdi-globe';
                                                            }
?>
                                                            </div>
                                                            <div class="select-items-wrapper add-tax-country-region"
                                                                data-target="<?php echo $target ?>">
<?php
                                                            $tooltip = $country ? JText::_('ADD_REGION') : JText::_('ADD_COUNTRY');
?>
                                                            <span class="ba-tooltip ba-top ba-hide-element"><?php echo $tooltip; ?></span>
                                                                <i class="<?php echo $icon; ?>"></i>
                                                            </div>
                                                        </div>
<?php
                                                    foreach ($tax->regions as $region) {
                                                        if ($country && isset($country->regions->{$region->state_id})) {
                                                            $regionObj = $country->regions->{$region->state_id};
?>
                                                        <div class="tax-country-state">
                                                            <span class="selected-items" data-id="<?php echo $regionObj->id ?>">
                                                                <span class="selected-items-name"><?php echo $regionObj->title ?></span>
                                                                <i class="zmdi zmdi-close delete-country-region"></i>
                                                            </span>
                                                        </div>
<?php
                                                        }
                                                    }
?>
                                                    </div>
                                                    <div class="sorting-tax-category-wrapper"
                                                        style="--placeholder-text: '<?php echo JText::_('CATEGORY'); ?>';">
<?php
                                                        $categories = gridboxHelper::getCategories($tax->categories);
                                                        $str = '';
                                                        foreach ($categories as $category) {
                                                            $str .= '<span class="selected-items" data-id="'.$category->id;
                                                            $str .= '"><span class="selected-items-name">'.$category->title;
                                                            $str .= '</span><i class="zmdi zmdi-close remove-selected-items"></i></span>';
                                                        }
?>
                                                        <div class="tax-rates-items-wrapper"><?php echo $str; ?></div>
                                                        <div class="select-items-wrapper">
<?php
                                                            $tooltip = JText::_('ADD_CATEGORY');
?>
                                                            <span class="ba-tooltip ba-top ba-hide-element"><?php echo $tooltip; ?></span>
                                                            <i class="zmdi zmdi-folder add-tax-category"></i>
                                                        </div>
                                                    </div>
<?php
                                                    $attr = ' data-shipping="'.((int)$tax->shipping).'"';
?>
                                                    <div class="sorting-more-options-wrapper">
                                                        <i class="zmdi zmdi-more show-more-tax-options"<?php echo $attr; ?>></i>
                                                    </div>
                                                </div>
<?php
                                            }
?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="store-sales-options" class="tab-pane">
                                    <div class="ba-options-group-wrapper">
                                        <div class="ba-options-group-header-wrapper">
                                            <span class="ba-options-group-header"><?php echo JText::_('GENERAL'); ?></span>
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('AMOUNT'); ?></label>
                                            <div class="ba-options-price-wrapper">
                                                <span class="ba-options-price-currency">%</span>
                                                <input type="text" class="integer-validation"  data-decimals="0"
                                                    data-key="amount" data-group="sales"
                                                    value="<?php echo htmlspecialchars($store->sales->amount, ENT_QUOTES); ?>">
                                            </div>
                                        </div>
                                        <div class="ba-options-group-element ba-options-group-applies-wrapper">
                                            <label class="ba-options-group-label"><?php echo JText::_('APPLIES_TO'); ?></label>
                                            <select data-key="applies_to" data-group="sales">
<?php
                                                $selected = $store->sales->applies_to == '*' ? ' selected' : '';
?>
                                                <option value="*"<?php echo $selected; ?>><?php echo JText::_('ALL_PRODUCTS'); ?></option>
<?php
                                                $selected = $store->sales->applies_to == 'category' ? ' selected' : '';
?>
                                                <option value="category"<?php echo $selected; ?>><?php echo JText::_('CATEGORY'); ?></option>
                                            </select>
<?php
                                                $style = $store->sales->applies_to == '*' ? ' style="display:none;"' : '';
?>
                                            <div class="ba-options-applies-wrapper"<?php echo $style; ?>>
                                                <span>
                                                    <i class="zmdi zmdi-playlist-plus trigger-picker-modal" data-type="category"
                                                        data-modal="category-applies-dialog"></i>
                                                    <span class="ba-tooltip ba-top ba-hide-element"><?php echo JText::_('SELECT'); ?></span>
                                                </span>
                                            </div>
                                            <div class="selected-applies-wrapper selected-items-wrapper">
<?php
                                            foreach ($map as $value) {
?>
                                                <span class="selected-applies selected-items" data-id="<?php echo $value->id; ?>">
<?php
                                                    if (!empty($value->image)) {
                                                        $style = ' style="background-image: url('.JUri::root().$value->image.')";';
                                                    } else {
                                                        $style = '';
                                                    }
                                                    
?>
                                                    <span class="ba-item-thumbnail"<?php echo $style; ?>>
<?php
                                                    if (empty($value->image)) {
?>
                                                        <i class="zmdi zmdi-folder"></i>
<?php
                                                    }
?>
                                                    </span>
                                                    <span class="selected-items-name"><?php echo $value->title; ?></span>
                                                    <i class="zmdi zmdi-close remove-selected-items"></i>
                                                </span>
<?php
                                            }
?>
                                            </div>
                                        </div>
                                        <div class="ba-options-group-header-wrapper">
                                            <span class="ba-options-group-header"><?php echo JText::_('DATE_LIMITATIONS'); ?></span>
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('START_DATE'); ?></label>
                                            <div class="date-field-wrapper">
                                                <input type="text" class="open-calendar-dialog" data-key="publish_up"
                                                    data-group="sales"
                                                    value="<?php echo htmlspecialchars($store->sales->publish_up, ENT_QUOTES); ?>">
                                                <div class="icons-cell">
                                                    <i class="zmdi zmdi-calendar-alt"></i>
                                                </div>
                                                <div class="reset reset-date-field">
                                                    <i class="zmdi zmdi-close"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ba-options-group-element">
                                            <label class="ba-options-group-label"><?php echo JText::_('END_DATE'); ?></label>
                                            <div class="date-field-wrapper">
                                                <input type="text" class="open-calendar-dialog" data-key="publish_down"
                                                    data-group="sales"
                                                    value="<?php echo htmlspecialchars($store->sales->publish_down, ENT_QUOTES); ?>">
                                                <div class="icons-cell">
                                                    <i class="zmdi zmdi-calendar-alt"></i>
                                                </div>
                                                <div class="reset reset-date-field">
                                                    <i class="zmdi zmdi-close"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="store-order-statuses-options" class="tab-pane">
                                    <div class="ba-options-group-wrapper">
                                        <div class="ba-options-group-element ba-options-group-sorting-wrapper">
                                            <div class="ba-options-group-toolbar">
                                                <div>
                                                    <label data-action="add" data-object="statuses">
                                                        <i class="zmdi zmdi-plus"></i>
                                                        <span class="ba-tooltip ba-top ba-hide-element">
                                                            <?php echo JText::_('ADD_NEW_ITEM'); ?>
                                                        </span>
                                                    </label>
                                                    <label data-action="delete" class="disabled">
                                                        <i class="zmdi zmdi-delete"></i>
                                                        <span class="ba-tooltip ba-top ba-hide-element">
                                                            <?php echo JText::_('DELETE'); ?>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="sorting-container color-picker-sorting-item">
<?php
                                            foreach ($store->statuses as $status) {
?>
                                                <div class="sorting-item">
                                                    <div class="sorting-icon">
                                                        <i class="zmdi zmdi-more-vert sortable-handle"></i>
                                                    </div>
                                                    <div class="sorting-checkbox">
                                                        <label class="ba-checkbox ba-hide-checkbox">
                                                            <input type="checkbox" data-ind="<?php echo $status->key; ?>">
                                                            <span></span>
                                                        </label>
                                                    </div>
                                                    <div class="sorting-title">
                                                        <input type="text" value="<?php echo $status->title; ?>">
                                                    </div>
                                                    <div class="sorting-color-picker">
                                                        <div class="minicolors minicolors-theme-bootstrap">
                                                            <input type="text" data-type="color" class="minicolors-input"
                                                                data-rgba="<?php echo $status->color; ?>">
                                                            <span class="minicolors-swatch minicolors-trigger">
                                                                <span class="minicolors-swatch-color"
                                                                    style="background-color: <?php echo $status->color; ?>;"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
<?php
                                            }
?>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="context-item" value="" id="context-item" />
                        <input type="hidden" name="task" value="" />
                        <input type="hidden" name="boxchecked" value="0" />
                        <input type="hidden" name="app_order_list" value="1">
                        <input type="hidden" name="ba_view" value="storesettings">
                        <?php echo JHtml::_('form.token'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
include(JPATH_COMPONENT.'/views/layouts/context.php');
include(JPATH_COMPONENT.'/views/layouts/categories-modal.php');
?>
<div id="more-tax-options-dialog" class="modal hide ba-modal-picker picker-modal-arrow" style="display: none;">
    <div class="modal-body">
        <div class="picker-modal-options-wrapper">
            <div class="picker-modal-options-row">
                <span class="picker-modal-options-title"><?php echo JText::_('TAX_ON_SHIPPING'); ?></span>
                <label class="ba-checkbox">
                    <input type="checkbox" class="ba-hide-element" data-option="shipping">
                    <span></span>
                </label>
            </div>
        </div>
    </div>
</div>
<div id="data-tags-dialog" class="modal hide ba-modal-picker picker-modal-arrow ba-modal-list-picker">
    <div class="modal-body">
        <div class="data-tags-searchbar">
            <div class="ba-settings-group">
                <div class="ba-settings-item ba-settings-select-type">
                    <select class="select-data-tags-type">
                        <option value=""><?php echo JText::_('All'); ?></option>
                        <option value="store"><?php echo JText::_('STORE'); ?></option>
                        <option value="order"><?php echo JText::_('ORDER'); ?></option>
                        <option value="customer"><?php echo JText::_('CUSTOMER'); ?></option>
                        <option value="product"><?php echo JText::_('PRODUCT'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="">
            <div class="ba-settings-group store-data-tags">
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('STORE_NAME'); ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Store Name]">
                </div>
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('STORE_LEGAL_NAME'); ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Store Legal Business Name]">
                </div>
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('STORE_PHONE'); ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Store Phone]">
                </div>
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('STORE_EMAIL'); ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Store Email]">
                </div>
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('STORE_ADDRESS'); ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Store Address]">
                </div>
            </div>
            <div class="ba-settings-group order-data-tags">
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('ORDER_NUMBER'); ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Order Number]">
                </div>
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('ORDER_DATE'); ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Order Date]">
                </div>
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('ORDER_DETAILS'); ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Order Details]">
                </div>
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('INVOICE_ATTACHED'); ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Invoice: Attached]">
                </div>
            </div>
            <div class="ba-settings-group customer-data-tags">
<?php
            foreach ($this->customerInfo as $info) {
?>
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('CUSTOMER').': '.$info->title; ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Customer ID=<?php echo $info->id; ?>]">
                </div>
<?php
            }
?>
            </div>

            <div class="ba-settings-group product-data-tags">
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('PRODUCT_TITLE'); ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Product Title]">
                </div>
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('PRODUCT_SKU'); ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Product SKU]">
                </div>
                <div class="ba-settings-item ba-settings-input-type">
                    <span class="ba-settings-item-title"><?php echo JText::_('PRODUCT_QUANTITY'); ?></span>
                    <input type="text" readonly onfocus="this.blur()" class="select-input" value="[Product Quantity]">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="uploader-modal" class="ba-modal-lg modal ba-modal-dialog hide" style="display:none" data-check="single">
    <div class="modal-body">
        <iframe src="javascript:''" name="uploader-iframe"></iframe>
        <input type="hidden" data-dismiss="modal">
    </div>
</div>
<div id="cke-image-modal" class="ba-modal-sm modal hide" style="display:none">
    <div class="modal-body">
        <h3><?php echo JText::_('ADD_IMAGE'); ?></h3>
        <div>
            <input type="text" class="cke-upload-image" readonly placeholder="<?php echo JText::_('BROWSE_PICTURE'); ?>">
            <span class="focus-underline"></span>
            <i class="zmdi zmdi-camera"></i>
        </div>
        <input type="text" class="cke-image-alt" placeholder="<?php echo JText::_('IMAGE_ALT'); ?>">
        <span class="focus-underline"></span>
        <div>
            <input type="text" class="cke-image-width" placeholder="<?php echo JText::_('WIDTH'); ?>">
            <span class="focus-underline"></span>
            <input type="text" class="cke-image-height" placeholder="<?php echo JText::_('HEIGHT'); ?>">
            <span class="focus-underline"></span>
        </div>
        <div class="ba-custom-select visible-select-top cke-image-select">
            <input type="text" class="cke-image-align" data-value="" readonly=""
                placeholder="<?php echo JText::_('ALIGNMENT'); ?>">
            <ul class="select-no-scroll">
                <li data-value=""><?php echo JText::_('NONE_SELECTED'); ?></li>
                <li data-value="left"><?php echo JText::_('LEFT'); ?></li>
                <li data-value="right"><?php echo JText::_('RIGHT'); ?></li>
            </ul>
            <i class="zmdi zmdi-caret-down"></i>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="ba-btn" data-dismiss="modal">
            <?php echo JText::_('CANCEL') ?>
        </a>
        <a href="#" class="ba-btn-primary" id="add-cke-image">
            <?php echo JText::_('JTOOLBAR_APPLY') ?>
        </a>
    </div>
</div>
<div id="color-variables-dialog" class="modal hide ba-modal-picker picker-modal-arrow" style="display: none;">
    <div class="modal-header">
        <i class="zmdi zmdi-eyedropper"></i>
    </div>
    <div class="modal-body">
        <div id="color-picker-cell">
            <input type="hidden" data-dismiss="modal">
            <input type="text" class="variables-color-picker">
            <span class="minicolors-opacity-wrapper">
                <input type="number" class="minicolors-opacity" min="0" max="1" step="0.01">
                <span class="ba-tooltip ba-top ba-hide-element"><?php echo JText::_('OPACITY'); ?></span>
            </span>
        </div>
    </div>
</div>
<div id="delete-dialog" class="ba-modal-sm modal hide" style="display:none">
    <div class="modal-body">
        <h3><?php echo JText::_('DELETE_ITEM'); ?></h3>
        <p class="modal-text can-delete"><?php echo JText::_('MODAL_DELETE') ?></p>
    </div>
    <div class="modal-footer">
        <a href="#" class="ba-btn" data-dismiss="modal">
            <?php echo JText::_('CANCEL') ?>
        </a>
        <a href="#" class="ba-btn-primary red-btn" id="apply-delete">
            <?php echo JText::_('DELETE') ?>
        </a>
    </div>
</div>
<div id="resized-ckeditor-dialog" class="ba-modal-lg modal hide" style="display: none;" aria-hidden="true">
    <div class="modal-header">
        <span class="ba-dialog-title"><?php echo JText::_('EMAIL_EDITOR'); ?></span>
        <div class="modal-header-icon">
            <i class="zmdi zmdi-check set-resized-ckeditor-data"></i>
            <i class="zmdi zmdi-close" data-dismiss="modal"></i>
        </div>
    </div>
    <div class="modal-body">
        <textarea data-key="resized"></textarea>
    </div>
</div>
<?php
include(JPATH_COMPONENT.'/views/layouts/countries-modal.php');
?>