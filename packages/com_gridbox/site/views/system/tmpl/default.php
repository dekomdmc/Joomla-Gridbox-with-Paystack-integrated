<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/


defined('_JEXEC') or die;

$user = JFactory::getUser();
if ($this->item->type == 'checkout' && gridboxHelper::$store->checkout->login && empty($user->id)) {
    $lang = JFactory::getLanguage();
    $lang->load('com_users');
?>
<div class="ba-checkout-authentication-backdrop">
    <i class="zmdi zmdi-close ba-leave-checkout"></i>
    <div class="ba-checkout-authentication-wrapper">
        <div class="ba-checkout-login-wrapper">
            <div class="ba-checkout-authentication-title">
                <span><?php echo JText::_('LOG_IN_YOUR_ACCOUNT'); ?></span>
            </div>
            <div class="ba-checkout-authentication-label">
                <span><?php echo JText::_('USERNAME'); ?></span>
            </div>
            <div class="ba-checkout-authentication-input">
                <input type="text" name="username">
            </div>
            <div class="ba-checkout-authentication-label">
                <span><?php echo JText::_('PASSWORD'); ?></span>
            </div>
            <div class="ba-checkout-authentication-input">
                <input type="password" name="password" autocomplete="new-password">
            </div>
<?php
        if (JPluginHelper::isEnabled('system', 'remember')) {
?>
            <div class="ba-checkout-authentication-checkbox">
                <div class="ba-checkbox-wrapper">
                    <span><?php echo JText::_('COM_USERS_LOGIN_REMEMBER_ME'); ?></span>
                    <label class="ba-checkbox">
                        <input type="checkbox" name="remember">
                        <span></span>
                    </label>
                </div>
            </div>
<?php
        }
?>
            <div class="ba-checkout-authentication-btn-wrapper">
                <span class="ba-checkout-authentication-btn ba-user-authentication"><?php echo JText::_('JLOGIN'); ?></span>
            </div>
            <div class="ba-checkout-authentication-links">
                <a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
                    <?php echo JText::_('COM_USERS_LOGIN_REMIND'); ?>
                </a>
                <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
                    <?php echo JText::_('COM_USERS_LOGIN_RESET'); ?>
                </a>
<?php
            if (gridboxHelper::$store->checkout->registration) {
?>
                <span class="ba-show-registration-dialog"><?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?></span>
<?php
        }
?>
            </div>
        </div>
<?php
    if (gridboxHelper::$store->checkout->guest) {
?>
        <div class="ba-checkout-guest-wrapper">
            <div class="ba-checkout-authentication-title">
                <span><?php echo JText::_('GUEST_CHECKOUT'); ?></span>
            </div>
            <div class="ba-checkout-authentication-text">
                <span><?php echo JText::_('PURCHASE_AS_GUEST'); ?></span>
            </div>
            <div class="ba-checkout-authentication-btn-wrapper">
                <span class="ba-checkout-authentication-btn ba-guest-authentication"><?php echo JText::_('CONTINUE_AS_GUEST'); ?></span>
            </div>
        </div>
<?php
    }
?>
    </div>
<?php
    if (gridboxHelper::$store->checkout->guest) {
?>
    <div class="ba-checkout-registration-backdrop">
        <div class="ba-checkout-registration-wrapper">
            <i class="zmdi zmdi-close close-registration-modal"></i>
            <div class="ba-checkout-authentication-title">
                <span><?php echo JText::_('COM_USERS_REGISTRATION'); ?></span>
            </div>
            <div class="ba-checkout-authentication-label">
                <span><?php echo JText::_('COM_USERS_REGISTER_NAME_LABEL'); ?> *</span>
            </div>
            <div class="ba-checkout-authentication-input">
                <input type="text" name="name">
            </div>
            <div class="ba-checkout-authentication-label">
                <span><?php echo JText::_('COM_USERS_REGISTER_USERNAME_LABEL'); ?> *</span>
            </div>
            <div class="ba-checkout-authentication-input">
                <input type="text" name="username">
            </div>
            <div class="ba-checkout-authentication-label">
                <span><?php echo JText::_('COM_USERS_PROFILE_PASSWORD1_LABEL'); ?> *</span>
            </div>
            <div class="ba-checkout-authentication-input">
                <input type="password" name="password1">
            </div>
            <div class="ba-checkout-authentication-label">
                <span><?php echo JText::_('COM_USERS_PROFILE_PASSWORD2_LABEL'); ?> *</span>
            </div>
            <div class="ba-checkout-authentication-input">
                <input type="password" name="password2">
            </div>
            <div class="ba-checkout-authentication-label">
                <span><?php echo JText::_('COM_USERS_REGISTER_EMAIL1_LABEL'); ?> *</span>
            </div>
            <div class="ba-checkout-authentication-input">
                <input type="email" name="email1">
            </div>
            <div class="ba-checkout-authentication-label">
                <span><?php echo JText::_('COM_USERS_REGISTER_EMAIL2_LABEL'); ?> *</span>
            </div>
            <div class="ba-checkout-authentication-input">
                <input type="email" name="email2">
            </div>
<?php
        if (gridboxHelper::$store->checkout->terms) {
?>
            <div class="ba-checkout-authentication-checkbox">
                <div class="ba-checkbox-wrapper">
                    <div><?php echo gridboxHelper::$store->checkout->terms_text; ?></div>
                    <label class="ba-checkbox">
                        <input type="checkbox" name="acceptance">
                        <span></span>
                    </label>
                </div>
            </div>
<?php
        }
?>
            <div class="ba-checkout-authentication-btn-wrapper">
                <span class="ba-checkout-authentication-btn ba-user-registration"><?php echo JText::_('JREGISTER'); ?></span>
            </div>
        </div>
    </div>
<?php
    }
?>
</div>
<?php
}
?>
<div class="row-fluid">
<?php
if ($user->authorise('core.edit', 'com_gridbox')) {
?>
    <a class="edit-page-btn" target="_blank"
        href="<?php echo JUri::root().'index.php?option=com_gridbox&view=editor&edit_type=system&tmpl=component&id='.$this->item->id; ?>">
        <i class="zmdi zmdi-settings"></i>
        <p class="edit-page"><?php echo JText::_('EDIT_PAGE'); ?></p>
    </a>
<?php
}
?>
    <div class="ba-gridbox-page row-fluid">
<?php
    if (!empty($this->item)) echo stripcslashes($this->item->html);
?>
    </div>
</div>