<?php
namespace Magebees\QuotationManagerPro\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

/**
 * Quote resource model
 */
class Quote extends AbstractDb
{
    
    protected $sequenceManager;   
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,      
        $dbconnectionName = null
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $dbconnectionName);
       
    }

    /**
     * Initialize table nad PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magebees_quote', 'quote_id');
    }
	public function loadByIdWithoutStore($quote, $quoteId)
    {
        $dbconnection = $this->getConnection();
        if ($dbconnection) {
            $select = parent::_getLoadSelect('quote_id', $quoteId, $quote);

            $data = $dbconnection->fetchRow($select);

            if ($data) {
                $quote->setData($data);
            }
        }
 return $this;
  //      $this->_afterLoad($quote);
	}
	 public function loadByCustomerId($quote, $qcustomerId)
    {
		
        $dbconnection = $this->getConnection();
        $select = $this->_getLoadSelect(
            'customer_id',
            $qcustomerId,
            $quote
        )->where(
            'is_active = ?',
            1
        )->order(
            'updated_at ' . \Magento\Framework\DB\Select::SQL_DESC
        )->limit(
            1
        );

        $data = $dbconnection->fetchRow($select);

        if ($data) {
            $quote->setData($data);
        }
		

    //    $this->_afterLoad($quote);

        return $this;
    }

}
