<?php
namespace Magebees\QuotationManagerPro\Model;

use Magento\Customer\Api\Data\CustomerInterface;

class Session extends \Magento\Framework\Session\SessionManager
{
   
    const QUOTE_STATE_BEGIN = 'begin';

    protected $_quote;
    protected $_customer;
    protected $_loadInactive = false;  
    protected $_customerSession;
    protected $mquoteRepository;
    protected $_qremoteAddress;
    protected $_eventManager;
    protected $_storeManager;
    protected $customerRepository;
    protected $mquoteIdMaskFactory;
    protected $isQuoteMasked;
    protected $mquoteFactory;
    public function __construct(
        \Magento\Framework\App\Request\Http $qrequest,
        \Magento\Framework\Session\SidResolverInterface $qsidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $qsessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $qsaveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $qcookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $qcookieMetadataFactory,
        \Magento\Framework\App\State $appState,      
        \Magento\Customer\Model\Session $customerSession,      
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,         
        \Magebees\QuotationManagerPro\Model\QuoteFactory $mquoteFactory,
        \Magebees\QuotationManagerPro\Model\QuoteItemFactory $mquoteItemFactory,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,		\Magebees\QuotationManagerPro\Model\QuoteRequestFactory $quoteRequestFactory, \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem\CollectionFactory $mquoteItemCollFactory,   
		\Magento\Framework\Stdlib\DateTime\DateTime $datetime,
		\Magebees\QuotationManagerPro\Model\QuoteRepository $mquoteRepository
    ) {
       
        $this->_customerSession = $customerSession;       
        $this->_qremoteAddress = $remoteAddress;
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;           
        $this->quoteFactory = $mquoteFactory;
		$this->quoteHelper = $quoteHelper; 
        $this->quoteItemFactory = $mquoteItemFactory;
        $this->quoteItemCollFactory = $mquoteItemCollFactory;
		 $this->_quoteRequestFactory = $quoteRequestFactory;
		$this->datetime = $datetime;
		 $this->quoteRepository = $mquoteRepository;
        parent::__construct(
            $qrequest,
            $qsidResolver,
            $qsessionConfig,
            $qsaveHandler,
            $validator,
            $storage,
            $qcookieManager,
            $qcookieMetadataFactory,
            $appState
        );
    }

    public function getQuote()
    {
		$product_id=$this->getLastAddedProductId();		
		$datetime = $this->datetime->gmtDate();
        if ($this->_quote === null) {
			
            $mquote = $this->quoteFactory->create();
            $mquote_item = $this->quoteItemFactory->create();
            if ($this->getQuoteId()) {	
				
				 try {
                    if ($this->_loadInactive) {
                        $mquote = $this->quoteRepository->get($this->getQuoteId());
                    } else {
                        $mquote = $this->quoteRepository->getActive($this->getQuoteId());
                    }
					 
				 }
				catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->setQuoteId(null);
                }				
            }
            if (!$this->getQuoteId()) {					
                if ($this->_customerSession->isLoggedIn() || $this->_customer) {				
                    $qcustomerId = $this->_customer
                        ? $this->_customer->getId()
                        : $this->_customerSession->getCustomerId();
                    try { 							
						 $mquote = $this->quoteRepository->getActiveForCustomer($qcustomerId);
						if($mquote->getId())
						{
							 $this->setQuoteId($mquote->getId());
						}
						else
						{
						$mquote= $this->quoteFactory->create();
						$mquote->setCustomerId($this->_customerSession->getCustomerId());
						$mquote->setStoreId($this->_storeManager->getStore()->getId());
						$mquote->setCreatedAt($datetime);
                   		$mquote->save();
					 	$this->setQuoteId($mquote->getId());
						}           
						
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
						$mquote= $this->quoteFactory->create();
						$mquote->setCustomerId($this->_customerSession->getCustomerId());
						$mquote->setStoreId($this->_storeManager->getStore()->getId());
						$mquote->setCreatedAt($datetime);
                   		$mquote->save();
					 	$this->setQuoteId($mquote->getId());	
                    }
                } else {
					
						$mquote->setCustomerId(NULL);
						$mquote->setCreatedAt($datetime);					
						$mquote->setStoreId($this->_storeManager->getStore()->getId());
                   		$mquote->save();
					 	$this->setQuoteId($mquote->getId());					
                }
            }			
            $this->_quote = $mquote;
        }		
        return $this->_quote;
    }
	  public function clearQuote()
		{
			
			$this->_quote = null;
			$this->setQuoteId(null);
			$this->setLastSuccessQuoteId(null);
			return $this;
		}
  	public function loadCustomerQuote()
    {
        if (!$this->_customerSession->getCustomerId()) {
            return $this;
        }
        try {
            $customerQuote = $this->quoteRepository->getForCustomer($this->_customerSession->getCustomerId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customerQuote = $this->quoteFactory->create();
        }
			
        $customerQuote->setStoreId($this->_storeManager->getStore()->getId());
	
	
		
        if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId()) {		
            if ($this->getQuoteId()) {		
			/* Start If customer already active and has quote item then merge current quote items*/
				$mquote=$this->quoteFactory->create();
				$mquote->load($this->getQuoteId(),'quote_id');
				$items = $this->quoteItemCollFactory->create();           
				$customer_quote_items=$items->addFieldToFilter('quote_id',$customerQuote->getId());
			//	echo $customerQuote->getId();die;
				 foreach ($mquote->getAllVisibleItems() as $item) {	 
					 $item->setQuoteRequest($item);
					  $found = false;
				 	foreach ($customer_quote_items as $mquoteItem) { 
						if ($mquoteItem->compare($item)) {
                    $mquoteItem->setQty($mquoteItem->getQty() + $item->getQty());	
					$mquoteItem->save();
					
					 $item_id=$mquoteItem->getId();
					 $quote_id=$mquoteItem->getQuoteId();
					 $quoteReqExist=$this->quoteHelper->checkQuoteRequestExist($item_id,$quote_id);
					if(!empty($quoteReqExist))
					 {						
						 $req_id=$quoteReqExist[0]['request_id'];		
						 $quoterequests=$this->_quoteRequestFactory->create()->load($req_id,'request_id');
						 $qty=$item->getQty()+ $quoteReqExist[0]['request_qty'];
						 $quoterequests->setRequestQty($qty);
						  $quoterequests->save();
						 
					 }
                    $found = true;
                    break;
                		}		
					
				 	}
				 if(!$found)
				 {
					$item->setQuoteId($customerQuote->getId());
					$item->save();
				 }
				}
				$mquote->delete();	
			/* End If customer already active and has quote item then merge current quote items*/
            }

            $this->setQuoteId($customerQuote->getId());
		
            if ($this->_quote) {
                $this->quoteRepository->delete($this->_quote);
            }
            $this->_quote = $customerQuote;
        } else {	
			 $mquote = $this->quoteRepository->getActive($this->getQuoteId());		
			 if ($mquote->getIsActive()) 
			 {						
				 $mquote->setCustomerId($this->_customerSession->getCustomerId());
				 $mquote->setStoreId($this->_storeManager->getStore()->getId());
				 $this->setQuoteId($mquote->getId());	
				 $mquote->save();	
			 }
			else
			{			
				$customerQuote = $this->quoteFactory->create();
				$customerQuote->setCustomerId($this->_customerSession->getCustomerId());
				$customerQuote->setStoreId($this->_storeManager->getStore()->getId());
				$customerQuote->save();
				$this->setQuoteId($customerQuote->getId());
			}
        }
        return $this;
    }
	
	/**
     * @return string
     * @codeCoverageIgnore
     */
    protected function _getQuoteIdKey()
    {
        return 'magebees_quote_id_' . $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * @param int $mquoteId
     * @return void
     * @codeCoverageIgnore
     */
    public function setQuoteId($mquoteId)
    {
        $this->storage->setData($this->_getQuoteIdKey(), $mquoteId);
    }

    /**
     * @return int
     * @codeCoverageIgnore
     */
    public function getQuoteId()
    {
        return  $this->storage->getData($this->_getQuoteIdKey());
    }
    
}
