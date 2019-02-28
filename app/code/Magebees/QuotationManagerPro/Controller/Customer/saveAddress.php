<?php
namespace Magebees\QuotationManagerPro\Controller\Customer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class saveAddress extends \Magento\Framework\App\Action\Action
{ 
	
    protected $resultPageFactory;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		 \Magebees\QuotationManagerPro\Model\Session $quotationSession,
		\Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory,
		\Magebees\QuotationManagerPro\Model\QuoteAddressFactory $quoteAddressFactory,
		 \Magento\Checkout\Model\Cart $cart,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		\Magento\Customer\Model\AddressFactory $addresssFactory,
		 \Magebees\QuotationManagerPro\Model\QuoteItemFactory $quoteItemFactory,	
		\Magento\Customer\Model\Session $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
		 $this->customerSession = $customerSession;
		 $this->_quotationSession = $quotationSession;
		 $this->_quoteFactory = $quoteFactory;
		 $this->_quoteAddressFactory = $quoteAddressFactory;
		 $this->_addresssFactory = $addresssFactory;
		 $this->_quoteItemFactory = $quoteItemFactory;
		 $this->cart = $cart;
		$this->quoteHelper=$quoteHelper;
        parent::__construct($context);
    }	
    public function execute()
    {	
		   $result = [];
			$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		  if (!$this->customerSession->isLoggedIn()) {			
			  $this->_redirect('customer/account/'); 
			  $this->messageManager->addSuccess($this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml('Please log into your account.'));
			   
        }
		else
		{
			$params=$this->getRequest()->getParams();	
			//print_r($params);die;
			$quote_id=$this->_quotationSession->getQuote()->getId();	
			$customer_id=$this->customerSession->getCustomerId();		
$quote=$this->_quoteFactory->create()->load($quote_id,'quote_id');		
		
/**start save shipping address*/
	

$firstname=$params['firstname'];
$lastname=$params['lastname'];
$country_id=$params['country_id_add'];
$postcode=$params['postcode'];
$city=$params['city'];
$street=$params['street'];
$telephone=$params['telephone'];
$type=$params['add_type'];	
			
			if($type=='add_quote_ship_add'||$type=='shipping')
			{				
				$address_type='shipping';
			}
			else
			{				
				$address_type='billing';
			}

$customer_email=$this->customerSession->getCustomer()->getEmail();	

	$street=is_array($street) ? implode("\n", $street):null;
	if(isset($params['address_id'])){	
		$custom_address_id=$params['address_id'];
		$quote_address=$this->_quoteAddressFactory->create()->load($custom_address_id,'address_id');
	}
	else
	{
		$quote_address=$this->_quoteAddressFactory->create();
	}
			if(isset($params['save_in_add_book']))
			{
				$save_in_add_book=1;
			}
			else
			{
				$save_in_add_book=0;
			}
			//echo $save_in_add_book;die;
	
	$quote_address->setQuoteId($quote_id);				
	$quote_address->setFirstname($firstname);
	$quote_address->setLastname($lastname);			
	$quote_address->setCustomerId($customer_id);	
	$quote_address->setSaveInAddressBook($save_in_add_book);
	$quote_address->setAddressType($address_type);
	$quote_address->setEmail($customer_email);		
	$quote_address->setStreet($street);				
	$quote_address->setCity($city);		
	$quote_address->setCountryId($country_id);
	$quote_address->setPostcode($postcode);		
	$quote_address->setTelephone($telephone);		
	$quote_address->setIsDefaultAddress(1);	
	$region_data='';
	if(isset($params['region_add']))
	{
		$region=$params['region_add'];
		$quote_address->setRegion($region);
		$region_data=$region;
	}
	if(isset($params['region_id_add']))
	{
		$region_id=$params['region_id_add'];
		$quote_address->setRegionId($region_id);	
		$region_data=$region_id;
	}		
	$quote_address->save();
	$quote_address_id=$quote_address->getAddressId();
	$result['save_in_add_book']=0;
	$result['quote_address_id']=$quote_address_id;
	$added_address=$this->quoteHelper->getCustomAddressById($quote_address_id);
	$formated_address=$this->quoteHelper->renderCustomAddress($added_address);
	$result['added_address']=$formated_address;
	$result['is_custom']=1;	
	$default_quote_id=$this->cart->getQuote()->getId();
	$mask_id=$this->quoteHelper->getDefaultQuoteMaskId($default_quote_id);
$result['city']=$city;
$result['added_address_type']=$address_type;
$result['country_id']=$country_id;
$result['postcode']=$postcode;
$result['mask_id']=$mask_id;
		
/**end save shipping address*/	 
			
				
	 $quote->save();
			
		/**Start for set custom quote tax shipping address*/
			if($address_type=='billing')
			{
				 $this->customerSession->setDefaultTaxBillingAddress(
                            [
                               'country_id' =>$country_id,
                                'region_id'  =>$region_data,
                                'postcode'   =>$postcode,
                            ]
                        );
			}
			else
			{
				  $this->customerSession->setDefaultTaxShippingAddress(
                            [
                                'country_id' =>$country_id,
                                'region_id'  =>$region_data,
                                'postcode'   =>$postcode,
                            ]
                        );
			}
		/*End for set custom quote tax shipping address*/	
			$items=$this->quoteHelper->getItemsByQuoteId($quote_id);
			//$items->addFieldToFilter('parent_item_id', ['null' => true]);
			foreach($items as $item)
			{
				$item_id=$item->getId();
				$product_id=$item->getProductId();				
				$product=$this->quoteHelper->loadProduct($product_id);
				
				$quotationtierQty=$this->quoteHelper->getDynamicQuoteQty($item_id,$quote_id,$product_id);
if(count($quotationtierQty->getData())>0){
		foreach($quotationtierQty as $qty):
			$qty->setRequestQtyPrice($item->getPrice());	
			$qty_price_incl_tax=$this->quoteHelper->getProductPriceInclTax($product,$item->getPrice(),true);
			$qty->setReqQtyPriceInclTax($qty_price_incl_tax);		
			$qty->save();
		endforeach;
	}
				
				
				$price_incl_tax=$this->quoteHelper->getProductPriceInclTax($product,$item->getPrice(),true);
			//	echo $item_id;die;
				$qItem=$this->_quoteItemFactory->create();
				$qItem->load($item_id);
				$qItem->setPriceInclTax($price_incl_tax);
				$qItem->save();
		/*End for save price include tax when change the address*/
			
			
			}
		
		} 
		$resultFactory= $this->_objectManager->create('\Magento\Framework\View\Result\PageFactory');
            $resultPage= $resultFactory->create();
            $layoutblk = $resultPage->addHandle('quotation_quote_index')->getLayout();
            $items= $layoutblk->getBlock('quotation.quote.form')->toHtml();
		$result['items']=$items;
			$resultJson->setData($result);
           		return $resultJson;	
    }
}
