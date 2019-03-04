<?php
namespace Magebees\QuotationManagerPro\CustomerData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magebees\QuotationManagerPro\Model\QuoteItem;

class ItemPool implements ItemPoolInterface
{
  
    protected $objectManager;
    protected $defaultItemId;
    protected $itemMap;
    public function __construct(
        ObjectManagerInterface $objectManager,
        $defaultItemId,
        array $itemMap = []
    ) {
        $this->objectManager = $objectManager;
        $this->defaultItemId = $defaultItemId;
        $this->itemMap = $itemMap;
    }

    public function getItemData(QuoteItem $item)
    {
        return $this->get($item->getProductType())->getItemData($item);
    }

    /**
     * Get section source by name   
     */
    protected function get($type)
    {
        $itemId = isset($this->itemMap[$type]) ? $this->itemMap[$type] : $this->defaultItemId;
        $item = $this->objectManager->get($itemId);

        if (!$item instanceof ItemInterface) {
            throw new LocalizedException(
                __('%1 doesn\'t extend \Magento\Checkout\CustomerData\ItemInterface', $type)
            );
        }
        return $item;
    }
}
