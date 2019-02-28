<?php

namespace Magebees\QuotationManagerPro\Block\Quote;
use \Magento\Framework\App\ObjectManager;
use \Magebees\QuotationManagerPro\Model\Quote\Status;

class Success extends \Magento\Framework\View\Element\Template
{     
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper	,
         \Magento\Framework\App\Request\Http $httpRequest,		 
        array $data = []
    ) {
       $this->httpRequest = $httpRequest;
		 $this->quoteHelper = $quoteHelper;	
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Success'));
    }	
	public function getQuoteId()
	{
		 $params=$this->httpRequest->getParams();
		$quote_id=$params['quote_id'];
		return $quote_id;
		
	}
	public function getIncrementId()
	{
		$quote_id=$this->getQuoteId();
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$quote_increment_id=$quote->getIncrementId();
		return $quote_increment_id;
	}
	public function getCustomerId()
	{
		$quote_id=$this->getQuoteId();
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$quote_customer_id=$quote->getCustomerId();
		return $quote_customer_id;
	}
	public function getContinueUrl()
	{
		return $this->quoteHelper->getBaseUrl();		 
	}	
	
}
