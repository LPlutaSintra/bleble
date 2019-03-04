<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
class Index extends \Magebees\QuotationManagerPro\Controller\Quote
{   
    public function execute()
    {		
        if ($invalid = $this->isInValidQuoteRequest()) {
            $this->_quoteSession->clearQuote();
            return $this->_redirect('quotation/quote/emptyQuote');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Quote'));
        return $resultPage;
    }

    /**
     * Checks if the request is valid     
     */
    public function isInValidQuoteRequest()
    {
        $quoteId = $this->_quoteSession->getQuoteId();
		if($quoteId != null )
		{			
        $quote = $this->quote->getQuote();
        //return $quoteId == null || !($quote && $quote->hasItems() && $quote->getIsActive());
       return $quoteId == null || !($quote && $quote->hasItems());
		}
        return $quoteId == null ;
    }
}
