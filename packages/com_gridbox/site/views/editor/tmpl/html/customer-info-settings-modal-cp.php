<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;
?>
<div id="customer-info-settings-dialog" class="ba-modal-cp draggable-modal-cp modal hide">
    <div class="modal-header">
        <span class="ba-dialog-title"></span>
        <div class="modal-header-icon">
            <i class="zmdi zmdi-close" data-dismiss="modal"></i>
        </div>
    </div>
    <div class="modal-body">
        <div class="general-tabs">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#customer-info-general-options" data-toggle="tab">
                        <?php echo JText::_('GENERAL'); ?>
                    </a>
                </li>
                <li>
                    <a href="#customer-info-design-options" data-toggle="tab">
                        <?php echo JText::_('DESIGN'); ?>
                    </a>
                </li>
                <li>
                    <a href="#customer-info-layout-options" data-toggle="tab">
                        <?php echo JText::_('LAYOUT'); ?>
                    </a>
                </li>
            </ul>
            <div class="tabs-underline"></div>
            <div class="tab-content">
                <div id="customer-info-general-options" class="row-fluid tab-pane active">
                    <div class="ba-settings-group items-list">
                        <div class="sorting-container"></div>
                        <div class="add-new-item">
                            <span>
                                <i class="zmdi zmdi-plus-circle"></i>
                                <span class="ba-tooltip ba-right"><?php echo JText::_('ADD_NEW_ITEM'); ?></span>
                            </span>
                        </div>
                    </div>
                    <div class="ba-settings-group">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-eye"></i>
                            <span><?php echo JText::_('DISABLE_ON'); ?></span>
                        </div>
                        <div class="ba-settings-item ba-inline-checkbox">
                            <span>
                                <?php echo JText::_('DESKTOP'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-option="disable" data-group="desktop">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item ba-inline-checkbox">
                            <span>
                                <?php echo JText::_('TABLET'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-option="disable" data-group="tablet">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item ba-inline-checkbox">
                            <span>
                                <?php echo JText::_('PHONE'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-option="disable" data-group="phone">
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="ba-settings-group">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-settings"></i>
                            <span><?php echo JText::_('ADVANCED'); ?></span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('EDIT'); ?>
                            </span>
                            <div class="ba-custom-select section-access-select visible-select-top">
                                <input readonly onfocus="this.blur()" type="text" value="">
                                <input type="hidden" value="">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
                                    <?php
                                    foreach ($this->access as $key => $access) {
                                        $str = '<li data-value="'.$key.'">';
                                        $str .= $access.'</li>';
                                        echo $str;
                                    }
                                    ?>
                                </ul>
                            </div>
                            <label class="ba-help-icon">
                                <i class="zmdi zmdi-help"></i>
                                <span class="ba-tooltip ba-help">
                                    <?php echo JText::_('ACCESS_EDIT_TOOLTIP'); ?>
                                </span>
                            </label>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('VIEW'); ?>
                            </span>
                            <div class="ba-custom-select section-access-view-select visible-select-top">
                                <input readonly onfocus="this.blur()" type="text" value="">
                                <input type="hidden" data-group="access_view" class="set-value-css">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
                                    <?php
                                    foreach ($this->access as $key => $access) {
                                        $str = '<li data-value="'.$key.'">';
                                        $str .= $access.'</li>';
                                        echo $str;
                                    }
                                    ?>
                                </ul>
                            </div>
                            <label class="ba-help-icon">
                                <i class="zmdi zmdi-help"></i>
                                <span class="ba-tooltip ba-help">
                                    <?php echo JText::_('ACCESS_TOOLTIP'); ?>
                                </span>
                            </label>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('CLASS_SUFFIX'); ?>
                            </span>
                            <input type="text" class="class-suffix" placeholder="<?php echo JText::_('CLASS_SUFFIX'); ?>">
                            <label class="ba-help-icon">
                                <i class="zmdi zmdi-help"></i>
                                <span class="ba-tooltip ba-help">
                                    <?php echo JText::_('CLASS_SUFFIX_TOOLTIP'); ?>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div id="customer-info-design-options" class="row-fluid tab-pane">
                    <div class="ba-settings-group slideshow-design-group">
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('SELECT'); ?>
                            </span>
                            <div class="ba-custom-select slideshow-style-custom-select">
                                <input readonly onfocus="this.blur()" type="text" value="">
                                <input type="hidden" value="">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
                                    <li data-value="headline"><?php echo JText::_('HEADLINE'); ?></li>
                                    <li data-value="title"><?php echo JText::_('LABEL'); ?></li>
                                    <li data-value="field"><?php echo JText::_('FIELD'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="ba-settings-group slideshow-typography-options">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-format-size"></i>
                            <span><?php echo JText::_('TYPOGRAPHY'); ?></span>
                        </div>
                        <div class="theme-typography-options">
                            <div class="typography-options">
                                <div class="ba-settings-item">
                                    <span>
                                        <?php echo JText::_('FONT_FAMILY'); ?>
                                    </span>
                                    <input readonly onfocus="this.blur()" type="text" data-option="font-family"
                                        data-group="" data-subgroup="typography" data-callback="sectionRules">
                                </div>
                                <div class="ba-settings-item">
                                    <span>
                                        <?php echo JText::_('FONT_WEIGHT'); ?>
                                    </span>
                                    <input readonly onfocus="this.blur()" type="text" data-option="font-weight" data-group=""
                                        data-subgroup="typography" data-callback="sectionRules">
                                </div>
                                <div class="ba-settings-item slideshow-typography-color">
                                    <span>
                                        <?php echo JText::_('COLOR'); ?>
                                    </span>
                                    <input type="text" data-type="color" data-option="color" data-group="" data-subgroup="typography">
                                    <span class="minicolors-opacity-wrapper">
                                        <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                        min="0" max="1" step="0.01">
                                        <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                                    </span>
                                </div>
                                <div class="ba-settings-item">
                                    <span>
                                        <?php echo JText::_('SIZE'); ?>
                                    </span>
                                    <div class="ba-range-wrapper">
                                        <span class="ba-range-liner"></span>
                                        <input type="range" class="ba-range" min="0" max="320">
                                        <input type="number" data-option="font-size" data-group="" data-subgroup="typography"
                                            data-callback="sectionRules">
                                    </div>
                                </div>
                                <div class="ba-settings-item">
                                    <span>
                                        <?php echo JText::_('LETTER_SPACING'); ?>
                                    </span>
                                    <div class="ba-range-wrapper">
                                        <span class="ba-range-liner letter-spacing"></span>
                                        <input type="range" class="ba-range" min="-10" max="10">
                                        <input type="number" data-option="letter-spacing" data-group="" data-subgroup="typography"
                                            data-callback="sectionRules">
                                    </div>
                                </div>
                                <div class="ba-settings-item">
                                    <span>
                                        <?php echo JText::_('LINE_HEIGHT'); ?>
                                    </span>
                                    <div class="ba-range-wrapper">
                                        <span class="ba-range-liner"></span>
                                        <input type="range" class="ba-range" min="0" max="640">
                                        <input type="number" data-option="line-height" data-group="" data-subgroup="typography"
                                            data-callback="sectionRules">
                                    </div>
                                </div>
                                <div class="ba-settings-toolbar">
                                    <label data-option="text-decoration" data-value="underline" data-group="" data-subgroup="typography"
                                        data-callback="sectionRules">
                                        <i class="zmdi zmdi-format-underlined"></i>
                                        <span class="ba-tooltip">
                                            <?php echo JText::_('UNDERLINE'); ?>
                                        </span>
                                    </label>
                                    <label data-option="text-transform" data-value="uppercase" data-group="" data-subgroup="typography"
                                        data-callback="sectionRules">
                                        <i class="zmdi zmdi-format-size"></i>
                                        <span class="ba-tooltip">
                                            <?php echo JText::_('UPPERCASE'); ?>
                                        </span>
                                    </label>
                                    <label data-option="font-style" data-value="italic" data-group="" data-subgroup="typography"
                                        data-callback="sectionRules">
                                        <i class="zmdi zmdi-format-italic"></i>
                                        <span class="ba-tooltip">
                                            <?php echo JText::_('ITALIC'); ?>
                                        </span>
                                    </label>
                                    <label data-option="text-align" data-value="left" data-group="" data-subgroup="typography"
                                        data-callback="sectionRules">
                                        <i class="zmdi zmdi-format-align-left"></i>
                                        <span class="ba-tooltip">
                                            <?php echo JText::_('LEFT'); ?>
                                        </span>
                                    </label>
                                    <label data-option="text-align" data-value="center" data-group="" data-subgroup="typography"
                                        data-callback="sectionRules">
                                        <i class="zmdi zmdi-format-align-center"></i>
                                        <span class="ba-tooltip">
                                            <?php echo JText::_('CENTER'); ?>
                                        </span>
                                    </label>
                                    <label data-option="text-align" data-value="right" data-group="" data-subgroup="typography"
                                        data-callback="sectionRules">
                                        <i class="zmdi zmdi-format-align-right"></i>
                                        <span class="ba-tooltip">
                                            <?php echo JText::_('RIGHT'); ?>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ba-settings-group slideshow-border-options">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-border-left"></i>
                            <span><?php echo JText::_('BORDER'); ?></span>
                        </div>
                        <div class="ba-settings-item ba-inline-checkbox">
                            <span>
                                <?php echo JText::_('TOP'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-subgroup="border" data-option="top" class="set-value-css">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item ba-inline-checkbox">
                            <span>
                                <?php echo JText::_('RIGHT'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-subgroup="border" data-option="right" class="set-value-css">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item ba-inline-checkbox">
                            <span>
                                <?php echo JText::_('BOTTOM'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-subgroup="border" data-option="bottom" class="set-value-css">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item ba-inline-checkbox">
                            <span>
                                <?php echo JText::_('LEFT'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-subgroup="border" data-option="left" class="set-value-css">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('BORDER_RADIUS'); ?>
                            </span>
                            <div class="ba-range-wrapper">
                                <span class="ba-range-liner"></span>
                                <input type="range" class="ba-range" min="0" max="500">
                                <input type="number" data-option="radius" data-subgroup="border" data-callback="sectionRules">
                            </div>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('COLOR'); ?>
                            </span>
                            <input type="text" data-type="color" data-option="color" data-subgroup="border" class="minicolors-top">
                            <span class="minicolors-opacity-wrapper">
                                <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                min="0" max="1" step="0.01">
                                <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                            </span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('WIDTH'); ?>
                            </span>
                            <div class="ba-range-wrapper">
                                <span class="ba-range-liner"></span>
                                <input type="range" class="ba-range" min="0" max="20">
                                <input type="number" data-option="width" data-subgroup="border" data-callback="sectionRules">
                            </div>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('STYLE'); ?>
                            </span>
                            <div class="ba-custom-select border-style-select visible-select-top">
                                <input readonly onfocus="this.blur()" value="" type="text">
                                <input type="hidden" value="" data-option="style" data-subgroup="border">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
                                    <li data-value="solid">Solid</li>
                                    <li data-value="dashed">Dashed</li>
                                    <li data-value="dotted">Dotted</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="ba-settings-group slideshow-normal-options">
                        <div class="settings-group-title">
                            <span><?php echo JText::_('BACKGROUND'); ?></span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('COLOR'); ?>
                            </span>
                            <input type="text" data-type="color" data-option="color" data-group="" data-subgroup="background"
                                class="icon-color">
                            <span class="minicolors-opacity-wrapper">
                                <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                min="0" max="1" step="0.01">
                                <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div id="customer-info-layout-options" class="row-fluid tab-pane">
                    <div class="ba-settings-group">
                        <div class="settings-group-title">
                            <span><?php echo JText::_('MARGIN'); ?></span>
                        </div>
                        <div class="ba-settings-toolbar">
                            <div>
                                <span>
                                    <?php echo JText::_('TOP'); ?>
                                </span>
                                <input type="number" data-group="margin" data-option="top" data-callback="sectionRules">
                            </div>
                            <div>
                                <span>
                                    <?php echo JText::_('BOTTOM'); ?>
                                </span>
                                <input type="number" data-group="margin" data-option="bottom" data-callback="sectionRules">
                            </div>
                            <div>
                                <i class="zmdi zmdi-close" data-type="reset" data-option="margin" data-action="sectionRules"></i>
                                <span class="ba-tooltip">
                                    <?php echo JText::_('RESET'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <i class="zmdi zmdi-more resize-handle-bottom"></i>
            </div>
        </div>
    </div>
</div>
<div id="edit-custom-info-dialog" class="ba-modal-sm modal hide">
    <div class="modal-body">
        <h3 class="ba-modal-title">
            <?php echo JText::_('ITEM'); ?>
        </h3>
        <div class="ba-input-lg">
            <input type="text" class="reset-input-margin" data-key="title" placeholder="<?php echo JText::_('LABEL'); ?>">
            <span class="focus-underline"></span>
        </div>
        <div class="ba-custom-select">
            <input readonly="" onfocus="this.blur()" type="text" class="reset-input-margin">
            <input type="hidden" data-key="type">
            <i class="zmdi zmdi-caret-down"></i>
            <ul>
                <li data-value="text"><?php echo JText::_('TEXT_INPUT'); ?></li>
                <li data-value="textarea"><?php echo JText::_('TEXTAREA'); ?></li>
                <li data-value="dropdown"><?php echo JText::_('DROPDOWN'); ?></li>
                <li data-value="checkbox"><?php echo JText::_('CHECKBOX'); ?></li>
                <li data-value="radio"><?php echo JText::_('RADIO'); ?></li>
                <li data-value="acceptance"><?php echo JText::_('ACCEPTANCE'); ?></li>
                <li data-value="headline"><?php echo JText::_('HEADLINE'); ?></li>
                <li data-value="country"><?php echo JText::_('COUNTRY'); ?></li>
            </ul>
        </div>
        <div class="ba-checkbox-parent">
            <label class="ba-checkbox ba-hide-checkbox">
                <input type="checkbox" data-key="required">
                <span></span>
            </label>
            <label><?php echo JText::_('REQUIRED'); ?></label>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="ba-btn" data-dismiss="modal">
            <?php echo JText::_('CANCEL'); ?>
        </a>
        <a href="#" class="ba-btn-primary active-button" id="apply-customer-info">
            <?php echo JText::_('SAVE'); ?>
        </a>
    </div>
</div>
<div id="customer-info-item-dialog" class="ba-modal-lg modal hide">
    <div class="modal-header">
        <div class="modal-header-icon">
            <i class="zmdi zmdi-check" id="apply-customer-info-item"></i>
            <i class="zmdi zmdi-close" data-dismiss="modal"></i>
        </div>
    </div>
    <div class="modal-body">
        <div class="general-tabs">
            <ul class="nav nav-tabs uploader-nav">
                <li class="active">
                    <a href="#customer-info-edit-item" data-toggle="tab">
                        <i class="zmdi zmdi-settings"></i>
                        <?php echo JText::_('ITEM'); ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="tab-content">
            <div id="customer-info-edit-item">
                <div class="ba-options-group">
                    <div class="ba-group-element">
                        <label>
                            <?php echo JText::_('TYPE'); ?>
                        </label>
                        <div class="ba-custom-select customer-info-type-select">
                            <input readonly onfocus="this.blur()" type="text">
                            <input type="hidden" data-key="type">
                            <i class="zmdi zmdi-caret-down"></i>
                            <ul>
                                <li data-value="text"><?php echo JText::_('TEXT'); ?></li>
                                <li data-value="textarea"><?php echo JText::_('TEXTAREA'); ?></li>
                                <li data-value="dropdown"><?php echo JText::_('DROPDOWN'); ?></li>
                                <li data-value="checkbox"><?php echo JText::_('CHECKBOX'); ?></li>
                                <li data-value="radio"><?php echo JText::_('RADIO'); ?></li>
                                <li data-value="acceptance"><?php echo JText::_('ACCEPTANCE'); ?></li>
                                <li data-value="headline"><?php echo JText::_('HEADLINE'); ?></li>
                                <li data-value="country"><?php echo JText::_('COUNTRY'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="ba-options-group">
                    <div class="ba-group-element">
                        <label><?php echo JText::_('LABEL'); ?></label>
                        <input type="text" placeholder="<?php echo JText::_('LABEL'); ?>" data-key="title">
                        <textarea data-settings="html"></textarea>
                    </div>
                    <div class="text-customer-info-options textarea-customer-info-options email-customer-info-options
                        dropdown-customer-info-options country-customer-info-options">
                        <div class="ba-group-element">
                            <label>
                                <?php echo JText::_('PLACEHOLDER'); ?>
                            </label>
                            <input type="text" placeholder="<?php echo JText::_('PLACEHOLDER'); ?>" data-settings="placeholder">
                        </div>
                    </div>
                    <div class="items-list radio-customer-info-options checkbox-customer-info-options dropdown-customer-info-options">
                        <div class="sorting-container"></div>
                        <div class="add-new-item">
                            <span>
                                <i class="zmdi zmdi-plus-circle add-new-item-action" data-action="single"></i>
                                <span class="ba-tooltip ba-top"><?php echo JText::_('ADD_NEW_ITEM'); ?></span>
                            </span>
                            <span>
                                <i class="zmdi zmdi-playlist-plus add-new-item-action" data-action="bulk"></i>
                                <span class="ba-tooltip ba-top"><?php echo JText::_('BULK_ADDING'); ?></span>
                            </span>
                        </div>
                    </div>
                    <div class="ba-group-element">
                        <label><?php echo JText::_('REQUIRED'); ?></label>
                        <label class="ba-checkbox">
                            <input type="checkbox" data-key="required">
                            <span></span>
                        </label>
                    </div>
                    <div class="ba-group-element">
                        <label><?php echo JText::_('ADD_TO_INVOICE'); ?></label>
                        <label class="ba-checkbox">
                            <input type="checkbox" data-key="invoice">
                            <span></span>
                        </label>
                    </div>
                </div>
                <div class="ba-options-group">
                    <div class="ba-group-element">
                        <label><?php echo JText::_('INFO_WIDTH'); ?>, %</label>
                        <div class="ba-range-wrapper">
                            <span class="ba-range-liner"></span>
                            <input type="range" class="ba-range" min="25" max="100" step="25">
                            <input type="number" readonly step="25" data-callback="emptyCallback" data-settings="width">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="add-single-option-modal" class="ba-modal-sm modal hide" style="display: none;" aria-hidden="false">
    <div class="modal-body">
        <h3 class="ba-modal-title"><?php echo JText::_('ITEM'); ?></h3>
        <div class="ba-input-lg">
            <input type="text" data-key="title" placeholder="<?php echo JText::_('TITLE'); ?>">
            <span class="focus-underline"></span>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="ba-btn" data-dismiss="modal">
            <?php echo JText::_('CANCEL'); ?>
        </a>
        <a href="#" class="ba-btn-primary disable-button apply-single-option">
            <?php echo JText::_('SAVE'); ?>
        </a>
    </div>
</div>
<div id="add-bulk-option-modal" class="ba-modal-md modal hide" style="display:none">
    <div class="modal-body">
        <div class="ba-modal-header">
            <h3 class="ba-modal-title"><?php echo JText::_('BULK_ADDING'); ?></h3>
            <i data-dismiss="modal" class="zmdi zmdi-close"></i>
        </div>
        <div class="bulk-options-wrapper">
            <textarea placeholder="<?php echo JText::_('ENTER_ONE_OPTION_PER_LINE'); ?>"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="ba-btn" data-dismiss="modal">
            <?php echo JText::_('CANCEL') ?>
        </a>
        <a href="#" class="ba-btn-primary apply-bulk-option disable-button">
            <?php echo JText::_('SAVE') ?>
        </a>
    </div>
</div>