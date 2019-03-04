<?php
namespace Magebees\QuotationManagerPro\CustomerData;
use Magebees\QuotationManagerPro\Model\QuoteItem;
interface ItemInterface
{
    public function getItemData(QuoteItem $item);
}
