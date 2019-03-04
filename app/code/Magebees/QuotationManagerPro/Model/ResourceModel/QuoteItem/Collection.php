<?php
namespace Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem;
use \Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
{   
	protected $_quote;
	protected $_totalRecords;
	  protected $_qproductIds = [];
	  public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magebees\QuotationManagerPro\Model\ResourceModel\Quote\Item\Option\CollectionFactory $itemOptionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $qproductCollectionFactory,
        \Magento\Quote\Model\Quote\Config $quoteConfig,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
        $this->_itemOptionCollectionFactory = $itemOptionCollectionFactory;
        $this->_productCollectionFactory = $qproductCollectionFactory;
        $this->_quoteConfig = $quoteConfig;
    }
    protected function _construct()
    {
        $this->_init(\Magebees\QuotationManagerPro\Model\QuoteItem::class, \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem::class);
    }
	 public function setQuote($quote)
    {
        $this->_quote = $quote;
        $quoteId = $quote->getId();
        if ($quoteId) {
            $this->addFieldToFilter('quote_id', $quote->getId());
        } else {
            $this->_totalRecords = 0;
            //$this->_setIsLoaded(true);
        }
        return $this;
    }
	 protected function _afterLoad()
    {
		parent::_afterLoad();

        $qproductIds = [];
        foreach ($this as $item) {
            // Assign parent items
            if ($item->getParentItemId()) {
                $item->setParentItem($this->getItemById($item->getParentItemId()));
            }
            if ($this->_quote) {
                $item->setQuote($this->_quote);
            }
            // Collect quote products ids
            $qproductIds[] = (int)$item->getProductId();
        }
        $this->_qproductIds = array_merge($this->_qproductIds, $qproductIds);
        $this->removeItemsWithAbsentProducts();
        /**
         * Assign options and products
         */
		
        $this->_assignOptions();		
        $this->_assignProducts();		
        $this->resetItemsDataChanged();

        return $this;
	}
	 private function removeItemsWithAbsentProducts()
    {
        $qproductCollection = $this->_productCollectionFactory->create()->addIdFilter($this->_qproductIds);
        $existingProductsIds = $qproductCollection->getAllIds();
        $absentProductsIds = array_diff($this->_qproductIds, $existingProductsIds);
		
        // Remove not existing products from items collection
        if (!empty($absentProductsIds)) {
            foreach ($absentProductsIds as $qproductIdToExclude) {
                /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
                $quoteItem = $this->getItemByColumnValue('product_id', $qproductIdToExclude);
                $this->removeItemByKey($quoteItem->getId());
            }
        }
    }
	   protected function _assignOptions()
    {
        $itemIds = array_keys($this->_items);
		
        $optionCollection = $this->_itemOptionCollectionFactory->create()->addQuoteItemFilter($itemIds);
        foreach ($this as $item) {			
            $item->setOptions($optionCollection->getOptionsByItem($item));
        }
        $qproductIds = $optionCollection->getProductIds();
        $this->_qproductIds = array_merge($this->_qproductIds, $qproductIds);

        return $this;
    }
	 protected function _assignProducts()
    {
      
        $qproductCollection = $this->_productCollectionFactory->create()->setStoreId(
           // $this->getStoreId()
           1
        )->addIdFilter(
            $this->_qproductIds
        )->addAttributeToSelect(
            $this->_quoteConfig->getProductAttributes()
        );
		
        $this->skipStockStatusFilter($qproductCollection);
        $qproductCollection->addOptionsToResult()->addStoreFilter()->addUrlRewrite();
     //   $this->addTierPriceData($qproductCollection);

        $recollectQuote = false;
        foreach ($this as $item) {
            $qproduct = $qproductCollection->getItemById($item->getProductId());
            if ($qproduct) {
                $qproduct->setCustomOptions([]);
                $qtyOptions = [];
                $optionProductIds = [];
                foreach ($item->getOptions() as $option) {
                    /**
                     * Call type-specific logic for product associated with quote item
                     */
                    $qproduct->getTypeInstance()->assignProductToOption(
                        $qproductCollection->getItemById($option->getProductId()),
                        $option,
                        $qproduct
                    );

                    if (is_object($option->getProduct()) && $option->getProduct()->getId() != $qproduct->getId()) {
                        $optionProductIds[$option->getProduct()->getId()] = $option->getProduct()->getId();
                    }
                }

                if ($optionProductIds) {
                    foreach ($optionProductIds as $optionProductId) {
                        $qtyOption = $item->getOptionByCode('product_qty_' . $optionProductId);
                        if ($qtyOption) {
                            $qtyOptions[$optionProductId] = $qtyOption;
                        }
                    }
                }

                $item->setItemQtyOptions($qtyOptions)->setProduct($qproduct);
            } else {
                $item->isDeleted(true);
                $recollectQuote = true;
            }
            $item->checkItemData();
        }

        if ($recollectQuote && $this->_quote) {
           // $this->_quote->collectTotals();
        }
        
        return $this;
    }
	 private function skipStockStatusFilter(ProductCollection $qproductCollection)
    {
        $qproductCollection->setFlag('has_stock_status_filter', true);
    }
	
}
