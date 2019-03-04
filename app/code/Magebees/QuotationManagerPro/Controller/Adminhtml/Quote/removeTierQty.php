<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;
use Magento\Framework\Controller\ResultFactory;

class removeTierQty extends  \Magento\Backend\App\Action
{ 
	  public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magebees\QuotationManagerPro\Model\QuoteRequestFactory $quoteRequestFactory, 		 
		 \Magebees\QuotationManagerPro\Model\ResourceModel\QuoteRequest\CollectionFactory $quoteRequestCollFactory
    ) {
    
        parent::__construct($context);
        $this->_quoteRequestFactory = $quoteRequestFactory;
		   $this->_quoteRequestCollFactory = $quoteRequestCollFactory;
    }
    public function execute()
    {
        $result = [];
        	$params = $this->getRequest()->getParams();		
		
			if($params['request_id'])
			{
			$request_id=$params['request_id'];
			$quoterequests=$this->_quoteRequestFactory->create()->load($request_id,'request_id');		
			$quoterequests->delete();			
			}

    }
}
