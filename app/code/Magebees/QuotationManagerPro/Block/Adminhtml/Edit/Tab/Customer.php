<?php
namespace Magebees\QuotationManagerPro\Block\Adminhtml\Edit\Tab;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
 
class Customer  extends \Magento\Framework\View\Element\Template implements TabInterface
{
    
    protected $_coreRegistry;
  
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
 

    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }
  
    public function getTabLabel()
    {
        return __('Customer Quote');
    }
  
    public function getTabTitle()
    {
        return __('Customer Quotation');
    }
    
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }
 
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }
   
    public function getTabClass()
    {
        return '';
    }
    
    public function getTabUrl()
    {
    //replace the tab with the url you want
        return $this->getUrl('quotation/quote/customertab', ['_current' => true]);
    }
    /**
     * Tab should be loaded trough Ajax call   
     */
    public function isAjaxLoaded()
    {
        return true;
    }

    }