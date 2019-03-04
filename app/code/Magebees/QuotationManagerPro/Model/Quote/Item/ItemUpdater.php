<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Model\Quote\Item;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magebees\QuotationManagerPro\Model\Quote;
use Magebees\QuotationManagerPro\Model\QuoteItem;
use Zend\Code\Exception\InvalidArgumentException;

class ItemUpdater
{
    
    protected $productFactory;
    protected $localeFormat;
    protected $objectFactory;
   
 
    
    public function updateQuoteItem(QuoteItem $item, array $info)
    {
        if (!isset($info['qty'])) {
            throw new InvalidArgumentException(__('The qty value is required to update quote item.'));
        }
        $itemQty = $info['qty'];
        if ($item->getProduct()->getStockItem()) {
            if (!$item->getProduct()->getStockItem()->getIsQtyDecimal()) {
                $itemQty = (int)$info['qty'];
            } else {
                $item->setIsQtyDecimal(1);
            }
        }
        $itemQty = $itemQty > 0 ? $itemQty : 1;
    

        if (empty($info['action']) || !empty($info['configured'])) {
            $noDiscount = !isset($info['use_discount']);
            $item->setQty($itemQty);
            $item->setNoDiscount($noDiscount);
            $item->getProduct()->setIsSuperMode(true);
            $item->getProduct()->unsSkipCheckRequiredOption();
            $item->checkItemData();
        }

        return $this;
    }

   
}
