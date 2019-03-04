<?php

namespace Magebees\QuotationManagerPro\Model\ResourceModel\QuoteRequest;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magebees\QuotationManagerPro\Model\QuoteRequest', 'Magebees\QuotationManagerPro\Model\ResourceModel\QuoteRequest');
    }
}
