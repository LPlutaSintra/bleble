<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;
use Magento\Framework\Controller\ResultFactory;

class removeFile extends  \Magento\Backend\App\Action
{ 
	  public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		   \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		  \Magebees\QuotationManagerPro\Model\QuoteFilesFactory $quoteFilesFactory		 
    ) {    
        parent::__construct($context);
        $this->_quoteFilesFactory = $quoteFilesFactory;
		$this->quoteHelper = $quoteHelper;
		  
    }
    public function execute()
    {
        $result = [];
        	$params = $this->getRequest()->getParams();				
			if($params['id'])
			{
			$file_id=$params['id'];
			$quote_id=$params['quote_id'];
			$quotefiles=$this->_quoteFilesFactory->create()->load($file_id,'id');		
			$quotefiles->delete();
			}
			if($this->quoteHelper->getQuoteFiles($quote_id))
			{
			$attachment_count=count($this->quoteHelper->getQuoteFiles($quote_id));
			}
			else
			{
				$attachment_count=0;
			}
			$result['attachment_count']=$attachment_count;
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($result);
            return $resultJson;	

    }
}
