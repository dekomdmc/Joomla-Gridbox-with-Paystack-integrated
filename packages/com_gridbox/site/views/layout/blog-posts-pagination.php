<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

ob_start();
$prev = $active == 0 ? 1 : $active;
$next = $active == $pages - 1 ? $pages : $active + 2;
?>
<div class="ba-blog-posts-pagination">
    <span class="<?php echo $active == 0 ? 'disabled' : ''; ?>">
        <a href="<?php echo JRoute::_($url.'&page=1'); ?>"
            <?php echo $active == 0 ? 'onclick="return false;"' : ''; ?>>
            <i class="zmdi zmdi-skip-previous"></i>
        </a>
    </span>
    <span class="<?php echo $active == 0 ? 'disabled' : ''; ?>">
        <a href="<?php echo JRoute::_($url.'&page='.$prev); ?>"
            <?php echo $active == 0 ? 'onclick="return false;"' : ''; ?>>
            <i class="zmdi zmdi-fast-rewind"></i>
        </a>
    </span>
<?php
    for ($i = $start; $i < $max; $i++) {
?>
    <span class="<?php echo $i == $active ? 'active' : ''; ?>">
        <a href="<?php echo JRoute::_($url.'&page='.($i + 1)); ?>"
            <?php echo $i == $active ? 'onclick="return false;"' : ''; ?>>
            <?php echo ($i + 1); ?>
        </a>
    </span>
<?php
    }
?>
    <span class="<?php echo $active == $pages - 1 ? 'disabled' : ''; ?>">
        <a href="<?php echo JRoute::_($url.'&page='.$next); ?>"
            <?php echo $active == $pages - 1 ? 'onclick="return false;"' : ''; ?>>
            <i class="zmdi zmdi-fast-forward"></i>
        </a>
    </span>
    <span class="<?php echo $active == $pages - 1 ? 'disabled' : ''; ?>">
        <a href="<?php echo JRoute::_($url.'&page='.$pages); ?>"
            <?php echo $active == $pages - 1 ? 'onclick="return false;"' : ''; ?>>
            <i class="zmdi zmdi-skip-next"></i>
        </a>
    </span>
</div>
<?php
$out = ob_get_contents();
ob_end_clean();