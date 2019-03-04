<?php

namespace Magebees\QuotationManagerPro\Model\Backend\Session;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use \Magebees\QuotationManagerPro\Model\Quote\Status;


class Quote extends \Magento\Framework\Session\SessionManager
{
   
    protected $_quote;
    protected $_qstore;
    protected $customerRepository;
    protected $quoteRepository;
    protected $_qstoreManager;
    protected $groupManagement;
    protected $quoteFactory;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $qsidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $qsaveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $qcookieManager,
		\Magebees\QuotationManagerPro\Helper\Address $addressHelper,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        CustomerRepositoryInterface $customerRepository,            
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        GroupManagementInterface $groupManagement,
		\Magebees\QuotationManagerPro\Model\QuoteRepository $quoteRepository,
        \Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory,
		 \Magebees\QuotationManagerPro\Model\QuoteMessageFactory $quoteMessageFactory	,
		 \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
    		\Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
		 \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		 \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        $this->customerRepository = $customerRepository;       
        $this->_qstoreManager = $storeManager;
        $this->groupManagement = $groupManagement;
        $this->quoteFactory = $quoteFactory;
		 $this->_quoteMessageFactory = $quoteMessageFactory;
		 $this->quoteRepository = $quoteRepository;
		   $this->quoteHelper = $quoteHelper;
		  $this->_resourceConfig = $resourceConfig;
		  $this->_cacheTypeList = $cacheTypeList;
			$this->addressHelper = $addressHelper;
    		$this->_cacheFrontendPool = $cacheFrontendPool;
		$this->datetime = $datetime;
        parent::__construct(
            $request,
            $qsidResolver,
            $sessionConfig,
            $qsaveHandler,
            $validator,
            $storage,
            $qcookieManager,
            $cookieMetadataFactory,
            $appState
        );
        if ($this->_qstoreManager->hasSingleStore()) {
           // $this->setStoreId($this->_qstoreManager->getStore(true)->getId());
        }
    }

   
    public function getQuote()
    {
			$config=$this->quoteHelper->getConfig();		
			$datetime = $this->datetime->gmtDate();
		$expiration_time=$config['expiration_time'];
			$expirydate = strtotime($datetime);
			$expirydate = strtotime("+".$expiration_time."day", $expirydate);
			$expirydate=date('Y-m-d', $expirydate);	
			$types = array('config','full_page');
			foreach ($types as $type) {
				$this->_cacheTypeList->cleanType($type);
			}
			foreach ($this->_cacheFrontendPool as $cacheFrontend) {
				$cacheFrontend->getBackend()->clean();
			}
			$base_currency_code=$this->quoteHelper->getBaseCurrencyCode();
			$quoteNoConfig=$this->quoteHelper->getQuoteNumberConfig();	  		
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
			$quote_inc_id=$quotePrefix." ".$increment_id;
			$inc_id_exist=$this->quoteHelper->checkIncIdExist($quote_inc_id);
			if(!empty($inc_id_exist))
			{
				$quote_inc_id=$quote_inc_id.'-1';
			}
        if ($this->_quote === null) {
			
            $this->_quote = $this->quoteFactory->create();
      //    if ($this->getStoreId()) {
			//  echo $this->getStoreId();die;
                if (!$this->getQuoteId()) {
					if ($this->getStoreId()) {
					if($this->getCustomerId())
					{
						$customer=$this->quoteHelper->loadCustomerById($this->getCustomerId());
					$billing_add_id=$customer->getDefaultBilling();
					$shipping_add_id=$customer->getDefaultShipping();	
						$this->_quote->setbillAddressId($billing_add_id);
					$this->_quote->setshipAddressId($shipping_add_id);
						
						
					}
					
					$this->_quote->setCustomerGroupId($this->groupManagement->getDefaultGroup()->getId());
                    $this->_quote->setIsActive(true);					
                    //$this->_quote->setIsActive(false);					
                    $this->_quote->setIsBackend(false);					
                    $this->_quote->setStoreId($this->getStoreId());   				
					$this->_quote->setIncrementId($quote_inc_id);
					
					if($this->getCurrencyId()!='false')
					{							
						$this->_quote->setCurrencyCode($this->getCurrencyId());
					}
					else
					{
						$this->_quote->setCurrencyCode($base_currency_code);
					}
					$this->_quote->setBaseCurrencyCode($base_currency_code);
					$this->_quote->setAssignTo($assign_to);
					$this->_quote->setStatus(Status::STARTING);
					$this->_quote->setCreatedAt($datetime);
					$this->_quote->setExpiredAt($expirydate);
                    $this->_quote->save();					  
				    $this->_resourceConfig->saveConfig(
                    'quotation/quotationsetting/current_quote',
                    $increment_id,
                    'default',
                    0
                );
					
                   $this->setQuoteId($this->_quote->getId());
                    //$this->_quote = $this->quoteRepository->get($this->getQuoteId(), [$this->getStoreId()]);
						
						// for save message detail for generated quote
					$message_detail=[];
					$message_arr=[];	
					$quoteStatus=$this->_quote->getStatus();			
					$sub_detail_arr=[];
					$sub_detail_arr['is_admin']=1;
					$sub_detail_arr['display_at_front']=0;
					$sub_detail_arr['is_customer_notify']=1;
					$sub_detail_arr['quoteStatus']=$quoteStatus;
					$sub_detail_arr['message']=' New Quotation Submit';
					$message_arr[$datetime]=$sub_detail_arr;					
					$customer_id=$this->getCustomerId()?$this->getCustomerId():'';
					$admin_id=$this->_quote->getAssignTo();
					$message_detail['customer_id']=$customer_id;
					$message_detail['admin_id']=$admin_id;
					$message_detail['quote_id']=$this->_quote->getId();
					$message_detail['messages']=$message_arr;
					$serialize_msg_detail=$this->quoteHelper->getSerializeData($message_detail);
					$quote_message=$this->_quoteMessageFactory->create();
					$quote_message->setQuoteId($this->_quote->getId());
					$quote_message->setCommunication($serialize_msg_detail);
					$quote_message->save();
						
					}
                } else {
				
                   // $this->_quote = $this->quoteRepository->get($this->getQuoteId(), [$this->getStrId()]);		
					 $this->_quote=$this->quoteHelper->loadQuoteById($this->getQuoteId());
                //    $this->_quote->setStoreId($this->getStrId());
					 $this->_quote->save();
					$this->setQuoteId($this->_quote->getId());
                }

                if ($this->getCustomerId() && $this->getCustomerId() != $this->_quote->getCustomerId()) {
				if ($this->getStoreId()) {
				//	$this->_quote = $this->quoteRepository->get($this->getQuoteId(), [$this->getStoreId()]);
					 $this->_quote=$this->quoteHelper->loadQuoteById($this->getQuoteId());
                    $customer = $this->customerRepository->getById($this->getCustomerId());
                  //  $this->_quote->setCustomerId($customer->getId());					
					 $this->_quote->setStoreId($this->getStoreId());
                   $this->_quote->save();
					 $this->setQuoteId($this->_quote->getId());
					/*try
					{
					$this->addressHelper->saveCustomAddress($shipping_add_id,$this->_quote,'shipping');
					$this->addressHelper->saveCustomAddress($billing_add_id,$this->_quote,'billing');
					}
					catch (\Exception $e) {
					}*/
					
				}
                }
			 
	//	 }
            
            $this->_quote->setIgnoreOldQty(true);
            $this->_quote->setIsSuperMode(true);
        }

        return $this->_quote;
    }

    /**
     * Retrieve store model object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if ($this->_qstore === null) {
            $this->_qstore = $this->_qstoreManager->getStore($this->getStoreId());
            $qcurrencyId = $this->getCurrencyId();
            if ($qcurrencyId) {
                $this->_qstore->setCurrentCurrencyCode($qcurrencyId);
            }
        }
        return $this->_qstore;
    }

   
}
