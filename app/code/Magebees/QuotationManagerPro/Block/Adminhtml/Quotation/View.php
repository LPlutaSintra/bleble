<?php
namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation;

class View extends \Magento\Backend\Block\Widget\Form\Container
{
  
    protected $_blockGroup = 'Magebees_QuotationManagerPro';
    protected $_coreRegistry = null;
    
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
		\Magebees\QuotationManagerPro\Helper\Admin $backend_helper,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
        \Magento\Framework\Registry $registry,      
        array $data = []
    ) {        
        $this->_coreRegistry = $registry;     
        $this->_backendHelper = $backend_helper;   
		 $this->quoteHelper = $quoteHelper;	
        parent::__construct($context, $data);
    }
   
    protected function _construct()
    {
        $this->_objectId = 'quote_id';
        $this->_controller = 'adminhtml_quotation';
        $this->_mode = 'view';
        parent::_construct();      
		$this->buttonList->update('save', 'label', __('Save Quotation And Continue'));
		$this->buttonList->update('delete', 'label', __('Delete Quotation'));
		$this->buttonList->update('back', 'label', __('Back'));
        $this->removeButton('reset');       
        $this->setId('quotation_quote_view');
      	$quote = $this->quoteHelper->loadQuoteById($this->getQuoteId());	
		$quote_status=$quote->getStatus();
		$button_allow=$this->_backendHelper->checkAllowForBackendButton($quote_status);
		
		/*if($button_allow)
		{*/
		  $this->addButton('edit', array(
				'label' =>  __('Edit Quote'),
				'onclick' => "setLocation('" . $this->getUrl('*/*/editQuote') . "')"
			));
		//}
		
		$this->addButton('print', array(
				'label' =>  __('Print PDF'),
				'onclick' => "setLocation('" . $this->getUrl('*/*/printPdf') . "')"
			));
		
        if (!$quote) {
            return;
        }		
    }    
    public function getQuote()
    {
        return $this->_coreRegistry->registry('quotation_quote');
    }  
    public function getQuoteId()
    {
        return $this->getQuote() ? $this->getQuote()->getQuoteId() : null;
    }   
    public function getUrl($params = '', $params2 = [])
    {
        $params2['quote_id'] = $this->getQuoteId();
        return parent::getUrl($params, $params2);
    }    
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    public function getBackUrl()
    {       
        return $this->getUrl('quotation/quote/index');
    }

 
}
