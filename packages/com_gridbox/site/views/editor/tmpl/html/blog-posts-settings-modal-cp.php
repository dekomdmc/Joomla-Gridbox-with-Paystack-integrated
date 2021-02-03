<?php

?>
<div id="blog-posts-settings-dialog" class="ba-modal-cp draggable-modal-cp modal hide">
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
                    <a href="#blog-posts-general-options" data-toggle="tab">
                        <?php echo JText::_('GENERAL'); ?>
                    </a>
                </li>
                <li>
                    <a href="#blog-posts-design-options" data-toggle="tab">
                        <?php echo JText::_('DESIGN'); ?>
                    </a>
                </li>
                <li>
                    <a href="#blog-posts-layout-options" data-toggle="tab">
                        <?php echo JText::_('LAYOUT'); ?>
                    </a>
                </li>
            </ul>
            <div class="tabs-underline"></div>
            <div class="tab-content">
                <div id="blog-posts-general-options" class="row-fluid tab-pane active">
                    <div class="ba-settings-group">
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('LAYOUT'); ?>
                            </span>
                            <div class="ba-custom-select blog-posts-layout-select">
                                <input readonly onfocus="this.blur()" type="text" value="">
                                <input type="hidden" value="">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
                                    <li data-value="ba-one-column-grid-layout"><?php echo JText::_('CLASSIC'); ?></li>
                                    <li data-value="ba-grid-layout"><?php echo JText::_('CARD'); ?></li>
                                    <li data-value="ba-cover-layout"><?php echo JText::_('COVER'); ?></li>
                                    <li data-value="ba-classic-layout"><?php echo JText::_('LIST'); ?></li>
                                    <li data-value="ba-masonry-layout" class="not-author-options"><?php echo JText::_('MASONRY'); ?></li>
                                </ul>
                            </div>
                        </div>
                        <div class="ba-settings-item blog-posts-grid-options">
                            <span>
                                <?php echo JText::_('NUMBER_OF_COLUMNS'); ?>
                            </span>
                            <input type="number" data-option="count" data-group="view" class="lightbox-settings-input set-value-css">
                        </div>
                        <div class="ba-settings-item blog-posts-cover-options">
                            <span>
                                <?php echo JText::_('COLUMNS_GUTTER'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-option="gutter" data-group="view" class="set-value-css">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('SORT_BY'); ?>
                            </span>
                            <div class="ba-custom-select blog-posts-sort-select">
                                <input readonly="" onfocus="this.blur()" type="text">
                                <input type="hidden">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
<?php
                                if ($this->edit_type == 'blog' && $this->item->type == 'products') {
                                    $list = gridboxHelper::getBlogPostsSortingList();
                                    foreach ($list as $key => $text) {
?>
                                        <li data-value="<?php echo $key; ?>"><?php echo $text; ?></li>
<?php
                                    }
?>

<?php
                                } else {
?>
                                    <li data-value="created"><?php echo JText::_('RECENT'); ?></li>
                                    <li data-value="hits"><?php echo JText::_('POPULAR'); ?></li>
                                    <li data-value="order_list"><?php echo JText::_('CUSTOM'); ?></li>
                                    <li data-value="random"><?php echo JText::_('RANDOM'); ?></li>
<?php
                                }
?>
                                </ul>
                            </div>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('ITEMS_PER_PAGE'); ?>
                            </span>
                            <input type="number" data-option="limit" class="lightbox-settings-input" placeholder="3">
                        </div>
                    </div>
                    <div class="ba-settings-group blog-posts-view-options">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-desktop-windows"></i>
                            <span><?php echo JText::_('VIEW'); ?></span>
                        </div>
<?php
                    if ($this->edit_type == 'blog' && $this->item->type == 'products') {
?>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('SORTING'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-group="view" data-option="sorting" class="set-value-css">
                                <span></span>
                            </label>
                        </div>
<?php
                    }
?>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('IMAGE'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-group="view" data-option="image" class="set-value-css">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('TITLE'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-group="view" data-option="title" class="set-value-css">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('INFO'); ?>
                            </span>
                            <span class="category-list-fields-wrapper">
                                <i class="zmdi zmdi-playlist-plus open-category-list-fields" data-target="info"></i>
                            </span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('REVIEWS'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-option="reviews" data-group="view" class="set-value-css">
                                <span></span>
                            </label>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('INTRO_TEXT'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-group="view" data-option="intro" class="set-value-css">
                                <span></span>
                            </label>
                        </div>
<?php
                    if ($this->edit_type == 'blog' && $this->item->type == 'products') {
?>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('STORE'); ?>
                            </span>
                            <span class="category-list-fields-wrapper">
                                <i class="zmdi zmdi-playlist-plus open-category-list-fields" data-target="store"></i>
                            </span>
                        </div>
<?php
                    }
?>
<?php
                    if ($this->edit_type == 'blog' && $this->item->type != 'blog') {
?>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('FIELDS'); ?>
                            </span>
                            <span class="category-list-fields-wrapper">
                                <i class="zmdi zmdi-playlist-plus open-category-list-fields" data-target="fields"></i>
                            </span>
                        </div>
<?php
                    }
?>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('BUTTON'); ?>
                            </span>
                            <label class="ba-checkbox">
                                <input type="checkbox" data-group="view" data-option="button" class="set-value-css">
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
                <div id="blog-posts-design-options" class="row-fluid tab-pane">
                    <div class="ba-settings-group slideshow-design-group">
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('SELECT'); ?>
                            </span>
                            <div class="ba-custom-select ba-style-custom-select">
                                <input readonly onfocus="this.blur()" type="text" value="">
                                <input type="hidden" value="">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
                                    <li data-value="image"><?php echo JText::_('IMAGE'); ?></li>
                                    <li data-value="title"><?php echo JText::_('TITLE'); ?></li>
                                    <li data-value="info"><?php echo JText::_('INFO'); ?></li>
                                    <li data-value="reviews"><?php echo JText::_('REVIEWS'); ?></li>
                                    <li data-value="intro"><?php echo JText::_('INTRO_TEXT'); ?></li>
<?php
                                if ($this->edit_type == 'blog' && $this->item->type != 'blog') {
?>
                                    <li data-value="postFields"><?php echo JText::_('FIELDS'); ?></li>
<?php
                                }
?>
                                    <li data-value="button"><?php echo JText::_('BUTTON'); ?></li>
                                    <li data-value="pagination"><?php echo JText::_('PAGINATION'); ?></li>
                                </ul>
                            </div>
                        </div>
                        <div class="ba-settings-item title-html-tag">
                            <span>
                                <?php echo JText::_('HTML_TAG'); ?>
                            </span>
                            <div class="ba-custom-select select-title-html-tag">
                                <input readonly onfocus="this.blur()" type="text" value="">
                                <input type="hidden" value="">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
                                    <li data-value="h1">H1</li>
                                    <li data-value="h2">H2</li>
                                    <li data-value="h3">H3</li>
                                    <li data-value="h4">H4</li>
                                    <li data-value="h5">H5</li>
                                    <li data-value="h6">H6</li>
                                </ul>
                            </div>
                        </div>
                        <div class="ba-settings-item ba-style-intro-options">
                            <span>
                                <?php echo JText::_('MAXIMUM_LENGTH'); ?>
                            </span>
                            <input type="number" data-option="maximum" class="lightbox-settings-input" placeholder="50">
                            <label class="ba-help-icon">
                                <i class="zmdi zmdi-help"></i>
                                <span class="ba-tooltip ba-help">
                                <?php echo JText::_('MAXIMUM_LENGTH_TOOLTIP'); ?>
                                </span>
                            </label>
                        </div>
                        <div class="ba-settings-item ba-style-button-options">
                            <span>
                                <?php echo JText::_('LABEL'); ?>
                            </span>
                            <input type="text" class="recent-posts-button-label">
                        </div>
                    </div>
                    <div class="ba-settings-group ba-style-typography-options">
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
                                    <input readonly onfocus="this.blur()" type="text" data-option="font-weight"
                                        data-group="" data-subgroup="typography" data-callback="sectionRules">
                                </div>
                                <div class="ba-settings-item ba-style-typography-color">
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
                                <div class="ba-settings-item ba-style-typography-hover-color desktop-only" style="display: none;">
                                    <span>
                                        <?php echo JText::_('HOVER'); ?>
                                    </span>
                                    <input type="text" data-type="color" data-option="color" data-group="" data-subgroup="hover">
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
                    <div class="ba-settings-group ba-style-pagination-options">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-format-size"></i>
                            <span><?php echo JText::_('TYPOGRAPHY'); ?></span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('COLOR'); ?>
                            </span>
                            <input type="text" data-type="color" data-option="color" data-group="pagination">
                            <span class="minicolors-opacity-wrapper">
                                <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                min="0" max="1" step="0.01">
                                <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                            </span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('HOVER_ACTIVE'); ?>
                            </span>
                            <input type="text" data-type="color" data-option="hover" data-group="pagination">
                            <span class="minicolors-opacity-wrapper">
                                <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                min="0" max="1" step="0.01">
                                <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                            </span>
                        </div>
                    </div>
                    <div class="ba-settings-group ba-style-image-options">
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('WIDTH'); ?>
                            </span>
                            <div class="ba-range-wrapper">
                                <span class="ba-range-liner"></span>
                                <input type="range" class="ba-range" min="10" max="1500">
                                <input type="number" data-option="width" data-group="image" data-callback="sectionRules">
                            </div>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('HEIGHT'); ?>
                            </span>
                            <div class="ba-range-wrapper">
                                <span class="ba-range-liner"></span>
                                <input type="range" class="ba-range" min="100" max="1500">
                                <input type="number" data-option="height" data-group="image" data-callback="sectionRules">
                            </div>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('SIZE'); ?>
                            </span>
                            <div class="ba-custom-select">
                                <input readonly onfocus="this.blur()" type="text">
                                <input type="hidden" data-option="size" data-group="image" class="set-value-css">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
                                    <li data-value="cover">Cover</li>
                                    <li data-value="contain">Contain</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="ba-settings-group ba-style-image-options blog-posts-cover-options">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-format-color-fill"></i>
                            <span><?php echo JText::_('OVERLAY'); ?></span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('TYPE'); ?>
                            </span>
                            <div class="ba-custom-select background-overlay-select">
                                <input readonly onfocus="this.blur()" type="text">
                                <input type="hidden" data-property="overlay" data-callback="sectionRules">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
                                    <li data-value="color"><?php echo JText::_('COLOR'); ?></li>
                                    <li data-value="gradient"><?php echo JText::_('GRADIENT'); ?></li>
                                    <li data-value="none"><?php echo JText::_('NO_NE'); ?></li>
                                </ul>
                            </div>
                        </div>
                        <div class="overlay-color-options">
                            <div class="ba-settings-item">
                                <span>
                                    <?php echo JText::_('COLOR'); ?>
                                </span>
                                <input type="text" data-type="color" class="minicolors-input"
                                    data-option="color" data-group="overlay">
                                <span class="minicolors-opacity-wrapper">
                                    <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                    min="0" max="1" step="0.01">
                                    <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                                </span>
                            </div>
                        </div>
                        <div class="overlay-gradient-options">
                            <div class="ba-settings-item">
                                <span>
                                    <?php echo JText::_('EFFECT'); ?>
                                </span>
                                <div class="ba-custom-select gradient-effect-select">
                                    <input readonly onfocus="this.blur()" value="" type="text">
                                    <input type="hidden" value="" data-property="overlay" data-callback="sectionRules">
                                    <i class="zmdi zmdi-caret-down"></i>
                                    <ul>
                                        <li data-value="linear">Linear</li>
                                        <li data-value="radial">Radial</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="ba-settings-item overlay-linear-gradient">
                                <span>
                                    <?php echo JText::_('ANGLE'); ?>
                                </span>
                                <div class="ba-range-wrapper">
                                    <span class="ba-range-liner"></span>
                                    <input type="range" class="ba-range" min="0" max="360" step="1">
                                    <input type="number" data-option="angle" data-group="overlay" data-subgroup="gradient"
                                        step="1" data-callback="sectionRules">
                                </div>
                            </div>
                            <div class="ba-settings-item">
                                <span>
                                    <?php echo JText::_('START_COLOR'); ?>
                                </span>
                                <input type="text" data-type="color" class="minicolors-input"
                                    data-option="color1" data-group="overlay" data-subgroup="gradient">
                                <span class="minicolors-opacity-wrapper">
                                    <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                    min="0" max="1" step="0.01">
                                    <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                                </span>
                            </div>
                            <div class="ba-settings-item">
                                <span>
                                    <?php echo JText::_('POSITION'); ?>
                                </span>
                                <div class="ba-range-wrapper">
                                    <span class="ba-range-liner"></span>
                                    <input type="range" class="ba-range" min="0" max="100" step="1">
                                    <input type="number" data-option="position1" data-group="overlay" data-subgroup="gradient"
                                        step="1" data-callback="sectionRules">
                                </div>
                            </div>
                            <div class="ba-settings-item">
                                <span>
                                    <?php echo JText::_('END_COLOR'); ?>
                                </span>
                                <input type="text" data-type="color" class="minicolors-input" data-option="color2"
                                    data-group="overlay" data-subgroup="gradient">
                                <span class="minicolors-opacity-wrapper">
                                    <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                    min="0" max="1" step="0.01">
                                    <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                                </span>
                            </div>
                            <div class="ba-settings-item">
                                <span>
                                    <?php echo JText::_('POSITION'); ?>
                                </span>
                                <div class="ba-range-wrapper">
                                    <span class="ba-range-liner"></span>
                                    <input type="range" class="ba-range" min="0" max="100" step="1">
                                    <input type="number" data-option="position2" data-group="overlay" data-subgroup="gradient"
                                        step="1" data-callback="sectionRules">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ba-settings-group ba-style-button-options">
                        <div class="settings-group-title">
                            <span><?php echo JText::_('NORMAL'); ?></span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('COLOR'); ?>
                            </span>
                            <input type="text" data-type="color" data-option="color" data-group="" data-subgroup="normal"
                                class="icon-color">
                            <span class="minicolors-opacity-wrapper">
                                <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                min="0" max="1" step="0.01">
                                <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                            </span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('BACKGROUND'); ?>
                            </span>
                            <input type="text" data-type="color" data-option="background" data-group="" data-subgroup="normal"
                                class="icon-background">
                            <span class="minicolors-opacity-wrapper">
                                <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                min="0" max="1" step="0.01">
                                <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                            </span>
                        </div>
                    </div>
                    <div class="ba-settings-group ba-style-button-options">
                        <div class="settings-group-title">
                            <span><?php echo JText::_('HOVER'); ?></span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('COLOR'); ?>
                            </span>
                            <input type="text" data-type="color" data-option="color" data-group="" data-subgroup="hover"
                                class="icon-color">
                            <span class="minicolors-opacity-wrapper">
                                <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                min="0" max="1" step="0.01">
                                <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                            </span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('BACKGROUND'); ?>
                            </span>
                            <input type="text" data-type="color" data-option="background" data-group="" data-subgroup="hover"
                                class="icon-background">
                            <span class="minicolors-opacity-wrapper">
                                <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                min="0" max="1" step="0.01">
                                <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                            </span>
                        </div>
                    </div>
                    <div class="ba-settings-group ba-style-margin-options">
                        <div class="settings-group-title">
                            <span><?php echo JText::_('MARGIN'); ?></span>
                        </div>
                        <div class="ba-settings-toolbar">
                            <div>
                                <span>
                                    <?php echo JText::_('TOP'); ?>
                                </span>
                                <input type="number" data-group="description" data-option="top" data-subgroup="margin"
                                    data-callback="sectionRules">
                            </div>
                            <div>
                                <span>
                                    <?php echo JText::_('BOTTOM'); ?>
                                </span>
                                <input type="number" data-group="description" data-option="bottom" data-subgroup="margin"
                                    data-callback="sectionRules">
                            </div>
                            <div>
                                <i class="zmdi zmdi-close" data-type="reset" data-option="description" data-subgroup="margin"
                                    data-action="sectionRules"></i>
                                <span class="ba-tooltip">
                                    <?php echo JText::_('RESET'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="ba-settings-group ba-style-button-options">
                        <div class="settings-group-title">
                            <span><?php echo JText::_('PADDING'); ?></span>
                        </div>
                        <div class="ba-settings-toolbar">
                            <div>
                                <span>
                                    <?php echo JText::_('TOP'); ?>
                                </span>
                                <input type="number" data-group="button" data-option="top" data-subgroup="padding"
                                    data-callback="sectionRules">
                            </div>
                            <div>
                                <span>
                                    <?php echo JText::_('RIGHT'); ?>
                                </span>
                                <input type="number" data-group="button" data-option="right" data-subgroup="padding"
                                    data-callback="sectionRules">
                            </div>
                            <div>
                                <span>
                                    <?php echo JText::_('BOTTOM'); ?>
                                </span>
                                <input type="number" data-group="button" data-option="bottom" data-subgroup="padding"
                                    data-callback="sectionRules">
                            </div>
                            <div>
                                <span>
                                    <?php echo JText::_('LEFT'); ?>
                                </span>
                                <input type="number" data-group="button" data-option="left" data-subgroup="padding"
                                    data-callback="sectionRules">
                            </div>
                            <div>
                                <i class="zmdi zmdi-close" data-type="reset" data-option="button" data-subgroup="padding"
                                    data-action="sectionRules"></i>
                                <span class="ba-tooltip">
                                    <?php echo JText::_('RESET'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="ba-settings-group ba-style-border-options">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-border-left"></i>
                            <span><?php echo JText::_('BORDER'); ?></span>
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
                            <input type="text" data-type="color" data-option="color" data-subgroup="border">
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
                            <div class="ba-custom-select border-style-select">
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
                    <div class="ba-settings-group ba-style-button-options">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-select-all"></i>
                            <span><?php echo JText::_('SHADOW'); ?></span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('VALUE'); ?>
                            </span>
                            <div class="ba-range-wrapper">
                                <span class="ba-range-liner"></span>
                                <input type="range" class="ba-range" min="0" max="10">
                                <input type="number" data-option="value" data-subgroup="shadow" data-callback="sectionRules">
                            </div>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('COLOR'); ?>
                            </span>
                            <input type="text" data-type="color" data-option="color" data-subgroup="shadow" class="minicolors-top">
                            <span class="minicolors-opacity-wrapper">
                                <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                min="0" max="1" step="0.01">
                                <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                            </span>
                        </div>
                    </div>
                    <div class="ba-settings-group blog-posts-background-options">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-format-color-fill"></i>
                            <span><?php echo JText::_('BACKGROUND'); ?></span>
                        </div>
                        <div class="ba-settings-item background">
                            <span>
                                <?php echo JText::_('COLOR'); ?>
                            </span>
                            <input type="text" data-type="color" data-option="color" data-group="background" class="minicolors-top">
                            <span class="minicolors-opacity-wrapper">
                                <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                min="0" max="1" step="0.01">
                                <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div id="blog-posts-layout-options" class="row-fluid tab-pane">
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
                            <div class="ba-custom-select border-style-select">
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
                    <div class="ba-settings-group blog-posts-shadow-options">
                        <div class="settings-group-title">
                            <i class="zmdi zmdi-select-all"></i>
                            <span><?php echo JText::_('SHADOW'); ?></span>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('VALUE'); ?>
                            </span>
                            <div class="ba-range-wrapper">
                                <span class="ba-range-liner"></span>
                                <input type="range" class="ba-range" min="0" max="10">
                                <input type="number" data-option="value" data-group="shadow" data-callback="sectionRules">
                            </div>
                        </div>
                        <div class="ba-settings-item">
                            <span>
                                <?php echo JText::_('COLOR'); ?>
                            </span>
                            <input type="text" data-type="color" data-option="color" data-group="shadow" class="minicolors-top">
                            <span class="minicolors-opacity-wrapper">
                                <input type="number" class="minicolors-opacity" data-callback="sectionRules"
                                min="0" max="1" step="0.01">
                                <span class="ba-tooltip"><?php echo JText::_('OPACITY') ?></span>
                            </span>
                        </div>
                    </div>
                </div>
                <i class="zmdi zmdi-more resize-handle-bottom"></i>
            </div>
        </div>
    </div>
</div>