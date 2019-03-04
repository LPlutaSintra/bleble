<?php

namespace Magebees\QuotationManagerPro\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magebees\QuotationManagerPro\Model\Quote\Status;

class BackendOrderPlaced implements ObserverInterface
{
	
	 public function __construct(
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,		
		 \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteSession 
    ) {
     
		 $this->quoteHelper = $quoteHelper;		
		   $this->session =$quoteSession;
    }
	   public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$quote_id=$this->session->getProcessQuoteId();
		if($quote_id)
		{			
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$quote->setStatus(Status::PROPOSAL_ORDERED);
		$quote->save();
		$this->session->setProcessQuoteId(null);
		$this->session->unsProcessQuoteId();	
		}
    }
}
