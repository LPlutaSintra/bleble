<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Model\Quote\Item;

use Magebees\QuotationManagerPro\Model\QuoteItem;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\JsonValidator;

class CompareItem
{
   
    public function __construct(
       
		 \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteRequest\CollectionFactory $quoteRequestCollFactory
    ) {
          
        $this->_quoteRequestCollFactory = $quoteRequestCollFactory;
    }

    public function compare(QuoteItem $target, QuoteItem $compared)
    {
        if ($target->getProductId() != $compared->getProductId()) {
            return false;
        }
        $targetItemOptions = $this->getOptionsForCompare($target);
        $comparedItemOptions = $this->getOptionsForCompare($compared);
        if (array_diff_key($targetItemOptions, $comparedItemOptions) != array_diff_key($comparedItemOptions, $targetItemOptions)
        ) {
				/**start remove quote item tier quantity when update with same option*/
				$quote_requestes = $this->_quoteRequestCollFactory->create();           
				$quote_requestes->addFieldToFilter('item_id',$target->getId())
				->addFieldToFilter('quote_id',$compared->getQuoteId())
				->addFieldToFilter('is_default',0);
						foreach($quote_requestes as $quote_request)
						{
							$quote_request->delete();
						}
			/**end remove quote item tier quantity when update with same option*/
            return false;
        }
        foreach ($targetItemOptions as $name => $value) {
            if ($comparedItemOptions[$name] != $value) {
				/**start remove quote item tier quantity when update with same option*/
				$quote_requestes = $this->_quoteRequestCollFactory->create();           
				$quote_requestes->addFieldToFilter('item_id',$target->getId())
				->addFieldToFilter('quote_id',$compared->getQuoteId())
				->addFieldToFilter('is_default',0);
						foreach($quote_requestes as $quote_request)
						{
							$quote_request->delete();
						}
			/**end remove quote item tier quantity when update with same option*/
                return false;
            }
        }
        return true;
    }

	 protected function getOptionValues($value)
    {
        if (is_string($value) && is_array(@unserialize($value))) {
            $value = @unserialize($value);
            unset($value['qty'], $value['uenc']);
            $value = array_filter($value, function ($optionValue) {
                return !empty($optionValue);
            });
        }
        return $value;
    }
    /**
     * Returns options adopted to compare
     *
     * @param Item $item
     * @return array
     */
    public function getOptionsForCompare(QuoteItem $item)
    {
		$itemId=$item->getId();
		$options = [];
		 $opt_coll=\Magento\Framework\App\ObjectManager::getInstance()->create(\Magebees\QuotationManagerPro\Model\ResourceModel\Quote\Item\Option\Collection::class)->addFieldToFilter('item_id',$itemId)->getData();		
		 foreach($opt_coll as $coll){		
		$option_code=$coll['code'];	
		$option_value=$coll['value'];	
			  $options[$option_code] = $this->getOptionValues($option_value);				
		 }
		return $options;
    }
}
