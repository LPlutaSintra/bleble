<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\Controller\ResultFactory;

class saveTierQty extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magebees\QuotationManagerPro\Model\QuoteItemFactory $quoteItemFactory,
		  \Magebees\QuotationManagerPro\Model\QuoteRequestFactory $quoteRequestFactory, 		 
		 \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteRequest\CollectionFactory $quoteRequestCollFactory,		  
		     \Magebees\QuotationManagerPro\Model\CustomerQuote $quote,
		  \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper
    ) {      
		   $this->_quoteItemFactory = $quoteItemFactory;
        	$this->_quoteRequestFactory = $quoteRequestFactory;
       		$this->_quoteRequestCollFactory = $quoteRequestCollFactory;
		   $this->quote = $quote;
		   $this->quoteHelper = $quoteHelper;		 	  
		    parent::__construct($context);
    }
    public function execute()
    {
        $result = [];
        $req_qty = [];
        $params = $this->getRequest()->getParams();		
		$qty=$params['qty'];
		$product_id=$params['product_id'];
		$quote_id=$params['quote_id'];
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
			 $this->messageManager->addError($this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml('Requested Quantity already available For This Product.'));
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
				if($params['isDefault'])
				{
				$item_id=$quoterequests->getItemId();			
				$quoteitem = $this->quote->getQuote()->getItemById($item_id);			
				$quoteitem->setQty($qty);
				if ($quoteitem->getHasError()) {
                 $this->messageManager->addError($this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($quoteitem->getMessage()));
				$result['error_msg'] = $quoteitem->getMessage();
                }
				else
				{
					$quoteitem->save();
					$quoterequests->setRequestQty($qty);
					$quoterequests->save();
				}				
				}
				else
				{
					$quoterequests->setRequestQty($qty);
					$quoterequests->save();
				}				
			}
			else
			{	
				$product=$this->quoteHelper->loadProduct($params['product_id']);
			$price_incl_tax=$this->quoteHelper->getProductPriceInclTax($product,$params['price'],true,$currentQuote->getStoreId());
			$quoterequests=$this->_quoteRequestFactory->create();
			$quoterequests->setQuoteId($params['quote_id']);
			$quoterequests->setItemId($params['qpid']);
			$quoterequests->setProductId($params['product_id']);
			$quoterequests->setRequestQty($qty);
			$quoterequests->setRequestQtyPrice($params['price']);	
			$quoterequests->setReqQtyPriceInclTax($price_incl_tax);
			
			$quoterequests->setIsDefault(0);
			$quoterequests->save();
			}
			$last_req_id=$quoterequests->getRequestId();
			 $result['request_id'] = $last_req_id;
			 $result['tier_qty'] = $quoterequests->getRequestQty();          
            $resultJson->setData($result);
            return $resultJson;
		}
			
		}

    }
}
