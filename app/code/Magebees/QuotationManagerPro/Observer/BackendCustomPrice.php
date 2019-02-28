<?php

namespace Magebees\QuotationManagerPro\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product\Type;

class BackendCustomPrice implements ObserverInterface
{	
	 public function __construct(
		//  \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
          \Magento\Framework\Registry $coreRegistry
    ) {
		//  $this->quoteRepository = $quoteRepository;
      	  $this->_coreRegistry = $coreRegistry;
    }
	   public function execute(\Magento\Framework\Event\Observer $observer) {
      	   $item =$observer->getItems()[0];
		   $custom_price=$this->_coreRegistry->registry('quoteitem_custom_price');
		   if($custom_price){
		   $this->_coreRegistry->unregister('quoteitem_custom_price');
            $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
            //set your price here		
		   if($item->getProduct()->getTypeId() == Type::TYPE_BUNDLE)
		   {
			   foreach($item->getChildren() as $child)
			   {
			$child_count=count($item->getChildren()); 
			$child->setCustomPrice($custom_price/$child_count);
            $child->setOriginalCustomPrice($custom_price/$child_count);
            $child->getProduct()->setIsSuperMode(true);
			   }
		   }
		   else
		   {
			    $item->setCustomPrice($custom_price);
            $item->setOriginalCustomPrice($custom_price);
            $item->getProduct()->setIsSuperMode(true);
		   }
		  	$item->calcRowTotal();
		   	$quote=$item->getQuote();
		    $quote->setTotalsCollectedFlag(false)->collectTotals();	
		   }
		   return $this;
        }
}
