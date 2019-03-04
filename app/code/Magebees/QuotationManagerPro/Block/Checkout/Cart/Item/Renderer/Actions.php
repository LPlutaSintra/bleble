<?php

namespace Magebees\QuotationManagerPro\Block\Checkout\Cart\Item\Renderer;

use Magebees\QuotationManagerPro\Block\Checkout\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\View\Element\Text;
use Magebees\QuotationManagerPro\Model\QuoteItem;

class Actions extends Text
{
    
    protected $qitem;

    public function getItem()
    {
        return $this->item;
    }

    
    public function setItem(QuoteItem $qitem)
    {
        $this->item = $qitem;
        return $this;
    }

    
    protected function _toHtml()
    {
        $this->setText('');

        $qlayout = $this->getLayout();
        foreach ($this->getChildNames() as $child) {
            /** @var Generic $qchildBlock */
            $qchildBlock = $qlayout->getBlock($child);
            if ($qchildBlock instanceof Generic) {
                $qchildBlock->setItem($this->getItem());
                $this->addText($qlayout->renderElement($child, false));
            }
        }

        return parent::_toHtml();
    }
}
