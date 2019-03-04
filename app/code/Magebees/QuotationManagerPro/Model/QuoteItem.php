<?php
namespace Magebees\QuotationManagerPro\Model;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Exception\LocalizedException;

class QuoteItem extends AbstractExtensibleModel implements
    \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface
{   
	
	protected $_items;
	protected $_quote;
	protected $_quoterequest=[];
	protected $_optionsByCode = [];
	 protected $_options = [];
	 protected $_itemmessages = [];
	  protected $_flagOptionsSaved;
	 protected $_parentItem = null;   
    protected $_children = [];
	 protected $_notRepresentOptions = ['info_buyRequest'];
	 public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,  
		AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,  
		  \Magento\Framework\Locale\FormatInterface $localeFormat,
		//  \Magento\Catalog\Model\ProductFactory $productFactory,
		  \Magento\Catalog\Model\ProductRepository $productRepository,
		 \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
		   \Magento\Sales\Model\Status\ListFactory $statusListFactory,
		 \Magebees\QuotationManagerPro\Model\Quote\Item\CompareItem $quoteItemCompare,
		 \Magebees\QuotationManagerPro\Model\Quote\Item\OptionFactory $itemOptionFactory,
		 \Magebees\QuotationManagerPro\Model\QuoteRequestFactory $quoteRequestFactory, 		 \Magebees\QuotationManagerPro\Model\ResourceModel\Quote\Item\Option\CollectionFactory $itemOptionCollFactory,		 \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteRequest\CollectionFactory $quoteRequestCollFactory,		
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		  \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
		 \Magento\Framework\App\Request\Http $request,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,  
		 
        array $data = []
       
    ) {
      $this->_errorInfos = $statusListFactory->create();
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;        
        $this->_localeFormat = $localeFormat;
		 	$this->quoteItemCompare = $quoteItemCompare;
		   $this->_itemOptionFactory = $itemOptionFactory;
		   $this->_quoteRequestFactory = $quoteRequestFactory;
		   $this->_quoteRequestCollFactory = $quoteRequestCollFactory;
		   $this->_itemOptionCollFactory = $itemOptionCollFactory;
		 $this->quoteHelper = $quoteHelper;
		  $this->stockRegistry = $stockRegistry;
		 $this->request = $request;		 
		  //$this->productFactory = $productFactory;
		    $this->productRepository = $productRepository;
		   $this->priceCurrency = $priceCurrency;
		   $this->_coreRegistry = $registry;
		
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
     
    protected function _construct()
    {
        $this->_init(\Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem::class);
    }
	 public function addQty($itemqty)
    {
		if (!$this->getParentItem() || !$this->getId()) {
            $itemqty = $this->_prepareQty($itemqty);
            $this->setQtyToAdd($itemqty);
            $this->setQty($this->getQty() + $itemqty);
        }
        return $this;
    }
	
	 public function setOptions($itemoptions)
    {
        if (is_array($itemoptions)) {
            foreach ($itemoptions as $itemoption) {
                $this->addOption($itemoption);
            }
        }
        return $this;
    }
	  public function addOption($itemoption)
    {
		
        if (is_array($itemoption)) {		
            $itemoption = $this->_itemOptionFactory->create()->setData($itemoption)->setItem($this);
        } elseif ($itemoption instanceof \Magento\Framework\DataObject &&
            !$itemoption instanceof \Magebees\QuotationManagerPro\Model\Quote\Item\Option
        ) {				
		  $itemoption = $this->_itemOptionFactory->create()->setData(
                $itemoption->getData()
            )->setProduct(
                $itemoption->getProduct()
            )->setItem(
                $this
            );
        } elseif ($itemoption instanceof \Magebees\QuotationManagerPro\Model\Quote\Item\Option) {
            $itemoption->setItem($this);
        } else {
            throw new LocalizedException(__('We found an invalid item option format.'));
        }

        $exOption = $this->getOptionByCode($itemoption->getCode());
        if ($exOption) {
            $exOption->addData($itemoption->getData());
        } else {
            $this->_addOptionCode($itemoption);
            $this->_options[] = $itemoption;
        }
        return $this;
    }
	 public function setQty($itemqty)
    {
		$front_config=$this->quoteHelper->getFrontendConfig();
        $itemqty = $this->_prepareQty($itemqty);
        $oldQty = $this->getData('qty');
        $this->setData('qty', $itemqty);
		$enable_manage_stock=$front_config['enable_manage_stock'];
		if($enable_manage_stock)
		{
		$this->_eventManager->dispatch('magebees_quote_item_qty_set_after', ['item' => $this]);
		}
        if ($this->getQuote() && $this->getQuote()->getIgnoreOldQty()) {
            return $this;
        }
        if ($this->getUseOldQty()) {
            $this->setData('qty', $oldQty);
        }
        return $this;
    }

	protected function _prepareQty($itemqty)
    {
        $itemqty = $this->_localeFormat->getNumber($itemqty);		
        $itemqty = $itemqty > 0 ? $itemqty : 1;
        return $itemqty;
    }
	public function getPrice()
    {
			/**Start for save product price including tax */
		$product=$this->getProduct();
		$price_incl_tax=$this->quoteHelper->getProductPriceInclTax($product,$this->getData('price'),true);
		$this->setPriceInclTax($price_incl_tax);
		/**End for save product price including tax */
		$this->save();
        return $this->getData('price');
    }
    public function setPrice($price)
    {
        return $this->setData('price', $price);
    }
	 public function getCalculationPrice()
    {
       
		if ($this->getParentItem())
		{
			$finalPrice = $this->getParentItem()->getProduct()->getPriceModel()->getChildFinalPrice(
                $this->getParentItem()->getProduct(),
                $this->getParentItem()->getQty(),
                $this->getProduct(),
                $this->getQty()
            );
		}
		else
		{
		$finalPrice = $this->getProduct()->getFinalPrice($this->getQty());		
		}
		$this->setPrice($finalPrice);
		/**Start for save product price including tax */
		$product=$this->getProduct();
		$price_incl_tax=$this->quoteHelper->getProductPriceInclTax($product,$finalPrice,true);
		//$this->setPriceInclTax($price_incl_tax);
		/**End for save product price including tax */
		$this->save();	
        return $finalPrice;
    }

	public function getConvertedPrice()
    {
        $price = $this->getData('converted_price');
        if ($price === null) {
            $price = $this->priceCurrency->convert($this->getPrice(), $this->getStore());
            $this->setData('converted_price', $price);
        }
        return $price;
    }
    public function setQuote(\Magebees\QuotationManagerPro\Model\Quote $quote)
    {
        $this->_quote = $quote;	
        $this->setQuoteId($quote->getQuoteId());
        $this->setStoreId($quote->getStoreId());	
        return $this;
    }
    public function getQuote()
    {
		if(!$this->_quote)	
		{
		if($this->request->getParam('quote_id'))
			{
				$quote_id=$this->request->getParam('quote_id');
				$this->_quote = $this->quoteHelper->loadQuoteById($quote_id);
			}
			else
			{
				$this->_quote=$this->_coreRegistry->registry('current_quote');
			}
		}
		
        return $this->_quote;
    }  
	
	public function setQuoteRequest($item)
	{
	/* start for add/update entry in magebees_quote_request_item table when quote item add/update */
	$quoterequest=$this->_quoteRequestFactory->create()->setItem($item);
			$this->_quoterequest[]= $quoterequest;
	}
	
	public function setProduct($product)
    {
		
        if ($this->getQuote()) {
            $product->setStoreId($this->getQuote()->getStoreId());          
        }
        $this->setData('product', $product)
            ->setProductId($product->getId())
            ->setProductType($product->getTypeId())
            ->setSku($this->getProduct()->getSku())
            ->setName($product->getName())
            ->setWeight($this->getProduct()->getWeight())
            ->setTaxClassId($product->getTaxClassId())
            ->setBaseCost($product->getCost());
		$version=$this->quoteHelper->getMagentoVersion();
		 if(version_compare($version, '2.1.0', '<'))
		{
		 $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $this->setIsQtyDecimal($stockItem->getIsQtyDecimal());
		}
		else
		{
			 $stockItem = $product->getExtensionAttributes()->getStockItem();
        $this->setIsQtyDecimal($stockItem ? $stockItem->getIsQtyDecimal() : false);  
		}
        return $this;
    }
	 public function getProduct()
    {       
		$product = $this->getData('product');
        if ($product === null && $this->getProductId()) {
            $product = clone $this->productRepository->getById(
                $this->getProductId(),
                false,
                $this->getStoreId()
            );
            $this->setProduct($product);
        }

        /**
         * Reset product final price because it related to custom options
         */
        $product->setFinalPrice(null);
        if (is_array($this->_optionsByCode)) {
            $product->setCustomOptions($this->_optionsByCode);
        }
        return $product;
    }
	 public function checkItemData()
    {
        $this->setHasError(false);
        $this->clearMessage();
        $itemqty = $this->_getData('qty');

        try {
            $this->setQty($itemqty);
        } catch (LocalizedException $exception) {
            $this->setHasError(true);
            $this->setMessage($exception->getMessage());
        } catch (\Exception $exception) {			
            $this->setHasError(true);
            $this->setMessage(__('Item qty declaration error'));
        }

        try {
            $this->getProduct()->getTypeInstance()->checkProductBuyState($this->getProduct());
        } catch (LocalizedException $exception) {
            $this->setHasError(true)->setMessage($exception->getMessage());
            $this->getQuote()->setHasError(
                true
            )->addMessage(
                __('Some of the products below do not have all the required options.')
            );
        } catch (\Exception $exception) {
            $optdec_err='Something went wrong during the item options declaration.';
            $opt_err='We found an item options declaration error.';
            $this->setHasError(true)->setMessage(__($optdec_err));
            $this->getQuote()->setHasError(true)->addMessage(__($opt_err));
        }

        if ($this->getProduct()->getHasError()) {
            $optavl_err='Some of the selected options are not currently available.';
            $this->setHasError(true)->setMessage(__($optavl_err));
          
			$this->getQuote()->setHasError(true)->addMessage($this->getProduct()->getMessage(), 'options');
		  
		   }

        if ($this->getHasConfigurationUnavailableError()) {
            $optcom_err='Selected option(s) or their combination is not currently available.';
            $itemcom_err='Some item options or their combination are not currently available.';
            $this->setHasError(
                true
            )->setMessage(
                __($optcom_err)
            );
            $this->getQuote()->setHasError(
                true
            )->addMessage(
                __($itemcom_err),
                'unavailable-configuration'
            );
            $this->unsHasConfigurationUnavailableError();
        }

        return $this;
    }
	 public function clearMessage()
    {
        $this->unsMessage();
        // For older compatibility, when we kept message inside data array
        $this->_itemmessages = [];
        return $this;
    }

	 public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
		
        if (!$product || $itemProduct->getId() != $product->getId()) {
            return false;
        }
        $stickWithinParent = $product->getStickWithinParent();
        if ($stickWithinParent) {
            if ($this->getParentItem() !== $stickWithinParent) {
                return false;
            }
        }

        // Check options
        $itemOptions = $this->getItemOptionsColl($this->getId());
        $productOptions = $product->getCustomOptions();

        if (!$this->compareOptions($itemOptions, $productOptions)) {
            return false;
        }
        if (!$this->compareOptions($productOptions, $itemOptions)) {
            return false;
        }
        return true;
    }
	public function getItemOptionsColl($itemId)
	{
		$itemoption_arr=array();		
		$opt_coll=$this->_itemOptionCollFactory->create()->addFieldToFilter('item_id',$itemId)->getData();
		
		 foreach($opt_coll as $coll){
		$itemoption_id=$coll['option_id'];	
		$itemoption_code=$coll['code'];	
			 $itemoption=$this->_itemOptionFactory->create()->load($itemoption_id,'option_id');
			$itemoption_arr[$itemoption_code]=$itemoption;	
		 }
		 return $itemoption_arr;
	}

    public function compareOptions($itemoptions1, $itemoptions2)
    {
		if($itemoptions1)
		{
        foreach ($itemoptions1 as $itemoption) {
            $code = $itemoption->getCode();
            if (in_array($code, $this->_notRepresentOptions)) {
                continue;
            }
            if (!isset($itemoptions2[$code]) || $itemoptions2[$code]->getValue() != $itemoption->getValue()) {
                return false;
            }
        }
		}
        return true;
    }
	   public function getOptionByCode($code)
    {
        if (isset($this->_optionsByCode[$code]) && !$this->_optionsByCode[$code]->isDeleted()) {
            return $this->_optionsByCode[$code];
        }
        return null;
    }
	 public function getItemOptionByCode($code,$itemId)
	 {
		
		 $opt_coll=$this->_itemOptionCollFactory->create()->addFieldToFilter('item_id',$itemId)->addFieldToFilter('code',$code)->getData();
		
		 if(isset($opt_coll[0])){
		$itemoption_id=$opt_coll[0]['option_id'];		 
		 $itemoption=$this->_itemOptionFactory->create()->load($itemoption_id,'option_id');
		return $itemoption;
		 }
		  return null;
	 }
	 public function getFileDownloadParams()
    {
        return null;
    }
	 public function getBuyRequest()
    {
		$itemId=$this->getId();
        $itemoption = $this->getItemOptionByCode('info_buyRequest',$itemId);
        $data = $itemoption ? $this->quoteHelper->getUnserializeData($itemoption->getValue()) : [];
        $buyRequest = new \Magento\Framework\DataObject($data);

        // Overwrite standard buy request qty, because item qty could have changed since adding to quote
        $buyRequest->setOriginalQty($buyRequest->getQty())->setQty($this->getQty() * 1);

        return $buyRequest;
    }
	 public function addErrorInfo($origin = null, $code = null, $itemmessage = null, $additionalData = null)
    {
        $this->_errorInfos->addItem($origin, $code, $itemmessage, $additionalData);
        if ($itemmessage !== null) {			
            $this->setMessage($itemmessage);
        }
        $this->_setHasError(true);

        return $this;
    }
	 protected function _setHasError($flag)
    {
        return $this->setData('has_error', $flag);
    }
	public function setMessage($itemmessages)
    {
        $itemMessagesExists = $this->getMessage(false);
        if (!is_array($itemmessages)) {
            $itemmessages = [$itemmessages];
        }
        foreach ($itemmessages as $itemmessage) {
            if (!in_array($itemmessage, $itemMessagesExists)) {
                $this->addMessage($itemmessage);
            }
        }
        return $this;
    }

    public function addMessage($itemmessage)
    {
        $this->_itemmessages[] = $itemmessage;
        return $this;
    }
	 public function getMessage($string = true)
    {
        if ($string) {
            return join("\n", $this->_itemmessages);
        }
        return $this->_itemmessages;
    }
	 public function compare($item)
    {
        return $this->quoteItemCompare->compare($this, $item);
    }
	 public function getOptions()
    {
        return $this->_options;
    }
	 protected function _addOptionCode($itemoption)
    {
        if (!isset($this->_optionsByCode[$itemoption->getCode()])) {
            $this->_optionsByCode[$itemoption->getCode()] = $itemoption;
        } else {
            throw new LocalizedException(
                __('An item option with code %1 already exists.', $itemoption->getCode())
            );
        }
        return $this;
    }
	
	 
	 public function saveQuoteItemOptions()
    {
	
        foreach ($this->_options as $index => $itemoption) {
			
            if ($itemoption->isDeleted()) {
                $itemoption->delete();
                unset($this->_options[$index]);
                unset($this->_optionsByCode[$itemoption->getCode()]);
            } else {
                $itemoption->save();
            }
        }

        $this->_flagOptionsSaved = true;
        // Report to watchers that options were saved

        return $this;
    }
	  public function setIsOptionsSaved($flag)
    {
        $this->_flagOptionsSaved = $flag;
    }
	 public function isOptionsSaved()
    {
        return $this->_flagOptionsSaved;
    }
 	public function afterSave()
    {
		/* update entry for quote updated time */
		$quote_id=$this->getQuoteId();
		if($quote_id)
		{
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$quote->setUpdatedAt($this->quoteHelper->getGmtTime());
		$quote->save();
		}
		/** end update entry for quote updated time **/
		// save item option and request quantity
        $this->saveQuoteItemOptions();
		$this->saveRequestItem();
		/**Start for save item and option in default quote_item table and set the 'is_magebees_item' to 1 */
		/*$itemId=$this->getId();
		$productId=$this->getProductId();
		$product=$this->quoteHelper->loadProduct($productId);
		$this->getBuyRequest();*/
		/**/
        return parent::afterSave();
    }
	public function saveRequestItem()
	{
	/* start for add/update entry in magebees_quote_request_item table when quote item add/update */
		 foreach ($this->_quoterequest as $index => $quoterequest) {
			// print_r($quoterequest->getData());die;
			 $item_id=$quoterequest->getItem()->getId();
			 $quote_id=$this->getQuoteId();
			 $load_item=$this->quoteHelper->getProductIdConfig($item_id)->getData();
			
			 if(isset($load_item[0]['id']))
			 {
				 $load_item_id=$load_item[0]['id'];
				 $load_item_opt_data=$this->quoteHelper->getItemOptByItemId($load_item_id)->getData();
				$product_id=$load_item_opt_data[0]['product_id'];					
			 }
			 else
			 {
				 $product_id=$quoterequest->getItem()->getProduct()->getId();				
			 }
			 
		$quoteReqExist=$this->checkQuoteRequestExist($quoterequest->getItem()->getId(),$this->getQuoteId());	
		$price_incl_tax=$this->quoteHelper->getProductPriceInclTax($quoterequest->getItem()->getProduct(),$quoterequest->getItem()->getPrice(),true);
			
			 $_product=$this->quoteHelper->loadProduct($product_id);
		 if(!empty($quoteReqExist))
		 {
			 $req_id=$quoteReqExist[0]['request_id'];					 
			$quoterequests=$this->_quoteRequestFactory->create()->load($req_id,'request_id');
			 $quoterequests->setQuoteId($this->getQuoteId());
			$quoterequests->setItemId($quoterequest->getItem()->getId());
			$quoterequests->setProductId($quoterequest->getItem()->getProduct()->getId());
			$quoterequests->setRequestQty($quoterequest->getItem()->getQty());	
			$quoterequests->setRequestQtyPrice($quoterequest->getItem()->getPrice());
			$quoterequests->setReqQtyPriceInclTax($price_incl_tax);
			 if($_product->getCost())
			 {
				 $quoterequests->setCostPrice($_product->getCost());
			 }
			$quoterequests->setIsDefault(1);
			$quoterequests->save();
		 }	
		 else
		 {
			$quoterequest->setQuoteId($this->getQuoteId());
			$quoterequest->setItemId($quoterequest->getItem()->getId());
			$quoterequest->setProductId($quoterequest->getItem()->getProduct()->getId());
			$quoterequest->setRequestQty($quoterequest->getItem()->getQty());
			$quoterequest->setRequestQtyPrice($quoterequest->getItem()->getPrice());	
			$quoterequest->setReqQtyPriceInclTax($price_incl_tax);
			  if($_product->getCost())
			 {
				 $quoterequest->setCostPrice($_product->getCost());
			 }
			$quoterequest->setIsDefault(1);
			$quoterequest->save();
		 }
		
			 
		 }
		 return;
					 	
	}
	public function checkQuoteRequestExist($itemId,$quoteId)
	{
		$quoterequest=$this->_quoteRequestCollFactory->create()->addFieldToFilter('item_id',$itemId)->addFieldToFilter('quote_id',$quoteId)->getData();
		return $quoterequest;
	}
	public function beforeSave()
	{
		  parent::beforeSave();
        if ($this->getParentItem()) {
            $this->setParentItemId($this->getParentItem()->getId());
        }	
		$this->setStoreId($this->_storeManager->getStore()->getId());		
        return $this;
	}
	  public function setParentItem($parentItem)
    {
        if ($parentItem) {
            $this->_parentItem = $parentItem;			
            $parentItem->addChild($this);
        }
        return $this;
    }

    public function getParentItem()
    {
        return $this->_parentItem;
    }
	   public function addChild($child)
    {
        $this->setHasChildren(true);
        $this->_children[] = $child;
        return $this;
    }
	   public function getChildren()
    {
        return $this->_children;
    }

   public function getItemQtyOptions()
    {
        $itemqtyOptions = $this->getData('qty_options');
        if ($itemqtyOptions === null) {
            $productIds = [];
            $itemqtyOptions = [];
			
            foreach ($this->getOptions() as $itemoption) {
              
                if (is_object($itemoption->getProduct())
                    && $itemoption->getProduct()->getId() != $this->getProduct()->getId()
                ) {
                    $productIds[$itemoption->getProduct()->getId()] = $itemoption->getProduct()->getId();
                }
            }

            foreach ($productIds as $productId) {
                $itemoption = $this->getOptionByCode('product_qty_' . $productId);
                if ($itemoption) {
                    $itemqtyOptions[$productId] = $itemoption;
                }
            }

            $this->setData('qty_options', $itemqtyOptions);
        }

        return $itemqtyOptions;
    }
  
    public function setItemQtyOptions($itemqtyOptions)
    {
        return $this->setData('qty_options', $itemqtyOptions);
    }
	    public function updateItemQtyOption(\Magento\Framework\DataObject $itemoption, $value)
    {
        $itemoptionProductObj = $itemoption->getProduct();
        $itemoptions = $this->getItemQtyOptions();

        if (isset($itemoptions[$itemoptionProductObj->getId()])) {
            $itemoptions[$itemoptionProductObj->getId()]->setValue($value);
        }

        $this->getProduct()->getTypeInstance()->updateQtyOption(
            $this->getOptions(),
            $itemoption,
            $value,
            $this->getProduct()
        );

        return $this;
    }
	 public function removeErrorInfosByParams($params)
    {
        $removedQuoteItems = $this->_errorInfos->removeItemsByParams($params);
        foreach ($removedQuoteItems as $item) {
            if ($item['message'] !== null) {
                $this->removeMessageByText($item['message']);
            }
        }

        if (!$this->_errorInfos->getItems()) {
            $this->_setHasError(false);
        }

        return $this;
    }
  public function removeMessageByText($text)
    {
        foreach ($this->_itemmessages as $key => $itemmessage) {
            if ($itemmessage == $text) {
                unset($this->_itemmessages[$key]);
            }
        }
        return $this;
    }


}