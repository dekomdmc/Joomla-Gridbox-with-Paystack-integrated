<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

ob_start();
?>
<div class="control-group" id="select-app" style="display: none;">
    <div class="control-label">
        <label>
            <?php echo JText::_('APP'); ?>
        </label>
    </div>
    <div class="controls">
        <span class="input-append">
            <input type="text" class="input-medium" id="gridbox_app_name" value="<?php echo $appTitle; ?>" disabled="disabled" size="35"
                placeholder="<?php echo JText::_('APP'); ?>">
            <a href="#gridbox_app_modal" class="btn" role="button" data-toggle="modal">
                <span class="icon-file"></span>
                <?php echo JText::_('JSELECT'); ?>
            </a>
        </span>
        <input type="hidden" id="gridbox_app_id" value="<?php echo $appId; ?>">
        <div id="gridbox_app_modal" class="modal hide fade" style="width: 740px; height: 545px; margin-left: -370px; overflow: hidden;">
            <div class="modal-body">
                <iframe src="index.php?option=com_gridbox&view=apps&layout=modal&tmpl=component"></iframe>
            </div>
        </div>
    </div>
</div>
<?php
$out = ob_get_contents();
ob_end_clean();