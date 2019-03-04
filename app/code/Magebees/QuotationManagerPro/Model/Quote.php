<?php
namespace Magebees\QuotationManagerPro\Model;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use \Magebees\QuotationManagerPro\Model\Quote\Status;

class Quote extends AbstractExtensibleModel 
{
     protected $_items;
     protected $_quoteitems;
	  protected $_statusListFactory;
	 protected $quotemessageFactory;
	  protected $_quoteErrorInfoGroups = [];
	 public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,  
		AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,  
		\Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem\CollectionFactory $quoteItemFactory,    
		 \Magebees\QuotationManagerPro\Model\Session $quoteSession,
		 \Magebees\QuotationManagerPro\Model\CustomerQuote $customerQuote,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		 \Magento\Catalog\Helper\Product $catalogProduct,
        \Magebees\QuotationManagerPro\Model\Quote\Item\Processor $quoteitemProcessor,  
		  \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		   \Magento\Sales\Model\Status\ListFactory $statusListFactory,
		   \Magento\Directory\Model\CurrencyFactory $currencyFactory,
		  \Magento\Framework\Message\Factory $quotemessageFactory,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,       
        array $data = []
       
    ) {
		$this->messageFactory = $quotemessageFactory;
       $this->_statusListFactory = $statusListFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;        
        $this->_quoteItemFactory = $quoteItemFactory;  
		$this->_quoteSession = $quoteSession;
		$this->_customerQuote = $customerQuote;
		 $this->_catalogProduct = $catalogProduct; 
		  $this->productRepository = $productRepository;
        $this->itemProcessor = $quoteitemProcessor;         
        $this->quoteHelper = $quoteHelper;   
		 $this->_currencyFactory = $currencyFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,  
			$customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magebees\QuotationManagerPro\Model\ResourceModel\Quote::class);
    }
	public function addProduct(
        \Magento\Catalog\Model\Product $product,
        $qrequest = null,
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ) {
		
		$quote_id=$this->getQuoteId();
        if ($qrequest === null) {
            $qrequest = 1;
        }
        if (is_numeric($qrequest)) {
            $qrequest = $this->objectFactory->create(['qty' => $qrequest]);
        }
        if (!$qrequest instanceof \Magento\Framework\DataObject) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

		$front_config=$this->quoteHelper->getFrontendConfig();
		$enable_manage_stock=$front_config['enable_manage_stock'];
		if($enable_manage_stock)
		{
        if (!$product->isSalable()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Product that you are trying to add is not available.')
            );
        }
		}

        $quoteCandidates = $product->getTypeInstance()->prepareForCartAdvanced($qrequest, $product, $processMode);

        /**
         * Error message
         */
        if (is_string($quoteCandidates) || $quoteCandidates instanceof \Magento\Framework\Phrase) {
            return strval($quoteCandidates);
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($quoteCandidates)) {
            $quoteCandidates = [$quoteCandidates];
        }

        $parentItem = null;
        $errors = [];
        $quoteitem = null;
        $quoteitems = [];
        foreach ($quoteCandidates as $quoteCandidate) {
            // Child items can be sticked together only within their parent
            $stickWithinParent = $quoteCandidate->getParentProductId() ? $parentItem : null;
            $quoteCandidate->setStickWithinParent($stickWithinParent);
 			         
           
            $quoteitem = $this->getItemByProduct($quoteCandidate);
            if (!$quoteitem) {	
			
                $quoteitem = $this->itemProcessor->init($quoteCandidate, $qrequest);
                $quoteitem->setQuote($this);
                $quoteitem->setOptions($quoteCandidate->getCustomOptions());
                $quoteitem->setProduct($quoteCandidate);
                // Add only item that is not in quote already
                $this->addItem($quoteitem);
            }
			else
			{
				$quoteitem->setQuote($this);
			}
			 $quoteitem->setStoreId($this->_storeManager->getStore()->getId());
            $quoteitems[] = $quoteitem;

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $quoteitem;
            }
            if ($parentItem && $quoteCandidate->getParentProductId() && !$quoteitem->getParentItem()) {
                $quoteitem->setParentItem($parentItem);
            }

            $this->itemProcessor->prepare($quoteitem, $qrequest, $quoteCandidate);
			
	
            // collect errors instead of throwing first one
            if ($quoteitem->getHasError()) {
				
                foreach ($quoteitem->getMessage(false) as $quotemessage) {
                    if (!in_array($quotemessage, $errors)) {
                        // filter duplicate messages
                        $errors[] = $quotemessage;
                    }
                }
            }
			
        }
        if (!empty($errors)) {
            throw new \Magento\Framework\Exception\LocalizedException(__(implode("\n", $errors)));
        }       
        return $parentItem;
    }

	 public function getItemByProduct($product)
    {
        foreach ($this->getAllItems() as $quoteitem) {
          if ($quoteitem->representProduct($product)) {
                return $quoteitem;
            }
			
        }
        return false;
    }
	public function getAllItems()
    {
        $quoteitems = [];
        foreach ($this->getItemsCollection() as $quoteitem) {
			
            /** @var \Magento\Quote\Model\ResourceModel\Quote\Item $quoteitem */
            if (!$quoteitem->isDeleted()) {
                $quoteitems[] = $quoteitem;
            }
			
        }
        return $quoteitems;
    }
	 public function getAllVisibleItems()
    {
		
        $quoteitems = [];
        foreach ($this->getItemsCollection() as $quoteitem) {          
			$quoteitems[] = $quoteitem;			
        }
		
        return $quoteitems;
    }
	  public function getItemsCollection($useCache = true)
    {
        if ($this->hasItemsCollection()) {			
            return $this->getData('items_collection');
        }
        if (null === $this->_items) {	
			
            $this->_items = $this->_quoteItemFactory->create();
           // $this->extensionAttributesJoinProcessor->process($this->_items);
       	 	$this->_items->setQuote($this);			
			//$quote_id=$this->_quoteSession->getQuoteId();
			//$quote_id=$this->getId();
			//$this->_items->addFieldToFilter('quote_id',$quote_id);
       }	
	
        return $this->_items;
    }
	 public function getQuotePageItemsCollection($useCache = true)
    {		
	 // if (null === $this->_items) {
		  
           $this->_items = $this->_quoteItemFactory->create();
           // $this->extensionAttributesJoinProcessor->process($this->_items);
       	 	$this->_items->setQuote($this);
			$quote_id=$this->_quoteSession->getQuoteId();
			$this->_items->addFieldToFilter('quote_id',$quote_id);
			$this->_items->addFieldToFilter('parent_item_id', ['null' => true]);	
		
	  //}
        return $this->_items;
    }
	 public function addItem(\Magebees\QuotationManagerPro\Model\QuoteItem $quoteitem)
    {
        $quoteitem->setQuote($this);
        if (!$quoteitem->getId()) {
            $this->getItemsCollection()->addItem($quoteitem);          
        }
        return $this;
    }
	 public function getItemsCount()
    {
		$count=0;
        $quoteitems = [];
        foreach ($this->getQuotePageItemsCollection() as $quoteitem) {
			$count=$count+($quoteitem->getQty());
         // $count++;
        }
		
        return $count;
    }
	  public function hasItems()
    {
        return sizeof($this->getAllItems()) > 0;
    }
	 public function getItemById($quoteitemId)
    {
	
        foreach ($this->getItemsCollection() as $quoteitem) {			
            if ($quoteitem->getId() == $quoteitemId) {
                return $quoteitem;
            }
        }

        return false;
    }
 public function updateItem($quoteitemId, $buyRequest, $params = null)
    {
        $quoteitem = $this->getItemById($quoteitemId);
        if (!$quoteitem) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This is the wrong quote item id to update configuration.')
            );
        }
        $productId = $quoteitem->getProduct()->getId();

        //We need to create new clear product instance with same $productId
        //to set new option values from $buyRequest
        $product = clone $this->productRepository->getById($productId, false,$this->_storeManager->getStore()->getId());

        if (!$params) {
            $params = new \Magento\Framework\DataObject();
        } elseif (is_array($params)) {
            $params = new \Magento\Framework\DataObject($params);
        }
        $params->setCurrentConfig($quoteitem->getBuyRequest());
        $buyRequest = $this->_catalogProduct->addParamsToBuyRequest($buyRequest, $params);

        $buyRequest->setResetCount(true);
        $resultQuoteItem = $this->addProduct($product, $buyRequest);

        if (is_string($resultQuoteItem)) {
            throw new \Magento\Framework\Exception\LocalizedException(__($resultQuoteItem));
        }

        if ($resultQuoteItem->getParentItem()) {
            $resultQuoteItem = $resultQuoteItem->getParentItem();
        }

        if ($resultQuoteItem->getId() != $quoteitemId) {
			
            /**
             * Product configuration didn't stick to original quote item
             * It either has same configuration as some other quote item's product or completely new configuration
             */
            $this->_customerQuote->removeItem($quoteitemId)->save();
            $quoteitems = $this->getAllItems();
            foreach ($quoteitems as $quoteitem) {
                if ($quoteitem->getProductId() == $productId && $quoteitem->getId() != $resultQuoteItem->getId()) {
                    if ($resultQuoteItem->compare($quoteitem)) {
					
                        // Product configuration is same as in other quote item
	/*commented below line for solve issue in backend for update configurable item gives wrong quantity*/
						
                       // $resultQuoteItem->setQty($resultQuoteItem->getQty() + $quoteitem->getQty());
                        $this->_customerQuote->removeItem($quoteitem->getId())->save();
                        break;
                    }
                }
            }
			$this->_customerQuote->save();
        } else {
			
			 $resultQuoteItem->setQty($buyRequest->getQty());
			$resultQuoteItem->save();
        }

        return $resultQuoteItem;
    }
public function addErrorInfo(
        $type = 'error',
        $origin = null,
        $errorcode = null,
        $quotemessage = null,
        $additionalData = null
    ) {
        if (!isset($this->_quoteErrorInfoGroups[$type])) {
            $this->_quoteErrorInfoGroups[$type] = $this->_statusListFactory->create();
        }

        $this->_quoteErrorInfoGroups[$type]->addItem($origin, $errorcode, $quotemessage, $additionalData);

        if ($quotemessage !== null) {
            $this->addMessage($quotemessage, $type);
        }
        $this->_setHasError(true);

        return $this;
    }
 public function addMessage($quotemessage, $index = 'error')
    {
        $quotemessages = $this->getData('messages');
        if (null === $quotemessages) {
            $quotemessages = [];
        }

        if (isset($quotemessages[$index])) {
            return $this;
        }

        $quotemessage = $this->messageFactory->create(\Magento\Framework\Message\MessageInterface::TYPE_ERROR, $quotemessage);

        $quotemessages[$index] = $quotemessage;
        $this->setData('messages', $quotemessages);
        return $this;
    }
	
	 protected function _setHasError($flag)
    {		
        return $this->setData('has_error', $flag);
    }
	 public function removeErrorInfosByParams($type, $params)
    {
        if ($type && !isset($this->_quoteErrorInfoGroups[$type])) {
            return $this;
        }

        $quoteErrorLists = [];
        if ($type) {
            $quoteErrorLists[] = $this->_quoteErrorInfoGroups[$type];
        } else {
            $quoteErrorLists = $this->_quoteErrorInfoGroups;
        }

        foreach ($quoteErrorLists as $type => $quoteErrorList) {
            $removedItems = $quoteErrorList->removeItemsByParams($params);
            foreach ($removedItems as $quoteitem) {
                if ($quoteitem['message'] !== null) {
                    $this->removeMessageByText($type, $quoteitem['message']);
                }
            }
        }

        $quoteErrorsExist = false;
        foreach ($this->_quoteErrorInfoGroups as $quoteErrorListCheck) {
            if ($quoteErrorListCheck->getItems()) {
                $quoteErrorsExist = true;
                break;
            }
        }
        if (!$quoteErrorsExist) {
            $this->_setHasError(false);
        }

        return $this;
    }
	  public function removeMessageByText($type, $text)
    {
        $quotemessages = $this->getData('messages');
        if (null === $quotemessages) {
            $quotemessages = [];
        }

        if (!isset($quotemessages[$type])) {
            return $this;
        }

        $quotemessage = $quotemessages[$type];
        if ($quotemessage instanceof \Magento\Framework\Message\AbstractMessage) {
            $quotemessage = $quotemessage->getText();
        } elseif (!is_string($quotemessage)) {
            return $this;
        }
        if ($quotemessage == $text) {
            unset($quotemessages[$type]);
            $this->setData('messages', $quotemessages);
        }
        return $this;
    }
	 public function removeItem($quoteitemId)
    {
        $quoteitem = $this->getItemById($quoteitemId);

        if ($quoteitem) {
            $quoteitem->setQuote($this);
            /**
             * If we remove item from quote - we can't use multishipping mode
             */
            $this->setIsMultiShipping(false);			
            $quoteitem->isDeleted(true);
            if ($quoteitem->getHasChildren()) {
                foreach ($quoteitem->getChildren() as $child) {
                    $child->isDeleted(true);
                }
            }

            $parent = $quoteitem->getParentItem();
            if ($parent) {
                $parent->isDeleted(true);
            }
     
        }

        return $this;
    }
	 public function removeAllItems()
    {	
        foreach ($this->getItemsCollection() as $quoteitemId => $quoteitem) {
			
            if ($quoteitem->getId() === null) {
                $this->getItemsCollection()->removeItemByKey($quoteitemId);
            } else {				
                $quoteitem->isDeleted(true);
            }
        }		
        return $this;
    }
	public function loadByIdWithoutStore($quoteId)
    {
        $this->_getResource()->loadByIdWithoutStore($this, $quoteId);
        //$this->_afterLoad();
        return $this;
    }
		public function loadByCustomer($customer)
    {
       
        if ($customer instanceof \Magento\Customer\Model\Customer || $customer instanceof CustomerInterface) {
            $customerId = $customer->getId();
        } else {
            $customerId = (int)$customer;
        }
        $this->_getResource()->loadByCustomerId($this, $customerId);     
        return $this;
    }
	public function getStoreId()
    {
        if (!$this->hasStoreId()) {
            return $this->_storeManager->getStore()->getId();
        }
        return (int)$this->_getData('store_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', (int)$storeId);
        return $this;
    }

    /**
     * Get quote store model object
     *
     * @return  \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->getStoreId());
    }
	
	public function formatPriceTxt($quoteitemPrice)
	{
		 $orderCurrency = $this->_currencyFactory->create();
            $orderCurrency->load($this->getCurrencyCode());
		 return $orderCurrency->formatTxt($quoteitemPrice);
		
	}

}