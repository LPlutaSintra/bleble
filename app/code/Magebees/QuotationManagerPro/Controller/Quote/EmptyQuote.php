<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
class EmptyQuote extends \Magebees\QuotationManagerPro\Controller\Quote
{   
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Quote'));
        return $resultPage;
    }
}
