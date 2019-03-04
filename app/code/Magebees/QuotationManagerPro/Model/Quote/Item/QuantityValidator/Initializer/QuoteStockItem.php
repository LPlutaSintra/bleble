<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;

class QuoteStockItem
{
    /**
     * @var QuoteItemQtyList
     */
    protected $mquoteItemQtyList;

    /**
     * @var ConfigInterface
     */
    protected $typeConfig;

    /**
     * @var StockStateInterface
     */
    protected $pstockState;

    /**
     * @param ConfigInterface $typeConfig
     * @param QuoteItemQtyList $mquoteItemQtyList
     * @param StockStateInterface $pstockState
     */
    public function __construct(
        ConfigInterface $typeConfig,
        QuoteItemQtyList $mquoteItemQtyList,
        StockStateInterface $pstockState
    ) {
        $this->quoteItemQtyList = $mquoteItemQtyList;
        $this->typeConfig = $typeConfig;
        $this->stockState = $pstockState;
    }

    /**
     * Initialize stock item
     *
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $stockquoteItem
     * @param \Magento\Quote\Model\Quote\Item $mquoteItem
     * @param int $pqty
     *
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function initialize(
        \Magento\CatalogInventory\Api\Data\StockItemInterface $stockquoteItem,
        \Magebees\QuotationManagerPro\Model\QuoteItem $mquoteItem,
        $pqty
    ) {
        $product = $mquoteItem->getProduct();
        /**
         * When we work with subitem
         */
        if ($mquoteItem->getParentItem()) {
            $itemrowQty = $mquoteItem->getParentItem()->getQty() * $pqty;
            /**
             * we are using 0 because original qty was processed
             */
            $pqtyForCheck = $this->quoteItemQtyList
                ->getQty($product->getId(), $mquoteItem->getId(), $mquoteItem->getQuoteId(), 0);
        } else {
			
            $increaseQty = $mquoteItem->getQtyToAdd() ? $mquoteItem->getQtyToAdd() : $pqty;
            $itemrowQty = $pqty;
            $pqtyForCheck = $this->quoteItemQtyList->getQty(
                $product->getId(),
                $mquoteItem->getId(),
                $mquoteItem->getQuoteId(),
                $increaseQty
            );
        }

        $productTypeCustomOption = $product->getCustomOption('product_type');
        if ($productTypeCustomOption !== null) {
            // Check if product related to current item is a part of product that represents product set
            if ($this->typeConfig->isProductSet($productTypeCustomOption->getValue())) {
                $stockquoteItem->setIsChildItem(true);
            }
        }

        $stockquoteItem->setProductName($product->getName());

        $quoteresult = $this->stockState->checkQuoteItemQty(
            $product->getId(),
            $itemrowQty,
            $pqtyForCheck,
            $pqty,
            $product->getStore()->getWebsiteId()
        );

        if ($stockquoteItem->hasIsChildItem()) {
            $stockquoteItem->unsIsChildItem();
        }

        if ($quoteresult->getItemIsQtyDecimal() !== null) {
            $mquoteItem->setIsQtyDecimal($quoteresult->getItemIsQtyDecimal());
            if ($mquoteItem->getParentItem()) {
                $mquoteItem->getParentItem()->setIsQtyDecimal($quoteresult->getItemIsQtyDecimal());
            }
        }

        /**
         * Just base (parent) item qty can be changed
         * qty of child products are declared just during add process
         * exception for updating also managed by product type
         */
        if ($quoteresult->getHasQtyOptionUpdate() && (!$mquoteItem->getParentItem() ||
                $mquoteItem->getParentItem()->getProduct()->getTypeInstance()->getForceChildItemQtyChanges(
                    $mquoteItem->getParentItem()->getProduct()
                )
            )
        ) {
            $mquoteItem->setData('qty', $quoteresult->getOrigQty());
        }

        if ($quoteresult->getItemUseOldQty() !== null) {
            $mquoteItem->setUseOldQty($quoteresult->getItemUseOldQty());
        }

        if ($quoteresult->getMessage() !== null) {
            $mquoteItem->setMessage($quoteresult->getMessage());
        }

        if ($quoteresult->getItemBackorders() !== null) {
            $mquoteItem->setBackorders($quoteresult->getItemBackorders());
        }

        return $quoteresult;
    }
}
