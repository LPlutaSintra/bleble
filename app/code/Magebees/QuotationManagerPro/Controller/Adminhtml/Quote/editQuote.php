<?php

namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;
use Magento\Framework\Controller\ResultFactory;
use Magebees\QuotationManagerPro\Model\Quote\Status;

class editQuote extends  \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
		\Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory,
		\Magebees\QuotationManagerPro\Model\QuoteItemFactory $quoteItemFactory,
		\Magebees\QuotationManagerPro\Model\QuoteRequestFactory $quoteRequestFactory,
		\Magebees\QuotationManagerPro\Model\Quote\Item\OptionFactory $quoteItemOptionFactory,
		\Magebees\QuotationManagerPro\Model\QuoteCustomerFactory $quoteCustomerFactory,
		\Magebees\QuotationManagerPro\Model\QuoteAddressFactory $quoteAddressFactory,
		\Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quotesession,
		\Magento\Config\Model\ResourceModel\Config $resourceConfig,
		 \Magento\Framework\Stdlib\DateTime\DateTime $datetime,	\Magebees\QuotationManagerPro\Helper\Email $emailHelper,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper		
    ) {
    
        parent::__construct($context);       
		  $this->quoteHelper = $quoteHelper;	
		 $this->quoteFactory = $quoteFactory;
		 $this->_quoteItemFactory = $quoteItemFactory;
		  $this->_quoteRequestFactory = $quoteRequestFactory;
		  $this->_quoteItemOptionFactory = $quoteItemOptionFactory;
		  $this->_quoteCustomerFactory = $quoteCustomerFactory;
		  $this->_quoteAddressFactory = $quoteAddressFactory;
		 $this->quotesession = $quotesession;
		 $this->_resourceConfig = $resourceConfig;
		$this->datetime = $datetime;
		 $this->emailHelper = $emailHelper;
		    
    }
	 public function execute()
    {
			$quote_id= $this->getRequest()->getParam('quote_id');				
			$quote = $this->quoteHelper->loadQuoteById($quote_id);	
			$config=$this->quoteHelper->getConfig();
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
			$datetime = $this->datetime->gmtDate();
		$expiration_time=$config['expiration_time'];
			$expirydate = strtotime($datetime);
			$expirydate = strtotime("+".$expiration_time."day", $expirydate);
			$expirydate=date('Y-m-d', $expirydate);	
			$quoteNoConfig=$this->quoteHelper->getQuoteNumberConfig();	  		
			 $quotePrefix=$quoteNoConfig['quote_prefix'];
			 $current_quote_id=$quoteNoConfig['current_quote'];
			 $incrementQuote=$quoteNoConfig['increment_quote'];
			 $increment_id=$current_quote_id+$incrementQuote;
			$quote_inc_id=$quotePrefix." ".$increment_id;
				$inc_id_exist=$this->quoteHelper->checkIncIdExist($quote_inc_id);
				if(!empty($inc_id_exist))
				{
					$quote_inc_id=$quote_inc_id.'-1';
				}
			$quote->setStatus(Status::PROPOSAL_CANCELLED);
			$customerId=$quote->getCustomerId();
			$storeId=$quote->getStoreId();
		 $new_quote = $this->quoteFactory->create();	
		 $new_quote->setCustomerId($customerId);
		 $new_quote->setStoreId($storeId);
		 $new_quote->setStatus(Status::STARTING);	
		 $new_quote->setCurrencyCode($quote->getCurrencyCode());
		 $new_quote->setBaseCurrencyCode($quote->getBaseCurrencyCode());
		 $new_quote->setAssignTo($assign_to);
		$new_quote->setCreatedAt($datetime);
		$new_quote->setExpiredAt($expirydate);
		$new_quote->setIncrementId($quote_inc_id);
		$new_quote->setIsActive(false);					
         
		  $this->_resourceConfig->saveConfig(
                    'quotation/quotationsetting/current_quote',
                    $increment_id,
                    'default',
                    0
                );
		
			$items=$this->quoteHelper->getItemsByQuoteId($quote_id);
		if(count($items))
		{
			$new_quote->setIsBackend(true);	
		}
		else
		{
			$new_quote->setIsBackend(false);	
		}
		
		 $new_quote->save();
		 $this->quotesession->setQuoteId($new_quote->getId());
			$new_quote_id=$new_quote->getId();
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
				$item_data['price_incl_tax']=$item->getPriceInclTax();
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
					$quote_tier_qty_data['req_qty_price_incl_tax']=$qty->getReqQtyPriceInclTax();
					$quote_tier_qty_data['cost_price']=$qty->getCostPrice();
					$quote_tier_qty_data['is_default']=$qty->getIsDefault();
					
				$quote_tier_qty=$this->_quoteRequestFactory->create();
				$quote_tier_qty->setData($quote_tier_qty_data);
				$quote_tier_qty->save();
				}
				
			}
		
		/**manage customer detail for new quote*/
		$customer=$this->quoteHelper->getQuoteCustomer($quote_id);
				
					$customer_data=[];					
					$customer_data['quote_id']=$new_quote_id;				if(isset($customer['customer_id']))
					{$customer_data['customer_id']=$customer['customer_id'];}
				$customer_data['email']=$customer['email'];
				$customer_data['fname']=$customer['fname'];
				$customer_data['lname']=$customer['lname'];
					
			$quote_customer_data=$this->_quoteCustomerFactory->create();
				$quote_customer_data->setData($customer_data);
				$quote_customer_data->save();		
		/**/
		
		/*manage customer address for new quote*/
	$ship_address=$this->quoteHelper->IsCustomShipAddressAvail($quote_id);
		if($ship_address)
		{
		$ship_address_data=[];						$ship_address_data['quote_id']=$new_quote_id;				$ship_address_data['customer_id']=$ship_address['customer_id'];
				$ship_address_data['save_in_address_book']=$ship_address['save_in_address_book'];				$ship_address_data['customer_address_id']=$ship_address['customer_address_id'];				$ship_address_data['address_type']=$ship_address['address_type'];				$ship_address_data['email']=$ship_address['email'];			$ship_address_data['firstname']=$ship_address['firstname'];
				$ship_address_data['middlename']=$ship_address['middlename'];				$ship_address_data['lastname']=$ship_address['lastname'];			$ship_address_data['street']=$ship_address['street'];
				$ship_address_data['city']=$ship_address['city'];				$ship_address_data['region']=$ship_address['region'];			$ship_address_data['region_id']=$ship_address['region_id'];
				$ship_address_data['country_id']=$ship_address['country_id'];				$ship_address_data['telephone']=$ship_address['telephone'];			$ship_address_data['same_as_billing']=$ship_address['same_as_billing'];
			$ship_address_data['is_default_address']=$ship_address['is_default_address'];
				$quote_ship_address_data=$this->_quoteAddressFactory->create();
			$quote_ship_address_data->setData($ship_address_data);
				$quote_ship_address_data->save();
		}
	$bill_address=$this->quoteHelper->IsCustomBillAddressAvail($quote_id);
		if($bill_address)
		{
			$bill_address_data=[];						$bill_address_data['quote_id']=$new_quote_id;				$bill_address_data['customer_id']=$bill_address['customer_id'];
				$bill_address_data['save_in_address_book']=$bill_address['save_in_address_book'];				$bill_address_data['customer_address_id']=$bill_address['customer_address_id'];				$bill_address_data['address_type']=$bill_address['address_type'];				$bill_address_data['email']=$bill_address['email'];			$bill_address_data['firstname']=$bill_address['firstname'];
				$bill_address_data['middlename']=$bill_address['middlename'];				$bill_address_data['lastname']=$bill_address['lastname'];			$bill_address_data['street']=$bill_address['street'];
				$bill_address_data['city']=$bill_address['city'];				$bill_address_data['region']=$bill_address['region'];			$bill_address_data['region_id']=$bill_address['region_id'];
				$bill_address_data['country_id']=$bill_address['country_id'];				$bill_address_data['telephone']=$bill_address['telephone'];			$bill_address_data['same_as_billing']=$bill_address['same_as_billing'];
			$bill_address_data['is_default_address']=$bill_address['is_default_address'];
				$quote_bill_address_data=$this->_quoteAddressFactory->create();
			$quote_bill_address_data->setData($bill_address_data);
				$quote_bill_address_data->save();
		}
		/**/
		
		$quote->save();
		$email_config=$this->quoteHelper->getEmailConfig();
		if($email_config['proposal_cancel'])
		{
			$new_quote=$this->quoteHelper->loadQuoteById($new_quote_id);
			$this->emailHelper->sendProposalStatusMail($quote_id,'cancelled',$new_quote->getIncrementId(),$new_quote_id,true);
		}
			
			$this->messageManager->addSuccess(__('Quote has been edited succesfully.')); 
				$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        		$resultRedirect->setUrl($this->_url->getUrl('quotation/quote/view',array('quote_id'=>$new_quote_id)));
				return $resultRedirect;	
				
	}
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_QuotationManagerPro::quotation');
    }
}
