<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
$sortFields = $this->getSortFields();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$state = $this->state->get('filter.state');
$themeState = $this->state->get('filter.theme');
$authorState = $this->state->get('filter.author');
$accessState = $this->state->get('filter.access');
$languageState = $this->state->get('filter.language');
$user = JFactory::getUser();
$appAssets = new gridboxAssetsHelper($this->blog->id, 'app');
if (!empty($this->category)) {
    $create = gridboxHelper::assetsCheckPermission($this->category, 'category', 'core.create');
} else {
    $create = $appAssets->checkPermission('core.create');
}
$limit = $this->pagination->limit;
$pagLimit = array(
    5 => 5,
    10 => 10,
    15 => 15,
    20 => 20,
    25 => 25,
    30 => 30,
    50 => 50,
    100 => 100,
    0 => JText::_('JALL'),
);
if (!isset($pagLimit[$limit])) {
    $limit = 0;
}
if (!empty($this->category)) {
    $url = gridboxHelper::getEditorLink($this->blog->type).'&app_id='.$this->blog->id.'&category='.$this->category.'&id=';
}
$catUrl = 'index.php?option=com_gridbox&view=apps&id='.$this->blog->id.'&category=';
$editBlog = gridboxHelper::getEditorLink().'&edit_type=blog&id='.$this->blog->id;
$editPostLayout = gridboxHelper::getEditorLink().'&edit_type=post-layout&id='.$this->blog->id;
?>
<script type="text/javascript" src="<?php echo JUri::root(true); ?>/media/system/js/calendar.js"></script>
<script type="text/javascript" src="<?php echo JUri::root(true); ?>/media/system/js/calendar-setup.js"></script>
<script type="text/javascript"><?php echo gridboxHelper::setCalendar(); ?></script>
<link rel="stylesheet" href="<?php echo JUri::root(true); ?>/media/system/css/calendar-jos.css">
<script src="components/com_gridbox/assets/js/ba-admin.js?<?php echo $this->about->version; ?>" type="text/javascript"></script>
<script src="<?php echo JUri::root(); ?>administrator/components/com_gridbox/assets/js/sortable.js" type="text/javascript"></script>
<script type="text/javascript">
    document.body.classList.add('view-blogs');
    jQuery('#toolbar-download, #toolbar-settings, #toolbar-delete').find('button').removeAttr('onclick');
    jQuery('#toolbar-settings span')[0].className = 'icon-options';
    jQuery('#toolbar-delete button').addClass('blog-delete');
</script>
<?php
include(JPATH_COMPONENT.'/views/layouts/ckeditor.php');
include(JPATH_COMPONENT.'/views/layouts/notification.php');
?>
<input type="hidden" value="<?php echo JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'); ?>" class="jlib-selection">
<input type="hidden" value="<?php echo JText::_('SUCCESS_UPLOAD'); ?>" id="upload-const">
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
<form action="<?php echo JUri::root().'administrator/index.php?option=com_gridbox&view=apps&id='.$this->blog->id; ?>" method="post"
    name="adminForm" id="adminForm" autocomplete="off">
    <div id="create-category-modal" class="ba-modal-sm modal hide" style="display:none">
        <div class="modal-body">
            <h3><?php echo JText::_('CREATE_CATEGORY'); ?></h3>
            <input type="text" class="category-name" name="category_name" placeholder="<?php echo JText::_('CATEGORY_NAME') ?>">
            <span class="focus-underline"></span>
            <input type="hidden" name="parent_id" class="parent-id">
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn" data-dismiss="modal">
                <?php echo JText::_('CANCEL') ?>
            </a>
            <a href="#" class="ba-btn-primary" id="create-new-category">
                <?php echo JText::_('JTOOLBAR_APPLY') ?>
            </a>
        </div>
    </div>
    <div id="move-to-modal" class="ba-modal-md modal hide" style="display:none">
        <div class="modal-body">
            <div class="ba-modal-header">
                <h3><?php echo JText::_('MOVE_TO'); ?></h3>
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
            <a href="#" class="ba-btn-primary apply-move">
                <?php echo JText::_('JTOOLBAR_APPLY') ?>
            </a>
        </div>
    </div>
    <div id="settings-dialog" class="ba-modal-lg modal hide" style="display:none">
        <div class="modal-header">
            <span class="ba-dialog-title"><?php echo JText::_('SETTINGS'); ?></span>
            <div class="modal-header-icon">
                <i class="zmdi zmdi-check settings-apply"></i>
                <i class="zmdi zmdi-close" data-dismiss="modal"></i>
            </div>
        </div>
        <div class="modal-body">
            <div class="general-tabs">
                <ul class="nav nav-tabs uploader-nav">
                    <li class="active">
                        <a href="#general-options" data-toggle="tab">
                            <i class="zmdi zmdi-settings"></i>
                            <?php echo JText::_('GENERAL'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="#publishing-options" data-toggle="tab">
                            <i class="zmdi zmdi-calendar-note"></i>
                            <?php echo JText::_('PUBLISHING'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="#seo-options" data-toggle="tab">
                            <i class="zmdi zmdi-globe"></i>
                            SEO
                        </a>
                    </li>
<?php
                    if ($user->authorise('core.admin', 'com_gridbox')) {
?>
                    <li>
                        <a href="#permissions-options" data-toggle="tab">
                            <i class="zmdi zmdi-account-circle"></i>
                            <?php echo JText::_('JCONFIG_PERMISSIONS_LABEL'); ?>
                        </a>
                    </li>
<?php
                    }
?>
                </ul>
                <div class="tabs-underline"></div>
                <div class="tab-content">
                    <div id="general-options" class="row-fluid tab-pane active">
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('JGLOBAL_TITLE'); ?><span class="required-fields-star">*</span>
                                </label>
                                <input type="hidden" name="ba_id" class="page-id">
                                <input type="text" name="page_title" class="page-title"
                                    placeholder="<?php echo JText::_('JGLOBAL_TITLE'); ?>">
                                <div class="ba-alert-container" style="display: none;">
                                    <i class="zmdi zmdi-alert-circle"></i>
                                    <span></span>
                                    <span class="ba-tooltip ba-top ba-hide-element">
                                        <?php echo JText::_('THIS_FIELD_REQUIRED'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('JFIELD_ALIAS_LABEL'); ?>
                                </label>
                                <input type="text" name="page_alias" class="page-alias"
                                    placeholder="<?php echo JText::_('JFIELD_ALIAS_LABEL'); ?>">
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('IMAGE'); ?>
                                </label>
                                <div class="share-image-wrapper">
                                    <div class="image-field-tooltip"></div>
                                    <input type="text" class="intro-image" name="intro_image"
                                        placeholder="<?php echo JText::_('IMAGE'); ?>" readonly="" onfocus="this.blur()">
                                    <i class="zmdi zmdi-camera"></i>
                                    <div class="reset disabled-reset reset-share-image">
                                        <i class="zmdi zmdi-close"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('INTRO_TEXT'); ?>
                                </label>
                                <textarea placeholder="<?php echo JText::_('INTRO_TEXT'); ?>"
                                    name="intro_text" class="intro-text"></textarea>
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('CATEGORY'); ?>
                                </label>
                                <div class="ba-custom-select">
                                <input readonly onfocus="this.blur()" type="text" value="">
                                <input type="hidden" id="page-category" name="page_category" value="">
                                <i class="zmdi zmdi-caret-down"></i>
                                <ul>
                                    <?php
                                    foreach ($this->categoryList as $key => $category) {
                                        $str = '<li data-value="'.$category->id.'">';
                                        $str .= $category->title.'</li>';
                                        echo $str;
                                    }
                                    ?>
                                </ul>
                            </div>
                            </div>
                        </div>
                        <div class="ba-options-group gridbox-page-tags-wrapper">
                            <div class="ba-group-element">
                                <div class="ba-tags">
                                    <label>
                                        <?php echo JText::_('TAGS'); ?>
                                    </label>
                                    <div class="meta-tags">
                                        <select style="display: none;" name="meta_tags[]" class="meta_tags" multiple></select>
                                        <ul class="picked-tags">
                                            <li class="search-tag">
                                                <input type="text" placeholder="<?php echo JText::_('TAGS'); ?>">
                                            </li>
                                        </ul>
                                        <ul class="all-tags">
                                            <?php foreach ($this->tags as $tag) {
                                                echo '<li data-id="'.$tag->id.'" style="display:none;">'.$tag->title.'</li>';
                                            } ?>
                                        </ul>
                                    </div>
                                </div>
                                <label class="ba-help-icon">
                                    <i class="zmdi zmdi-help"></i>
                                    <span class="ba-tooltip ba-help ba-hide-element">
                                        <?php echo JText::_('TAGS_DESC'); ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('THEME'); ?>
                                </label>
                                <div class="ba-custom-select theme-select visible-select-top">
                                    <input readonly value="" type="text">
                                    <input type="hidden" name="theme_list" class="theme-list" value="">
                                    <ul>
                                        <?php
                                        foreach ($this->themes as $theme) {
                                            $str = '<li data-value="'.$theme->id.'">';
                                            $str .= $theme->title.'</li>';
                                            echo $str;
                                        }
                                        ?>
                                    </ul>
                                    <i class="zmdi zmdi-caret-down"></i>
                                </div>
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('CLASS_SUFFIX'); ?>
                                </label>
                                <input type="text" class="page-class-suffix" 
                                    placeholder="<?php echo JText::_('CLASS_SUFFIX'); ?>" name="class_suffix">
                                <label class="ba-help-icon">
                                    <i class="zmdi zmdi-help"></i>
                                    <span class="ba-tooltip ba-help ba-hide-element">
                                        <?php echo JText::_('CLASS_SUFFIX_TOOLTIP'); ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div id="publishing-options" class="row-fluid tab-pane">
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('JFIELD_ACCESS_LABEL'); ?>
                                </label>
                                <div class="ba-custom-select access-select">
                                    <input readonly value="" type="text">
                                    <input type="hidden" name="access" id="access" value="">
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
                                    <span class="ba-tooltip ba-help ba-hide-element">
                                        <?php echo JText::_('JFIELD_ACCESS_DESC'); ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('START_PUBLISHING'); ?>
                                </label>
                                <div class="container-icon">
                                    <input type="text" name="published_on" id="published_on">
                                    <div class="icons-cell" id="calendar-button">
                                        <i class="zmdi zmdi-calendar-alt"></i>
                                    </div>
                                </div>
                                <label class="ba-help-icon">
                                    <i class="zmdi zmdi-help"></i>
                                    <span class="ba-tooltip ba-help ba-hide-element">
                                        <?php echo JText::_('START_DESC'); ?>
                                    </span>
                                </label>
                            </div>
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('END_PUBLISHING'); ?>
                                </label>
                                <div class="container-icon">
                                    <input type="text" name="published_down" id="published_down">
                                    <div class="icons-cell" id="calendar-down-button">
                                        <i class="zmdi zmdi-calendar-alt"></i>
                                    </div>
                                </div>
                                <label class="ba-help-icon">
                                    <i class="zmdi zmdi-help"></i>
                                    <span class="ba-tooltip ba-help ba-hide-element">
                                        <?php echo JText::_('END_DESC'); ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element ba-author-element-wrapper">
                                <label>
                                    <?php echo JText::_('AUTHOR'); ?>
                                </label>
                                <div class="ba-custom-author-select-wrapper">
                                    <div class="ba-custom-author-select select-post-author">
                                        <input readonly type="text" placeholder="<?php echo JText::_('AUTHOR'); ?>">
                                        <input type="hidden" name="author">
                                        <ul>
                                            <?php
                                            foreach ($this->authors as $author) {
                                                if (empty($author->avatar)) {
                                                    $author->avatar = 'components/com_gridbox/assets/images/default-user.png';
                                                }
                                                $str = '<li data-value="'.$author->id.'" data-image="'.JUri::root().$author->avatar;
                                                $str .= '"><span class="ba-author-avatar" ';
                                                $str .= 'style="background-image: url('.JUri::root().$author->avatar.')"></span>';
                                                $str .= $author->title.'</li>';
                                                echo $str;
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('JFIELD_LANGUAGE_LABEL'); ?>
                                </label>
                                <div class="ba-custom-select language-select visible-select-top">
                                    <input readonly value="" type="text">
                                    <input type="hidden" name="language" id="language" value="">
                                    <ul>
                                        <?php
                                        foreach ($this->languages as $key => $language) {
                                            $str = '<li data-value="'.$key.'">';
                                            $str .= $language.'</li>';
                                            echo $str;
                                        }
                                        ?>
                                    </ul>
                                    <i class="zmdi zmdi-caret-down"></i>
                                </div>
                                <label class="ba-help-icon">
                                    <i class="zmdi zmdi-help"></i>
                                    <span class="ba-tooltip ba-help ba-hide-element">
                                        <?php echo JText::_('LANGUAGE_DESC'); ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div id="seo-options" class="row-fluid tab-pane left-tabs-wrapper">
                        <div class="left-tabs">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#seo-general-options" data-toggle="tab">
                                        <i class="zmdi zmdi-settings"></i>
                                        <?php echo JText::_('BASIC'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#sharing-options" data-toggle="tab">
                                        <i class="zmdi zmdi-share"></i>
                                        <?php echo JText::_('SHARING'); ?>
                                    </a>
                                </li>
<?php
                            if (gridboxHelper::checkSystemApp('sitemap')) {
?>
                                <li>
                                    <a href="#sitemap-options" data-toggle="tab">
                                        <i class="zmdi zmdi-device-hub"></i>
                                        <?php echo JText::_('SITEMAP'); ?>
                                    </a>
                                </li>
<?php
                            }
?>
                            </ul>
                            <div class="tab-content">
                                <div id="seo-general-options" class="row-fluid tab-pane active">
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('BROWSER_PAGE_TITLE'); ?>
                                            </label>
                                            <input type="text" name="page_meta_title" class="page-meta-title"
                                                placeholder="<?php echo JText::_('BROWSER_PAGE_TITLE'); ?>">
                                        </div>
                                    </div>
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('JFIELD_META_DESCRIPTION_LABEL'); ?>
                                            </label>
                                            <textarea name="page_meta_description" class="page-meta-description"
                                                placeholder="<?php echo JText::_('JFIELD_META_DESCRIPTION_LABEL'); ?>"></textarea>
                                        </div>
                                    </div>
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('JFIELD_META_KEYWORDS_LABEL'); ?>
                                            </label>
                                            <textarea name="page_meta_keywords" class="page-meta-keywords"
                                                placeholder="<?php echo JText::_('JFIELD_META_KEYWORDS_LABEL'); ?>"></textarea>
                                        </div>
                                    </div>
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('JFIELD_METADATA_ROBOTS_LABEL'); ?>
                                            </label>
                                            <div class="ba-custom-select robots-select visible-select-top">
                                                <input readonly value="" type="text">
                                                <input type="hidden" name="robots" id="robots" value="">
                                                <ul>
                                                    <li data-value=""><?php echo JText::_('JGLOBAL_USE_GLOBAL'); ?></li>
                                                    <li data-value="index, follow"><?php echo JText::_('JGLOBAL_INDEX_FOLLOW'); ?></li>
                                                    <li data-value="noindex, follow"><?php echo JText::_('JGLOBAL_NOINDEX_FOLLOW'); ?></li>
                                                    <li data-value="index, nofollow"><?php echo JText::_('JGLOBAL_INDEX_NOFOLLOW'); ?></li>
                                                    <li data-value="noindex, nofollow">
                                                        <?php echo JText::_('JGLOBAL_NOINDEX_NOFOLLOW'); ?>
                                                    </li>
                                                </ul>
                                                <i class="zmdi zmdi-caret-down"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="sharing-options" class="row-fluid tab-pane">
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('IMAGE'); ?>
                                            </label>
                                            <div class="share-image-wrapper">
                                                <div class="image-field-tooltip"></div>
                                                <input type="text" class="share-image" name="share_image"
                                                    placeholder="<?php echo JText::_('IMAGE'); ?>" readonly="" onfocus="this.blur()">
                                                <i class="zmdi zmdi-camera"></i>
                                                <div class="reset disabled-reset reset-share-image">
                                                    <i class="zmdi zmdi-close"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('JGLOBAL_TITLE'); ?>
                                            </label>
                                            <input type="text" name="share_title" class="share-title"
                                                placeholder="<?php echo JText::_('JGLOBAL_TITLE'); ?>">
                                        </div>
                                    </div>
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('DESCRIPTION'); ?>
                                            </label>
                                            <textarea name="share_description" class="share-description"
                                                placeholder="<?php echo JText::_('DESCRIPTION'); ?>"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div id="sitemap-options" class="row-fluid tab-pane">
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('INCLUDE_ITEM'); ?>
                                            </label>
                                            <label class="ba-checkbox">
                                                <input type="checkbox" name="sitemap_include" value="1"
                                                    class="sitemap-include ba-hide-element set-group-display">
                                                <span></span>
                                            </label>
                                            <label class="ba-help-icon">
                                                <i class="zmdi zmdi-help"></i>
                                                <span class="ba-tooltip ba-help ba-hide-element">
                                                    <?php echo JText::_('INCLUDE_ITEM_TOOLTIP'); ?>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="ba-subgroup-element " style="--subgroup-childs:2;">
                                            <div class="ba-group-element">
                                                <label>Changefreq</label>
                                                <div class="ba-custom-select">
                                                    <input readonly="" onfocus="this.blur()" type="text">
                                                    <input type="hidden" name="changefreq" class="changefreq">
                                                    <i class="zmdi zmdi-caret-down"></i>
                                                    <ul>
                                                        <li data-value="always"><?php echo JText::_('ALWAYS'); ?></li>
                                                        <li data-value="hourly"><?php echo JText::_('HOURLY'); ?></li>
                                                        <li data-value="daily"><?php echo JText::_('DAILY'); ?></li>
                                                        <li data-value="weekly"><?php echo JText::_('INCLUDE_ITEM'); ?></li>
                                                        <li data-value="monthly"><?php echo JText::_('MONTHLY'); ?></li>
                                                        <li data-value="yearly"><?php echo JText::_('YEARLY'); ?></li>
                                                        <li data-value="never"><?php echo JText::_('NEVER'); ?></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="ba-group-element">
                                                <label>Priority</label>
                                                <div class="ba-range-wrapper">
                                                    <span class="ba-range-liner"></span>
                                                    <input type="range" class="ba-range" min="0" max="1" step="0.1">
                                                    <input type="number" data-callback="emptyCallback" name="priority" class="priority">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="permissions-options" class="row-fluid tab-pane permissions-options">
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label><?php echo JText::_('USERGROUP'); ?></label>
                                <div class="ba-custom-select select-permission-usergroup">
<?php
                                    $userGroups = gridboxHelper::getUserGroups();
?>
                                    <input readonly="" onfocus="this.blur()" type="text">
                                    <input type="hidden">
                                    <i class="zmdi zmdi-caret-down"></i>
                                    <ul>
<?php
                                    foreach ($userGroups as $key => $group) {
?>
                                        <li data-value="<?php echo $group->id; ?>" style="--permissions-level: <?php echo $group->level; ?>">
                                            <?php echo $group->title; ?>
                                        </li>
<?php
                                    }
?>
                                    </ul>
                                </div>
                            </div>
                            <div class="ba-subgroup-element visible-subgroup permission-action-wrapper">
                                <div class="ba-group-element">
                                    <label><?php echo JText::_('DELETE'); ?></label>
                                    <div class="ba-custom-select select-permission-action">
                                        <input readonly="" onfocus="this.blur()" type="text"
                                            value="<?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?>">
                                        <input type="hidden" data-key="core.delete">
                                        <i class="zmdi zmdi-caret-down"></i>
                                        <ul>
                                            <li data-value=""><?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?></li>
                                            <li data-value="1"><?php echo JText::_('JLIB_RULES_ALLOWED'); ?></li>
                                            <li data-value="0"><?php echo JText::_('JLIB_RULES_DENIED'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="calculated-permission-wrapper">
                                        <span class="calculated-permission" data-status="allowed">
                                            <i class="zmdi zmdi-check-circle"></i>
                                            <span class="ba-tooltip ba-top ba-hide-element">
                                                <?php echo JText::_('JLIB_RULES_ALLOWED'); ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div class="ba-group-element">
                                    <label><?php echo JText::_('EDIT'); ?></label>
                                    <div class="ba-custom-select select-permission-action">
                                        <input readonly="" onfocus="this.blur()" type="text"
                                            value="<?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?>">
                                        <input type="hidden" data-key="core.edit">
                                        <i class="zmdi zmdi-caret-down"></i>
                                        <ul>
                                            <li data-value=""><?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?></li>
                                            <li data-value="1"><?php echo JText::_('JLIB_RULES_ALLOWED'); ?></li>
                                            <li data-value="0"><?php echo JText::_('JLIB_RULES_DENIED'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="calculated-permission-wrapper">
                                        <span class="calculated-permission" data-status="not-allowed">
                                            <i class="zmdi zmdi-close-circle"></i>
                                            <span class="ba-tooltip ba-top ba-hide-element">
                                                <?php echo JText::_('JLIB_RULES_NOT_ALLOWED'); ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div class="ba-group-element">
                                    <label><?php echo JText::_('JACTION_EDITSTATE'); ?></label>
                                    <div class="ba-custom-select select-permission-action">
                                        <input readonly="" onfocus="this.blur()" type="text"
                                            value="<?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?>">
                                        <input type="hidden" data-key="core.edit.state">
                                        <i class="zmdi zmdi-caret-down"></i>
                                        <ul>
                                            <li data-value=""><?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?></li>
                                            <li data-value="1"><?php echo JText::_('JLIB_RULES_ALLOWED'); ?></li>
                                            <li data-value="0"><?php echo JText::_('JLIB_RULES_DENIED'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="calculated-permission-wrapper">
                                        <span class="calculated-permission" data-status="not-allowed">
                                            <i class="zmdi zmdi-close-circle"></i>
                                            <span class="ba-tooltip ba-top ba-hide-element">
                                                <?php echo JText::_('JLIB_RULES_NOT_ALLOWED'); ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="category-settings-dialog" class="ba-modal-lg modal hide" style="display:none">
        <div class="modal-header">
            <span class="ba-dialog-title"><?php echo JText::_('SETTINGS'); ?></span>
            <div class="modal-header-icon">
                <i class="zmdi zmdi-check apply-blog-settings"></i>
                <i class="zmdi zmdi-check category-settings-apply"></i>
                <i class="zmdi zmdi-close" data-dismiss="modal"></i>
            </div>
        </div>
        <div class="modal-body">
            <div class="general-tabs">
                <ul class="nav nav-tabs uploader-nav">
                    <li class="active">
                        <a href="#category-general-options" data-toggle="tab">
                            <i class="zmdi zmdi-settings"></i>
                            <?php echo JText::_('GENERAL'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="#category-publishing-options" data-toggle="tab">
                            <i class="zmdi zmdi-calendar-note"></i>
                            <?php echo JText::_('PUBLISHING'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="#category-seo-options" data-toggle="tab">
                            <i class="zmdi zmdi-globe"></i>
                            SEO
                        </a>
                    </li>
<?php
                    if ($user->authorise('core.admin', 'com_gridbox')) {
?>
                    <li>
                        <a href="#category-permissions-options" data-toggle="tab">
                            <i class="zmdi zmdi-account-circle"></i>
                            <?php echo JText::_('JCONFIG_PERMISSIONS_LABEL'); ?>
                        </a>
                    </li>
<?php
                    }
?>
                </ul>
                <div class="tabs-underline"></div>
                <div class="tab-content">
                    <div id="category-general-options" class="row-fluid tab-pane active">
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('JGLOBAL_TITLE'); ?><span class="required-fields-star">*</span>
                                </label>
                                <input type="text" name="category_title" class="category-title"
                                    placeholder="<?php echo JText::_('JGLOBAL_TITLE'); ?>">
                                    <input type="hidden" name="category-id" class="category-id">
                                    <input type="hidden" name="category_parent" class="category-parent">
                                <div class="ba-alert-container" style="display: none;">
                                    <i class="zmdi zmdi-alert-circle"></i>
                                    <span></span>
                                    <span class="ba-tooltip ba-top ba-hide-element">
                                        <?php echo JText::_('THIS_FIELD_REQUIRED'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('JFIELD_ALIAS_LABEL'); ?>
                                </label>
                                <input type="text" name="category_alias" class="category-alias"
                                    placeholder="<?php echo JText::_('JFIELD_ALIAS_LABEL'); ?>">
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('IMAGE'); ?>
                                </label>
                                <div class="share-image-wrapper">
                                    <div class="image-field-tooltip"></div>
                                    <input type="text" class="category-intro-image input-with-icon" name="category_intro_image"
                                        placeholder="<?php echo JText::_('IMAGE'); ?>" readonly="" onfocus="this.blur()">
                                    <i class="zmdi zmdi-camera"></i>
                                    <div class="reset disabled-reset reset-share-image">
                                        <i class="zmdi zmdi-close"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('THEME'); ?>
                                </label>
                                <div class="ba-custom-select blog-theme-select">
                                    <input readonly value="" type="text">
                                    <input type="hidden" name="blog_theme" class="blog-theme" value="">
                                    <ul>
                                        <?php
                                        foreach ($this->themes as $theme) {
                                            $str = '<li data-value="'.$theme->id.'">';
                                            $str .= $theme->title.'</li>';
                                            echo $str;
                                        }
                                        ?>
                                    </ul>
                                    <i class="zmdi zmdi-caret-down"></i>
                                </div>
                            </div>
                        </div>
                        <p class="ba-group-title"><?php echo JText::_('DESCRIPTION'); ?></p>
                        <div class="ba-options-group">
                            <div class="ba-group-element cke-editor-container">
                                <textarea class="category-description" name="category_description" data-key="description"></textarea>
                            </div>
                        </div>
                    </div>
                    <div id="category-publishing-options" class="row-fluid tab-pane">
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('JTOOLBAR_PUBLISH'); ?>
                                </label>
                                <label class="ba-checkbox ba-hide-checkbox">
                                    <input type="checkbox" name="category_publish" class="category-publish" value="1">
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('JFIELD_ACCESS_LABEL'); ?>
                                </label>
                                <div class="ba-custom-select category-access-select">
                                    <input readonly value="" type="text">
                                    <input type="hidden" name="category_access" id="category-access" value="">
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
                                    <span class="ba-tooltip ba-help ba-hide-element">
                                        <?php echo JText::_('JFIELD_ACCESS_DESC'); ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label>
                                    <?php echo JText::_('JFIELD_LANGUAGE_LABEL'); ?>
                                </label>
                                <div class="ba-custom-select category-language-select">
                                    <input readonly value="" type="text">
                                    <input type="hidden" name="category_language" id="category-language" value="">
                                    <i class="zmdi zmdi-caret-down"></i>
                                    <ul>
                                        <?php
                                        foreach ($this->languages as $key => $language) {
                                            $str = '<li data-value="'.$key.'">';
                                            $str .= $language.'</li>';
                                            echo $str;
                                        }
                                        ?>
                                    </ul>
                                </div>
                                <label class="ba-help-icon">
                                    <i class="zmdi zmdi-help"></i>
                                    <span class="ba-tooltip ba-help ba-hide-element">
                                        <?php echo JText::_('LANGUAGE_DESC'); ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div id="category-seo-options" class="row-fluid tab-pane left-tabs-wrapper">
                        <div class="left-tabs">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#category-seo-general-options" data-toggle="tab">
                                        <i class="zmdi zmdi-settings"></i>
                                        <?php echo JText::_('BASIC'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#category-sharing-options" data-toggle="tab">
                                        <i class="zmdi zmdi-share"></i>
                                        <?php echo JText::_('SHARING'); ?>
                                    </a>
                                </li>
<?php
                            if (gridboxHelper::checkSystemApp('sitemap')) {
?>
                                <li>
                                    <a href="#category-sitemap-options" data-toggle="tab">
                                        <i class="zmdi zmdi-device-hub"></i>
                                        <?php echo JText::_('SITEMAP'); ?>
                                    </a>
                                </li>
<?php
                            }
?>
                            </ul>
                            <div class="tab-content">
                                <div id="category-seo-general-options" class="row-fluid tab-pane active">
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('BROWSER_PAGE_TITLE'); ?>
                                            </label>
                                            <input type="text" name="category_meta_title" class="category-meta-title"
                                                placeholder="<?php echo JText::_('BROWSER_PAGE_TITLE'); ?>">
                                        </div>
                                    </div>
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('JFIELD_META_DESCRIPTION_LABEL'); ?>
                                            </label>
                                            <textarea name="category_meta_description" class="category-meta-description"
                                                placeholder="<?php echo JText::_('JFIELD_META_DESCRIPTION_LABEL'); ?>"></textarea>
                                        </div>
                                    </div>
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('JFIELD_META_KEYWORDS_LABEL'); ?>
                                            </label>
                                            <textarea name="category_meta_keywords" class="category-meta-keywords"
                                                placeholder="<?php echo JText::_('JFIELD_META_KEYWORDS_LABEL'); ?>"></textarea>
                                        </div>
                                    </div>
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('JFIELD_METADATA_ROBOTS_LABEL'); ?>
                                            </label>
                                            <div class="ba-custom-select category-robots-select visible-select-top">
                                                <input readonly value="" type="text">
                                                <input type="hidden" name="category_robots" id="category_robots" value="">
                                                <ul>
                                                    <li data-value=""><?php echo JText::_('JGLOBAL_USE_GLOBAL'); ?></li>
                                                    <li data-value="index, follow"><?php echo JText::_('JGLOBAL_INDEX_FOLLOW'); ?></li>
                                                    <li data-value="noindex, follow"><?php echo JText::_('JGLOBAL_NOINDEX_FOLLOW'); ?></li>
                                                    <li data-value="index, nofollow"><?php echo JText::_('JGLOBAL_INDEX_NOFOLLOW'); ?></li>
                                                    <li data-value="noindex, nofollow">
                                                        <?php echo JText::_('JGLOBAL_NOINDEX_NOFOLLOW'); ?>
                                                    </li>
                                                </ul>
                                                <i class="zmdi zmdi-caret-down"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="category-sharing-options" class="row-fluid tab-pane">
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('IMAGE'); ?>
                                            </label>
                                            <div class="share-image-wrapper">
                                                <div class="image-field-tooltip"></div>
                                                <input type="text" class="category-share-image" name="category_share_image"
                                                    placeholder="<?php echo JText::_('IMAGE'); ?>" readonly="" onfocus="this.blur()">
                                                <i class="zmdi zmdi-camera"></i>
                                                <div class="reset disabled-reset reset-share-image">
                                                    <i class="zmdi zmdi-close"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('JGLOBAL_TITLE'); ?>
                                            </label>
                                            <input type="text" name="category_share_title" class="category-share-title"
                                                placeholder="<?php echo JText::_('JGLOBAL_TITLE'); ?>">
                                        </div>
                                    </div>
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('DESCRIPTION'); ?>
                                            </label>
                                            <textarea name="category_share_description" class="category-share-description"
                                                placeholder="<?php echo JText::_('DESCRIPTION'); ?>"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div id="category-sitemap-options" class="row-fluid tab-pane">
                                    <div class="ba-options-group">
                                        <div class="ba-group-element">
                                            <label>
                                                <?php echo JText::_('INCLUDE_ITEM'); ?>
                                            </label>
                                            <label class="ba-checkbox">
                                                <input type="checkbox" name="category_sitemap_include" value="1"
                                                    class="sitemap-include ba-hide-element set-group-display">
                                                <span></span>
                                            </label>
                                            <label class="ba-help-icon">
                                                <i class="zmdi zmdi-help"></i>
                                                <span class="ba-tooltip ba-help ba-hide-element">
                                                    <?php echo JText::_('INCLUDE_ITEM_TOOLTIP'); ?>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="ba-subgroup-element " style="--subgroup-childs:2;">
                                            <div class="ba-group-element">
                                                <label>Changefreq</label>
                                                <div class="ba-custom-select">
                                                    <input readonly="" onfocus="this.blur()" type="text">
                                                    <input type="hidden" name="category_changefreq" class="changefreq">
                                                    <i class="zmdi zmdi-caret-down"></i>
                                                    <ul>
                                                        <li data-value="always"><?php echo JText::_('ALWAYS'); ?></li>
                                                        <li data-value="hourly"><?php echo JText::_('HOURLY'); ?></li>
                                                        <li data-value="daily"><?php echo JText::_('DAILY'); ?></li>
                                                        <li data-value="weekly"><?php echo JText::_('INCLUDE_ITEM'); ?></li>
                                                        <li data-value="monthly"><?php echo JText::_('MONTHLY'); ?></li>
                                                        <li data-value="yearly"><?php echo JText::_('YEARLY'); ?></li>
                                                        <li data-value="never"><?php echo JText::_('NEVER'); ?></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="ba-group-element">
                                                <label>Priority</label>
                                                <div class="ba-range-wrapper">
                                                    <span class="ba-range-liner"></span>
                                                    <input type="range" class="ba-range" min="0" max="1" step="0.1">
                                                    <input type="number" data-callback="emptyCallback" name="category_priority"
                                                        class="priority">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="category-permissions-options" class="row-fluid tab-pane permissions-options">
                        <div class="ba-options-group">
                            <div class="ba-group-element">
                                <label><?php echo JText::_('USERGROUP'); ?></label>
                                <div class="ba-custom-select select-permission-usergroup">
<?php
                                    $userGroups = gridboxHelper::getUserGroups();
?>
                                    <input readonly="" onfocus="this.blur()" type="text">
                                    <input type="hidden">
                                    <i class="zmdi zmdi-caret-down"></i>
                                    <ul>
<?php
                                    foreach ($userGroups as $key => $group) {
?>
                                        <li data-value="<?php echo $group->id; ?>" style="--permissions-level: <?php echo $group->level; ?>">
                                            <?php echo $group->title; ?>
                                        </li>
<?php
                                    }
?>
                                    </ul>
                                </div>
                            </div>
                            <div class="ba-subgroup-element visible-subgroup permission-action-wrapper">
                                <div class="ba-group-element">
                                    <label><?php echo JText::_('JACTION_CREATE'); ?></label>
                                    <div class="ba-custom-select select-permission-action">
                                        <input readonly="" onfocus="this.blur()" type="text"
                                            value="<?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?>">
                                        <input type="hidden" data-key="core.create">
                                        <i class="zmdi zmdi-caret-down"></i>
                                        <ul>
                                            <li data-value=""><?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?></li>
                                            <li data-value="1"><?php echo JText::_('JLIB_RULES_ALLOWED'); ?></li>
                                            <li data-value="0"><?php echo JText::_('JLIB_RULES_DENIED'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="calculated-permission-wrapper">
                                        <span class="calculated-permission" data-status="allowed">
                                            <i class="zmdi zmdi-check-circle"></i>
                                            <span class="ba-tooltip ba-top ba-hide-element">
                                                <?php echo JText::_('JLIB_RULES_ALLOWED'); ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div class="ba-group-element">
                                    <label><?php echo JText::_('DELETE'); ?></label>
                                    <div class="ba-custom-select select-permission-action">
                                        <input readonly="" onfocus="this.blur()" type="text"
                                            value="<?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?>">
                                        <input type="hidden" data-key="core.delete">
                                        <i class="zmdi zmdi-caret-down"></i>
                                        <ul>
                                            <li data-value=""><?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?></li>
                                            <li data-value="1"><?php echo JText::_('JLIB_RULES_ALLOWED'); ?></li>
                                            <li data-value="0"><?php echo JText::_('JLIB_RULES_DENIED'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="calculated-permission-wrapper">
                                        <span class="calculated-permission" data-status="allowed">
                                            <i class="zmdi zmdi-check-circle"></i>
                                            <span class="ba-tooltip ba-top ba-hide-element">
                                                <?php echo JText::_('JLIB_RULES_ALLOWED'); ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div class="ba-group-element">
                                    <label><?php echo JText::_('EDIT'); ?></label>
                                    <div class="ba-custom-select select-permission-action">
                                        <input readonly="" onfocus="this.blur()" type="text"
                                            value="<?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?>">
                                        <input type="hidden" data-key="core.edit">
                                        <i class="zmdi zmdi-caret-down"></i>
                                        <ul>
                                            <li data-value=""><?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?></li>
                                            <li data-value="1"><?php echo JText::_('JLIB_RULES_ALLOWED'); ?></li>
                                            <li data-value="0"><?php echo JText::_('JLIB_RULES_DENIED'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="calculated-permission-wrapper">
                                        <span class="calculated-permission" data-status="not-allowed">
                                            <i class="zmdi zmdi-close-circle"></i>
                                            <span class="ba-tooltip ba-top ba-hide-element">
                                                <?php echo JText::_('JLIB_RULES_NOT_ALLOWED'); ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div class="ba-group-element">
                                    <label><?php echo JText::_('JACTION_EDITSTATE'); ?></label>
                                    <div class="ba-custom-select select-permission-action visible-select-top">
                                        <input readonly="" onfocus="this.blur()" type="text"
                                            value="<?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?>">
                                        <input type="hidden" data-key="core.edit.state">
                                        <i class="zmdi zmdi-caret-down"></i>
                                        <ul>
                                            <li data-value=""><?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?></li>
                                            <li data-value="1"><?php echo JText::_('JLIB_RULES_ALLOWED'); ?></li>
                                            <li data-value="0"><?php echo JText::_('JLIB_RULES_DENIED'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="calculated-permission-wrapper">
                                        <span class="calculated-permission" data-status="not-allowed">
                                            <i class="zmdi zmdi-close-circle"></i>
                                            <span class="ba-tooltip ba-top ba-hide-element">
                                                <?php echo JText::_('JLIB_RULES_NOT_ALLOWED'); ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <div class="ba-group-element">
                                    <label><?php echo JText::_('JACTION_EDITOWN'); ?></label>
                                    <div class="ba-custom-select select-permission-action visible-select-top">
                                        <input readonly="" onfocus="this.blur()" type="text"
                                            value="<?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?>">
                                        <input type="hidden" data-key="core.edit.own">
                                        <i class="zmdi zmdi-caret-down"></i>
                                        <ul>
                                            <li data-value=""><?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?></li>
                                            <li data-value="1"><?php echo JText::_('JLIB_RULES_ALLOWED'); ?></li>
                                            <li data-value="0"><?php echo JText::_('JLIB_RULES_DENIED'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="calculated-permission-wrapper">
                                        <span class="calculated-permission" data-status="not-allowed">
                                            <i class="zmdi zmdi-close-circle"></i>
                                            <span class="ba-tooltip ba-top ba-hide-element">
                                                <?php echo JText::_('JLIB_RULES_NOT_ALLOWED'); ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <div class="ba-group-element">
                                    <label><?php echo JText::_('EDIT_LAYOUTS'); ?></label>
                                    <div class="ba-custom-select select-permission-action visible-select-top">
                                        <input readonly="" onfocus="this.blur()" type="text"
                                            value="<?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?>">
                                        <input type="hidden" data-key="core.edit.layouts">
                                        <i class="zmdi zmdi-caret-down"></i>
                                        <ul>
                                            <li data-value=""><?php echo JText::_('JLIB_FORM_VALUE_INHERITED'); ?></li>
                                            <li data-value="1"><?php echo JText::_('JLIB_RULES_ALLOWED'); ?></li>
                                            <li data-value="0"><?php echo JText::_('JLIB_RULES_DENIED'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="calculated-permission-wrapper">
                                        <span class="calculated-permission" data-status="not-allowed">
                                            <i class="zmdi zmdi-close-circle"></i>
                                            <span class="ba-tooltip ba-top ba-hide-element">
                                                <?php echo JText::_('JLIB_RULES_NOT_ALLOWED'); ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row-fluid">
        <div id="gridbox-container">
            <div id="gridbox-content">
                <?php include(JPATH_COMPONENT.'/views/layouts/sidebar.php'); ?>
                <div class="ba-main-view">
                    <div id="filter-bar">
                        <div class="app-title-wrapper">
                            <h1><?php echo $this->blog->title; ?></h1>
                            <span class="blog-icons">
<?php
                if ($appAssets->checkPermission('core.edit') || $appAssets->checkPermission('core.edit.layouts')
                    || $user->authorise('core.duplicate', 'com_gridbox')) {
?>
                                <span class="ba-dashboard-popover-trigger" data-target="blog-settings-context-menu">
                                    <i class="zmdi zmdi-settings"></i>
                                    <i class="zmdi zmdi-caret-down"></i>
                                    <span class="ba-tooltip ba-top ba-hide-element"><?php echo JText::_('SETTINGS'); ?></span>
                                </span>
<?php
                }
?>
                            </span>
                        </div>
                        <div class="filter-search-wrapper">
                            <div>
                                <input type="text" name="filter_search" id="filter_search"
                                       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                                       placeholder="<?php echo JText::_('JSEARCH_FILTER') ?>">
                                <i class="zmdi zmdi-search"></i>
                            </div>
                        </div>
                        <div class="filter-icons-wrapper">
                            <div class="pagination-limit">
                                <div class="ba-custom-select">
                                    <input readonly value="<?php echo $pagLimit[$limit]; ?>" type="text">
                                    <input type="hidden" name="limit" id="limit" value="<?php echo $limit; ?>">
                                    <i class="zmdi zmdi-caret-down"></i>
                                    <ul>
                                        <?php
                                        foreach ($pagLimit as $key => $lim) {
                                            $str = '<li data-value="'.$key.'">';
                                            if ($key == $limit) {
                                                $str .= '<i class="zmdi zmdi-check"></i>';
                                            }
                                            $str .= $lim.'</li>';
                                            echo $str;
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="reset-filtering">
                                <i class="zmdi zmdi-replay"></i>
                                <span class="ba-tooltip ba-top ba-hide-element"><?php echo JText::_('RESET_FILTER'); ?></span>
                            </div>
                            <div class="enable-custom-pages-order<?php echo $listOrder == 'order_list' ? ' active' : ''; ?>">
                                <i class="zmdi zmdi-format-line-spacing"></i>
                                <span class="ba-tooltip ba-top ba-hide-element"><?php echo JText::_('DRAG_DROP_SORT_ITEMS'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="span3">
                        <div class="category-list">
                            <a class="create-categery" href="#"<?php echo !$create ? ' data-permitted="false"': ''; ?>>
                                + <?php echo JText::_('CATEGORY'); ?>
                            </a>
                            <ul class="root-list">
                                <li class="root <?php echo $this->root; ?>">
                                    <a href="index.php?option=com_gridbox&view=apps&id=<?php echo $this->blog->id; ?>">
                                        <label><i class="zmdi zmdi-folder"></i></label><span><?php echo JText::_('ROOT'); ?></span>
                                    </a>
                                    <?php echo $this->drawCategoryList($this->categories); ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="span9 blog-layout">
                        <div class="main-table pages-list">
<?php
                            if ($this->blog->type != 'products') {
                                include JPATH_COMPONENT.'/views/apps/tmpl/apps-table.php';
                            } else {
                                include JPATH_COMPONENT.'/views/apps/tmpl/products-table.php';
                            }
?>
                        </div>
<?php
                        echo $this->pagination->getListFooter();
                        if ($create && !empty($this->category) && $this->blog->type != 'products') {
?>
                        <div class="ba-create-item">
                            <a href="<?php echo $url; ?>" target="_blank">
                                <i class="zmdi zmdi-file"></i>
                            </a>
                            <span class="ba-tooltip ba-top ba-hide-element align-center">
                                <?php echo JText::_('ADD_NEW_ITEM'); ?>
                            </span>
                        </div>
<?php
                        } else if ($create && !empty($this->category)) {
?>
                        <div class="ba-create-item ba-create-store-product">
                            <a href="#" target="_blank">
                                <i class="zmdi zmdi-file"></i>
                            </a>
                        </div>
                        <div class="ba-select-store-product-type">
                            <a href="<?php echo str_replace('{product_type}', 'physical', $url); ?>"
                                target="_blank" data-type="physical">
                                <i class="zmdi zmdi-shopping-basket"></i>
                                <span class="ba-tooltip ba-left ba-hide-element"><?php echo JText::_('PHYSICAL_PRODUCT'); ?></span>
                            </a>
                            <a href="<?php echo str_replace('{product_type}', 'digital', $url); ?>"
                                target="_blank" data-type="digital">
                                <i class="zmdi zmdi-case-download"></i>
                                <span class="ba-tooltip ba-left ba-hide-element"><?php echo JText::_('DIGITAL_PRODUCT'); ?></span>
                            </a>
                        </div>
<?php
                        }
                        if ($create && empty($this->category)) {
?>
                        <div class="ba-create-item ba-uncategorised">
                            <a href="#" onclick="return false;">
                                <i class="zmdi zmdi-file"></i>
                            </a>
                            <span class="ba-tooltip ba-top ba-hide-element"><?php echo JText::_('ADD_NEW_ITEM'); ?></span>
                        </div>
<?php
                        }
?>
                    </div>
                    <div>
                        <input type="hidden" name="context-item" value="" id="context-item" />
                        <input type="hidden" name="blog" value="<?php echo $this->blog->id; ?>" />
                        <input type="hidden" name="task" value="" />
                        <input type="hidden" value='<?php echo htmlspecialchars(json_encode($this->blog), ENT_QUOTES); ?>' id="blog-data">
                        <input type="hidden" name="boxchecked" value="0" />
                        <input type="hidden" name="ba_category" value="<?php echo $this->category; ?>">
                        <input type="hidden" name="category_order_list" value="1">
                        <input type="hidden" name="app_order_list" value="1">
                        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
                        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
                        <input type="hidden" name="filter_state" value="<?php echo $state; ?>">
                        <input type="hidden" name="author_filter" value="<?php echo $authorState; ?>">
                        <input type="hidden" name="theme_filter" value="<?php echo $themeState; ?>">
                        <input type="hidden" name="language_filter" value="<?php echo $languageState; ?>">
                        <input type="hidden" name="access_filter" value="<?php echo $accessState; ?>">
                        <input type="hidden" name="ba_view" value="apps">
<?php
                        echo JHtml::_('form.token');
?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<div class="ba-context-menu page-context-menu" style="display: none">
    <span class="page-settings"><i class="zmdi zmdi-settings"></i><?php echo JText::_('SETTINGS'); ?></span>
    <span class="blog-duplicate"><i class="zmdi zmdi-copy"></i><?php echo JText::_('DUPLICATE'); ?></span>
    <span class="page-move"><i class="zmdi zmdi-forward"></i><?php echo JText::_('MOVE_TO'); ?>...</span>
    <span class="blog-trash ba-group-element"><i class="zmdi zmdi-delete"></i><?php echo JText::_('TRASH'); ?></span>
</div>
<div class="ba-context-menu category-context-menu" style="display: none">
    <span class="category-settings"><i class="zmdi zmdi-settings"></i><?php echo JText::_('SETTINGS'); ?></span>
    <span class="category-duplicate"><i class="zmdi zmdi-copy"></i><?php echo JText::_('DUPLICATE'); ?></span>
    <span class="category-move"><i class="zmdi zmdi-forward"></i><?php echo JText::_('MOVE_TO'); ?>...</span>
    <span class="category-delete ba-group-element"><i class="zmdi zmdi-delete"></i><?php echo JText::_('DELETE'); ?></span>
</div>
<div class="ba-dashboard-apps-dialog blog-settings-context-menu">
    <div class="ba-dashboard-apps-body">
<?php
    if ($appAssets->checkPermission('core.edit')) {
?>
        <div class="ba-gridbox-dashboard-row blog-settings">
            <i class="zmdi zmdi-settings"></i>
            <span><?php echo JText::_('SETTINGS'); ?></span>
        </div>
<?php
    }
    if ($appAssets->checkPermission('core.edit.layouts')) {
?>
        <div class="ba-gridbox-dashboard-row context-link-wrapper">
            <a href="<?php echo $editBlog; ?>" class="default-action" target="_blank">
                <i class="zmdi zmdi-file-text"></i>
                <span><?php echo JText::_('CATEGORY_LIST_LAYOUT') ?></span>
            </a>
        </div>
        <div class="ba-gridbox-dashboard-row context-link-wrapper">
            <a href="<?php echo $editPostLayout; ?>" class="default-action single-post-layout" target="_blank">
                <i class="zmdi zmdi-file"></i>
                <span><?php echo JText::_('SINGLE_POST_LAYOUT') ?></span>
            </a>
        </div>
<?php
    }
    if ($user->authorise('core.duplicate', 'com_gridbox')) {
?>
        <div class="ba-gridbox-dashboard-row app-duplicate ba-group-element">
            <i class="zmdi zmdi-copy"></i>
            <span><?php echo JText::_('JTOOLBAR_DUPLICATE'); ?></span>
        </div>
<?php
    }
?>
    </div>
</div>
<?php
include(JPATH_COMPONENT.'/views/layouts/context.php');
include(JPATH_COMPONENT.'/views/layouts/photo-editor.php');
?>