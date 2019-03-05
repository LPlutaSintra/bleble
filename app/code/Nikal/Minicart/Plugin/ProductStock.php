<?php
namespace Nikal\Minicart\Plugin;
use \Magento\CatalogInventory\Api\StockStateInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Catalog\Api\ProductRepositoryInterface;


class ProductStock
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
     * @param \Magento\Checkout\CustomerData\DefaultItem $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetItemData(\Magento\Checkout\CustomerData\DefaultItem $subject, $result)
    {
        $product_sku = $result['product_sku'];
        $product = $this->productRepository->get($product_sku);
        if ($product){
            $itemStock = $this->stockItem->getStockQty($product->getId(), $this->getCurrentWebsiteId());
            $result['stock_available_qty'] = $itemStock;
        }

        return $result;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCurrentWebsiteId(){
        return $this->storeManager->getStore()->getWebsiteId();
    }
}
