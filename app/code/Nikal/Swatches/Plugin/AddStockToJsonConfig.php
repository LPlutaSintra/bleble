<?php

namespace Nikal\Swatches\Plugin;

use \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use \Magento\CatalogInventory\Api\StockStateInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Catalog\Api\ProductRepositoryInterface;

class AddStockToJsonConfig
{
    private $stockItem;
    private $storeManager;
    private $productRepository;

    /**
     * ProductStock constructor.
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockItem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        StockStateInterface $stockItem,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->stockItem = $stockItem;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Configurable $subject
     * @param $result
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetJsonConfig(Configurable $subject, $result)
    {
        $result = json_decode($result, true);
        $result['stock_available_qty'] = [];
        foreach($result['index'] as $key => $value) {
            $result['stock_available_qty'][$key] = $this->getProductStock($key);
        }

        return json_encode($result);
    }

    /**
     * @param $productID
     * @return float|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductStock($productID)
    {
        $product = $this->productRepository->getById($productID);
        if ($product){
            $itemStock = $this->stockItem->getStockQty($product->getId(), $this->getCurrentWebsiteId());
            return $itemStock;
        }

        return null;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCurrentWebsiteId(){
        return $this->storeManager->getStore()->getWebsiteId();
    }

}