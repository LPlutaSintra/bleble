<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;
use Magento\Framework\Controller\ResultFactory;
use \Magebees\QuotationManagerPro\Model\Quote\Status;

class saveTierQty extends  \Magento\Backend\App\Action
{ 
	 protected $_items;
	  public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magebees\QuotationManagerPro\Model\QuoteItemFactory $quoteItemFactory,
		  \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem\CollectionFactory $quoteItemCollFactory,
		  \Magebees\QuotationManagerPro\Model\QuoteRequestFactory $quoteRequestFactory, 		 
		 \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteRequest\CollectionFactory $quoteRequestCollFactory,
		     \Magebees\QuotationManagerPro\Model\CustomerQuote $quote,
		  \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		  \Magebees\QuotationManagerPro\Helper\Admin $backendHelper,
		  \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteBackendSession,		
		  \Magento\Checkout\Helper\Data $checkoutHelper,
		    \Magento\Framework\Registry $coreRegistry
    ) {
    
		parent::__construct($context);
		$this->_quoteItemFactory = $quoteItemFactory;
		$this->_quoteItemCollFactory = $quoteItemCollFactory;
		$this->_quoteRequestFactory = $quoteRequestFactory;
		   $this->_quoteRequestCollFactory = $quoteRequestCollFactory;
		   $this->quote = $quote;
		   $this->quoteHelper = $quoteHelper;
		   $this->backendHelper = $backendHelper;
		   $this->quoteBackendSession = $quoteBackendSession;
		   $this->checkoutHelper = $checkoutHelper;
		   $this->_coreRegistry = $coreRegistry;		
    }
    public function execute()
    {
        $result = [];
        $req_qty = [];
        $params = $this->getRequest()->getParams();			
		$qty=$params['qty'];
		$product_id=$params['product_id'];
		$quote_id=$params['quote_id'];	
		$currentQuote=$this->quoteHelper->loadQuoteById($quote_id);
		$old_status=$currentQuote->getStatus();
		/* Start set processing stage when tier quantity update in process*/
		$currentQuote->setStatus(Status::PROCESSING);
		$currentQuote->save();
		/* End set processing stage when tier quantity update in process*/
		
		$this->quoteBackendSession->setQuoteId($quote_id);
		$base_price=$params['base_price'];	
		$request_id=$params['request_id'];
		$qpid=$params['qpid'];
		 $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$quote_request=$this->quoteHelper->getDynamicQuoteQty($qpid,$quote_id,$product_id,$request_id);
		foreach($quote_request as $request)
		{
			$req_qty[]=$request['request_qty'];
		}
		
	
		if($qty)
		{
			if (in_array($qty,$req_qty))
			{			
			 $this->messageManager->addError(__('Requested Quantity already available For This Product.'));
				$result['error_qty']='Requested Quantity already available For This Product.';	
				if($params['request_id'])
				{
					$request_id=$params['request_id'];
					$quoterequests=$this->_quoteRequestFactory->create()->load($request_id,'request_id');
					$result['tier_qty'] = $quoterequests->getRequestQty();
				}
				
				$resultJson->setData($result);
           		return $resultJson;
				
			}
			else
			{
				  	
			
			if($params['request_id'])
			{
				
				$request_id=$params['request_id'];
				$quoterequests=$this->_quoteRequestFactory->create()->load($request_id,'request_id');
				$item_id=$quoterequests->getItemId();		
				$quoteitem = $this->quoteBackendSession->getQuote()->getItemById($item_id);
				$price_incl_tax=$this->quoteHelper->getProductPriceInclTax($quoteitem->getProduct(),$params['base_price'],true);
				if(isset($params['cost_price']))
				{
					$quoterequests->setCostPrice($params['cost_price']);
				}
				$quoterequests->setRequestQtyPrice($params['base_price']);	
				$quoterequests->setReqQtyPriceInclTax($price_incl_tax);	
				$quoterequests->setIsDefault($params['isDefault']);
							
				$quoteitem->setQty($qty);			
				if ($quoteitem->getHasError()) {
				 $this->messageManager->addError(__($quoteitem->getMessage()));
				$result['error_msg'] = $quoteitem->getMessage();
				}
				else
				{
					if($params['isDefault']==1)
					{
						$quoteitem->save();
					}
					$quoterequests->setRequestQty($qty);
					$quoterequests->save();					
				}				
			}
			else
			{
				$quoteitem = $this->quoteBackendSession->getQuote()->getItemById($params['qpid']);	
					$price_incl_tax=$this->quoteHelper->getProductPriceInclTax($quoteitem->getProduct(),$params['base_price'],true);
			$quoterequests=$this->_quoteRequestFactory->create();
			$quoterequests->setQuoteId($params['quote_id']);
			$quoterequests->setItemId($params['qpid']);
			$quoterequests->setProductId($params['product_id']);
			$quoterequests->setRequestQty($qty);
			$quoterequests->setRequestQtyPrice($params['base_price']);	
			$quoterequests->setReqQtyPriceInclTax($price_incl_tax);
			if(isset($params['cost_price']))
			{$quoterequests->setCostPrice($params['cost_price']);
			}
			else
			{
				$product_id=$params['product_id'];
				$product=$this->quoteHelper->loadProduct($product_id);
				if($product->getCost())
				{
					$quoterequests->setCostPrice($product->getCost());
				}
			}
			$quoterequests->setIsDefault($params['isDefault']);
			$quoterequests->save();
			}
			$request_id=$quoterequests->getRequestId();
				if($params['isDefault']==1)
				{
						if (!$quoteitem->getHasError()) {
				$requestedqties=$this->quoteHelper->checkIsDefaultQty($qpid,$request_id);
						foreach($requestedqties as $key => $qty):					
						$requestqties = $this->_quoteRequestFactory->create()->load($qty['request_id'],'request_id');						
						$requestqties->setIsDefault(0);
						$requestqties->save();
					endforeach;
						}
				}
				/* Start reset old quote status when tier quantity update complete*/
				$currentQuote->setStatus($old_status);
				$currentQuote->save();
				/* End reset old quote status when tier quantity update complete*/		
				 $result['tier_qty'] = $quoterequests->getRequestQty();				
            $resultFactory= $this->_objectManager->create('\Magento\Framework\View\Result\PageFactory');
            $resultPage= $resultFactory->create();
            $itemblk = $resultPage->addHandle('quotation_quote_create_load_block_items')->getLayout();
            $totalblk = $resultPage->addHandle('quotation_quote_create_load_block_totals')->getLayout();
            $msgblk = $resultPage->addHandle('quotation_quote_create_load_block_message')->getLayout();
            $items= $itemblk->getBlock('items')->toHtml();
            $totals= $totalblk->getBlock('totals')->toHtml();
            $messages= $msgblk->getBlock('message')->toHtml();
			$result['items']=$items;
			$result['totals']=$totals;
			$result['messages']=$messages;
            $resultJson->setData($result);
            return $resultJson;
		}
			
		}

    }
	
}
