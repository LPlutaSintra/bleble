<?php
namespace Magebees\QuotationManagerPro\Model\ResourceModel;

class QuoteAddress extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magebees_quote_address', 'address_id');
    }
}
