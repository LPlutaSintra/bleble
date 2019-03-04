<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;
use Magento\Framework\Controller\ResultFactory;

class quoteStatus extends  \Magento\Backend\App\Action
{ 
	 protected $_publicActions = ['quoteStatus'];
	  public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,		
		  \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper	  
		  
    ) {
    
        parent::__construct($context);       
        $this->resultPageFactory = $resultPageFactory;			  
		$this->quoteHelper = $quoteHelper;
		  
    }
	
    public function execute()
    {
		
			$result = [];
			$params = $this->getRequest()->getParams();		
			$quoteId = $this->getRequest()->getParam('quote_id');
			$quote=$this->quoteHelper->loadQuoteById($quoteId);
			$quote_status=$quote->getStatus();
			$resultPage = $this->resultPageFactory->create();
				$layoutblk=$resultPage->addHandle('quotation_quote_view')->getLayout();
				$items= $layoutblk->getBlock('quote_items')->toHtml();
				$totals= $layoutblk->getBlock('quote_totals')->toHtml();
				
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
				else
				{
						$quote=$this->quoteHelper->loadQuoteById($quoteId);
						$quote->setStatus($outofStockStatus);
						$quote->save();
				}
			
				$quoteStatus=$quote->getStatus();				
				 $result['quote_status'] = $quoteStatus;
				 $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
				 $resultJson->setData($result);
				return $resultJson;
    }
}
