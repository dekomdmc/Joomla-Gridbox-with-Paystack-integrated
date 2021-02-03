<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class gridboxViewthemes extends JViewLegacy
{
    protected $items;
    protected $state;
    protected $about;
    protected $apps;
    protected $plugins;

    public function display($tpl = null) 
    {
        $this->items = $this->get('Items');
        $this->apps = gridboxHelper::getApps();
        $this->plugins = $this->get('Plugins');
        foreach ($this->items as $key => $item) {
            if (empty($item->params)) {
                $item->params = '{}';
            }
            $params = json_decode($item->params);
            if (!isset($params->image) || empty($params->image)) {
                $params->image = 'components/com_gridbox/assets/images/default-theme.png';
            } else if (strpos($params->image, 'www.balbooa.com') === false) {
                $params->image = '../'.$params->image;
            }
            unset($this->items[$key]->params);
            $this->items[$key]->image = $params->image;
        }
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->about = gridboxHelper::aboutUs();
        $doc = JFactory::getDocument();
        $doc->addStyleSheet('components/com_gridbox/assets/css/ba-admin.css?'.$this->about->version);
        $doc->addScriptDeclaration('var installedPlugins = '.$this->plugins.';');
        //$this->addToolBar();
        foreach ($this->items as &$item) {
            $item->order_up = true;
            $item->order_dn = true;
        }
        
        parent::display($tpl);
    }

    protected function addToolBar ()
	{
        if (JFactory::getUser()->authorise('core.duplicate', 'com_gridbox')) {
            JToolBarHelper::custom('themes.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
        }
        if (JFactory::getUser()->authorise('core.delete', 'com_gridbox')) {
            JToolBarHelper::deleteList('', 'themes.delete');
        }
    }
    
    protected function getSortFields()
	{
		return array(
            'title' => JText::_('JGLOBAL_TITLE'),
			'id' => JText::_('JGRID_HEADING_ID')
		);
	}

    public function preferences()
    {
        $url = 'index.php?option=com_config&amp;view=component&amp;component=com_gridbox&amp;path=';
        
        return $url;
    }
}