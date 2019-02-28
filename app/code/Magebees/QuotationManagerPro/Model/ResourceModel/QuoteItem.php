<?php
namespace Magebees\QuotationManagerPro\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

/**
 * Quote resource model
 */
class QuoteItem extends AbstractDb
{
    
    protected $sequenceManager;   
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,      
        $connectionName = null
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
       
    }

    /**
     * Initialize table nad PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magebees_quote_item', 'id');
    }
	 public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $hasDataChanges = $this->isModified($object);
        $object->setIsOptionsSaved(false);

        $result = parent::save($object);

        if ($hasDataChanges && !$object->isOptionsSaved()) {
            $object->saveQuoteItemOptions();
        }
        return $result;
    }

}
