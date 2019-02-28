<?php

namespace Magebees\QuotationManagerPro\Model\ResourceModel\QuoteFiles;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magebees\QuotationManagerPro\Model\QuoteFiles', 'Magebees\QuotationManagerPro\Model\ResourceModel\QuoteFiles');
    }
}
