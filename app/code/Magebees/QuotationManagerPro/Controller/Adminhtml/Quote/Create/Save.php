<?php

namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote\Create;

class Save extends \Magento\Backend\App\Action
{
    
	 public function __construct(
        \Magento\Backend\App\Action\Context $context,  		\Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory,			 \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $mquoteSession,		 \Magebees\QuotationManagerPro\Model\QuoteAddressFactory $quoteAddressFactory,		 \Magebees\QuotationManagerPro\Model\QuoteCustomerFactory $quoteCustomerFactory,		
		  \Magento\Customer\Model\CustomerFactory $customerFactory,		
		 \Magento\Customer\Model\AddressFactory $addressFactory,
		    \Magento\Store\Model\StoreManagerInterface $storeManager,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper
    ) {
    
        parent::__construct($context);
		  $this->_storeManager = $storeManager;
         $this->quoteFactory = $quoteFactory;
		   $this->_quoteCustomerFactory = $quoteCustomerFactory;
		   $this->_qsession = $mquoteSession;
		  $this->_addressFactory = $addressFactory;
		  $this->_customerFactory = $customerFactory;
		  $this->_quoteAddressFactory = $quoteAddressFactory;
		 $this->quoteHelper = $quoteHelper;
		  
		
    }
    public function execute()
    {		
		// \Magento\Backend\Model\Session -> $this->_session
		$this->_session->unsCurrentQuoteId();
		$resultRedirect = $this->resultRedirectFactory->create();
		$data=$this->getRequest()->getPost()->toArray();
		$quote_id=$data['current_quote'];
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		
		if($data['current_customer']!=0)
		{			
			$customer_id=$data['current_customer'];	
		}
		else
		{		
			try
			{
			$customer_id=$this->createCustomer($data,$quote);	
			}
			catch (\Exception $e) {
				$this->_session->unsCurrentQuoteId();
				$this->messageManager->addError(__($e->getMessage()));
				$this->_qsession->unsCustomerId();
				$this->_qsession->unsStoreId();
				 return $resultRedirect->setPath('quotation/quote_create/index');
			}
		}
		$quote_customer_data=$this->_quoteCustomerFactory->create()->load($quote_id,'quote_id');
		//print_r($quote_customer_data->getData());die;
		if(count($quote_customer_data->getData()))
		{
			$customer_data=$quote_customer_data->getData();
			$id=$customer_data['id'];
			$quote_customer=$this->_quoteCustomerFactory->create()->load($id);
		}
		else
		{
			$quote_customer=$this->_quoteCustomerFactory->create();
		}
			$quote_customer->setQuoteId($quote_id);		
			$current_customer=$this->quoteHelper->loadCustomerById($customer_id);					
			$quote_customer->setFname($current_customer->getFirstName());
$quote_customer->setLname($current_customer->getLastName());
			$quote_customer->setEmail($current_customer->getEmail());
			$quote_customer->setCustomerId($customer_id);
			$quote_customer->save();	
		
		
		
		
		if(isset($data['shipping_same_as_billing']))
		{	
			try
			{
				if(isset($data['quote']['billing_address'] ['customer_address_id']))
				{
					$customer_address_id=$data['quote']['billing_address'] ['customer_address_id'];
					if($customer_address_id)
					{
						$bill_address=$this->quoteHelper->getDefaultAddressInQuote($quote_id,'billing',$customer_address_id);
					$ship_address=$this->quoteHelper->getDefaultAddressInQuote($quote_id,'shipping',$customer_address_id);					$bill_address_id=$bill_address['address_id'];					$ship_address_id=$ship_address['address_id'];
					}
					else
					{
						$bill_address_id=null;
						$ship_address_id=null;
					}
									
					$this->setBillingAsShipping($quote_id,$customer_id,$bill_address_id,'billing_address',$data,'billing');				
					$this->setBillingAsShipping($quote_id,$customer_id,$ship_address_id,'billing_address',$data,'shipping');				
				}
				else
				{
					$this->setBillingAsShipping($quote_id,$customer_id,null,'billing_address',$data,'billing');
					$this->setBillingAsShipping($quote_id,$customer_id,null,'billing_address',$data,'shipping');
				}
			}
			catch (\Exception $e) {
				$this->_session->unsCurrentQuoteId();
				//print_r($e->getMessage());die;
			}
			
		}
		else
		{
			if(isset($data['quote']['shipping_address'] ['customer_address_id']))
			{
				$customer_ship_address_id=$data['quote']['shipping_address'] ['customer_address_id'];
			$ship_address=$this->quoteHelper->getDefaultAddressInQuote($quote_id,'shipping',$customer_ship_address_id);
			$ship_address_id=$ship_address['address_id'];
			$this->setBillingAsShipping($quote_id,$customer_id,$ship_address_id,'shipping_address',$data,'shipping');
			}
			else
			{
				$this->setBillingAsShipping($quote_id,$customer_id,null,'shipping_address',$data,'shipping');
			}
			
			if(isset($data['quote']['billing_address'] ['customer_address_id']))
			{
				$customer_bill_address_id=$data['quote']['billing_address'] ['customer_address_id'];
			$bill_address=$this->quoteHelper->getDefaultAddressInQuote($quote_id,'billing',$customer_bill_address_id);
			$bill_address_id=$bill_address['address_id'];
			$this->setBillingAsShipping($quote_id,$customer_id,$bill_address_id,'billing_address',$data,'billing');
			}
			else
			{
			$this->setBillingAsShipping($quote_id,$customer_id,null,'billing_address',$data,'billing');
			}
			
						
		}
		
		//$quote=$this->quoteFactory->create()->load();
		
		$quote->setIsActive(false);
		$quote->setCustomerId($customer_id);
		$quote->save();
		$this->_session->unsCurrentQuoteId();
		 return $resultRedirect->setPath('quotation/quote/view', ['quote_id' => $quote_id]);
		
        
    }
	public function createCustomer($data,$quote)
	{
		//$websiteId = $this->_storeManager->getWebsite()->getWebsiteId(); 
		$storeId = $quote->getStoreId();
$store = $this->_storeManager->getStore()->load($storeId);  // Get Store ID 
		$websiteId =$store->getWebsiteId();
$customer = $this->_customerFactory->create();
$customer->setWebsiteId($websiteId);
$customer->setEmail($data['quote']['account']['email']); 
$customer->setFirstname($data['quote']['billing_address']['firstname']); 
$customer->setLastname($data['quote']['billing_address']['lastname']); 

$customer->setGroupId($data['quote']['account']['group_id']);
$customer->save();
		return $customer->getId();
	}
	public function setBillingAsShipping($quote_id,$customer_id,$address_id,$type,$data,$address_type)
	{
	$current_customer=$this->quoteHelper->loadCustomerById($customer_id);
		if($address_id!=null)
		{
			//echo "ok";print_r($data);die;
		$quote_address=$this->quoteHelper->loadAddressByCustomerAddId($address_id);	
		}
		else
		{
			//echo "elseok";print_r($data);die;
		$quote_address=$this->_quoteAddressFactory->create();
		}
		
			$quote_address->setQuoteId($quote_id);				
	$quote_address->setFirstname($data['quote'][$type]['firstname']);
	$quote_address->setLastname($data['quote'][$type]['lastname']);		
	$quote_address->setCustomerId($customer_id);	
	
	if(isset($data['quote'][$type]['save_in_address_book']))
	{
		
	$inserted_add_id=$this->saveCustomAddressInBook($data,$customer_id,$type,$quote_id);
	$quote_address->setSaveInAddressBook(1);
	$quote_address->setCustomerAddressId($inserted_add_id);
	}
	else
	{
		
		/*if($address_type=='billing')
		{
	$customer_address_id=$data['quote']['billing_address'] ['customer_address_id'];
		}
		else
		{
			if(isset($data['shipping_same_as_billing']))
			{
				$customer_address_id=$data['quote']['billing_address'] ['customer_address_id'];
			}
			else
			{
			$customer_address_id=$data['quote']['shipping_address'] ['customer_address_id'];
			}
		}*/
		$quote_address->setCustomerAddressId($address_id);
	$quote_address->setSaveInAddressBook(0);
	}
	$quote_address->setAddressType($address_type);
	$quote_address->setEmail($current_customer->getEmail());		
	$street=is_array($data['quote'][$type]['street']) ? implode("\n", $data['quote'][$type]['street']):null;
	$quote_address->setStreet($street);				
	$quote_address->setCity($data['quote'][$type]['city']);		
	$quote_address->setCountryId($data['quote'][$type]['country_id']);
	$quote_address->setPostcode($data['quote'][$type]['postcode']);		
	$quote_address->setTelephone($data['quote'][$type]['telephone']);		
	$quote_address->setIsDefaultAddress(1);		
	//$quote_address->setIsCustom(0);		
	$quote_address->setRegion(isset($data['quote'][$type]['region'])?$data['quote'][$type]['region']:'');		
	$quote_address->setRegionId(isset($data['quote'][$type]['region_id'])?$data['quote'][$type]['region_id']:'');	
			
	$quote_address->save();
		
	}
	public function saveCustomAddressInBook($data,$customer_id,$type,$quote_id)
	{
		$quote=$this->quoteHelper->loadQuoteById($quote_id);	
		$street=is_array($data['quote'][$type]['street']) ? implode("\n", $data['quote'][$type]['street']):null;
		
			$address= $this->_addressFactory->create();
			$address->setCustomerId($customer_id);
			$address->setFirstname($data['quote'][$type]['firstname'])->setLastname($data['quote'][$type]['lastname'])
->setCountryId($data['quote'][$type]['country_id'])
->setPostcode($data['quote'][$type]['postcode'])
->setCity($data['quote'][$type]['city'])
->setTelephone($data['quote'][$type]['telephone'])
->setStreet($street);
$address->setSaveInAddressBook(1);	
$address->save();
		if($type=='billing_address')
		{
			 $quote->setbillAddressId($address->getId());
		}
		else
		{
			 $quote->setshipAddressId($address->getId());
		}
		$quote->save();
		return $address->getId();
		
	}
}
