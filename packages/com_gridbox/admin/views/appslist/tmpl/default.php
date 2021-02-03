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
$createUrl = gridboxHelper::getEditorLink().'&app_id=';
$search = $this->escape($this->state->get('filter.search'));
?>
<script src="<?php echo JUri::root(); ?>administrator/components/com_gridbox/assets/js/sortable.js" type="text/javascript"></script>
<script src="components/com_gridbox/assets/js/ba-admin.js?<?php echo $this->about->version; ?>" type="text/javascript"></script>
<script type="text/javascript">
    if (!window.Joomla) {
        window.Joomla = {};
    }
</script>
<?php
include(JPATH_COMPONENT.'/views/layouts/notification.php');
?>
<input type="hidden" value="<?php echo JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'); ?>" class="jlib-selection">
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
<div id="ba-gridbox-apps-dialog" class="ba-modal-lg modal hide" style="display:none">
    <div class="modal-header">
        <span class="ba-dialog-title"><?php echo JText::_('APPS'); ?></span>
        <div class="modal-header-icon">
            <i class="zmdi zmdi-close" data-dismiss="modal"></i>
        </div>
    </div>
    <div class="modal-body">
        <div class="ba-filter-bar">
            <input type="text" class="search-gridbox-apps">
            <i class="zmdi zmdi-search"></i>
        </div>
        <div class="ba-group-wrapper gridbox-apps-wrapper">
<?php
        $list = $this->getAppsList();
        foreach ($list as $value) {
            $attr = $user->authorise('core.create', 'com_gridbox') ? 'data-'.($value->system ? 'system' : 'type').'="'.$value->type.'"' : '';
?>
            <div class="gridbox-app-element gridbox-app-item-<?php echo $value->type; ?>" <?php echo $attr; ?>>
                <div class="gridbox-app-item-body">
                    <i class="<?php echo $value->icon; ?>"></i>
                    <span class="ba-title"><?php echo $value->title; ?></span>
                </div>
            </div>
<?php
        }
?>
        </div>
    </div>
</div>
<form autocomplete="off" action="<?php echo JRoute::_('index.php?option=com_gridbox&view=appslist'); ?>"
    enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
    <div id="update-dialog" class="modal hide" style="display:none">
        <div class="modal-header">
            <h3><?php echo JText::_('ACCOUNT_LOGIN') ?></h3>
        </div>
        <div class="modal-body">
            <div id="form-update">
                
            </div>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div class="row-fluid">
        <div id="gridbox-container">
            <div id="gridbox-content">
                <?php include(JPATH_COMPONENT.'/views/layouts/sidebar.php'); ?>
                <div class="ba-main-view">
                    <div id="filter-bar">
                        <div class="app-title-wrapper">
                            <h1><?php echo JText::_('APPS') ?></h1>
                        </div>
                        <div class="filter-search-wrapper">
                            <div>
                                <input type="text" name="filter_search" id="filter_search"
                                       value="<?php echo $search; ?>"
                                       placeholder="<?php echo JText::_('JSEARCH_FILTER') ?>">
                                <i class="zmdi zmdi-search"></i>
                            </div>
                        </div>
                    </div>
                    <div class="apps-list-wrapper">
<?php
                    $pages = strtolower(JText::_('PAGES'));
                    if (empty($search) || strpos($pages, $search) !== false) {
?>
                        <div class="gridbox-app-item gridbox-app-item-single">
                            <div class="gridbox-app-item-body">
                                <a href="index.php?option=com_gridbox&view=pages">
                                    <i class="zmdi zmdi-file"></i>
                                    <span><?php echo JText::_('PAGES'); ?></span>
                                </a>
                            </div>
                            <div class="gridbox-app-item-footer">
<?php
                            if ($user->authorise('core.create', 'com_gridbox')) {
?>
                                <a class="gridbox-app-item-footer-action footer-action-create"
                                    href="<?php echo $createUrl.'0&id='; ?>" target="_blank">
                                    <i class="zmdi zmdi-plus-circle"></i>
                                    <span class="ba-tooltip ba-bottom ba-hide-element"><?php echo JText::_('ADD_NEW_ITEM'); ?></span>
                                </a>
<?php
                            }
?>
                                <a class="gridbox-app-item-footer-action footer-action-view" href="index.php?option=com_gridbox&view=pages">
                                    <i class="zmdi zmdi-eye"></i>
                                    <span class="ba-tooltip ba-bottom ba-hide-element"><?php echo JText::_('VIEW'); ?></span>
                                </a>
                            </div>
                        </div>
<?php
                    }
                    foreach ($this->items as $app) {
                        $view = $app->type == 'single' ? 'single' : 'apps';
                        $appType = $app->type != 'system_apps' ? $app->type : $app->title;
                        $appTitle = $app->title;
                        if ($app->type == 'system_apps') {
                            $appTitle = str_replace('-', '_', $app->title);
                            $appTitle = strtoupper($appTitle);
                            $appTitle = JText::_($appTitle);
                        }
                        if ($app->type == 'system_apps' && $app->title == 'sitemap') {
                            $appTitle = 'XML '.$appTitle;
                        }
                        $target = '_self';
                        if ($app->type == 'system_apps' && $app->title == 'comments') {
                            $viewLink = 'index.php?option=com_gridbox&view=comments';
                        } else if ($app->type == 'system_apps' && $app->title == 'preloader') {
                            $viewLink = gridboxHelper::getEditorLink().'&edit_type=system&id=4';
                            $target = '_blank';
                        } else {
                            $viewLink = 'index.php?option=com_gridbox&view='.$view.'&id='.$app->id;
                        }
?>
                        <div class="gridbox-app-item gridbox-app-item-<?php echo $appType; ?>" data-type="<?php echo $app->type; ?>">
                            <div class="gridbox-app-item-body">
<?php
                            if ($app->type != 'system_apps' || $app->title == 'comments' || $app->title == 'preloader') {
?>
                                <a href="<?php echo $viewLink; ?>" target="<?php echo $target; ?>">
<?php
                            }
?>
                                    <i class="<?php echo gridboxHelper::getIcon($app); ?>"></i>
                                    <span><?php echo $appTitle; ?></span>
<?php
                            if ($app->type != 'system_apps' || $app->title == 'comments' || $app->title == 'preloader') {
?>
                                </a>
<?php
                            }
?>
                            </div>
                            <div class="gridbox-app-item-footer">
<?php
                            if ($app->type != 'system_apps' && $user->authorise('core.create', 'com_gridbox.app.'.$app->id)) {
?>
                                <a class="gridbox-app-item-footer-action footer-action-create"
                                    href="<?php echo $createUrl.$app->id.'&id='; ?>" target="_blank">
                                    <i class="zmdi zmdi-plus-circle"></i>
                                    <span class="ba-tooltip ba-bottom ba-hide-element"><?php echo JText::_('ADD_NEW_ITEM'); ?></span>
                                </a>
<?php
                            }
?>
<?php
                            if ($app->type != 'system_apps' || $app->title == 'comments' || $app->title == 'preloader') {
?>
                                <a class="gridbox-app-item-footer-action footer-action-view"
                                    href="<?php echo $viewLink; ?>" target="<?php echo $target; ?>">
                                    <i class="zmdi zmdi-eye"></i>
                                    <span class="ba-tooltip ba-bottom ba-hide-element"><?php echo JText::_('VIEW'); ?></span>
                                </a>
<?php
                            }
                            if (($app->type != 'system_apps' && $user->authorise('core.delete', 'com_gridbox.app.'.$app->id))
                                || ($app->type == 'system_apps' && $user->authorise('core.delete', 'com_gridbox'))) {
?>
                                <a class="gridbox-app-item-footer-action footer-action-delete delete-gridbox-app-item"
                                    href="#" data-id="<?php echo $app->id; ?>">
                                    <i class="zmdi zmdi-delete"></i>
                                    <span class="ba-tooltip ba-bottom ba-hide-element"><?php echo JText::_('DELETE'); ?></span>
                                </a>
<?php
                            }
?>
                            </div>
                        </div>
<?php
                    }
?>
                        <div class="gridbox-app-item add-item add-new-app">
                            <div class="gridbox-app-item-body">
                                <i class="zmdi zmdi-plus"></i>
                                <span><?php echo JText::_('ADD_NEW_APP'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div>
                    	<input type="hidden" name="context-item" value="" id="context-item" />
                        <input type="hidden" name="task" value="" />
                        <input type="hidden" name="app_order_list" value="1">
                        <input type="hidden" name="boxchecked" value="0" />
                        <input type="hidden" name="ba_view" value="appslist">
                        <?php echo JHtml::_('form.token'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?php include(JPATH_COMPONENT.'/views/layouts/context.php'); ?>
<?php include(JPATH_COMPONENT.'/views/layouts/photo-editor.php'); ?>