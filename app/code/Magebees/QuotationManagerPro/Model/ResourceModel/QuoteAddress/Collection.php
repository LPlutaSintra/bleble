<?php

namespace Magebees\QuotationManagerPro\Model\ResourceModel\QuoteAddress;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magebees\QuotationManagerPro\Model\QuoteAddress', 'Magebees\QuotationManagerPro\Model\ResourceModel\QuoteAddress');
    }
}
