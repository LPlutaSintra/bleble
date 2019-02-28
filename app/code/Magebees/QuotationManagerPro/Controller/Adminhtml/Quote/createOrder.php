<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magebees\QuotationManagerPro\Model\Quote\Status;
class createOrder extends  \Magento\Backend\App\Action
{ 
	 public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		 \Magebees\QuotationManagerPro\Helper\Email $emailHelper,
		  \Magebees\QuotationManagerPro\Helper\Admin $backendHelper,
		 \Magento\Backend\Model\Session\Quote $session,
		 \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteSession,
		   \Magento\Store\Model\StoreManagerInterface $storeManager,
		   ProductRepositoryInterface $productRepository,
		   \Magento\Framework\Registry $coreRegistry,		
		 \Magento\Sales\Model\AdminOrder\Create $orderModel
    ) {
    
        parent::__construct($context);
		  $this->quoteHelper = $quoteHelper;
		  $this->emailHelper = $emailHelper;
		  $this->backendHelper = $backendHelper;
		 $this->_session = $session;
		 $this->_quoteSession = $quoteSession;
		 $this->_orderModel = $orderModel;
		  $this->_storeManager = $storeManager;
		    $this->productRepository = $productRepository;
		  $this->_coreRegistry = $coreRegistry;
		
		
      
    }
	protected function _getSession()
    {
        return $this->_session;
    }
	 protected function _getQuote()
    {
        return $this->_getSession()->getQuote();
    }
	  protected function _getOrderCreateModel()
    {
        return $this->_orderModel;
    }
    public function execute()
    {
		  $this->_getSession()->clearStorage();
		
        	if ($data = $this->getRequest()->getPost()) {
		
			try {
				
				$quote_id= $this->getRequest()->getParam('quote_id');				
				$quote = $this->quoteHelper->loadQuoteById($quote_id);	
				$billing_add_id=$quote->getbillAddressId();
				$shipping_add_id=$quote->getshipAddressId();
				/*if(!$shipping_add_id)
				{
					$this->messageManager->addNotice(__('Please set the shipping address before create order.')); 
					   $this->_redirect('quotation/quote/view', ['quote_id' => $quote_id, '_current' => true]);
					return;
				}*/
				$productId=$quote->getProductId();
				$customer_id = $quote->getCustomerId();
				$store_id = $quote->getStoreId();
				$this->_getSession()->setStoreId($store_id);
				$this->_getSession()->setCustomerId($customer_id);
				$order_quote = $this->_getSession()->getQuote();
				$order_quote->getId();
				
				
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
							$data['qty']=$item_qty;
							$this->_getOrderCreateModel()->addProduct($product,$data);

				}
				
		/**start set the shipping address in create order*/
				$avail_shipping=$this->quoteHelper->IsCustomShipAddressAvail($quote_id);
				$shipping_address=$this->_getOrderCreateModel()->getShippingAddress();
			$quote_shipping_method=$quote->getShippingMethod();	$street=explode("\n",$avail_shipping['street']);
				$shipping_address->setCity($avail_shipping['city'])
				->setFirstName($avail_shipping['firstname'])
				->setLastName($avail_shipping['lastname'])
				->setCountryId($avail_shipping['country_id'])
				->setPostcode($avail_shipping['postcode'])
				->setTelephone($avail_shipping['telephone'])
				->setRegion($avail_shipping['region'])
				->setStreet($street)
				->setRegionId($avail_shipping['region_id'])
				->setSameAsBilling(false)
				->setShippingMethod($quote_shipping_method)
->setCustomerAddressId($avail_shipping['customer_address_id'])
				->save();
		/**End set the shipping address in create order*/
				
				
		/**start set the billing address in create order*/
				
				$avail_billing=$this->quoteHelper->IsCustomBillAddressAvail($quote_id);								
				$billing_address=$this->_getOrderCreateModel()->getBillingAddress();
			$billing_address->setCity($avail_billing['city'])				->setFirstName($avail_billing['firstname'])
				->setLastName($avail_billing['lastname'])					->setTelephone($avail_billing['telephone'])
				->setCountryId($avail_billing['country_id'])
				->setPostcode($avail_billing['postcode'])
				->setRegion($avail_billing['region'])
				->setRegionId($avail_billing['region_id'])		
				->setStreet($street)
				->setSameAsBilling(false)
->setCustomerAddressId($avail_billing['customer_address_id'])
				->save();
		/**End set the billing address in create order*/
				
		$this->_getOrderCreateModel()->collectShippingRates();
		
				 $this->_getOrderCreateModel()->saveQuote();
				$this->_quoteSession->setProcessQuoteId($quote_id);
				$quote=$this->quoteHelper->loadQuoteById($quote_id);
				$quote->setStatus(Status::PROPOSAL_ACCEPTED);
				$quote->save();	
				$email_config=$this->quoteHelper->getEmailConfig();
				if($email_config['proposal_accept'])
				{
					$this->emailHelper->sendProposalStatusMail($quote_id,'accepted');
				}			
				
			$this->_redirect('sales/order_create/index');	
		
              
            }catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __($e->getMessage()));
				
            }
        }
		$this->quoteHelper->unsetErrorMsgArr();

        $this->_redirect('sales/order_create/index');
    }
	
	
}
