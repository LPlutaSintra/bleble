<?php

namespace Magebees\QuotationManagerPro\Helper;
use Magento\Framework\App\ObjectManager;
use \Magebees\QuotationManagerPro\Model\Quote\Status;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Tax\Api\Data\TaxClassKeyInterface;


class Quotation extends \Magento\Framework\Url\Helper\Data
{
    /**
     * Path to controller to delete item from quote
     */
    const DELETE_URL_QUOTE = 'quotation/quote/delete';
	const AREA_CODE = \Magento\Framework\App\Area::AREA_ADMINHTML;
	protected $_quoteError;
   
	  public function __construct(
        \Magento\Framework\App\Helper\Context $context,
		 \Magento\Framework\App\Http\Context $http_context,
        \Magento\Catalog\Model\Product\OptionFactory $qproductOptionFactory,
        \Magento\Framework\Filter\FilterManager $filter,
        \Magento\Framework\Stdlib\StringUtils $string,
		  \Magento\Catalog\Model\ResourceModel\Eav\Attribute $catalogeav,
		  \Magento\Framework\Escaper $escaper,
		   \Magento\Framework\Pricing\Helper\Data $pricingHelper,
		   \Magento\User\Model\UserFactory $userFactory,
		   \Magebees\QuotationManagerPro\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory,
		   \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteRequest\CollectionFactory $quoteRequestCollFactory,
		  	\Magebees\QuotationManagerPro\Model\Quote\Status $status,
		    \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
			\Magento\Customer\Model\Address\Config $addressConfig,
			\Magento\Customer\Model\Address\Mapper $addressMapper,
		   \Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory,
		   \Magebees\QuotationManagerPro\Model\QuoteCustomerFactory $quoteCustomerFactory,
		   \Magebees\QuotationManagerPro\Model\QuoteItemFactory $quoteItemFactory,		  \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem\CollectionFactory $quoteItemCollFactory,		  \Magebees\QuotationManagerPro\Model\ResourceModel\Quote\Item\Option\CollectionFactory $quoteItemOptCollFactory,		  \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteFiles\CollectionFactory $quoteFilesCollFactory,		  \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteMessage\CollectionFactory $quoteMessageCollFactory,		  \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteCustomer\CollectionFactory $quoteCustomerCollFactory,		   \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteAddress\CollectionFactory $quoteAddressCollFactory,
		  \Magebees\QuotationManagerPro\Model\QuoteAddressFactory $quoteAddressFactory,
		   QuoteIdMaskFactory $quoteIdMaskFactory,
		   CheckoutSession $checkoutSession,
		    LocaleFormat $localeFormat,
		   \Magento\Catalog\Model\ProductFactory $productFactory,
		    \Magento\Catalog\Helper\Data $catalog_helper,
		   \Magento\Customer\Api\CustomerRepositoryInterfaceFactory $customerRepositoryFactory,
		   \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
		   \Magento\Store\Model\StoreManagerInterface $storeManager, 
		  \Magento\Customer\Model\Session $customerSession,
		   \Magento\Framework\Session\SessionManager $session,
		   \Magento\Customer\Model\Address $customerAddress,
		  \Magento\Framework\App\ProductMetadataInterface $productMetadata,
		   \Magento\Framework\App\Request\Http $request,
		   \Magento\Customer\Model\AddressFactory $addressFactory,
		  \Magento\Customer\Api\Data\AddressInterfaceFactory $_addressFactory,
		  \Magento\Customer\Model\AccountManagement $customerAccount,
		   \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyFactory,		   \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory,
		  \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsFactory,
		  \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,		    \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory,
		   \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository,
		  \Magento\Framework\App\State $state,
		  \Magento\Backend\Model\Session $backendSession,
		  	\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
		  \Magento\Framework\Stdlib\DateTime\DateTime $datetime
    ) {
		  $this->regionFactory = $regionFactory;
        $this->_productOptionFactory = $qproductOptionFactory;
		  $this->priceCurrency = $priceCurrency;
		   $this->_request = $request;
		  $this->_state = $state;
        $this->filter = $filter;
        $this->httpContext = $http_context;
        $this->string = $string;
		  $this->_quoteAddressFactory = $quoteAddressFactory;
		$this->_catalogeav = $catalogeav;
		   $this->userFactory = $userFactory;
		   $this->escaper = $escaper;
		   $this->pricingHelper = $pricingHelper;       
		  $this->_quoteRequestCollFactory = $quoteRequestCollFactory;
		   $this->customerGroupRepository = $customerGroupRepository;
		   $this->_quoteCollectionFactory = $_quoteCollectionFactory;
		   $this->status = $status;
		   $this->addressRepository = $addressRepository;
			$this->_addressConfig = $addressConfig;
			$this->addressMapper = $addressMapper;
		   $this->_quoteFactory = $quoteFactory;
		   $this->_quoteItemFactory = $quoteItemFactory;
		   $this->_quoteCustomerFactory = $quoteCustomerFactory;
		  $this->_quoteItemCollFactory = $quoteItemCollFactory; 
		  $this->_quoteItemOptCollFactory = $quoteItemOptCollFactory; 
		    $this->_quoteFilesCollFactory = $quoteFilesCollFactory;
		    $this->_quoteMessageCollFactory = $quoteMessageCollFactory;
		    $this->_quoteCustomerCollFactory = $quoteCustomerCollFactory;
		    $this->_quoteAddressCollFactory = $quoteAddressCollFactory;
		   $this->productFactory = $productFactory;
		  $this->customerRepository = $customerRepositoryFactory->create();
		 
		   $this->_storeManager = $storeManager;  
		   $this->_customerAddress = $customerAddress;   
		    $this->_customerSession=$customerSession;
		    $this->_customerAccount=$customerAccount;
		   $this->datetime = $datetime;
		  $this->timezone = $timezone;
		   $this->productMetadata = $productMetadata;
		    $this->session =$session;
		   $this->catalog_helper = $catalog_helper;
		   $this->_addressFactory = $addressFactory;
		   $this->addressFactory = $_addressFactory;
		     $this->localeFormat = $localeFormat;
		    $this->checkoutSession = $checkoutSession;
		     $this->quoteIdMaskFactory = $quoteIdMaskFactory;
		    $this->_taxClassKeyFactory = $taxClassKeyFactory;
		  $this->_quoteDetailsItemFactory = $quoteDetailsItemFactory;
		  $this->_quoteDetailsFactory = $quoteDetailsFactory;
		    $this->_taxCalculationService = $taxCalculationService;
		    $this->backendSession = $backendSession;
        parent::__construct($context);
    }
	public function getExpiredDateFormat($expiryDate)
	{
		$expiryDate = $this->timezone->date($expiryDate)->format('Y-m-d');
		return $expiryDate;
	}
	public function getSaveAddressUrl()
    {
        return $this->_getUrl('quotation/customer/saveAddress');
    }
	public function getSubmitQuoteUrl()
    {
        return $this->_getUrl('quotation/quote/submitQuote');
    }
	public function getBaseUrl()
    {
        return $this->_urlBuilder->getBaseUrl();
    }
	public function isEmailAvailableUrl()
    {
        return $this->_getUrl('quotation/customer/emailavail');
    }
	  public function getForgotPasswordUrl()
    {
        return $this->_urlBuilder->getUrl('customer/account/forgotpassword');
    }
	public function getLoginPostUrl()
    {
		return $this->_getUrl('quotation/customer/loginPost',array('referer' =>base64_encode($this->_urlBuilder->getCurrentUrl())));
	}
	public function getQuoteViewLoginUrl($quote_id)
    {
		$url=$this->_getUrl('quotation/quote/view/',array('quote_id'=>$quote_id));
		return $this->_getUrl('customer/account/',array('referer' =>base64_encode($url)));
	}
	
	public function isEmailAvailable($email)
	{
		return $this->_customerAccount->isEmailAvailable($email);
	}
	public function getSerializedQuoteConfig()
	{
		return json_encode($this->getQuoteConfig(), JSON_HEX_TAG);
	}
	public function getDefaultQuoteMaskId($default_quote_id)
	{
		$quoteIdMask = $this->quoteIdMaskFactory->create();	  
		$mask_id=$quoteIdMask->load($default_quote_id,'quote_id')->getMaskedId();
		if($mask_id)
		{
			return $mask_id;
		}
		else
		{
        $quoteIdMask->setQuoteId($default_quote_id)->save();
        return $quoteIdMask->getMaskedId();
		}
		//return $quoteIdMask->load($default_quote_id,'quote_id')->getMaskedId();	
	}
	public function getQuoteConfig()
	{
		 $quoteIdMask = $this->quoteIdMaskFactory->create();
		
		$output['entity_id'] = $quoteIdMask->load(
			$this->checkoutSession->getQuote()->getId(),
			'quote_id'
		)->getMaskedId();		
		 $output['priceFormat'] = $this->localeFormat->getPriceFormat(
            null,
            $this->getBaseCurrencyCode()
        );
		$output['storeCode'] = $this->_storeManager->getStore()->getCode();
		 $output['is_customer_login'] =$this->isCustomerLoggedIn();
		 return $output;
	}
	public function getAddressObjFromId($shippingAddressId)
	{
		  return $this->_addressFactory->create()->load($shippingAddressId);
		//return  $this->addressRepository->getById($addressId);
	}
	 public function loadProduct($productId)
    {
        $product=$this->productFactory->create()->load($productId);
        return $product;
    }
	public function getProductPriceInclTax($product,$finalPrice,$test,$store_id=null)
	{
	//$price_incl_tax=$this-	>getTaxPrice($product,$finalPrice,$test);
//$price_incl_tax=$this->getCustomTaxPrice($product,$finalPrice);
		//$price_incl_tax=$this->catalog_helper->getTaxPrice($product,$finalPrice,$test);
		if($this->isAdmin())
		{
			$price_incl_tax=$this->catalog_helper->getTaxPrice($product,$finalPrice,true,null,null,null,$store_id,null,$test);
		}
		else
		{
			$store_id=$this->_storeManager->getStore()->getId();
			$price_incl_tax=$this->catalog_helper->getTaxPrice($product,$finalPrice,true,null,null,null,$store_id,null,$test);
		}
		
		
		return $price_incl_tax;
	}
	public function getCustomTaxPrice($product,$finalPrice)
	{
    if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
        // First get base price (=price excluding tax)
        $productRateId = $taxAttribute->getValue();
        $rate = $this->_taxCalculationService->getCalculatedRate($productRateId);          
        $priceIncludingTax = $finalPrice + ($finalPrice * ($rate / 100));		
return $priceIncludingTax;		
    } 
	}
	 public function getAjaxLoginUrl()
    {
        return $this->_getUrl('quotation/customer/ajaxlogin');
    }	  
	 
	public function getOutOfStockStatus()
	{
		return Status::PROPOSAL_CANCELLED_OUTOF_STOCK;
		
	}
	public function getStartingStatus()
	{
		return Status::STARTING;
	}
	public function setErrorMsgArr($flag)
	{		
		$this->_customerSession->setErrorMsgArr($flag);		
	}
	public function getErrorMsgArr()
	{		
		return $this->_customerSession->getErrorMsgArr();
	}
	public function unsetErrorMsgArr()
	{
		$this->_customerSession->setErrorMsgArr(null);		
		$this->_customerSession->unsErrorMsgArr();
	}
	public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
	public function getSerializeData($data)
	{
		$version=$this->getMagentoVersion();
		 if(version_compare($version, '2.2.0', '<'))
		{
			return serialize($data);
		}
		else
		{
			return json_encode($data);
		}
	}
	public function getUnserializeData($data)
	{
		$version=$this->getMagentoVersion();
		 if(version_compare($version, '2.2.0', '<'))
		{
			return unserialize($data);
		}
		else
		{
			return json_decode($data,true);
		}
	}
	public function getGmtTime()
	{
		return $this->datetime->gmtDate();
	}
	public function getBaseCurrencyCode()
	{
		return $this->_storeManager->getStore()->getBaseCurrency()->getCode();
	}
	public function getFormatedPrice($price, $currency=null)
	{
		$precision = 2;  // for displaying price decimals 2 point
		return $this->priceCurrency->format(
			$price,
			$includeContainer = true,
			$precision,
			$this->_storeManager->getStore()->getId(),
			$currency
		);
		 
	}
	public function getConvertedPrice($price,$currency=null)
    {     
      	$price = $this->priceCurrency->convert($price, $this->_storeManager->getStore()->getId(),$currency);
		return $price;
    }
	public function getConfig()
	{
		return $this->scopeConfig->getValue('quotation/setting', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	public function getFrontendConfig()
	{
		return $this->scopeConfig->getValue('quotation/frontendsetting', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	public function getHidePriceConfig()
	{
		return $this->scopeConfig->getValue('quotation/hidepricesetting', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	public function getTaxConfig()
	{
		return $this->scopeConfig->getValue('quotation/taxsetting', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	public function getQuoteNumberConfig()
	{
		return $this->scopeConfig->getValue('quotation/quotationsetting', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	public function getFileUploadConfig()
	{
		return $this->scopeConfig->getValue('quotation/filesetting', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	public function getEmailConfig()
	{
		return $this->scopeConfig->getValue('quotation/emailsetting', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	public function getPdfConfig()
	{
		return $this->scopeConfig->getValue('quotation/pdfsetting', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	public function isEnableStockWise($_product)
	{
		$front_config=$this->getFrontendConfig();
		$enable_manage_stock=$front_config['enable_manage_stock'];
		if($enable_manage_stock){
		if($_product->isSaleable())
		{
			$is_display_quote=true;	
		}
		else
		{
			$is_display_quote=false;		
		}
		}
		else
		{
			$is_display_quote=true;		
		}
		return $is_display_quote;
	}
	public function isEnableAttributeWise($_product)
	{
		$front_config=$this->getFrontendConfig();
		if($front_config['enable_quote_button']==0){
 			$quote_attribute='Yes';
		}
		else{
		 $quote_attribute=$_product->getResource()->getAttribute('magebees_quote')->getFrontend()->getValue($_product); 
		}
		return $quote_attribute;
	}
	public function isEnableCustomerGroupWise()
	{
		$front_config=$this->getFrontendConfig();
		if($front_config['enable_qcustomer_group'])
		{			
			$group_ids=$front_config['quote_customer_group'];
			$group_ids_arr=explode(',',$group_ids);
			
$isLoggedIn=$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
		if($isLoggedIn):
		$customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
		else:
		$customerGroupId = 0;
		endif;
		if (in_array($customerGroupId,$group_ids_arr))
		{
			return true;
		}
		else
		{
			return false;
		}
			
		}
		else
		{
			return true;
		}
	}
	public function isEnablePriceCustGroupWise($_product)
	{
		$hideprice_config=$this->getHidePriceConfig();
		$product_sku=$_product->getSku();
		if($hideprice_config['enable_qprice'])
		{			
			$group_ids=$hideprice_config['price_customer_group'];
								$product_skus=$hideprice_config['hide_price_not_applied_for'];
			$group_ids_arr=explode(',',$group_ids);
			$product_skus_arr=explode(',',$product_skus);
			
$isLoggedIn=$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
		if($isLoggedIn):
		$customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
		else:
		$customerGroupId = 0;
		endif;
			
				
		if ((in_array($customerGroupId,$group_ids_arr)))
		{
			if(in_array($product_sku,$product_skus_arr))
			{
			return true;				
			}
			else
			{
			return false;
			}
		}
		else
		{
			return true;
		}
			
		}
		else
		{
			return true;
		}
	}
	public function isShowPriceWithoutProduct()
	{
		$hideprice_config=$this->getHidePriceConfig();		
		if($hideprice_config['enable_qprice'])
		{			
			$group_ids=$hideprice_config['price_customer_group'];	
			$group_ids_arr=explode(',',$group_ids);			
$isLoggedIn=$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
		if($isLoggedIn):
		$customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
		else:
		$customerGroupId = 0;
		endif;
			
				
		if ((in_array($customerGroupId,$group_ids_arr)))
		{			
			return false;			
		}
		else
		{
			return true;
		}
			
		}
		else
		{
			return true;
		}
	}
	public function isEnableActionWise()
	{
		 	$current_action=$this->_request->getFullActionName();
			$front_config=$this->getFrontendConfig();
			$enable_btn_cat=$front_config['enable_btn_cat'];
			$enable_btn_product=$front_config['enable_btn_product'];
			if($current_action=='catalog_category_view')
			{
				$enable_actionwise=$enable_btn_cat;
			}
			else if($current_action=='catalog_product_view')
			{
				$enable_actionwise=$enable_btn_product;
			}
			else
			{
				$enable_actionwise=true;
			}
		return $enable_actionwise;
	}
	public function getSenderMail()
	{
		$email_config=$this->getEmailConfig();
		$default_sender=$email_config['email_sender'];
		$email_path = 'trans_email/ident_'.$default_sender.'/email';
		return $this->scopeConfig->getValue($email_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}
	public function getSenderName()
	{
		$email_config=$this->getEmailConfig();
		$default_sender=$email_config['email_sender'];
		$name_path = 'trans_email/ident_'.$default_sender.'/name';
		return $this->scopeConfig->getValue($name_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);		
	}
	public function getEmailCopyTo()
	{
		$email_config=$this->getEmailConfig();
		if(isset($email_config['copy_mail_to']))
		{
			$copy_mail_to=$email_config['copy_mail_to'];		
			return $copy_mail_to_arr=explode(",",$copy_mail_to);
		}
		else
		{
			return false;
		}
	}
	public function getAllUserInfo()
	{
		$all_admin=$this->userFactory->create()->getCollection()->getData();
			return $all_admin;
	}
	public function getUserInfoById($user_id)
	{
		$user_detail=$this->userFactory->create()->load($user_id,'user_id');
			return $user_detail;
	}
	
    /**
     * Retrieve url for add product to quote     
     */
    public function getAddUrl($qproduct, $additional = [])
    {
        $continueUrl = $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl());
        $urlParamName = \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED;

        $routeParams = [
            $urlParamName => $continueUrl,
            'product' => $qproduct->getEntityId(),
            '_secure' => $this->_getRequest()->isSecure()
        ];

        if (!empty($additional)) {
            $routeParams = array_merge($routeParams, $additional);
        }

        if ($qproduct->hasUrlDataObject()) {
            $routeParams['_scope'] = $qproduct->getUrlDataObject()->getStoreId();
            $routeParams['_scope_to_url'] = true;
        }
        return $this->_getUrl('quotation/quote/add', $routeParams);
    }

    /**
     * Get post parameters for delete from quote   
     */
    public function getDeletePostJson($qitem)
    {
        $url = $this->_getUrl(self::DELETE_URL_QUOTE);

        $data = ['id' => $qitem->getId()];
        if (!$this->_request->isAjax()) {
            $data[\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED] = $this->getCurrentBase64Url();
        }
        return json_encode(['action' => $url, 'data' => $data]);
    }

    /**
     * Retrieve quotation quote url
     * @return string
     */
    public function getCartUrl()
    {
        return $this->_getUrl('quotation/quote');
    }
	public function getQuoteViewUrl($quote_id)
	{
		 return $this->_getUrl('quotation/quote/view/',array('quote_id'=>$quote_id));
	}
	public function getGuestCheckoutUrl($quote_id)
	{
		 return $this->_getUrl('quotation/customer/guestQuoteProcess/',array('quote_id'=>$quote_id));
	}
	/*****<<<======Start Downloadable product option display in quote==============>>>>>**/
	
	/*Magento\Downloadable\Helper\Catalog\Product\Configuration =>getOptions*/
	
	 public function getDownloadableOptions(\Magebees\QuotationManagerPro\Model\QuoteItem $qitem)
    {
        $qitemoptions = $this->getOptions($qitem);

        $qlinks = $this->getLinks($qitem);
        if ($qlinks) {
            $qlinksOption = ['label' => $this->getLinksTitle($qitem->getProduct()), 'value' => []];
            foreach ($qlinks as $link) {
                $qlinksOption['value'][] = $link->getTitle();
            }
            $qitemoptions[] = $qlinksOption;
        }

        return $qitemoptions;
    }
	/*Magento\Downloadable\Helper\Catalog\Product\Configuration =>getLinks*/
	 public function getLinks(\Magebees\QuotationManagerPro\Model\QuoteItem $qitem)
    {
		$qitemId=$qitem->getId();
        $qproduct = $qitem->getProduct();
        $qitemLinks = [];
        $linkIds = $qitem->getItemOptionByCode('downloadable_link_ids',$qitemId);
        if ($linkIds) {
            $qproductLinks = $qproduct->getTypeInstance()->getLinks($qproduct);
            foreach (explode(',', $linkIds->getValue()) as $linkId) {
                if (isset($qproductLinks[$linkId])) {
                    $qitemLinks[] = $qproductLinks[$linkId];
                }
            }
        }
        return $qitemLinks;
    }
	/*Magento\Downloadable\Helper\Catalog\Product\Configuration =>getLinksTitle*/
	public function getLinksTitle($qproduct)
    {
        $linktitle = $qproduct->getLinksTitle();
        if (strlen($linktitle)) {
            return $linktitle;
        }
        return $this->scopeConfig->getValue(\Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
	/*****<<<====== End Downloadable product option display in quote==============>>>>>**/	
	
	 public function getOptions(\Magebees\QuotationManagerPro\Model\QuoteItem $qitem)
    {
        return $this->getCustomOptions($qitem);
    }
	 public function getBackendOptions(\Magebees\QuotationManagerPro\Model\QuoteItem $qitem)
    {
		 $qproduct = $qitem->getProduct();
		 $typeId = $qproduct->getTypeId();
		    if ($typeId == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
				return $this->getBundleOptions($qitem);
			}
		elseif($typeId == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE)
		{
			return $this->getDownloadableOptions($qitem);	
		}
        return $this->getCustomOptions($qitem);
    }
	
 	public function getCustomOptions(\Magebees\QuotationManagerPro\Model\QuoteItem $qitem)
    {
		$qitemId=$qitem->getId();
        $qproduct = $qitem->getProduct();
        $qitemoptions = [];
        $qitemoptionIds = $qitem->getItemOptionByCode('option_ids',$qitemId);
        if ($qitemoptionIds) {
            $qitemoptions = [];
            foreach (explode(',', $qitemoptionIds->getValue()) as $qitemoptionId) {
                $qitemoption = $qproduct->getOptionById($qitemoptionId);
                if ($qitemoption) {
                    $qitemOption = $qitem->getItemOptionByCode('option_' . $qitemoption->getId(),$qitemId);
                    /** @var $group \Magento\Catalog\Model\Product\Option\Type\DefaultType */
                    $group = $qitemoption->groupFactory($qitemoption->getType())
                        ->setOption($qitemoption)
                        ->setConfigurationItem($qitem)
                        ->setConfigurationItemOption($qitemOption);
				
                    if ('file' == $qitemoption->getType()) {
                        $downloadParams = $qitem->getFileDownloadParams();
                        if ($downloadParams) {
                            $url = $downloadParams->getUrl();
                            if ($url) {
                                $group->setCustomOptionDownloadUrl($url);
                            }
                            $urlParams = $downloadParams->getUrlParams();
                            if ($urlParams) {
                                $group->setCustomOptionUrlParams($urlParams);
                            }
                        }
                    }				
				if($group instanceof \Magento\Catalog\Model\Product\Option\Type\File)
					{						
				$value= $group->getFormattedOptionValue($qitemOption->getValue());			$value=str_replace("sales/download/downloadCustomOption","quotation/download/customOption",$value);	
				}
				else
				{					
					$value= $group->getFormattedOptionValue($qitemOption->getValue());
				}						
                    $qitemoptions[] = [
                        'label' => $qitemoption->getTitle(),
                        'value' => $value,
                        'print_value' => $group->getPrintableOptionValue($qitemOption->getValue()),
                        'option_id' => $qitemoption->getId(),
                        'option_type' => $qitemoption->getType(),
                        'custom_view' => $group->isCustomizedView(),
                    ];
                }
            }
        }

        $addOptions = $qitem->getItemOptionByCode('additional_options',$qitemId);
        if ($addOptions) {			
            $qitemoptions = array_merge($qitemoptions, $this->getUnserializeData($addOptions->getValue()));
        }
		 $typeId = $qproduct->getTypeId();
		   $attributes = [];
        if ($typeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
			
			$attributesOption=$qitem->getItemOptionByCode('attributes',$qitemId);
			 if ($attributesOption) {
				 $data = $attributesOption->getValue();
            if (!$data) {
                return $attributes;
            }
            $data = $this->getUnserializeData($data);
				
				   foreach ($data as $attributeId => $attributeValue) {
					   $attribute=$this->_catalogeav->load($attributeId,'attribute_id');
					  $label = $attribute->getStoreLabel();
                    $value = $attribute;
                    if ($value->getSourceModel()) {
                        $value = $value->getSource()->getOptionText($attributeValue);
                    } else {
                        $value = '';
                        $attributeValue = '';
                    }

                    $attributes[] = [
                        'label' => $label,
                        'value' => $value,
                        'option_id' => $attributeId,
                        'option_value' => $attributeValue
                    ];
				   }
				
			 }
			
			 $qitemoptions = array_merge($qitemoptions,$attributes);
		}

        return $qitemoptions;
    }
	public function getBundleOptions(\Magebees\QuotationManagerPro\Model\QuoteItem $qitem)
    {
		
		$qitemId=$qitem->getId();
        $qitemoptions = [];
        $qproduct = $qitem->getProduct();

        /** @var \Magento\Bundle\Model\Product\Type $qproductTypeInstance */
        $qproductTypeInstance = $qproduct->getTypeInstance();

        // get bundle options
        $qitemoptionsQuoteItemOption = $qitem->getItemOptionByCode('bundle_option_ids',$qitemId);
        $bundleOptionsIds = $qitemoptionsQuoteItemOption
            ? $this->getUnserializeData($qitemoptionsQuoteItemOption->getValue())
            : [];

        if ($bundleOptionsIds) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $qitemoptionsCollection */
            $qitemoptionsCollection = $qproductTypeInstance->getOptionsByIds($bundleOptionsIds, $qproduct);

            // get and add bundle selections collection
            $selectionsQItemOption = $qitem->getItemOptionByCode('bundle_selection_ids',$qitemId);

            $bundleProSelectionIds = $this->getUnserializeData($selectionsQItemOption->getValue());

            if (!empty($bundleProSelectionIds)) {
                $selectionsCollection = $qproductTypeInstance->getSelectionsByIds($bundleProSelectionIds, $qproduct);

                $bundleOptions = $qitemoptionsCollection->appendSelections($selectionsCollection, true);
                foreach ($bundleOptions as $bundleOption) {
					
                    if ($bundleOption->getSelections()) {
                        $qitemoption = ['label' => $bundleOption->getTitle(), 'value' => []];

                        $bundleSelections = $bundleOption->getSelections();

                        foreach ($bundleSelections as $bundleSelection) {
							
                            $qty = $this->getSelectionQty($qitem, $bundleSelection->getSelectionId()) * 1;
                            if ($qty) {								
                                $qitemoption['value'][] = $qty . ' x '
                                    . $this->escaper->escapeHtml($bundleSelection->getName())
                                    . ' '
                                    . $this->pricingHelper->currency(
                                        $this->getSelectionFinalPrice($qitem, $bundleSelection)
                                    );
                                $qitemoption['has_html'] = true;
                            }
                        }

                        if ($qitemoption['value']) {
                            $qitemoptions[] = $qitemoption;
                        }
                    }
                }
            }
        }
		 
 return array_merge(
            $qitemoptions,
            $this->getCustomOptions($qitem)
        );
     
    }
	 public function getSelectionFinalPrice(\Magebees\QuotationManagerPro\Model\QuoteItem $qitem, \Magento\Catalog\Model\Product $selectionProduct)
    {
        $selectionProduct->unsetData('final_price');

        $qproduct = $qitem->getProduct();
        /** @var \Magento\Bundle\Model\Product\Price $price */
        $price = $qproduct->getPriceModel();

        return $price->getSelectionFinalTotalPrice(
            $qproduct,
            $selectionProduct,
            $qitem->getQty(),
            $this->getSelectionQty($qitem, $selectionProduct->getSelectionId()),
            false,
            true
        );
    }
	  public function getSelectionQty(\Magebees\QuotationManagerPro\Model\QuoteItem $qitem, $selectionId)
    {
		$qitemId=$qitem->getId();
        $selectionQty = $qitem->getItemOptionByCode('selection_qty_' . $selectionId,$qitemId);
        if ($selectionQty) {			
            return $selectionQty->getValue();
        }
        return 0;
    }
	public function checkQuoteRequestExist($itemId,$quoteId)
	{
		$quoterequest=$this->_quoteRequestCollFactory->create()->addFieldToFilter('item_id',$itemId)->addFieldToFilter('quote_id',$quoteId)->getData();
		return $quoterequest;
	}
	public function getDynamicQuoteQty($qitemId,$quote_id,$qproductId,$request_id=null)
	{
		
		 	$quote_request = $this->_quoteRequestCollFactory->create();           
			$quote_request->addFieldToFilter('item_id',$qitemId)
				->addFieldToFilter('quote_id',$quote_id)
				->addFieldToFilter('product_id',$qproductId)
				->addFieldToFilter('request_id',array('neq' => $request_id))			
				//->addFieldToFilter('is_default',0)
				->setOrder('is_default', 'DESC');
			return $quote_request;
	}
	public function getQuoteDataById($quote_id)
	{
		$quote=$this->_quoteCollectionFactory->create()->addFieldToFilter('quote_id',$quote_id);
			return $quote;
	}
	public function getStatus($status)
	{
		if($status)
		{
		$status_arr=$this->status->getOptionArray();	
		return $status_arr[$status];
		}
		else
		{
			return '';
		}
	}
	public function getAllStatus()
	{
		$status_arr=$this->status->getOptionArray();	
		return $status_arr;
	}
	 public function prepareString($string)
    {
        return $this->escaper->escapeHtml($this->string->splitInjection($string));
    }
	public function renderCustomAddress($addressObject,$type=null)
	{
		if($type=='')
		{
			$type='html';
		}
		try {
			
			/** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
			$renderer = $this->_addressConfig->getFormatByCode($type)->getRenderer();
			return $renderer->renderArray($addressObject);
		} catch (\Magento\Framework\Exception\NoSuchEntityException $e) 		{
			return null;
		}
	}
	public function getAddressArray($addressId)
	{
		$addressObject = $this->addressRepository->getById($addressId);		
		$addressArr=$this->addressMapper->toFlatArray($addressObject);
		return $addressArr;
	}
	public function getFormattedAddress($addressId,$type=null)
	{
		if($type=='')
		{
			$type='html';
		}
		try {
			$addressObject = $this->addressRepository->getById($addressId);
			/** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
			$renderer = $this->_addressConfig->getFormatByCode($type)->getRenderer();
			return $renderer->renderArray($this->addressMapper->toFlatArray($addressObject));
		} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
			return null;
		}
	}
	 public function loadCustomerById($customerId) {
         $cst = $this->customerRepository->getById($customerId);
         return $cst;
    } 
	public function checkIncIdExist($quote_inc_id)
	{
		$quote=$this->_quoteCollectionFactory->create()->addFieldToFilter('increment_id',$quote_inc_id);
		
		return $quote->getData();
	}
	public function loadQuoteById($quote_id)
	{
		$quote=$this->_quoteFactory->create()->load($quote_id,'quote_id');
		return $quote;
	}
	public function loadQuoteItemById($qitem_id)
	{
		$quoteItem=$this->_quoteItemFactory->create()->load($qitem_id,'id');
		return $quoteItem;
	}
	public function loadQuoteCustomerByQuoteId($quote_id)
	{
		$quoteCustomer=$this->_quoteCustomerFactory->create()->load($quote_id,'quote_id');
		return $quoteCustomer;
	}
	
	public function checkIsDefaultQty($qitem_id,$request_id)
	{
		$requestedqties=$this->_quoteRequestCollFactory->create();
					$requestedqties->addFieldToFilter('item_id',$qitem_id)
					->addFieldToFilter('request_id',array('neq' => $request_id));			
				return $requestedqties;
	}
	public function getDefaultRequestQty($quote_id,$qitem_id)
	{
		$requestedqties=$this->_quoteRequestCollFactory->create();
					$requestedqties->addFieldToFilter('item_id',$qitem_id)
					->addFieldToFilter('quote_id',$quote_id)			
					->addFieldToFilter('is_default',1);	
		$request_qty_data=$requestedqties->getData();
	if(isset($request_qty_data[0]))
	{
				return $request_qty_data[0];
	}
		return false;
	}
	public function getQuoteFiles($quote_id)
	{
	$file_data=[];
	$quote_files=$this->_quoteFilesCollFactory->create()->addFieldToFilter('quote_id',$quote_id);
	$quote_files_data=$quote_files->getData();
	if(count($quote_files_data))
	{
		foreach($quote_files_data as $quote_files)
		{
			
			$file_data[]=$quote_files;
		}
				return $file_data;
	}
		return false;
		
	}
	public function getQuoteCustomer($quote_id)
	{	
	$quote_customers=$this->_quoteCustomerCollFactory->create()->addFieldToFilter('quote_id',$quote_id);
	$quote_customer_data=$quote_customers->getData();
	if(count($quote_customer_data))
	{		
		return $quote_customer_data[0];
	}
		return false;
		
	}
	public function getQuoteMessages($quote_id)
	{
	$msg_data=[];
	$quote_msgs=$this->_quoteMessageCollFactory->create()->addFieldToFilter('quote_id',$quote_id);
	$quote_msgs_data=$quote_msgs->getData();
	if(count($quote_msgs_data))
	{
		return $quote_msgs_data[0];
	}
		return false;
		
	}
	public function getQuoteFilePath($file)
	{
		$mediaUrlDirectory = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
		return $file_path=$mediaUrlDirectory.'quotation'.$file;
	}
	public function IsStatusAllowForProposal($status)
	{
		$not_allow_for_proposal = array();
		$not_allow_for_proposal[] = Status::STARTING;	
		$not_allow_for_proposal[] =Status::STARTING_ACTION_STORE_OWNER;
		$not_allow_for_proposal[] =Status::STARTING_ACTION_CUSTOMER;
		$not_allow_for_proposal[] =Status::PROCESSING;
		$not_allow_for_proposal[] = Status::PROCESSING_ACTION_STORE_OWNER;	
		$not_allow_for_proposal[] = Status::PROCESSING_ACTION_CUSTOMER;
		$not_allow_for_proposal[] = Status::PROPOSAL_CREATED;
		$not_allow_for_proposal[] = Status::PROPOSAL_SENT_ACTION_STORE_OWNER;
		$not_allow_for_proposal[] = Status::PROPOSAL_CANCELLED;
		$not_allow_for_proposal[] = Status::PROPOSAL_CANCELLED_OUTOF_STOCK;
		$not_allow_for_proposal[] = Status::PROPOSAL_REJECTED;
		$not_allow_for_proposal[] = Status::PROPOSAL_ORDERED;
		return !in_array($status, $not_allow_for_proposal);		
	}
	 public function getItemsByQuoteId($quote_id)
    {
		  	$qitems = $this->_quoteItemCollFactory->create();   		
			$qitems->addFieldToFilter('quote_id',$quote_id);				
			return $qitems;
    }
	 public function getProductIdConfig($item_id)
    {
		  	$qitems = $this->_quoteItemCollFactory->create();   			
			$qitems->addFieldToFilter('parent_item_id',$item_id);			
			return $qitems;
    }
	
	 public function getItemOptByItemId($qitem_id)
    {
		  	$qitems = $this->_quoteItemOptCollFactory->create();   		
			$qitems->addFieldToFilter('item_id',$qitem_id);				
			return $qitems;
    }
	public function isCustomerLoggedIn()
	{
		return $this->_customerSession->isLoggedIn();
	}
	public function getLoggedInCustomer()
	{
		return $this->_customerSession->getCustomer();
	}
	public function getDefaultShippingAdd()
	{
		$shippingID =  $this->_customerSession->getCustomer()->getDefaultShipping();
		//$billingID =  $this->_customerSession->getCustomer()->getDefaultBilling();
		$address =  $this->_customerAddress->load($shippingID);
		if($shippingID)
		{
		return $address->getData();
		}
		else
		{
			return false;
		}
		
	}
	public function getDefaultBillingAdd()
	{
		$billingID =  $this->_customerSession->getCustomer()->getDefaultBilling();
		$address =  $this->_customerAddress->load($billingID);
		if($billingID)
		{
		return $address->getData();
		}
		else
		{
			return false;
		}
		
		
	}
	public function getCustomerAddresses()
	{
		return $this->_customerSession->getCustomer()->getAddresses();
	}
	public function IsCustomShipAddressAvail($quoteId)
    {
		  	$quoteAddress= $this->_quoteAddressCollFactory->create(); 	
			$quoteAddress->addFieldToFilter('quote_id',$quoteId)->addFieldToFilter('address_type','shipping');	
			$quoteAddress_data=$quoteAddress->getData();
			if(count($quoteAddress_data))
			{
				return $quoteAddress_data[0];
			}
			return false;
			
    }
	public function IsCustomBillAddressAvail($quoteId)
    {
		  	$quoteAddress= $this->_quoteAddressCollFactory->create(); 	
			$quoteAddress->addFieldToFilter('quote_id',$quoteId)->addFieldToFilter('address_type','billing');	
			$quoteAddress_data=$quoteAddress->getData();
			if(count($quoteAddress_data))
			{
				return $quoteAddress_data[0];
			}
			return false;
			
    }
	public function getDefaultAddressInQuote($quoteId,$type,$address_id)
	{
			$quoteAddress= $this->_quoteAddressCollFactory->create(); 	
			$quoteAddress->addFieldToFilter('quote_id',$quoteId)->addFieldToFilter('address_type',$type)->addFieldToFilter('customer_address_id',$address_id);	
			$quoteAddress_data=$quoteAddress->getData();
			if(count($quoteAddress_data))
			{
				return $quoteAddress_data[0];
			}
			return false;
		
	}
	public function loadAddressByCustomerAddId($address_id)
	{
		$address=$this->_quoteAddressFactory->create()->load($address_id,'address_id');
		return $address;
	}
	public function getCustomAddressById($addressId)
    {
		  	$quoteAddress= $this->_quoteAddressCollFactory->create(); 	
			$quoteAddress->addFieldToFilter('address_id',$addressId);
			$quoteAddress_data=$quoteAddress->getData();
			if(count($quoteAddress_data))
			{
				return $quoteAddress_data[0];
			}
			return false;
			
    }
	 public function getMediaUrl(){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
	public function getBackendSession()
	{
		return $this->backendSession;
	}
	public function isShowPriceBeforeProposal($status)
	{
		//$front_config=$this->getHidePriceConfig();
		$hideprice_config=$this->getHidePriceConfig();	
		$currentUrl=$this->_storeManager->getStore()->getCurrentUrl();
		$isadmin=$this->isAdmin();
		if (!($isadmin))
		{
		if($hideprice_config['enable_qprice'])
		{
			$group_ids=$hideprice_config['price_customer_group'];		
			$group_ids_arr=explode(',',$group_ids);			
$isLoggedIn=$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
		if($isLoggedIn):
		$customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
		else:
		$customerGroupId = 0;
		endif;
			
				
		if ((in_array($customerGroupId,$group_ids_arr)))
		{
			$display_price=$hideprice_config['enable_price_before_proposal'];
		if($display_price)
		{
			return true;
		}
		else
		{
			if($status<20)
			 {
				 return false;
			 }
			else
			{
				return true;
			}
		}
		}
		else
		{
			return true;			
		}			
		}
		else
		{
			return true;
		}
		}
		else
		{
			return true;
		}

	}
	public function isAdmin()
	{
		$areaCode = $this->_state->getAreaCode();

		if ($areaCode == self::AREA_CODE) {
			return true;
		} else {
			return false;
		}
	}


}
