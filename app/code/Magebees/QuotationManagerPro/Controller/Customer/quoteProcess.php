<?php
namespace Magebees\QuotationManagerPro\Controller\Customer;
use Magento\Framework\Controller\ResultFactory;
use Magebees\QuotationManagerPro\Model\Quote\Status;
use Magento\Catalog\Api\ProductRepositoryInterface;

class quoteProcess extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magebees\QuotationManagerPro\Helper\Email $emailHelper,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		  \Magento\Checkout\Model\Cart $cart,
		    \Magento\Customer\Model\Address $customerAddress,
		    \Magento\Customer\Model\Session $customerSession,
		  \Magento\Customer\Model\CustomerFactory $customerFactory,
		    \Magento\Store\Model\StoreManagerInterface $storeManager,
		   ProductRepositoryInterface $productRepository,
		   \Magento\Framework\Registry $coreRegistry,		  
		  \Magento\Framework\Session\SessionManager $session
		  
    ) {
    
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
		   $this->_customerAddress = $customerAddress;  
		   $this->emailHelper = $emailHelper;
		   $this->quoteHelper = $quoteHelper;
		   $this->cart = $cart;
		  $this->session =$session;
		  $this->_storeManager = $storeManager;
		  $this->_customerSession=$customerSession;
		  $this->_customerFactory = $customerFactory;
		  $this->productRepository = $productRepository;  
		   
    }
    public function execute()
    {
		 $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
     $config=$this->quoteHelper->getConfig();
		$enable_expiration_time=$config['enable_expiration_time'];
        $post = $this->getRequest()->getPost();
		$quote_id=$post['currentQuoteId'];
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		if($quote_id)
		{
			$expiryDate=$quote->getExpiredAt();	
		$expiryDate=$this->quoteHelper->getExpiredDateFormat($expiryDate);
		$inc_id=$quote->getIncrementId();
		if(($enable_expiration_time)&&($expiryDate <= date('Y-m-d')))
		{
		 $resultRedirect->setUrl($this->quoteHelper->getQuoteViewUrl($quote_id));
			//$quote->setStatus(Status::PROPOSAL_EXPIRED);
			//$quote->save();
			$this->messageManager->addNotice('Quotation # '.$inc_id.' had been Expired'); 	
		}
			else
			{
				   $resultRedirect->setUrl($this->_url->getUrl('checkout/index/index'));
			$this->session->setProcessingQuoteId(null);
			$this->session->unsProcessingQuoteId();
			$this->session->setProcessingQuoteId($quote_id);
			 $this->cart->truncate();
			
			$items=$this->quoteHelper->getItemsByQuoteId($quote_id);
			$items->addFieldToFilter('parent_item_id', ['null' => true]);
			foreach($items as $item)
			{
				$item_id=$item->getId();
				$product_id=$item->getProductId();
				 $storeId = $this->_storeManager->getStore()->getId();
				$product = $this->productRepository->getById($product_id, false, $storeId, true);
				 $option = $item->getItemOptionByCode('info_buyRequest',$item->getId());
				
        $data = $option ? $this->quoteHelper->getUnserializeData($option->getValue()) : [];
        $buyRequest = new \Magento\Framework\DataObject($data);

        // Overwrite standard buy request qty, because item qty could have changed since adding to quote
		$default_tier_request=$this->quoteHelper->getDefaultRequestQty($quote_id,$item_id);
		$custom_price=$default_tier_request['request_qty_price'];
		$item_qty=$default_tier_request['request_qty'];
		$this->_coreRegistry->register('quoteitem_custom_price', $custom_price);
        $buyRequest->setOriginalQty($buyRequest->getQty())->setQty($item_qty * 1);
		try
		{
			$this->cart->addProduct($product,$buyRequest);
		}
	 	catch (\Exception $e) {

		 $this->messageManager->addError(
				$this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
			);
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
		return $resultRedirect;	 

		 }
			
			}		
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$quote->setStatus(Status::PROPOSAL_ACCEPTED);
		$quote->save();	
		$this->cart->save();	
			 // $this->cart->getQuote()->collectTotals();
			$customer = $this->_customerFactory->create()->load($quote->getCustomerId());    
    		$billingAddressId = $customer->getDefaultBilling();
			$shippingAddressId = $customer->getDefaultShipping();			
			$this->_customerSession->setMainDefaultBilling($billingAddressId);
			$this->_customerSession->setMainDefaultShipping($shippingAddressId);
			$quote_shipping_id=$quote->getshipAddressId();
			$quote_bill_id=$quote->getbillAddressId();
			$all_address_data=$this->_customerSession->getCustomer()->getAddresses();
			
			/**end set default shipping and billind address from quote address id */
			
		$email_config=$this->quoteHelper->getEmailConfig();
		if($email_config['proposal_accept'])
		{
			//$this->emailHelper->sendProposalStatusMail($quote_id,'accepted');
		}
		}
		}
		return $resultRedirect;
    }
}
