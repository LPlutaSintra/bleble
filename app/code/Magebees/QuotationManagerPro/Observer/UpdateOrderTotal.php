<?php

namespace Magebees\QuotationManagerPro\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateOrderTotal implements ObserverInterface
{
	
	 public function __construct(
		  // \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
		 \Magento\Sales\Model\AdminOrder\Create $orderModel          
    ) {
     
		  //  $this->quoteRepository = $quoteRepository;
		    $this->orderModel = $orderModel;
    }
	   public function execute(\Magento\Framework\Event\Observer $observer) {		 
		    $controller = $observer->getControllerAction();		
		    $quote=$this->orderModel->getQuote();    	   
		   $quote->setTotalsCollectedFlag(false)->collectTotals();        	
        }
}
