<?php
namespace Magebees\QuotationManagerPro\Model\ResourceModel;

class QuoteRequest extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magebees_quote_request_item', 'request_id');
    }
}
