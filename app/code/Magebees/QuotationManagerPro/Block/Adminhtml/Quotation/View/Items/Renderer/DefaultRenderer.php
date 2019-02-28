<?php
namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\View\Items\Renderer;

class DefaultRenderer extends \Magebees\QuotationManagerPro\Block\Adminhtml\Items\Renderer\DefaultRenderer
{
    
    protected $_messageHelper;

    protected $_checkoutHelper;

    protected $_giftMessage = [];

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
		\Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem\CollectionFactory $mquoteItemFactory,
		\Magebees\QuotationManagerPro\Helper\Admin $backendHelper,
        \Magento\GiftMessage\Helper\Message $qmessageHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,	
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		 \Magento\Catalog\Helper\Product\Configuration $productConfig,	
		 \Magento\Framework\Message\ManagerInterface $qmessageManager,
        array $data = []
    ) {
        $this->_checkoutHelper = $checkoutHelper;
        $this->_messageHelper = $qmessageHelper;
		$this->_quoteItemFactory = $mquoteItemFactory;
		$this->_quoteHelper = $quoteHelper;
		$this->_productConfig = $productConfig;
		$this->messageManager = $qmessageManager;
		 $this->backendHelper = $backendHelper;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry,$mquoteItemFactory,$backendHelper,$data);
    }

    public function getItem()
    {
        return $this->_getData('item');
    }
	public function getOptionList()
    {
        return $this->getProductOptions();
    }
	 public function getProductOptions()
    {
   		$qhelper =$this->_quoteHelper;		
		return $qhelper->getBackendOptions($this->getItem());
		
    }
	 public function getFormatedOptionValue($qoptionValue)
    {
        /* @var $qhelper \Magento\Catalog\Helper\Product\Configuration */
        $qhelper = $this->_productConfig;
        $params = [
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
        ];
        return $qhelper->getFormattedOptionValue($qoptionValue, $params);
    }
	 public function getMessages()
    {
        $qmessages = [];
        $mquoteItem = $this->getItem();

        // Add basic messages occurring during this page load
        $qbaseMessages = $mquoteItem->getMessage(false);
        if ($qbaseMessages) {
            foreach ($qbaseMessages as $qmessage) {
                $qmessages[] = ['text' => $qmessage, 'type' => $mquoteItem->getHasError() ? 'error' : 'notice'];
            }
        }

        /* @var $msgcollection \Magento\Framework\Message\Collection */
        $msgcollection = $this->messageManager->getMessages(true, 'quote_item' . $mquoteItem->getId());
        if ($msgcollection) {
            $additionalMessages = $msgcollection->getItems();
            foreach ($additionalMessages as $qmessage) {
                /* @var $qmessage \Magento\Framework\Message\MessageInterface */
                $qmessages[] = [
                    'text' => $this->messageInterpretationStrategy->interpret($qmessage),
                    'type' => $qmessage->getType()
                ];
            }
        }
        $this->messageManager->getMessages(true, 'quote_item' . $mquoteItem->getId())->clear();

        return $qmessages;
    }

}
