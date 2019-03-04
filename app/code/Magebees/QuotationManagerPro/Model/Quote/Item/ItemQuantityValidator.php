<?php

namespace Magebees\QuotationManagerPro\Model\Quote\Item;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Helper\Data;
use Magebees\QuotationManagerPro\Model\Quote\Item\QuantityValidator\Initializer\ItemOption;
use Magebees\QuotationManagerPro\Model\Quote\Item\QuantityValidator\Initializer\QuoteStockItem;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Event\Observer;
use Magebees\QuotationManagerPro\Model\Quote\Status;
use Magento\Framework\Exception\LocalizedException;

class ItemQuantityValidator
{
   
    protected $itemoptionInitializer;
    protected $stockquoteItemInitializer;
    protected $stockRegistry;
    protected $stockState;

    public function __construct(
        ItemOption $itemoptionInitializer,
        QuoteStockItem $stockquoteItemInitializer,
        StockRegistryInterface $stockRegistry,
	 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
        StockStateInterface $stockState
    ) {
        $this->optionInitializer = $itemoptionInitializer;
        $this->stockItemInitializer = $stockquoteItemInitializer;
        $this->stockRegistry = $stockRegistry;
        $this->stockState = $stockState;
		 $this->quoteHelper = $quoteHelper;
    }

    private function addErrorInfoToQuote($result, $mquoteItem)
    {
		
        $mquoteItem->addErrorInfo(
            'cataloginventory',
            Data::ERROR_QTY,
            $result->getMessage()
        );

        $mquoteItem->getQuote()->addErrorInfo(
            $result->getQuoteMessageIndex(),
            'cataloginventory',
            Data::ERROR_QTY,
            $result->getQuoteMessage()
        );
		
    }

    /**
     * Check product inventory data when quote item quantity declaring
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validate(Observer $observer)
    {
        /* @var $mquoteItem \Magento\Quote\Model\Quote\Item */
        $mquoteItem = $observer->getEvent()->getItem();
		
        if (!$mquoteItem ||
            !$mquoteItem->getProductId() ||
            !$mquoteItem->getQuote() 
			//||$mquoteItem->getQuote()->getIsSuperMode()
        ) {
			
            return;
			
        }
		
        $product = $mquoteItem->getProduct();
        $qty = $mquoteItem->getQty();

        /* @var \Magento\CatalogInventory\Model\Stock\Item $stockquoteItem */
        $stockquoteItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        if (!$stockquoteItem instanceof StockItemInterface) {
            throw new LocalizedException(__('The stock item for Product is not valid.'));
        }

        /* @var \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus */
        $stockStatus = $this->stockRegistry->getStockStatus($product->getId(), $product->getStore()->getWebsiteId());

        /* @var \Magento\CatalogInventory\Api\Data\StockStatusInterface $parentStockStatus */
        $parentStockStatus = false;

        /**
         * Check if product in stock. For composite products check base (parent) item stock status
         */
        if ($mquoteItem->getParentItem()) {
            $product = $mquoteItem->getParentItem()->getProduct();
            $parentStockStatus = $this->stockRegistry->getStockStatus(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            );
        }

        if ($stockStatus) {
            if ($stockStatus->getStockStatus() === Stock::STOCK_OUT_OF_STOCK
                    || $parentStockStatus && $parentStockStatus->getStockStatus() == Stock::STOCK_OUT_OF_STOCK
            ) {
                $mquoteItem->addErrorInfo(
                    'cataloginventory',
                    Data::ERROR_QTY,
                    __('This product is out of stock.')
                );
                $mquoteItem->getQuote()->addErrorInfo(
                    'stock',
                    'cataloginventory',
                    Data::ERROR_QTY,
                    __('Some of the products are out of stock.')
                );
                return;
            } else {
                // Delete error from item and its quote, if it was set due to item out of stock
                $this->_removeErrorsFromQuoteAndItem($mquoteItem, Data::ERROR_QTY);
            }
        }

        /**
         * Check item for options
         */
        if (($itemoptions = $mquoteItem->getItemQtyOptions()) && $qty > 0) {
            $qty = $product->getTypeInstance()->prepareQuoteItemQty($qty, $product);
            $mquoteItem->setData('qty', $qty);
            if ($stockStatus) {
                $result = $this->stockState->checkQtyIncrements(
                    $product->getId(),
                    $qty,
                    $product->getStore()->getWebsiteId()
                );
                if ($result->getHasError()) {
                    $mquoteItem->addErrorInfo(
                        'cataloginventory',
                        Data::ERROR_QTY_INCREMENTS,
                        $result->getMessage()
                    );

                    $mquoteItem->getQuote()->addErrorInfo(
                        $result->getQuoteMessageIndex(),
                        'cataloginventory',
                        Data::ERROR_QTY_INCREMENTS,
                        $result->getQuoteMessage()
                    );
                } else {
                    // Delete error from item and its quote, if it was set due to qty problems
                    $this->_removeErrorsFromQuoteAndItem(
                        $mquoteItem,
                        Data::ERROR_QTY_INCREMENTS
                    );
                }
            }
            // variable to keep track if we have previously encountered an error in one of the options
            $removeError = true;

            foreach ($itemoptions as $itemoption) {
                $result = $this->optionInitializer->initialize($itemoption, $mquoteItem, $qty);
				
                if ($result->getHasError()) {
                    $itemoption->setHasError(true);
                    //Setting this to false, so no error statuses are cleared
                    $removeError = false;
                    $this->addErrorInfoToQuote($result, $mquoteItem, $removeError);
                }
            }
            if ($removeError) {
                $this->_removeErrorsFromQuoteAndItem($mquoteItem, Data::ERROR_QTY);
            }
        } else {
			
            if ($mquoteItem->getParentItem() === null) {
				
                $result = $this->stockItemInitializer->initialize($stockquoteItem, $mquoteItem, $qty);
                if ($result->getHasError()) {
                    $this->addErrorInfoToQuote($result, $mquoteItem);
                } else {
                    $this->_removeErrorsFromQuoteAndItem($mquoteItem, Data::ERROR_QTY);
                }
            }
        }
    }

    /**
     * Removes error statuses from quote and item, set by this observer
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param int $code
     * @return void
     */
    protected function _removeErrorsFromQuoteAndItem($item, $code)
    {
        if ($item->getHasError()) {
            $params = ['origin' => 'cataloginventory', 'code' => $code];
            $item->removeErrorInfosByParams($params);
        }

        $mquote = $item->getQuote();
        if ($mquote->getHasError()) {
			// check from here
            $mquoteItems = $mquote->getItemsCollection();
            $canRemoveErrorFromQuote = true;
            foreach ($mquoteItems as $mquoteItem) {
                if ($mquoteItem->getItemId() == $item->getItemId()) {
                    continue;
                }

                $errorInfos = $mquoteItem->getErrorInfos();
                foreach ($errorInfos as $errorInfo) {
                    if ($errorInfo['code'] == $code) {
                        $canRemoveErrorFromQuote = false;
                        break;
                    }
                }

                if (!$canRemoveErrorFromQuote) {
                    break;
                }
            }

            if ($canRemoveErrorFromQuote) {
                $params = ['origin' => 'cataloginventory', 'code' => $code];
                $mquote->removeErrorInfosByParams(null, $params);
            }
        }
	
    }
}
