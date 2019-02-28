<?php
namespace Magebees\QuotationManagerPro\Model\Quote\Item;
use \Magento\Catalog\Model\Product;
use Magebees\QuotationManagerPro\Model\QuoteItemFactory;
use Magebees\QuotationManagerPro\Model\QuoteItem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
/**
 * Class Processor
 *  - initializes quote item with store_id and qty data
 *  - updates quote item qty and custom price data
 */
class Processor
{
    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $quoteItemFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @param ItemFactory $quoteItemFactory
     * @param StoreManagerInterface $storeManager
     * @param State $appState
     */
    public function __construct(
        QuoteItemFactory $quoteItemFactory,
        StoreManagerInterface $storeManager,
        State $appState,
		\Magebees\QuotationManagerPro\Model\CustomerQuote $customerQuote
    ) {
        $this->quoteItemFactory = $quoteItemFactory;
        $this->storeManager = $storeManager;
        $this->appState = $appState;
        $this->customerQuote = $customerQuote;
    }

    /**
     * Initialize quote item object
     *
     * @param \Magento\Framework\DataObject $request
     * @param Product $product
     *
     * @return \Magento\Quote\Model\Quote\Item
     */
    public function init(Product $product, $request)
    {
        $item = $this->quoteItemFactory->create();

        $this->setItemStoreId($item);

        /**
         * We can't modify existing child items
         */
        if ($item->getId() && $product->getParentProductId()) {
            return $item;
        }

        if ($request->getResetCount() && !$product->getStickWithinParent() && $item->getId() === $request->getId()) {
            $item->setData('qty', 0);
        }

        return $item;
    }

    /**
     * Set qty and custom price for quote item
     *
     * @param Item $item
     * @param \Magento\Framework\DataObject $request
     * @param Product $candidate
     * @return void
     */
    public function prepare(QuoteItem $item, DataObject $request, Product $candidate)
    {
        /**
         * We specify qty after we know about parent (for stock)
         */
        if ($request->getResetCount() && !$candidate->getStickWithinParent() && $item->getId() == $request->getId()) {
            $item->setData('qty', 0);
        }
        $item->addQty($candidate->getCartQty());
		 if ($item->getParentItem() )
		 {
			  $finalPrice = $item->getParentItem()->getProduct()->getPriceModel()->getChildFinalPrice(
                $item->getParentItem()->getProduct(),
                $item->getParentItem()->getQty(),
                $candidate,
                $item->getQty()
            );
		 }
		else
		{
			   $finalPrice = $candidate->getFinalPrice($item->getQty());
			/* start for add/update entry in magebees_quote_request_item table when quote item add/update */
			$item->setQuoteRequest($item);
	/*End for add/update entry in magebees_quote_request_item table when quote item add/update*/

		}
		$item->setPrice($finalPrice);		
        $customPrice = $request->getCustomPrice();
        if (!empty($customPrice)) {
            $item->setCustomPrice($customPrice);
            $item->setOriginalCustomPrice($customPrice);
        }		
		 $this->customerQuote->addQuoteItem($item);
		
    }

    /**
     * Set store_id value to quote item
     *
     * @param Item $item
     * @return void
     */
    protected function setItemStoreId(QuoteItem $item)
    {
        if ($this->appState->getAreaCode() === \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            $storeId = $this->storeManager->getStore($this->storeManager->getStore()->getId())
                ->getId();
            $item->setStoreId($storeId);
        } else {
            $item->setStoreId($this->storeManager->getStore()->getId());
        }
    }
}
