<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;
use Magento\Framework\Controller\ResultFactory;

class removeQuoteItem extends  \Magento\Backend\App\Action
{ 
	 
	  public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magebees\QuotationManagerPro\Model\QuoteItemFactory $quoteItemFactory,
		  \Magebees\QuotationManagerPro\Helper\Admin $backendHelper,
		  \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,		  
		  \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quotesession
    ) {
    
        parent::__construct($context);
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->resultPageFactory = $resultPageFactory;
		  $this->quotesession = $quotesession;
		  $this->backendHelper = $backendHelper;
		  $this->quoteHelper = $quoteHelper;
		  
    }
	
    public function execute()
    {
        $result = [];
        	$params = $this->getRequest()->getParams();		
		$quoteId = $this->getRequest()->getParam('quote_id');
		$this->quotesession->setQuoteId($quoteId);
			if($params['item_id'])
			{
			$item_id=$params['item_id'];
			$quote_item=$this->_quoteItemFactory->create()->load($item_id,'id');		
			$quote_item->delete();
				
			/**Start for set the flag when quote is empty and will not be display in frontend **/

			$item_count=count($this->backendHelper->getItemsCollection($quoteId));
				if($item_count<=0)
				{
					$quote=$this->quoteHelper->loadQuoteById($quoteId);
					$quote->setIsBackend(false);	
					$quote->save();
				}
			/**End for set the flag when quote is empty and will not be display in frontend **/
				
				
				
				$quote=$this->quoteHelper->loadQuoteById($quoteId);
				$quote_status=$quote->getStatus();
			  	$resultPage = $this->resultPageFactory->create();
				$layoutblk=$resultPage->addHandle('quotation_quote_view')->getLayout();
				$items= $layoutblk->getBlock('quote_items')->toHtml();
				$totals= $layoutblk->getBlock('quote_totals')->toHtml();
				$shipping_address= $layoutblk->getBlock('shipping_address')->toHtml();
				$shipping_method= $layoutblk->getBlock('shipping_method')->toHtml();
				
				//*Start Check if quote has error and set the status***/
				$outofStockStatus=$this->quoteHelper->getOutOfStockStatus();
				$startStatus=$this->quoteHelper->getStartingStatus();
			//	print_r(count($this->quoteHelper->getErrorMsgArr()));
			//	print_r($this->quoteHelper->getErrorMsgArr());
				 if(!($this->quoteHelper->getErrorMsgArr()))
				 {
					 if($quote_status==$outofStockStatus){
						$quote=$this->quoteHelper->loadQuoteById($quoteId);
						$quote->setStatus($startStatus);
						$quote->save();
					 }
				 }			
				
				//*End Check if quote has error and set the status**/
				
				$quoteStatus=$quote->getStatus();
				 $result['items'] = $items;
				 $result['totals'] = $totals;
				 $result['quote_status'] = $quoteStatus;
				 $result['shipping_address'] = $shipping_address;
				 $result['shipping_method'] = $shipping_method;
				 $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
				 $resultJson->setData($result);
				return $resultJson;
			
				}

    }
}
