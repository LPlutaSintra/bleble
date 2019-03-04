<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Model\ResourceModel\Quote\Item\Option;

use Magebees\QuotationManagerPro\Model\QuoteItem;

/**
 * Item option collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Array of option ids grouped by item id
     *
     * @var array
     */
    protected $_optionsByItem = [];

    /**
     * Array of option ids grouped by product id
     *
     * @var array
     */
    protected $_qoptionsByProduct = [];

    /**
     * Define resource model for collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magebees\QuotationManagerPro\Model\Quote\Item\Option::class,
            \Magebees\QuotationManagerPro\Model\ResourceModel\Quote\Item\Option::class
        );
    }

    /**
     * Fill array of options by item and product
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this as $qitemoption) {
            $qitemoptionId = $qitemoption->getId();
            $qitemId = $qitemoption->getItemId();
            $productId = $qitemoption->getProductId();
            if (isset($this->_optionsByItem[$qitemId])) {
                $this->_optionsByItem[$qitemId][] = $qitemoptionId;
            } else {
                $this->_optionsByItem[$qitemId] = [$qitemoptionId];
            }
            if (isset($this->_qoptionsByProduct[$productId])) {
                $this->_qoptionsByProduct[$productId][] = $qitemoptionId;
            } else {
                $this->_qoptionsByProduct[$productId] = [$qitemoptionId];
            }
        }

        return $this;
    }

    /**
     * Apply quote item(s) filter to collection
     *
     * @param int|array|Item $qitem
     * @return $this
     */
    public function addQuoteItemFilter($qitem)
    {
        if (empty($qitem)) {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
            //$this->addFieldToFilter('item_id', '');
        } elseif (is_array($qitem)) {
            $this->addFieldToFilter('item_id', ['in' => $qitem]);
        } elseif ($qitem instanceof QuoteItem) {
            $this->addFieldToFilter('item_id', $qitem->getId());
        } else {
            $this->addFieldToFilter('item_id', $qitem);
        }

        return $this;
    }

    /**
     * Get array of all product ids
     *
     * @return array
     */
    public function getProductIds()
    {
        $this->load();

        return array_keys($this->_qoptionsByProduct);
    }

    /**
     * Get all option for item
     *
     * @param mixed $qitem
     * @return array
     */
    public function getOptionsByItem($qitem)
    {
        if ($qitem instanceof QuoteItem) {
            $qitemId = $qitem->getId();
        } else {
            $qitemId = $qitem;
        }

        $this->load();

        $qitemoptions = [];
        if (isset($this->_optionsByItem[$qitemId])) {
            foreach ($this->_optionsByItem[$qitemId] as $qitemoptionId) {
                $qitemoptions[] = $this->_items[$qitemoptionId];
            }
        }

        return $qitemoptions;
    }

    
}
