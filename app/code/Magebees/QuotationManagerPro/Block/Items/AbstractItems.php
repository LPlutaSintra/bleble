<?php

namespace Magebees\QuotationManagerPro\Block\Items;

class AbstractItems extends \Magento\Sales\Block\Items\AbstractItems
{
   
    protected function _getQuoteItemType(\Magebees\QuotationManagerPro\Model\QuoteItem $item)
    {
         $type = $item->getProductType();        
        	return $type;
    }

    /**
     * Get item row html
     *
     * @param   \Magento\Framework\DataObject $item
     * @return  string
     */
    public function getEmailItemHtml(\Magebees\QuotationManagerPro\Model\QuoteItem $item)
    {
        $type = $this->_getQuoteItemType($item);
        $block = $this->getItemRenderer($type)->setItem($item);
        $this->_prepareItem($block);
        return $block->toHtml();
    }
}
