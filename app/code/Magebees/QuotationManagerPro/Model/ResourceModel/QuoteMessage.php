<?php
namespace Magebees\QuotationManagerPro\Model\ResourceModel;

class QuoteMessage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magebees_quote_message', 'message_id');
    }
}
