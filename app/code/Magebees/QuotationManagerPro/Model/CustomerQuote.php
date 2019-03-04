<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerQuote extends DataObject 
{
  
    protected $_summaryQty;  
    protected $_productIds;
    protected $_eventManager;
    protected $_scopeConfig;
    protected $_items=[];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Checkout\Model\ResourceModel\Cart
     */
    protected $_resourceQuote;

    /**
     * @var Session
     */
    protected $_quoteSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState; 

    /**
     * @var ProductRepositoryInterface
     */
    protected $qproductRepository;
 
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magebees\QuotationManagerPro\Model\ResourceModel\Quote $resourceQuote,
        Session $quoteSession,
		\Magebees\QuotationManagerPro\Model\Backend\Session\Quote $backendSession,
		\Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem\CollectionFactory $quoteItemFactory, 
		 \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteRequest\CollectionFactory $quoteRequestCollFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,       
        ProductRepositoryInterface $qproductRepository,		
		  \Magento\Catalog\Helper\Product $catalogProduct,
		\Magebees\QuotationManagerPro\Model\Quote\Item\CompareItem $quoteItemCompare,
		\Magebees\QuotationManagerPro\Model\QuoteItem $quoteItem,
		\Magebees\QuotationManagerPro\Model\QuoteRequestFactory $quoteRequestFactory, 	
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper, 	
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
		  $this->_quoteItemFactory = $quoteItemFactory; 
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_resourceQuote = $resourceQuote;
        $this->_quoteSession = $quoteSession;
        $this->_backendSession = $backendSession;
        $this->_customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->stockRegistry = $stockRegistry;
        $this->stockState = $stockState;             
        $this->quoteItem = $quoteItem;             
		$this->_catalogProduct = $catalogProduct; 
		$this->quoteItemCompare = $quoteItemCompare;
		$this->_quoteRequestFactory = $quoteRequestFactory;       
        $this->productRepository = $qproductRepository;
		 $this->_quoteRequestCollFactory = $quoteRequestCollFactory;
		 $this->_quoteHelper = $quoteHelper;
		 parent::__construct($data);
    }

    public function getCustomerSession()
    {
        return $this->_customerSession;
    }
    /**
     * List of Quote items
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection|array
     */
    public function getItems()
    {
        if (!$this->getQuote()->getId()) {
            return [];
        }
        return $this->getQuote()->getItemsCollection();
    }

    /**
     * Retrieve array of cart product ids
     *
     * @return array
     */
    public function getQuoteProductIds()
    {
        $qproducts = $this->getData('product_ids');
        if ($qproducts === null) {
            $qproducts = [];
            foreach ($this->getQuote()->getAllItems() as $qitem) {
                $qproducts[$qitem->getProductId()] = $qitem->getProductId();
            }
            $this->setData('product_ids', $qproducts);
        }
        return $qproducts;
    }

    /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->hasData('quote')) {			
            $this->setData('quote', $this->_quoteSession->getQuote());
        }
        return $this->_getData('quote');
    }

    /**
     * Set quote object associated with the cart
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     * @codeCoverageIgnore
     */
    public function setQuote(\Magebees\QuotationManagerPro\Model\Quote $quote)
    {
        $this->setData('quote', $quote);
        return $this;
    }   
    /**
     * Get product object based on requested product information
     *
     * @param   Product|int|string $qproductInfo
     * @return  Product
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProduct($qproductInfo)
    {
        $qproduct = null;
        if ($qproductInfo instanceof Product) {
            $qproduct = $qproductInfo;
            if (!$qproduct->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the product.'));
            }
        } elseif (is_int($qproductInfo) || is_string($qproductInfo)) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $qproduct = $this->productRepository->getById($qproductInfo, false, $storeId);
            } catch (NoSuchEntityException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the product.'), $e);
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the product.'));
        }
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if (!is_array($qproduct->getWebsiteIds()) || !in_array($currentWebsiteId, $qproduct->getWebsiteIds())) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the product.'));
        }
        return $qproduct;
    }

    /**
     * Get request for product add to cart procedure
     *
     * @param   \Magento\Framework\DataObject|int|array $quoterequestInfo
     * @return  \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProductRequest($quoterequestInfo)
    {
        if ($quoterequestInfo instanceof \Magento\Framework\DataObject) {
            $quoterequest = $quoterequestInfo;
        } elseif (is_numeric($quoterequestInfo)) {
            $quoterequest = new \Magento\Framework\DataObject(['qty' => $quoterequestInfo]);
        } elseif (is_array($quoterequestInfo)) {
            $quoterequest = new \Magento\Framework\DataObject($quoterequestInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }
     

        return $quoterequest;
    }   
public function updateItem($qitemId, $quoterequestInfo = null, $updatingParams = null)
    {
        try {
            $qitem = $this->getQuote()->getItemById($qitemId);
            if (!$qitem) {
                throw new \Magento\Framework\Exception\LocalizedException(__('This quote item does not exist.'));
            }
            $qproductId = $qitem->getProduct()->getId();
            $qproduct = $this->_getProduct($qproductId);
            $quoterequest = $this->_getProductRequest($quoterequestInfo);

            if ($qproductId) {
                $qstockItem = $this->stockRegistry->getStockItem($qproductId, $qproduct->getStore()->getWebsiteId());
                $minimumProQty = $qstockItem->getMinSaleQty();
                // If product was not found in cart and there is set minimal qty for it
                if ($minimumProQty
                    && $minimumProQty > 0
                    && !$quoterequest->getQty()
                    && !$this->getQuote()->hasProductId($qproductId)
                ) {
                    $quoterequest->setQty($minimumProQty);
                }
            }

            $quoteresult = $this->getQuote()->updateItem($qitemId, $quoterequest, $updatingParams);
			
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_quoteSession->setUseNotice(false);
            $quoteresult = $e->getMessage();
        }

        /**
         * We can get string if updating process had some errors
         */
        if (is_string($quoteresult)) {
            if ($this->_quoteSession->getUseNotice() === null) {
                $this->_quoteSession->setUseNotice(true);
            }
            throw new \Magento\Framework\Exception\LocalizedException(__($quoteresult));
        }

      /*  $this->_eventManager->dispatch(
            'checkout_cart_product_update_after',
            ['quote_item' => $quoteresult, 'product' => $qproduct]
        );*/
        $this->_quoteSession->setLastAddedProductId($qproductId);
        return $quoteresult;
    }
 	public function removeItem($qitemId)
    {
	
        $qitem = $this->_quoteHelper->loadQuoteItemById($qitemId);
        if ($qitem) {
            /**
             * If we remove item from quote - we can't use multishipping mode
             */
       
            $qitem->isDeleted(true);
			 $qitems = $this->_quoteItemFactory->create();           
			$qitems->addFieldToFilter('parent_item_id',$qitemId);
			
			foreach($qitems as $child)
			{				
				 $child->delete();
			}
			$qitem->setChildren($qitems);
			 foreach ($qitem->getChildren() as $child) {
                    $child->isDeleted(true);
                }
           
        }

        return $qitem;
    }
 	public function compare($qitem)
    {
        return $this->quoteItemCompare->compare($this->quoteItem, $qitem);
    }
	  public function addProduct($qproductInfo, $quoterequestInfo = null)
    {
		
        $qproduct = $this->_getProduct($qproductInfo);
        $quoterequest = $this->_getProductRequest($quoterequestInfo);
        $qproductId = $qproduct->getId();

        if ($qproductId) {
            $qstockItem = $this->stockRegistry->getStockItem($qproductId, $qproduct->getStore()->getWebsiteId());
            $minimumProQty = $qstockItem->getMinSaleQty();
            //If product quantity is not specified in request and there is set minimal qty for it
            if ($minimumProQty
                && $minimumProQty > 0
                && !$quoterequest->getQty()
            ) {
                $quoterequest->setQty($minimumProQty);
            }
        }

        if ($qproductId) {
			  $this->_quoteSession->setLastAddedProductId($qproductId);
            try {				
                $quoteresult = $this->getQuote()->addProduct($qproduct, $quoterequest);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_quoteSession->setUseNotice(false);
                $quoteresult = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            if (is_string($quoteresult)) {
                if ($qproduct->hasOptionsValidationFail()) {
                    $redirectUrl = $qproduct->getUrlModel()->getUrl(
                        $qproduct,
                        ['_query' => ['startcustomization' => 1]]
                    );
                } else {
                    $redirectUrl = $qproduct->getProductUrl();
                }
                $this->_quoteSession->setRedirectUrl($redirectUrl);
                if ($this->_quoteSession->getUseNotice() === null) {
                    $this->_quoteSession->setUseNotice(true);
                }
                throw new \Magento\Framework\Exception\LocalizedException(__($quoteresult));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }      
		
      
        return $this;
    }
	public function addQuoteItem($qitem)
	{
		$this->_items[]=$qitem;
		
	}
	public function save()
	{
		foreach($this->_items as $qitem)
		{	
			 if (!$qitem->isDeleted()) {
				$qitem->save();
			 }
		}
		
	}
	public function truncate()
    {
        $quote_id=$this->getQuote()->getId();
		$this->getQuote()->load($quote_id)->delete();	
		$this->_quoteSession->setQuoteId(null);
       return $this;
    }
	 public function suggestItemsQty($data)
    {
        foreach ($data as $qitemId => $qitemInfo) {
            if (!isset($qitemInfo['qty'])) {
                continue;
            }
            $qty = (float)$qitemInfo['qty'];
            if ($qty <= 0) {
                continue;
            }

            $quoteItem = $this->getQuote()->getItemById($qitemId);
            if (!$quoteItem) {
                continue;
            }

            $qproduct = $quoteItem->getProduct();
            if (!$qproduct) {
                continue;
            }

            $data[$qitemId]['before_suggest_qty'] = $qty;
            $data[$qitemId]['qty'] = $this->stockState->suggestQty(
                $qproduct->getId(),
                $qty,
                $qproduct->getStore()->getWebsiteId()
            );
        }
        return $data;
    }
	 public function updateItems($data)
    {
        $infoDataObject = new \Magento\Framework\DataObject($data);
        $qtyRecalculatedFlag = false;
        foreach ($data as $qitemId => $qitemInfo) {
            $qitem = $this->getQuote()->getItemById($qitemId);
            if (!$qitem) {
                continue;
            }

            if (!empty($qitemInfo['remove']) || isset($qitemInfo['qty']) && $qitemInfo['qty'] == '0') {
                $this->removeItem($qitemId);
                continue;
            }
			$quoterequest_info=$qitemInfo['request_info'];
			$qitem->setRequestInfo($quoterequest_info);
			$qitem->save();
            $qty = isset($qitemInfo['qty']) ? (double)$qitemInfo['qty'] : false;
            if ($qty > 0) {
				
				
                $qitem->setQty($qty);
                if ($qitem->getHasError()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__($qitem->getMessage()));
                }
				else
				{
				$quoteReqData=$this->getQuoteRequestData($qitemId)->getData();
				$quoterequest_id=$quoteReqData[0]['request_id'];				
				$quoterequest=$this->_quoteRequestFactory->create()->load($quoterequest_id,'request_id');	
				$quoterequest->setRequestQty($qty);
				$quoterequest->save();
					$qitem->save();
				}

                if (isset($qitemInfo['before_suggest_qty']) && $qitemInfo['before_suggest_qty'] != $qty) {
                    $qtyRecalculatedFlag = true;
                    $this->messageManager->addNotice(
                        __('Quantity was recalculated from %1 to %2', $qitemInfo['before_suggest_qty'], $qty),
                        'quote_item' . $qitem->getId()
                    );
                }
            }
        }

        if ($qtyRecalculatedFlag) {
            $this->messageManager->addNotice(
                __('We adjusted product quantities to fit the required increments.')
            );
        }

        return $this;
    }
	public function getQuoteRequestData($qitemId)
	{
			 $quote_request = $this->_quoteRequestCollFactory->create();           
			$quote_request->addFieldToFilter('item_id',$qitemId)->addFieldToFilter('is_default',1);
			return $quote_request;
	}
   
    
}
