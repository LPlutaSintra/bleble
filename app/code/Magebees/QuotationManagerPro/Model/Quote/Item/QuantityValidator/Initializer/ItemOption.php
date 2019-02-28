<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;

class ItemOption
{
    /**
     * @var QuoteItemQtyList
     */
    protected $mquoteItemQtyList;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var StockStateInterface
     */
    protected $stockState;

    /**
     * @param QuoteItemQtyList $mquoteItemQtyList
     * @param StockRegistryInterface $stockRegistry
     * @param StockStateInterface $stockState
     */
    public function __construct(
        QuoteItemQtyList $mquoteItemQtyList,
        StockRegistryInterface $stockRegistry,
        StockStateInterface $stockState
    ) {
        $this->quoteItemQtyList = $mquoteItemQtyList;
        $this->stockRegistry = $stockRegistry;
        $this->stockState = $stockState;
    }

    /**
     * Init stock item
     *
     * @param \Magento\Quote\Model\Quote\Item\Option $itemopt
     * @param \Magento\Quote\Model\Quote\Item $mquoteItem
     *
     * @return \Magento\CatalogInventory\Model\Stock\Item
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStockItem(
        \Magebees\QuotationManagerPro\Model\Quote\Item\Option $itemopt,
        \Magebees\QuotationManagerPro\Model\QuoteItem $mquoteItem
    ) {		
        $stockquoteItem = $this->stockRegistry->getStockItem(
            $itemopt->getProduct()->getId(),
           	1
            //$mquoteItem->getStore()->getWebsiteId()
        );
        if (!$stockquoteItem->getItemId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The stock item for Product in option is not valid.')
            );
        }
        /**
         * define that stock item is child for composite product
         */
        $stockquoteItem->setIsChildItem(true);
        /**
         * don't check qty increments value for option product
         */
        $stockquoteItem->setSuppressCheckQtyIncrements(true);

        return $stockquoteItem;
    }

    /**
     * Initialize item option
     *
     * @param \Magento\Quote\Model\Quote\Item\Option $itemopt
     * @param \Magento\Quote\Model\Quote\Item $mquoteItem
     * @param int $qty
     *
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initialize(
        \Magebees\QuotationManagerPro\Model\Quote\Item\Option $itemopt,
        \Magebees\QuotationManagerPro\Model\QuoteItem $mquoteItem,
        $qty
    ) {
        $itemoptValue = $itemopt->getValue();
        $itemoptQty = $qty * $itemoptValue;
        $increaseOptionQty = ($mquoteItem->getQtyToAdd() ? $mquoteItem->getQtyToAdd() : $qty) * $itemoptValue;
        $qtyForCheck = $this->quoteItemQtyList->getQty(
            $itemopt->getProduct()->getId(),
            $mquoteItem->getId(),
            $mquoteItem->getQuoteId(),
            $increaseOptionQty
        );

        $stockquoteItem = $this->getStockItem($itemopt, $mquoteItem);
		
        $stockquoteItem->setProductName($itemopt->getProduct()->getName());
        $optresult = $this->stockState->checkQuoteItemQty(
            $itemopt->getProduct()->getId(),
            $itemoptQty,
            $qtyForCheck,
            $itemoptValue,
            $itemopt->getProduct()->getStore()->getWebsiteId()
        );

        if ($optresult->getItemIsQtyDecimal() !== null) {
            $itemopt->setIsQtyDecimal($optresult->getItemIsQtyDecimal());
        }

        if ($optresult->getHasQtyOptionUpdate()) {
            $itemopt->setHasQtyOptionUpdate(true);
            $mquoteItem->updateItemQtyOption($itemopt, $optresult->getOrigQty());
            $itemopt->setValue($optresult->getOrigQty());
            /**
             * if option's qty was updates we also need to update quote item qty
             */
            $mquoteItem->setData('qty', intval($qty));
        }
        if ($optresult->getMessage() !== null) {
            $itemopt->setMessage($optresult->getMessage());
            $mquoteItem->setMessage($optresult->getMessage());
        }
        if ($optresult->getItemBackorders() !== null) {
            $itemopt->setBackorders($optresult->getItemBackorders());
        }

        $stockquoteItem->unsIsChildItem();

        return $optresult;
    }
}
