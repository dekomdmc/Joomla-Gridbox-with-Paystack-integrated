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

class gridboxControllerAppslist extends JControllerAdmin
{
    public function getModel($name = 'appslist', $prefix = 'gridboxModel', $config = array())
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }

    public function getSystemApps()
    {
        $model = $this->getModel();
        $systemApps = $model->getSystemApps();
        $str = json_encode($systemApps);
        echo $str;
        exit();
    }

    public function addSystemApp()
    {
        $input = JFactory::getApplication()->input;
        $type = $input->post->get('type', '', 'string');
        if (!empty($type)) {
            $model = $this->getModel();
            $model->addSystemApp($type);
        }
        print_r($type);exit;
        exit();
    }
}