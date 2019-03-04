<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product quote initializer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 */
namespace Magebees\QuotationManagerPro\Model\Backend\Quote;

class Initializer
{
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
		\Magebees\QuotationManagerPro\Model\CustomerQuote $customerQuote
    ) {
        $this->stockRegistry = $stockRegistry;
		 $this->customerQuote = $customerQuote;
    }

   
    public function init(
        \Magebees\QuotationManagerPro\Model\Quote $quote,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\DataObject $config
    ) {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $quote->getStore()->getWebsiteId());
        if ($stockItem->getIsQtyDecimal()) {
            $product->setIsQtyDecimal(1);
        } else {
            $config->setQty((int)$config->getQty());
        }

        $product->setCartQty($config->getQty());

        $item = $quote->addProduct(
            $product,
            $config,
            \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
        );
			$this->customerQuote->save();

        return $item;
    }
}
