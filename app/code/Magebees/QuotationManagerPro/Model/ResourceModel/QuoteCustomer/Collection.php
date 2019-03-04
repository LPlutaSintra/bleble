<?php

namespace Magebees\QuotationManagerPro\Model\ResourceModel\QuoteCustomer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magebees\QuotationManagerPro\Model\QuoteCustomer', 'Magebees\QuotationManagerPro\Model\ResourceModel\QuoteCustomer');
    }
}
