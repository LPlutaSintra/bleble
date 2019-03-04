<?php


namespace Magebees\QuotationManagerPro\Block\Checkout\Cart\Item\Renderer\Actions;

use Magento\Framework\View\Element\Template;
use Magebees\QuotationManagerPro\Model\QuoteItem;

class Generic extends Template
{
	
    protected $item;
	
    public function getItem()
    {
        return $this->item;
    }

   
    public function setItem(QuoteItem $item)
    {
        $this->item = $item;
        return $this;
    }

   
    public function isProductVisibleInSiteVisibility()
    {
        return $this->getItem()->getProduct()->isVisibleInSiteVisibility();
    }

    /**
     * Check if cart item is virtual
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isVirtual()
    {
        return (bool)$this->getItem()->getIsVirtual();
    }
}
