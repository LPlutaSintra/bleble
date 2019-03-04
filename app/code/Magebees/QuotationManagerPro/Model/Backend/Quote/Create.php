<?php

namespace Magebees\QuotationManagerPro\Model\Backend\Quote;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Model\Metadata\Form as CustomerForm;
use Magento\Framework\App\ObjectManager;
use Magebees\QuotationManagerPro\Model\QuoteItem;

class Create extends \Magento\Framework\DataObject 
{
   
   
    protected $_qsession;
    protected $_wishlist;
    protected $_cart;
    protected $_compareList;
    protected $_needCollect;
    protected $_needCollectCart = false;
    protected $_isValidate = false;
    protected $_errors = [];
    protected $_quote;
    protected $_qcoreRegistry = null;
    protected $_logger;
    protected $_eventManager = null;    
	protected $_objectManager;
    protected $_objectCopyService;
    protected $messageManager;
    protected $mquoteInitializer;
    protected $_scopeConfig;
    protected $stockRegistry; 
    protected $mquoteitemUpdater;
    protected $objectFactory;   
    protected $dataObjectHelper; 
  
  
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $coreRegistry,       
        \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $mquoteSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magebees\QuotationManagerPro\Model\Backend\Quote\Initializer $mquoteInitializer,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,       
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magebees\QuotationManagerPro\Model\Quote\Item\ItemUpdater $mquoteitemUpdater,
        \Magento\Framework\DataObject\Factory $objectFactory,     
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,            
        array $data = []
       
    ) {
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_qcoreRegistry = $coreRegistry;       
        $this->_qsession = $mquoteSession;
        $this->_logger = $logger;
        $this->_objectCopyService = $objectCopyService;
        $this->mquoteInitializer = $mquoteInitializer;
        $this->messageManager = $messageManager;
        $this->_scopeConfig = $scopeConfig;       
        $this->stockRegistry = $stockRegistry;
        $this->quoteItemUpdater = $mquoteitemUpdater;
        $this->objectFactory = $objectFactory;    
        $this->dataObjectHelper = $dataObjectHelper;             
        parent::__construct($data);
    }

   
    protected function _getQuoteItem($mquoteitem)
    {
        if ($mquoteitem instanceof \Magebees\QuotationManagerPro\Model\QuoteItem) {
            return $mquoteitem;
        } elseif (is_numeric($mquoteitem)) {
            return $this->getSession()->getQuote()->getItemById($mquoteitem);
        }

        return false;
    }

    /**
     * Retrieve session model object of quote
     *
     * @return \Magebees\QuotationManagerPro\Model\Backend\Session\Quote
     */
    public function getSession()
    {
        return $this->_qsession;
    }

    /**
     * Retrieve quote object model
     *
     * @return \Magebees\QuotationManagerPro\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->_quote) {			
            $this->_quote = $this->getSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Set quote object
     *
     * @param \Magebees\QuotationManagerPro\Model\Quote $quote
     * @return $this
     */
    public function setQuote(\Magebees\QuotationManagerPro\Model\Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    public function getCustomerGroupId()
    {
        $groupId = $this->getQuote()->getCustomerGroupId();
        if (!$groupId) {
            $groupId = $this->getSession()->getCustomerGroupId();
        }

        return $groupId;
    }   
   
    public function addProduct($quoteproduct, $config = 1)
    {
        if (!is_array($config) && !$config instanceof \Magento\Framework\DataObject) {
            $config = ['qty' => $config];
        }
        $config = new \Magento\Framework\DataObject($config);

        if (!$quoteproduct instanceof \Magento\Catalog\Model\Product) {
            $quoteproductId = $quoteproduct;
            $quoteproduct = $this->_objectManager->create(
                \Magento\Catalog\Model\Product::class
            )->setStore(
                $this->getSession()->getStore()
            )->setStoreId(
                $this->getSession()->getStoreId()
            )->load(
                $quoteproduct
            );
            if (!$quoteproduct->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We could not add a product to quote by the ID "%1".', $quoteproductId)
                );
            }
        }

        $mquoteitem = $this->mquoteInitializer->init($this->getQuote(), $quoteproduct, $config);

        if (is_string($mquoteitem)) {
            throw new \Magento\Framework\Exception\LocalizedException(__($mquoteitem));
        }
        $mquoteitem->checkItemData();
        $this->setRecollect(true);

        return $this;
    }

    /**
     * Add multiple products to current quote
     *
     * @param array $quoteproducts
     * @return $this
     */
    public function addQuoteProducts(array $quoteproducts)
    {
        foreach ($quoteproducts as $quoteproductId => $config) {
            $config['qty'] = isset($config['qty']) ? (double)$config['qty'] : 1;
            try {
                $this->addProduct($quoteproductId, $config);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(__($e->getMessage()));
            } catch (\Exception $e) {
                return $e;
            }
        }

        return $this;
    }

    /**
     * Update quantity of quote items
     *
     * @param array $mquoteitems
     * @return $this
     * @throws \Exception|\Magento\Framework\Exception\LocalizedException
     */
    public function updateQuoteItemsData($mquoteitems)
    {
        if (!is_array($mquoteitems)) {
            return $this;
        }

        try {
            foreach ($mquoteitems as $mquoteitemId => $quoteinfo) {
                if (!empty($quoteinfo['configured'])) {
                    $mquoteitem = $this->getQuote()->updateItem($mquoteitemId, $this->objectFactory->create($quoteinfo));
                    $quoteinfo['qty'] = (double)$mquoteitem->getQty();
                } else {
                    $mquoteitem = $this->getQuote()->getItemById($mquoteitemId);
                    if (!$mquoteitem) {
                        continue;
                    }
                    $quoteinfo['qty'] = (double)$quoteinfo['qty'];
                }
                $this->quoteItemUpdater->updateQuoteItem($mquoteitem, $quoteinfo);
                if ($mquoteitem && !empty($quoteinfo['action'])) {
                    $this->moveQuoteItem($mquoteitem, $quoteinfo['action'], $mquoteitem->getQty());
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            //$this->recollectCart();
            throw $e;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        //$this->recollectCart();

        return $this;
    }

    
    /**
     * Prepare options array for info buy request
     *
     * @param \Magebees\QuotationManagerPro\Model\QuoteItem $mquoteitem
     * @return array
     */
    protected function _prepareItemOptionsForRequest($mquoteitem)
    {
        $newInfoOptions = [];
        $itemoptionIds = $mquoteitem->getOptionByCode('option_ids');
        if ($itemoptionIds) {
            foreach (explode(',', $itemoptionIds->getValue()) as $itemoptionId) {
                $itemoption = $mquoteitem->getProduct()->getOptionById($itemoptionId);
                $itemoptionValue = $mquoteitem->getOptionByCode('option_' . $itemoptionId)->getValue();

                $group = $this->_objectManager->get(
                    \Magento\Catalog\Model\Product\Option::class
                )->groupFactory(
                    $itemoption->getType()
                )->setOption(
                    $itemoption
                )->setQuoteItem(
                    $mquoteitem
                );

                $newInfoOptions[$itemoptionId] = $group->prepareOptionValueForRequest($itemoptionValue);
            }
        }

        return $newInfoOptions;
    }
   
  
    /**
     * Prepare item otions
     *
     * @return $this
     */
    protected function _prepareQuoteItems()
    {
        foreach ($this->getQuote()->getAllItems() as $mquoteitem) {
            $itemoptions = [];
            $quoteproductOptions = $mquoteitem->getProduct()->getTypeInstance()->getOrderOptions($mquoteitem->getProduct());
            if ($quoteproductOptions) {
                $quoteproductOptions['info_buyRequest']['options'] = $this->_prepareItemOptionsForRequest($mquoteitem);
                $itemoptions = $quoteproductOptions;
            }
            $addOptions = $mquoteitem->getOptionByCode('additional_options');
            if ($addOptions) {
                $itemoptions['additional_options'] = $this->quoteHelper->getUnserializeData($addOptions->getValue());
            }
            $mquoteitem->setProductOrderOptions($itemoptions);
        }
        return $this;
    }

   
}
