<?php
namespace Magebees\QuotationManagerPro\CustomerData;
use Magebees\QuotationManagerPro\Model\QuoteItem;
abstract class AbstractItem implements ItemInterface
{
    protected $item;
    public function getItemData(QuoteItem $item)
    {
        $this->item = $item;
        return \array_merge(
            ['product_type' => $item->getProductType()],
            $this->doGetItemData()
        );
    }  
    abstract protected function doGetItemData();
}
