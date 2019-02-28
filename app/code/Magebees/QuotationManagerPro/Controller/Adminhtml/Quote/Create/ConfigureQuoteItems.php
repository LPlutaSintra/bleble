<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote\Create;
class ConfigureQuoteItems extends \Magento\Backend\App\Action
{
	protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper		
    ) {    
        parent::__construct($context);
		$this->quoteHelper = $quoteHelper;
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Ajax handler to response configuration fieldset of composite product in quote items
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        // Prepare data
        $configureResult = new \Magento\Framework\DataObject();
        try {
            $quoteItemId = (int)$this->getRequest()->getParam('id');
            if (!$quoteItemId) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Quote item id is not received.'));
            }
			$quoteItem =$this->quoteHelper->loadQuoteItemById($quoteItemId);			
            if (!$quoteItem->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Quote item is not loaded.'));
            }

            $configureResult->setOk(true);
            $optionCollection = $this->_objectManager->create(\Magebees\QuotationManagerPro\Model\ResourceModel\Quote\Item\Option\Collection::class)->addQuoteItemFilter([$quoteItemId]);
            $quoteItem->setOptions($optionCollection->getOptionsByItem($quoteItem));
 			$configureResult->setQty($quoteItem->getQty());
            $configureResult->setBuyRequest($quoteItem->getBuyRequest());
            $configureResult->setCurrentStoreId($quoteItem->getStoreId());
            $configureResult->setProductId($quoteItem->getProductId());
            $sessionQuote = $this->_objectManager->get(\Magento\Backend\Model\Session\Quote::class);
            $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());
        } catch (\Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        $helper = $this->_objectManager->get(\Magento\Catalog\Helper\Product\Composite::class);
        return $helper->renderConfigureResult($configureResult);
    }
}
