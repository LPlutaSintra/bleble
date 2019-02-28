<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magebees\QuotationManagerPro\Model\Quote\Status;

class quoteStatus extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
		\Magento\Framework\App\Action\Context $context,
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
				$items= $layoutblk->getBlock('quotation.quote.view')->toHtml();
				$totals= $layoutblk->getBlock('quote_actions')->toHtml();
				
				//*Start Check if quote has error and set the status on page load***/
				$outofStockStatus=$this->quoteHelper->getOutOfStockStatus();
				$startStatus=$this->quoteHelper->getStartingStatus();			
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
				$quoteStatus=$this->quoteHelper->getStatus($quote->getStatus());				
				 $result['quote_status'] = $quoteStatus;
				 $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
				 $resultJson->setData($result);
				return $resultJson;
	}
}
