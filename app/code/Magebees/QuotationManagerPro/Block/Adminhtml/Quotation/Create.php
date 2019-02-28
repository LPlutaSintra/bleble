<?php
namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation;

class Create extends \Magento\Backend\Block\Widget\Form\Container
{
  
    protected $_blockGroup = 'Magebees_QuotationManagerPro';
    protected $_coreRegistry = null;
    
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
		\Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteSession,
        \Magento\Framework\Registry $registry,      
        array $data = []
    ) {        
        $this->_coreRegistry = $registry;     
        $this->_quoteSession = $quoteSession;     
        parent::__construct($context, $data);
    }
   
    protected function _construct()
    {
        $this->_objectId = 'quote_id';
        $this->_controller = 'adminhtml_quotation';
        $this->_mode = 'view';
        parent::_construct();
        $this->removeButton('delete');
        $this->removeButton('reset');
    //   $this->removeButton('save');
		 $this->buttonList->update('save', 'label', __('Save Quote'));
        $this->buttonList->update('save', 'onclick', 'quote.submit()');
        $this->buttonList->update('save', 'class', 'primary');
        // Temporary solution, unset button widget. Will have to wait till jQuery migration is complete
        $this->buttonList->update('save', 'data_attribute', []);
        $this->buttonList->update('save', 'id', 'submit_quote_top_button');
		$customerId=$this->_quoteSession->getCustomerId();
		$storeId=$this->_quoteSession->getStoreId();
        if ($customerId === null || !$storeId) {
            $this->buttonList->update('save', 'style', 'display:none');
        }
        $this->setId('quotation_quote_view');
        $quote = $this->getQuote();

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
	/* public function getHeaderHtml()
    {
        $out = '<div id="quote-header">' . $this->getLayout()->createBlock(
            \Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Header::class
        )->toHtml() . '</div>';
        return $out;
    }*/

 
}
