<?php

namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote\Create;

class Index extends \Magebees\QuotationManagerPro\Controller\Adminhtml\Quote\Create
{
    
    public function execute()
    {
		if($this->_getSession()->getQuoteId())
		{
			//echo "if";die;
			$quote_id=$this->_getSession()->getQuoteId();
			$quote=$this->quoteHelper->loadQuoteById($quote_id);
			$customer_id=$quote->getCustomerId();
			$store_id=$quote->getStoreId();
			$this->_getSession()->setCustomerId($customer_id);
       		$this->_getSession()->setStoreId($store_id);		
			$title=sprintf("Edit Quote #%s",$quote->getIncrementId());
		}
		else
		{
			//echo "idasf";die;
		$this->quoteHelper->getBackendSession()->unsCurrentQuoteId();
		$this->_getSession()->setCustomerId(null);
       	$this->_getSession()->setStoreId(null);
			$title='Create New Quote';
		}
        $this->_initSession();	
        $resultPage = $this->resultPageFactory->create();
         $resultPage->setActiveMenu('Magebees_QuotationManagerPro::grid');
        $resultPage->getConfig()->getTitle()->prepend(__('Quotes'));
		 $customerId = $this->_getSession()->getCustomerId();
        $storeId = $this->_getSession()->getStoreId();
        $resultPage->getConfig()->getTitle()->prepend(__($title));
        return $resultPage;
    }
}
