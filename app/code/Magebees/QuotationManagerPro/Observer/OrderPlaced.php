<?php

namespace Magebees\QuotationManagerPro\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magebees\QuotationManagerPro\Model\Quote\Status;

class OrderPlaced implements ObserverInterface
{
	
	 public function __construct(
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		  \Magento\Customer\Model\Session $customerSession,
		  \Magento\Customer\Model\CustomerFactory $customerFactory,
		 \Magento\Framework\Session\SessionManager $session      
    ) {
     
		 $this->quoteHelper = $quoteHelper;
		 $this->_customerSession=$customerSession;
		   $this->_customerFactory = $customerFactory;
		   $this->session =$session;
    }
	   public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$quote_id=$this->session->getProcessingQuoteId();
		if($quote_id)
		{
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$quote->setStatus(Status::PROPOSAL_ORDERED);
		$quote->save();
		$this->session->setProcessingQuoteId(null);
		$this->session->unsProcessingQuoteId();
			
		/**Start reset customer default address after place order*/
		$all_address_data=$this->_customerSession->getCustomer()->getAddresses();
		$default_bill_id=$this->_customerSession->getMainDefaultBilling();
		$default_shipping_id=$this->_customerSession->getMainDefaultShipping();
			$customer = $this->_customerFactory->create()->load($quote->getCustomerId());   
		$customer->setDefaultBilling($default_bill_id);
		$customer->setDefaultShipping($default_shipping_id);
		$customer->save();
		/** End reset customer default address after place order */	
	
		}
    }
}
