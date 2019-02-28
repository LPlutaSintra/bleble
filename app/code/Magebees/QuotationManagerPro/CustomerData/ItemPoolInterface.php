<?php
namespace Magebees\QuotationManagerPro\CustomerData;

use Magebees\QuotationManagerPro\Model\QuoteItem;


interface ItemPoolInterface
{
    /**
     * Get item data by quote item   
     */
    public function getItemData(QuoteItem $item);
}
