<?php

namespace Magebees\QuotationManagerPro\Block\Item\Price;

use Magebees\QuotationManagerPro\Model\QuoteItem;

/**
 * Item price render block 
 */
class Renderer extends \Magento\Framework\View\Element\Template
{
   
    protected $item;

    /**
     * Set item for render    
     */
    public function setItem(\Magebees\QuotationManagerPro\Model\QuoteItem $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Get quote item     
     */
    public function getItem()
    {
        return $this->item;
    }
}
