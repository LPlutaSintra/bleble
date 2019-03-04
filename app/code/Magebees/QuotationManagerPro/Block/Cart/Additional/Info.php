<?php
namespace Magebees\QuotationManagerPro\Block\Cart\Additional;
class Info extends \Magento\Framework\View\Element\Template
{
    protected $_item;
    public function setItem(\Magebees\QuotationManagerPro\Model\QuoteItem $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * @return \Magebees\QuotationManagerPro\Model\QuoteItem     
     */
    public function getItem()
    {
        return $this->_item;
    }
}
