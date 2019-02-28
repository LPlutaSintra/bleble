<?php

namespace Magebees\QuotationManagerPro\Model\ResourceModel\QuoteMessage;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magebees\QuotationManagerPro\Model\QuoteMessage', 'Magebees\QuotationManagerPro\Model\ResourceModel\QuoteMessage');
    }
}
