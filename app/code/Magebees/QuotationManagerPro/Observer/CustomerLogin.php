<?php

namespace Magebees\QuotationManagerPro\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface
{
	
	 public function __construct(
         \Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory,
		 \Magebees\QuotationManagerPro\Model\Session $quoteSession,
		 \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteSession = $quoteSession;
		$this->messageManager = $messageManager;
    }
     public function execute(\Magento\Framework\Event\Observer $observer)
    {
          try {
            $this->quoteSession->loadCustomerQuote();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Load customer quote error'));
        }
      
        
    }
}
