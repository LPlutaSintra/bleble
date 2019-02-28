<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\Controller\ResultFactory;

class removeTierQty extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
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
			 $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        	$params = $this->getRequest()->getParams();				
			if($params['request_id'])
			{
			$request_id=$params['request_id'];
			$quoterequests=$this->_quoteRequestFactory->create()->load($request_id,'request_id');		
			$quoterequests->delete();		
			}
			$resultJson->setData($result);
           		return $resultJson;	
    }
}
