<?php
namespace Magebees\QuotationManagerPro\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;

class ProductAddToCart implements ObserverInterface
{
    public function __construct(
        Session $session,
        QuoteRepository $quoteRepository
    )
    {
        $this->session = $session;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(Observer $observer)
    {
        $quoteId = $this->session->getQuote()->getId();
        if ($quoteId) {
            $quote = $this->quoteRepository->get($quoteId);
            if (!$quote->getIsActive()) {
                return;
            }

        $product = $observer->getEvent()->getDataByKey('product');
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $this->session->getQuote()->getItemByProduct($product);
        $itemId = $item->getId();
        $quoteItem = $quote->getItemById($itemId);
    //    $customValue = 10;  // Prepare your custom field value here
        $quoteItem->setIsMagebeesItem(1); // Set custom field value
        $quoteItem->save();
        }
        return $this;
    }
}