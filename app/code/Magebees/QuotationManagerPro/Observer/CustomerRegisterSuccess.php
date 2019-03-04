<?php

namespace Magebees\QuotationManagerPro\Observer;

use Magento\Framework\Event\ObserverInterface;
class CustomerRegisterSuccess implements ObserverInterface
{
	
	 public function __construct(
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,			 \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteCustomer\CollectionFactory $quoteCustomerCollFactory      
    ) {
     
		 $this->quoteHelper = $quoteHelper;		
		 $this->_quoteCustomerCollFactory = $quoteCustomerCollFactory;
		  
    }
	   public function execute(\Magento\Framework\Event\Observer $observer)
    {
		
		$customer_email=$observer->getCustomer()->getEmail();		
		$customer_id=$observer->getCustomer()->getId();		
		$quote_customers=$this->_quoteCustomerCollFactory->create()->addFieldToFilter('email',$customer_email);
	$quote_customer_data=$quote_customers->getData();
	if(count($quote_customer_data))
	{
		foreach($quote_customer_data as $quote_data)
		{
			$quote_id=$quote_data['quote_id'];
			$quoteCustomer=$this->quoteHelper->loadQuoteCustomerByQuoteId($quote_id);
			$quoteCustomer->setCustomerId($customer_id)->save();
			$quote=$this->quoteHelper->loadQuoteById($quote_id);
			$quote->setCustomerId($customer_id)->save();
		}
		
	}
		
	}
    
}
