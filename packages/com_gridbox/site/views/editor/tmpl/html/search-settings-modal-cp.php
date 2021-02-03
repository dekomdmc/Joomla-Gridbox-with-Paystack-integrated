<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;
?>
<div id="search-settings-dialog" class="ba-modal-cp draggable-modal-cp modal hide">
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
                    <a href="#search-general-options" data-toggle="tab">
                        <?php echo JText::_('GENERAL'); ?>
                    </a>
                </li>
                <li>
                    <a href="#search-design-options" data-toggle="tab">
                        <?php echo JText::_('DESIGN'); ?>
                    </a>
                </li>
                <li>
                    <a href="#search-layout-options" data-toggle="tab">
                        <?php echo JText::_('LAYOUT'); ?>
                    </a>
                </li>
            </ul>
            <div class="tabs-underline"></div>
            <div class="tab-content">
                <div id="search-general-options" class="row-fluid tab-pane active">
                    <div class="ba-settings-group">
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('PLACEHOLDER'); ?>
                            </span>
                            <input type="text" placeholder="<?php echo JText::_('PLACEHOLDER'); ?>" class="search-placeholder">
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('ICON'); ?>
                            </span>
                            <input class="select-input" type="text" readonly onfocus="this.blur()"
                                data-option="icon" data-group="icon"
                                placeholder="<?php echo JText::_('ICON'); ?>">
                            <i class="zmdi zmdi-attachment-alt"></i>
                            <div class="reset">
                                <i class="zmdi zmdi-close" data-group="icon" data-option="icon"
                                    data-action="sectionRules" data-callback="removeSearchIcon"></i>
                                <span class="ba-tooltip">
                                    <?php echo JText::_('RESET'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="ba-settings-item ba-store-search-options">
                            <span><?php echo JText::_('LIVE_SEARCH'); ?></span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-option="live" class="live-store-search">
                                <span></span>
                            </label>
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
                <div id="search-design-options" class="row-fluid tab-pane">
                    <div class="ba-settings-group">
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
                                        data-group="typography" data-callback="sectionRules">
                                </div>
                                <div class="ba-settings-item">
                                    <span>
                                        <?php echo JText::_('FONT_WEIGHT'); ?>
                                    </span>
                                    <input readonly onfocus="this.blur()" type="text" data-option="font-weight"
                                        data-group="typography" data-callback="sectionRules">
                                </div>
                                <div class="ba-settings-item">
                                    <span>
                                        <?php echo JText::_('COLOR'); ?>
                                    </span>
                                    <input type="text" data-type="color" data-option="color" data-group="typography">
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
                                        <input type="number" data-option="font-size" data-group="typography" data-callback="sectionRules">
                                    </div>
                                </div>
                                <div class="ba-settings-item">
                                    <span>
                                        <?php echo JText::_('LETTER_SPACING'); ?>
                                    </span>
                                    <div class="ba-range-wrapper">
                                        <span class="ba-range-liner letter-spacing"></span>
                                        <input type="range" class="ba-range" min="-10" max="10">
                                        <input type="number" data-option="letter-spacing" data-group="typography"
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
                                        <input type="number" data-option="line-height" data-group="typography" data-callback="sectionRules">
                                    </div>
                                </div>
                                <div class="ba-settings-toolbar">
                                    <label data-option="text-decoration" data-value="underline" data-group="typography"
                                        data-callback="sectionRules">
                                        <i class="zmdi zmdi-format-underlined"></i>
                                        <span class="ba-tooltip">
                                            <?php echo JText::_('UNDERLINE'); ?>
                                        </span>
                                    </label>
                                    <label data-option="text-transform" data-value="uppercase" data-group="typography"
                                        data-callback="sectionRules">
                                        <i class="zmdi zmdi-format-size"></i>
                                        <span class="ba-tooltip">
                                            <?php echo JText::_('UPPERCASE'); ?>
                                        </span>
                                    </label>
                                    <label data-option="font-style" data-value="italic" data-group="typography" data-callback="sectionRules">
                                        <i class="zmdi zmdi-format-italic"></i>
                                        <span class="ba-tooltip">
                                            <?php echo JText::_('ITALIC'); ?>
                                        </span>
                                    </label>
                                    <label data-option="text-align" data-value="left" data-group="h1" data-callback="sectionRules">
                                        <i class="zmdi zmdi-format-align-left"></i>
                                        <span class="ba-tooltip">
                                            <?php echo JText::_('LEFT'); ?>
                                        </span>
                                    </label>
                                    <label data-option="text-align" data-value="center" data-group="h1" data-callback="sectionRules">
                                        <i class="zmdi zmdi-format-align-center"></i>
                                        <span class="ba-tooltip">
                                            <?php echo JText::_('CENTER'); ?>
                                        </span>
                                    </label>
                                    <label data-option="text-align" data-value="right" data-group="h1" data-callback="sectionRules">
                                        <i class="zmdi zmdi-format-align-right"></i>
                                        <span class="ba-tooltip">
                                            <?php echo JText::_('RIGHT'); ?>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ba-settings-group">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-local-florist"></i>
                            <span><?php echo JText::_('ICON'); ?></span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('POSITION'); ?>
                            </span>
                            <div class="ba-custom-select search-icon-position visible-select-top">
                                <input readonly onfocus="this.blur()" value="" type="text">
                                <input type="hidden" value="" data-option="position" data-group="icons" class="set-value-css">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
                                    <li data-value=""><?php echo JText::_('BEFORE'); ?></li>
                                    <li data-value="after"><?php echo JText::_('AFTER'); ?></li>
                                </ul>
                            </div>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('SIZE'); ?>
                            </span>
                            <div class="ba-range-wrapper">
                                <span class="ba-range-liner"></span>
                                <input type="range" class="ba-range" min="0" max="320">
                                <input type="number" data-option="size" data-group="icons" data-callback="sectionRules">
                            </div>
                        </div>
                    </div>
                </div>
            <div id="search-layout-options" class="row-fluid tab-pane">
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
                    <div class="ba-settings-group">
                        <div class="settings-group-title">
                            <span><?php echo JText::_('PADDING'); ?></span>
                        </div>
                        <div class="ba-settings-toolbar">
                            <div>
                                <span>
                                    <?php echo JText::_('TOP'); ?>
                                </span>
                                <input type="number" data-group="padding" data-option="top" data-callback="sectionRules">
                            </div>
                            <div>
                                <span>
                                    <?php echo JText::_('RIGHT'); ?>
                                </span>
                                <input type="number" data-group="padding" data-option="right" data-callback="sectionRules">
                            </div>
                            <div>
                                <span>
                                    <?php echo JText::_('BOTTOM'); ?>
                                </span>
                                <input type="number" data-group="padding" data-option="bottom" data-callback="sectionRules">
                            </div>
                            <div>
                                <span>
                                    <?php echo JText::_('LEFT'); ?>
                                </span>
                                <input type="number" data-group="padding" data-option="left" data-callback="sectionRules">
                            </div>
                            <div>
                                <i class="zmdi zmdi-close" data-type="reset" data-option="padding" data-action="sectionRules"></i>
                                <span class="ba-tooltip">
                                    <?php echo JText::_('RESET'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="ba-settings-group">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-border-left"></i>
                            <span><?php echo JText::_('BORDER'); ?></span>
                        </div>
                        <div class="ba-settings-item ba-inline-checkbox">
                            <span>
                                <?php echo JText::_('TOP'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-group="border" data-option="top">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item ba-inline-checkbox">
                            <span>
                                <?php echo JText::_('RIGHT'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-group="border" data-option="right">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item ba-inline-checkbox">
                            <span>
                                <?php echo JText::_('BOTTOM'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-group="border" data-option="bottom">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item ba-inline-checkbox">
                            <span>
                                <?php echo JText::_('LEFT'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-group="border" data-option="left">
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
                                <input type="number" data-option="radius" data-group="border" data-callback="sectionRules">
                            </div>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('COLOR'); ?>
                            </span>
                            <input type="text" data-type="color" data-option="color" data-group="border">
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
                                <input type="number" data-option="width" data-group="border" data-callback="sectionRules">
                            </div>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('STYLE'); ?>
                            </span>
                            <div class="ba-custom-select border-style-select visible-select-top">
                                <input readonly onfocus="this.blur()" value="" type="text">
                                <input type="hidden" value="" data-option="style" data-group="border">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
                                    <li data-value="solid">Solid</li>
                                    <li data-value="dashed">Dashed</li>
                                    <li data-value="dotted">Dotted</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <i class="zmdi zmdi-more resize-handle-bottom"></i>
            </div>
        </div>
    </div>
</div>