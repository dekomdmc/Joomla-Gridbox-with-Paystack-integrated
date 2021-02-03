<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

$app = JFactory::getApplication();
if (!JFactory::getUser()->authorise('core.manage', 'com_gridbox')) {
    return JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
JLoader::register('gridboxHelper', dirname(__FILE__) . '/helpers/gridbox.php');
include_once JPATH_ROOT.'/components/com_gridbox/helpers/assets.php';
gridboxHelper::prepareGridbox();
JHtml::addIncludePath(dirname(__FILE__) . '/helpers/html');
define('IMAGE_PATH', gridboxHelper::$website->image_path);
$controller = JControllerLegacy::getInstance('gridbox');
$controller->execute($app->input->getCmd('task', 'display'));
$controller->redirect();