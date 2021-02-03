<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class gridboxControllerSystem extends JControllerAdmin
{
    public function getModel($name = 'system', $prefix = 'gridboxModel', $config = array())
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }

    public function applySettings()
    {
        gridboxHelper::checkUserEditLevel();
        $model = $this->getModel();
        $model->applySettings();
        gridboxHelper::ajaxReload('JLIB_APPLICATION_SAVE_SUCCESS');
    }
}