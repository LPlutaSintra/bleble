<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magebees\QuotationManagerPro\Model\Quote\Status;

class submitQuote extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
  		\Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory, 						\Magebees\QuotationManagerPro\Model\QuoteFilesFactory $quoteFilesFactory,
		\Magebees\QuotationManagerPro\Model\QuoteMessageFactory $quoteMessageFactory,
		\Magebees\QuotationManagerPro\Model\QuoteCustomerFactory $quoteCustomerFactory,
		  \Magento\Customer\Model\Session $customerSession,
		  \Magento\Customer\Model\CustomerFactory $customerFactory,
		  \Magento\Customer\Model\AddressFactory $addressFactory,
		  \Magebees\QuotationManagerPro\Model\Session $quoteSession,
		  \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		  \Magebees\QuotationManagerPro\Helper\Email $emailHelper,
		  \Magebees\QuotationManagerPro\Helper\Address $addressHelper,
		   \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
    		\Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
		    \Magento\Config\Model\ResourceModel\Config $resourceConfig,
		   \Magento\Store\Model\StoreManagerInterface $storeManager,
		   \Magento\Framework\Stdlib\DateTime\DateTime $date,		   \Magebees\QuotationManagerPro\Model\QuoteAddressFactory $quoteAddressFactory,
		  
		   \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		   \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
		   $this->_quoteAddressFactory = $quoteAddressFactory;
           $this->_quoteFactory = $quoteFactory;
           $this->_quoteFilesFactory = $quoteFilesFactory;
		    $this->_quoteMessageFactory = $quoteMessageFactory;
		    $this->_quoteCustomerFactory = $quoteCustomerFactory;
		    $this->_customerFactory = $customerFactory;
		   $this->customerSession = $customerSession;
		    $this->_addressFactory = $addressFactory;
		   $this->_quoteSession = $quoteSession;
		   $this->quoteHelper = $quoteHelper;
		  $this->_resourceConfig = $resourceConfig;
		   $this->_storeManager = $storeManager;  
		   $this->_scopeConfig = $scopeConfig;
		   $this->inlineTranslation = $inlineTranslation;
		  	$this->_transportBuilder = $transportBuilder;
		  	$this->emailHelper = $emailHelper;
		  	$this->addressHelper = $addressHelper;
		   $this->_cacheTypeList = $cacheTypeList;
    		$this->_cacheFrontendPool = $cacheFrontendPool;
		   $this->date = $date;
		   parent::__construct($context);
		 
    }
	public function createCustomer($params)
	{
		//$objectManager = $bootstrap->getObjectManager();
 try { 
$websiteId = $this->_storeManager->getWebsite()->getWebsiteId();
 
$store = $this->_storeManager->getStore();  // Get Store ID
 
$storeId = $store->getStoreId();
 
// Instantiate object (this is the most important part)
 
$customer = $this->_customerFactory->create();
// print_r($params);die;
$customer->setWebsiteId($websiteId);
 $quote_id=$params['quote_id'];
 $telephone=$params['telephone'];
$email=$params['login']['username']; 
$firstname=$params['firstname'];
$lastname=$params['lastname'];
$country_id=$params['country_id'];
$postcode=$params['postcode'];
$city=$params['city'];
//$region=$params['region'];
//$region_id=$params['region_id'];
$street=$params['street'];
$password=$params['password'];
$customer->setEmail($email); 
$customer->setFirstname($firstname); 
$customer->setLastname($lastname); 
$customer->setPassword($password); 
$customer->save();
	
$quote=$this->_quoteFactory->create()->load($quote_id,'quote_id');
$quote->setCustomerId($customer->getId());		
$quote->save();		
		

	 
 }
		catch (\Exception $e) {
			
		}
		
/**end save billing address*/
		
		
 
//echo 'Create customer successfully'.$customer->getId();
	}
	public function saveAddress($params)
	{
		$quote_id=$params['quote_id'];
$quote=$this->_quoteFactory->create()->load($quote_id,'quote_id'); 		
/**start save shipping address*/
if(isset($params['firstname_ship'])&&($params['firstname_ship']!='')&&(isset($params['city']))&&($params['city']!='')&&(isset($params['street']))&&($params['street']!='')&&(isset($params['postcode']))&&($params['postcode']!='')&&(isset($params['lastname_ship']))&&($params['lastname_ship']!='')&&(isset($params['telephone']))&&($params['telephone']!='')&&(isset($params['country_id']))&&($params['country_id']!='')&&($quote->getCustomerId()))
{	
$shipping_address = $this->_addressFactory->create();
$firstname_ship=$params['firstname_ship'];
$lastname_ship=$params['lastname_ship'];
$country_id=$params['country_id'];
$postcode=$params['postcode'];
$city=$params['city'];
$telephone=$params['telephone'];
$street=$params['street'];
$shipping_address->setCustomerId($quote->getCustomerId())

->setFirstname($firstname_ship)
->setLastname($lastname_ship)
->setCountryId($country_id)
->setPostcode($postcode)
->setCity($city)
->setStreet($street)
->setTelephone($telephone)
->setIsDefaultShipping('1')
->setSaveInAddressBook('1');

if(isset($params['region']))
{
	$region=$params['region'];
	 $shipping_address->setRegion($region);

}
if(isset($params['region_id']))
{
	$region_id=$params['region_id'];		
	$shipping_address->setRegionId($region_id);
}
if(isset($params['same_billing_shipping'])){
		 $shipping_address->setIsDefaultBilling('1');
}
	
$shipping_address->save();
$ship_address_id=$shipping_address->getId();

}
		
/**end save shipping address*/
		
/**start save billing address*/
 if(!isset($params['same_billing_shipping'])){
if(isset($params['firstname_bill'])&&($params['firstname_bill']!='')&&(isset($params['city_bill']))&&($params['city_bill']!='')&&(isset($params['street_bill']))&&($params['street_bill']!='')&&(isset($params['lastname_bill']))&&($params['lastname_bill']!='')&&(isset($params['postcode_bill']))&&($params['postcode_bill']!='')&&(isset($params['telephone']))&&($params['telephone']!='')&&(isset($params['country_id_bill']))&&($params['country_id_bill']!='')&&($quote->getCustomerId()))
{
$firstname_bill=$params['firstname_bill'];
$lastname_bill=$params['lastname_bill'];
$country_id_bill=$params['country_id_bill'];
$postcode_bill=$params['postcode_bill'];
$city_bill=$params['city_bill'];
$street_bill=$params['street_bill'];		
$telephone_bill=$params['telephone'];		
$billing_address = $this->_addressFactory->create();
$billing_address->setCustomerId($quote->getCustomerId())
->setFirstname($firstname_bill)
->setLastname($lastname_bill)
->setCountryId($country_id_bill)
->setPostcode($postcode_bill)
->setCity($city_bill)
->setStreet($street_bill)
->setTelephone($telephone_bill)
->setIsDefaultBilling('1')
->setSaveInAddressBook('1');
	 if(isset($params['region_bill']))
	{
		$region_bill=$params['region_bill'];
		 $billing_address->setRegion($region_bill);
               
	}
	if(isset($params['region_id_bill']))
	{
		$region_id_bill=$params['region_id_bill'];	
        $billing_address->setRegionId($region_id_bill);
	}
	
	
	 $billing_address->save();
	 $bill_address_id=$billing_address->getId();

 }
	 }
	  if(isset($params['same_billing_shipping'])&&(isset($ship_address_id))){
		   $bill_address_id=$ship_address_id;
	  }
	
	// $quote->setCustomerId($customer->getId());
	 if(isset($ship_address_id)){
		// echo $ship_address_id;die;
	 $quote->setshipAddressId($ship_address_id);
		 $this->addressHelper->saveCustomAddress($ship_address_id,$quote,'shipping');
	 }
	 if(isset($bill_address_id)){
	 $quote->setbillAddressId($bill_address_id);
$this->addressHelper->saveCustomAddress($bill_address_id,$quote,'billing');
	  }
	 $quote->save();
		
		
		
		
	}
    public function execute()
    {		
		   $result = [];
		 	$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);  
			
			$date = $this->date->gmtDate();
			$params = $this->getRequest()->getParams();	
			$quote_id=$params['quote_id'];
			$quote=$this->_quoteFactory->create()->load($quote_id,'quote_id');
	/*start for save the address in book for custom default address*/
$avail_shipping=$this->quoteHelper->IsCustomShipAddressAvail($quote_id);
if(($avail_shipping['is_default_address']!=0)&&($avail_shipping['save_in_address_book']==1))
{
	/*address book*/
	$this->addressHelper->saveAddressInBook($quote,$avail_shipping);	
}
		
$avail_billing=$this->quoteHelper->IsCustomBillAddressAvail($quote_id);
if(($avail_billing['is_default_address']!=0)&&($avail_billing['save_in_address_book']==1))
{	
	$this->addressHelper->saveAddressInBook($quote,$avail_billing);	
}
/*end for save the address in book for custom default address*/		
	
		if((isset($params['create_account']))||(isset($params['quote_allow_guest'])&&($params['quote_allow_guest']==0)))
		{
		$this->createCustomer($params);
		}
		if((!isset($params['custom_ship_address_id']))||(isset($params['quote_allow_guest'])&&($params['quote_allow_guest']==0)))
		{
		$this->saveAddress($params);
		}
			$types = array('config','full_page');
			foreach ($types as $type) {
				$this->_cacheTypeList->cleanType($type);
			}
			foreach ($this->_cacheFrontendPool as $cacheFrontend) {
				$cacheFrontend->getBackend()->clean();
			}
			$base_currency_code=$this->quoteHelper->getBaseCurrencyCode();
	  		$quoteNoConfig=$this->quoteHelper->getQuoteNumberConfig();
	  		$config=$this->quoteHelper->getConfig();
		$enable_expiration_time=$config['enable_expiration_time'];
			$expiration_time=$config['expiration_time'];
			$expirydate = strtotime($date);
			$expirydate = strtotime("+".$expiration_time."day", $expirydate);
			$expirydate=date('Y-m-d', $expirydate);		
			 $quotePrefix=$quoteNoConfig['quote_prefix'];
			 $current_quote_id=$quoteNoConfig['current_quote'];
			 $incrementQuote=$quoteNoConfig['increment_quote'];
			 $increment_id=$current_quote_id+$incrementQuote;
			if(isset($config['assign_to']))
			{
				 $assign_to=$config['assign_to'];
			}
			else
			{
					$all_admin=$this->quoteHelper->getAllUserInfo();
					foreach($all_admin as $admin)
					{
						$user_id=$admin['user_id'];
						$assign_to=$user_id;
					}

			}		
			if($params['quote_id'])
			{
				$quote_id=$params['quote_id'];	
				 //for upload multiple files
            $uploaded_files = [];
            $post_files = [];
            $files = $this->getRequest()->getFiles();
			$files_arr = $this->getRequest()->getFiles()->toArray();
			if(!empty($files_arr)){
				
					
				$files_count = count($files_arr['upload_file']);
				if(array_key_exists('upload_file',$files_arr) && $files_arr['upload_file'][0]['name']){
					for($i=0;$i<$files_count;$i++){
						$post=[];
						$fileId = "upload_file[".$i."]";
						try {
							$uploader = $this->_objectManager->create('Magebees\QuotationManagerPro\Model\File\Uploader', ['fileId' => $fileId,'files'=>$files_arr]);
							$file_upload_config = $this->quoteHelper->getFileUploadConfig();
							$allowed_ext=$file_upload_config['file_type'];
							$allowed_ext_array = [];
							$allowed_ext_array = explode(',', $allowed_ext);	
							$uploader->setAllowedExtensions($allowed_ext_array);
							$uploader->setAllowRenameFiles(true);
							$uploader->setFilesDispersion(true);
							$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
								->getDirectoryRead(DirectoryList::MEDIA);
							$result = $uploader->save($mediaDirectory->getAbsolutePath('quotation/'));
							unset($result['tmp_name']);
							unset($result['path']);
							$file_name=$result['name'];
							$file_path=$result['file'];		
							$quote_files=$this->_quoteFilesFactory->create();
							$quote_files->setQuoteId($quote_id);
							$quote_files->setFileName($file_name);
							$quote_files->setFilePath($file_path);
							$quote_files->save();
							
						} catch (\Exception $e) {
							$this->messageManager->addError(__($e->getMessage()));
							return $this->_redirect('quotation/quote/index');
						}
					}
				}
				
			} 
				
				$currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
					
				$quote_inc_id=$quotePrefix." ".$increment_id;
				$inc_id_exist=$this->quoteHelper->checkIncIdExist($quote_inc_id);
				if(!empty($inc_id_exist))
				{
					$quote_inc_id=$quote_inc_id.'-1';
				}
				
				//var_dump($this->quoteHelper->getQuoteError());				
				//if(!count($this->quoteHelper->getErrorMsgArr()))
				if($this->quoteHelper->getErrorMsgArr()==NULL)
				{		
					if(isset($params['ship_address_id']))
					{
					 	$ship_address_id=$params['ship_address_id'];
						$quote->setshipAddressId($ship_address_id);		
	
					}
					/*else
					{
						$quote->setshipAddressId('');
					}*/
						
					if(isset($params['bill_address_id']))
					{
						$bill_address_id=$params['bill_address_id'];
						$quote->setbillAddressId($bill_address_id);
						
					}
					/*else
					{
						$quote->setbillAddressId('');
					}*/
if(isset($params['ship_address_id']))
{
$avail_shipping=$this->quoteHelper->IsCustomShipAddressAvail($quote_id);					if($avail_shipping['is_default_address']==0)
{
	$ship_address_id=$params['ship_address_id'];
	$this->addressHelper->saveCustomAddress($ship_address_id,$quote,'shipping');
}
}
if(isset($params['bill_address_id']))
{
$avail_billing=$this->quoteHelper->IsCustomBillAddressAvail($quote_id);
if($avail_billing['is_default_address']==0)
{
	$bill_address_id=$params['bill_address_id'];
$this->addressHelper->saveCustomAddress($bill_address_id,$quote,'billing');
}
}
					if(isset($params['custom_bill_address_id']))
					{
						if(($params['custom_bill_address_id'])!=0){
						$custom_quote_address=$this->_quoteAddressFactory->create()->load($params['custom_bill_address_id'],'address_id');
					$custom_quote_address->setIsDefaultAddress(1)->save();
						}
						
					}
					
					if(isset($params['custom_ship_address_id']))
					{
						if(($params['custom_ship_address_id'])!=0){
						$custom_quote_address=$this->_quoteAddressFactory->create()->load($params['custom_ship_address_id'],'address_id');
					$custom_quote_address->setIsDefaultAddress(1)->save();}
						
					}				
				$quote_request_info=$params['quote_request_info'];			
				$quote->setIncrementId($quote_inc_id);
				$quote->setAssignTo($assign_to);
				$quote->setStatus(Status::STARTING);
				$quote->setIsActive(0);
				$quote->setIsBackend(1);
				$quote->setQuoteRequestInfo($quote_request_info);
				$quote->setCurrencyCode($currencyCode);
				$quote->setExpiredAt($expirydate);
				$quote->setCreatedAt($date);
				$quote->setBaseCurrencyCode($base_currency_code);						if(isset($params['shipping_method_val']))
				{
				$quote->setShippingMethod($params['shipping_method_val']);
				}if(isset($params['applied_rate_incl_tax']))
				{
								$quote->setShippingRateInclTax($params['applied_rate_incl_tax']);
				}if(isset($params['applied_rate_excl_tax']))
				{
								$quote->setShippingRateExclTax($params['applied_rate_excl_tax']);
				}
				$quote->save();	
			
				$this->_quoteSession->setQuoteId(null);
				  $this->_resourceConfig->saveConfig(
                    'quotation/quotationsetting/current_quote',
                    $increment_id,
                    'default',
                    0
                );
				
			// for save customer detail in magebees_quote_customer table	
				
				$quote_customer=$this->_quoteCustomerFactory->create();
				$quote_customer->setQuoteId($quote_id);
				$customer_id=$quote->getCustomerId();
				if(!$customer_id)
				{
				if(isset($params['firstname']))
				{
				$quote_customer->setFname($params['firstname']);
				}
				if(isset($params['lastname']))
				{
				$quote_customer->setLname($params['lastname']);
				}
				if(isset($params['login']['username']))
				{
				$quote_customer->setEmail($params['login']['username']);
				}
				}
				else
				{
				$login_customer=$this->quoteHelper->loadCustomerById($customer_id);
					$quote_customer->setFname($login_customer->getFirstName());
			$quote_customer->setLname($login_customer->getLastName());
			$quote_customer->setEmail($login_customer->getEmail());
			$quote_customer->setCustomerId($customer_id);
	/*start reset the setDefaultTaxShippingAddress after submit quote*/		
					
	//$this->_eventManager->dispatch('magebees_reset_tax_address_submitquote', ['customer' => $login_customer,'quote_id'=>$quote_id]);				

	/*end reset the setDefaultTaxShippingAddress after submit quote*/					
			
				}
				$quote_customer->save();	
			
				// for save message detail for generated quote
					$message_detail=[];
					$message_arr=[];	
					$quoteStatus=$quote->getStatus();			
					$sub_detail_arr=[];
					$sub_detail_arr['is_admin']=1;
					$sub_detail_arr['display_at_front']=0;
					$sub_detail_arr['is_customer_notify']=1;
					$sub_detail_arr['quoteStatus']=$quoteStatus;
					$sub_detail_arr['message']=' New Quotation Submit';
					$message_arr[$date]=$sub_detail_arr;					
					$customer_id=$quote->getCustomerId();
					$admin_id=$quote->getAssignTo();
					$message_detail['customer_id']=$customer_id;
					$message_detail['admin_id']=$admin_id;
					$message_detail['quote_id']=$quote_id;
					$message_detail['messages']=$message_arr;
					$serialize_msg_detail=$this->quoteHelper->getSerializeData($message_detail);
					$quote_message=$this->_quoteMessageFactory->create();
					$quote_message->setQuoteId($quote_id);
					$quote_message->setCommunication($serialize_msg_detail);
					$quote_message->save();
				
				// send mail to customer after create new quote
					$email_config=$this->quoteHelper->getEmailConfig();
				if($email_config['quote_request'])
				{
					$this->emailHelper->sendQuoteCreateMail($quote_id);
				}
				//echo "ok";die;
					if($customer_id)
					{
						$redirect_url=$this->_url->getUrl('quotation/customer/quotehistory');
					}
					else
					{
						$redirect_url=$this->_url->getUrl('quotation/quote/success', ['quote_id' => $quote_id]);
					}
				
				$this->messageManager->addSuccess($this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml('Your quote request generated successfully.'));
					$this->quoteHelper->unsetErrorMsgArr();
				}
				else
				{					
					$redirect_url=$this->_url->getUrl('quotation/quote/index');
					$this->messageManager->addError($this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml('We can\'t generate the Quote.'));
				}
				
				$resultRedirect->setUrl($redirect_url);		
           		return $resultRedirect;
				
			}
    }

}
