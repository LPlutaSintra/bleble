<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;

class addressData extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magento\Shipping\Model\Config $shipconfig,
		     \Magento\Checkout\Model\Session $checkoutSession,	
		    \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
		  \Magebees\QuotationManagerPro\Model\QuoteAddressFactory $quoteAddressFactory,
		  \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteAddress\CollectionFactory $quoteQuoteAddressCollFactory,
		    \Magento\Quote\Model\Quote\ItemFactory $itemFactory,
		  	\Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory,		  
		    \Magento\Checkout\Model\Cart $cart,
		     \Magento\Framework\Registry $registry,
		    \Magento\Store\Model\StoreManagerInterface $storeManager,
		   ProductRepositoryInterface $productRepository,
		   CustomerSession $customerSession,
		   \Magebees\QuotationManagerPro\Model\QuoteItemFactory $quoteItemFactory,	
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper
    ) {
    
        parent::__construct($context);
		   $this->_quoteItemFactory = $quoteItemFactory;
		   $this->_quoteFactory = $quoteFactory;
		  $this->quoteHelper=$quoteHelper;
		  $this->shipconfig=$shipconfig;
		   $this->scopeConfig = $scopeConfig;
		    $this->cart = $cart;
		    $this->itemFactory = $itemFactory;
		    $this->_storeManager = $storeManager;
		   $this->productRepository = $productRepository;  
		    $this->addressRepository = $addressRepository;
		  $this->_quoteAddressFactory = $quoteAddressFactory;
		  $this->_quoteQuoteAddressCollFactory = $quoteQuoteAddressCollFactory;
		   $this->_coreRegistry = $registry;
		   $this->_customerSession = $customerSession;
		   $this->checkoutSession = $checkoutSession;
       
      
    }
    public function execute()
    {
		
        	$result = [];
			$params = $this->getRequest()->getParams();	
			$quote_id=$params['quote_id'];
			$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        		
		$previous_cart_items=$this->cart->getQuote()->getAllVisibleItems();
			if(isset($params['shipping_add_id']))
			{	
				if($params['shipping_add_id']!='')
				{
			$shipping_add_id=$params['shipping_add_id'];				
			$shipping_add=$this->quoteHelper->getAddressObjFromId($shipping_add_id);					
			$quote_address=$this->_quoteQuoteAddressCollFactory->create()->addFieldToFilter('quote_id',$quote_id)->addFieldToFilter('address_type','shipping')->getData();
		if(isset($quote_address[0]))
		{
			$custom_ship_add_id=$quote_address[0]['address_id'];
					$custom_quote_address=$this->_quoteAddressFactory->create()->load($custom_ship_add_id,'address_id');
					$custom_quote_address->setIsDefaultAddress(0)->save();
		}
					
					$quote=$this->_quoteFactory->create()->load($quote_id,'quote_id');	
					$quote->setshipAddressId($params['shipping_add_id'])->save();
					
				}
				else if(isset($params['custom_shipping_add_id']))
				{
					$shipping_add_id=$params['custom_shipping_add_id'];		
					$shipping_add=$this->quoteHelper->getCustomAddressById($shipping_add_id);
					$custom_quote_address=$this->_quoteAddressFactory->create()->load($shipping_add_id,'address_id');
					$custom_quote_address->setIsDefaultAddress(1)->save();
				}
			$city=$shipping_add['city'];
			$country_id=$shipping_add['country_id'];
			$postcode=$shipping_add['postcode'];	
			$result['city']=$city;
			$result['country_id']=$country_id;
			$result['postcode']=$postcode;
		/**Start for set custom quote tax shipping address*/
				  $this->_customerSession->setDefaultTaxShippingAddress(
                            [
                                'country_id' =>$country_id,
                                'region_id'  =>null,
                                'postcode'   =>$postcode,
                            ]
                        );
				/*End for set custom quote tax shipping address*/
			}
		   else
		   {
			   $country_id=$params['country_id'];
			   $result['country_id']=$country_id;
				//echo "ok";die;
			}
		
		
		$quote_items=$this->cart->getQuote()->getItemsCollection();
		foreach($quote_items as $q)
		{
			if($q->getIsMagebeesItem())
			{
			 $itemId=$q->getId();
			 $model =$this->itemFactory->create();
            $model->load($itemId);
            $model->delete();
			}
			
		}
		
			$items=$this->quoteHelper->getItemsByQuoteId($quote_id);
			$items->addFieldToFilter('parent_item_id', ['null' => true]);
			foreach($items as $item)
			{
				$item_id=$item->getId();
				$product_id=$item->getProductId();	
				$storeId = $this->_storeManager->getStore()->getId();
				$product = $this->productRepository->getById($product_id, false, $storeId, true);
				
				$quotationtierQty=$this->quoteHelper->getDynamicQuoteQty($item_id,$quote_id,$product_id);
if(count($quotationtierQty->getData())>0){
		foreach($quotationtierQty as $qty):
			$qty->setRequestQtyPrice($item->getPrice());	
			$qty_price_incl_tax=$this->quoteHelper->getProductPriceInclTax($product,$item->getPrice(),true);
			$qty->setReqQtyPriceInclTax($qty_price_incl_tax);		
			$qty->save();
		endforeach;
	}	
				 
		/*Start for save include tax price in 'magebees_quote_item' table for  when change the address*/
				$price_incl_tax=$this->quoteHelper->getProductPriceInclTax($product,$item->getPrice(),true);
			//	echo $item_id;die;
				$quoteItem=$this->_quoteItemFactory->create()->load($item_id,'id');
				$quoteItem->setPriceInclTax($price_incl_tax);
				$quoteItem->save();
		/*End for save price include tax when change the address*/
				 $option = $item->getItemOptionByCode('info_buyRequest',$item->getId());
				
        $data = $option ? $this->quoteHelper->getUnserializeData($option->getValue()) : [];
        $buyRequest = new \Magento\Framework\DataObject($data);

        // Overwrite standard buy request qty, because item qty could have changed since adding to quote
		$default_tier_request=$this->quoteHelper->getDefaultRequestQty($quote_id,$item_id);	
		$item_qty=$default_tier_request['request_qty'];		
        $buyRequest->setOriginalQty($buyRequest->getQty())->setQty($item_qty * 1);			
		$this->cart->addProduct($product,$buyRequest);		
			
			}	
		
		$this->cart->getQuote()->save();
		$default_quote_id=$this->cart->getQuote()->getId();
		//echo $default_quote_id;die;
		$mask_id=$this->quoteHelper->getDefaultQuoteMaskId($default_quote_id);
		 $resultFactory= $this->_objectManager->create('\Magento\Framework\View\Result\PageFactory');
            $resultPage= $resultFactory->create();
            $layoutblk = $resultPage->addHandle('quotation_quote_index')->getLayout();
            $items= $layoutblk->getBlock('quotation.quote.form')->toHtml();
		$result['mask_id']=$mask_id;
		$result['items']=$items;
			$resultJson->setData($result);
           		return $resultJson;	
    }
}
