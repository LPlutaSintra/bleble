<?php
namespace Magebees\QuotationManagerPro\Block;

class CustomerForm extends \Magebees\QuotationManagerPro\Block\Quote\AbstractQuote
{
    protected $_collection;
	 protected $pager;
	 public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magebees\QuotationManagerPro\Model\Session $quotationSession,
       \Magento\Customer\Model\Address $customerAddress,
      
        array $data = []
    ) {
		  $this->_customerAddress = $customerAddress;     
        parent::__construct($context, $customerSession, $quotationSession, $data); 
    }
 public function getQuoteId()
	{
		return $this->getQuote()->getId();
	}
	
}
