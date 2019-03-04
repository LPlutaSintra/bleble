<?php
namespace Magebees\QuotationManagerPro\Model\ResourceModel;

class QuoteFiles extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magebees_quote_files', 'id');
    }
}
