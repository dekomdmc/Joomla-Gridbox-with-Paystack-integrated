<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class gridboxViewStoresettings extends JViewLegacy
{
    protected $about;
    protected $apps;
    protected $store;
    protected $customerInfo;
    
    public function display($tpl = null) 
    {
        $app = JFactory::getApplication();
        $this->about = gridboxHelper::aboutUs();
        $items = $this->get('Items');
        $this->store = $items[0];
        $doc = JFactory::getDocument();
        $this->apps = gridboxHelper::getApps();
        $this->customerInfo = $this->get('CustomerInfo');
        $doc->addStyleSheet('components/com_gridbox/assets/css/ba-admin.css?'.$this->about->version);
        
        parent::display($tpl);
    }

    public function preferences()
    {
        $url = 'index.php?option=com_config&amp;view=component&amp;component=com_gridbox&amp;path=';
        
        return $url;
    }
}