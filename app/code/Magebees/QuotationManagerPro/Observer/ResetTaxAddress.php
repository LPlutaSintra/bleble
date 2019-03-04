<?php

namespace Magebees\QuotationManagerPro\Observer;

use Magento\Framework\Event\ObserverInterface;
class ResetTaxAddress implements ObserverInterface
{
	
	 public function __construct(
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,		
		 \Magento\Customer\Model\Session $customerSession,
		 \Magebees\QuotationManagerPro\Model\QuoteItemFactory $quoteItemFactory     
    ) {
     
		 $this->quoteHelper = $quoteHelper;		
		   $this->customerSession = $customerSession;
		 $this->_quoteItemFactory = $quoteItemFactory;
		  
    }
	   public function execute(\Magento\Framework\Event\Observer $observer)
    {
		
		$customer=$observer->getCustomer();		
		$quote_id=$observer->getQuoteId();		
			/*start reset the setDefaultTaxShippingAddress after submit quote*/		
					$default_tax_ship_add=$this->customerSession->getDefaultTaxShippingAddress();
					if(isset($default_tax_ship_add))
					{
						
						$items=$this->quoteHelper->getItemsByQuoteId($quote_id);
		//	$items->addFieldToFilter('parent_item_id', ['null' => true]);
			foreach($items as $item)
			{
				$item_id=$item->getId();
				$product_id=$item->getProductId();	
			//	$storeId = $this->_storeManager->getStore()->getId();
				$product = $this->quoteHelper->loadProduct($product_id);
				
				$quotationtierQty=$this->quoteHelper->getDynamicQuoteQty($item_id,$quote_id,$product_id);
if(count($quotationtierQty->getData())>0){
		foreach($quotationtierQty as $qty):
			$qty->setRequestQtyPrice($item->getPrice());	
			$qty_price_incl_tax=$this->quoteHelper->getProductPriceInclTax($product,$item->getPrice(),true);
			$qty->setReqQtyPriceInclTax($qty_price_incl_tax);		
			$qty->save();
		endforeach;
	}	
				 
		/*Start for save include tax price in 'magebees_quote_item' table for  when change the address*/
				$price_incl_tax=$this->quoteHelper->getProductPriceInclTax($product,$item->getPrice(),true);
			//	echo $item_id;die;
				$quoteItem=$this->_quoteItemFactory->create()->load($item_id,'id');
				$quoteItem->setPriceInclTax($price_incl_tax);
				$quoteItem->save();
			}
						
					}
					$addresses=$customer->getAddresses();
					/* if (isset($addresses)) {
					    foreach ($addresses as $address) {
							 if ($address->isDefaultShipping()) {
                       
                        $this->customerSession->setDefaultTaxShippingAddress(
                            [
                                'country_id' => $address->getCountryId(),
                                'region_id'  => $address->getRegion() ? $address->getRegion()->getRegionId() : null,
                                'postcode'   => $address->getPostcode(),
                            ]
                        );
                    }
					if ($address->isDefaultBilling()) {

                        $this->customerSession->setDefaultTaxBillingAddress(
                            [
                                'country_id' => $address->getCountryId(),
                                'region_id'  => $address->getRegion() ? $address->getRegion()->getRegionId() : null,
                                'postcode'   => $address->getPostcode(),
                            ]
                        );
                    }
						}
					 }*/
	/*end reset the setDefaultTaxShippingAddress after submit quote*/					
			
		
	}
    
}
