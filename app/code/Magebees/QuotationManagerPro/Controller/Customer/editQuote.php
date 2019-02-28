<?php
namespace Magebees\QuotationManagerPro\Controller\Customer;
use Magento\Framework\Controller\ResultFactory;
use Magebees\QuotationManagerPro\Model\Quote\Status;

class editQuote extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magebees\QuotationManagerPro\Helper\Email $emailHelper,
		\Magebees\QuotationManagerPro\Model\Session $quoteSession,
		\Magento\Customer\Model\Session $customerSession,   
		\Magebees\QuotationManagerPro\Model\QuoteItemFactory $quoteItemFactory,
		\Magebees\QuotationManagerPro\Model\QuoteRequestFactory $quoteRequestFactory,
		\Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory,
		  \Magento\Store\Model\StoreManagerInterface $storeManager,     
		\Magebees\QuotationManagerPro\Model\Quote\Item\OptionFactory $quoteItemOptionFactory,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper
    ) {
		  
           $this->_quoteSession = $quoteSession;
		   $this->quoteFactory = $quoteFactory;
		  $this->_quoteItemFactory = $quoteItemFactory;
		  $this->_quoteRequestFactory = $quoteRequestFactory;
		  $this->_quoteItemOptionFactory = $quoteItemOptionFactory;
		   $this->emailHelper = $emailHelper;
		   $this->quoteHelper = $quoteHelper;	
		   $this->_storeManager = $storeManager; 
		     $this->_customerSession = $customerSession;
		  	parent::__construct($context);
    }
    public function execute()
    {
		$config=$this->quoteHelper->getConfig();
		$enable_expiration_time=$config['enable_expiration_time'];
		 $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
       // $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        $post = $this->getRequest()->getPost();
		$quote_id=$post['currentQuoteId'];
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
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
		$quote->setStatus(Status::PROPOSAL_CANCELLED);		
		 $new_quote = $this->quoteFactory->create();
		 if ($this->_customerSession->isLoggedIn()) {
		 $new_quote->setCustomerId($this->_customerSession->getCustomerId());
		 }
		else
		{
		$new_quote->setCustomerId(NULL);
		}
		$new_quote->setStoreId($this->_storeManager->getStore()->getId());
		$new_quote->save();
		$this->_quoteSession->setQuoteId($new_quote->getId());	
		
			$new_quote_id=$new_quote->getId();
			$items=$this->quoteHelper->getItemsByQuoteId($quote_id);
			foreach($items as $item)
			{			
				$item_data=[];						
				$item_id=$item->getId();
				$product_id=$item->getProductId();
				$item_data['quote_id'] = $new_quote_id;
				$item_data['product_id']=$item->getProductId();
				$item_data['store_id']=$item->getStoreId();
				$item_data['parent_item_id']=$item->getParentItemId();
				$item_data['qty']=$item->getQty();
				$item_data['price']=$item->getPrice();
				$item_data['product_type']=$item->getProductType();
				$item_data['has_options']=$item->getHasOptions();
				$item_data['request_info']=$item->getRequestInfo();
				$quote_item=$this->_quoteItemFactory->create();
				$quote_item->setData($item_data);
				$quote_item->save();
				
				// save item option for new quote
				$options=$this->quoteHelper->getItemOptByItemId($item_id);
				foreach($options as $option)
				{
					$option_data=[];
					$new_item_id=$quote_item->getId();
					$option_data['item_id']=$new_item_id;
					$option_data['product_id']=$option->getProductId();
					$option_data['code']=$option->getCode();
					$option_data['value']=$option->getValue();
					
				$quote_item_option=$this->_quoteItemOptionFactory->create();
				$quote_item_option->setData($option_data);
				$quote_item_option->save();
				}
				
				// save tier quantity for new quote
				$quotationtierQty=$this->quoteHelper->getDynamicQuoteQty($item_id,$quote_id,$product_id);
				foreach($quotationtierQty as $qty)
				{
					$quote_tier_qty_data=[];
					$new_item_id=$quote_item->getId();
					$quote_tier_qty_data['item_id']=$new_item_id;
					$quote_tier_qty_data['quote_id']=$new_quote_id;
					$quote_tier_qty_data['product_id']=$qty->getProductId();
					$quote_tier_qty_data['request_qty']=$qty->getRequestQty();
					$quote_tier_qty_data['request_qty_price']=$qty->getRequestQtyPrice();
					$quote_tier_qty_data['cost_price']=$qty->getCostPrice();
					$quote_tier_qty_data['is_default']=$qty->getIsDefault();
					
				$quote_tier_qty=$this->_quoteRequestFactory->create();
				$quote_tier_qty->setData($quote_tier_qty_data);
				$quote_tier_qty->save();
				}
				
			}
		$quote->save();
		$email_config=$this->quoteHelper->getEmailConfig();
		if($email_config['proposal_cancel'])
		{
			$this->emailHelper->sendProposalStatusMail($quote_id,'cancelled');
		}
			$this->messageManager->addSuccess(__('Quote has been edited succesfully')); 
				$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        		$resultRedirect->setUrl($this->_url->getUrl('quotation/quote'));
		}
				return $resultRedirect;	

    }
}
