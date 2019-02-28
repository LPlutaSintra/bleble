<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\Controller\ResultFactory;
use Magebees\QuotationManagerPro\Model\Quote\Status;

class editQtyProposal extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magebees\QuotationManagerPro\Model\QuoteRequestFactory $quoteRequestFactory, 		 
		 \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteRequest\CollectionFactory $quoteRequestCollFactory,
		     \Magebees\QuotationManagerPro\Model\CustomerQuote $quote,
		   \Magebees\QuotationManagerPro\Helper\Admin $backendHelper,
		   \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		   \Magento\Checkout\Helper\Data $checkoutHelper
    ) {
    
        parent::__construct($context);
        $this->_quoteRequestFactory = $quoteRequestFactory;
		   $this->quote = $quote;
		  $this->checkoutHelper = $checkoutHelper;
		   $this->backendHelper = $backendHelper;
		   $this->quoteHelper = $quoteHelper;
		   $this->_quoteRequestCollFactory = $quoteRequestCollFactory;
    }
    public function execute()
    {
        $result = [];
        	$params = $this->getRequest()->getParams();		
		$config=$this->quoteHelper->getConfig();
		$enable_expiration_time=$config['enable_expiration_time'];
			if($params['request_id'])
			{
				$quote_id=$params['quote_id'];
				$current_quote=$this->quoteHelper->loadQuoteById($quote_id);
				$expiryDate=$current_quote->getExpiredAt();	
		$expiryDate = $this->quoteHelper->getExpiredDateFormat($expiryDate);
				$inc_id=$current_quote->getIncrementId();
				if(($enable_expiration_time)&&($expiryDate <= date('Y-m-d')))
		
				{
					//$current_quote->setStatus(Status::PROPOSAL_EXPIRED);
					//$current_quote->save();
			$this->messageManager->addNotice('Quotation # '.$inc_id.' had been Expired'); 
					$result['error_msg'] = 'Quotation # '.$inc_id.' had been Expired';
				}
				else
				{
			$request_id=$params['request_id'];
			$qty=$params['qty'];
			
			$item_id=$params['item_id'];
			$base_price=$params['base_price'];
			$quote_request = $this->_quoteRequestCollFactory->create();           
			$quote_request->addFieldToFilter('quote_id',$quote_id);
			$quote_request->addFieldToFilter('item_id',$item_id);	
				$items=$this->quoteHelper->getItemsByQuoteId($quote_id);
				foreach($items as $item)
				{
					if($item->getId()==$item_id)
					{
						$quoteitem=$item;
					}
				}
				
			//	$quoteitem = $this->quote->getQuote()->getItemById($item_id);			
				$quoteitem->setQty($qty);
					if ($quoteitem->getHasError()) {
                 $this->messageManager->addError($this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($quoteitem->getMessage()));
				$result['error_msg'] = $quoteitem->getMessage();
                }
				else
				{
					$quoteitem->save();
					foreach($quote_request as $request)
				{
					$request->setIsDefault(false)->save();
				}
					$quoterequests=$this->_quoteRequestFactory->create()->load($request_id,'request_id');		
			$quoterequests->setIsDefault(true)->save();
				}
				}
				 $total_data=$this->backendHelper->getDefaultTotalData($quote_id);
				 $total_orig_price=$total_data['orig_price'];
			$total_cost_price=$total_data['cost_price'];
			$total_row_price=$total_data['row_total'];
				$converted_total_orig_price=$this->quoteHelper->getConvertedPrice($total_orig_price);
				$converted_total_row_price=$this->quoteHelper->getConvertedPrice($total_row_price);
				
				$result['total_orig_price'] = $this->quoteHelper->getFormatedPrice($converted_total_orig_price);	 
			 $result['total_row_price'] = $this->quoteHelper->getFormatedPrice($converted_total_row_price);
			$adjustment_quote=$total_row_price-$total_orig_price;
$converted_adjustment_quote=$this->quoteHelper->getConvertedPrice($adjustment_quote);
			 $result['adjust_quote'] = $this->quoteHelper->getFormatedPrice($converted_adjustment_quote);
			 $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
				$resultJson->setData($result);
           		return $resultJson;		
				}
    }
}
