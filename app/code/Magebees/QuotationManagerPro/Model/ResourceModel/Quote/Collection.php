<?php
namespace Magebees\QuotationManagerPro\Model\ResourceModel\Quote;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
{   
    protected function _construct()
    {
        $this->_init(\Magebees\QuotationManagerPro\Model\Quote::class, \Magebees\QuotationManagerPro\Model\ResourceModel\Quote::class);
    }
}
