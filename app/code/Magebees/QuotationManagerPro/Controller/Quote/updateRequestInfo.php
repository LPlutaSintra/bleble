<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\Controller\ResultFactory;

class updateRequestInfo extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magebees\QuotationManagerPro\Model\QuoteItemFactory $quoteItemFactory	
		    
    ) {  $this->_quoteItemFactory = $quoteItemFactory;  
		    parent::__construct($context);
    }
    public function execute()
    {
        $result = [];        
        $params = $this->getRequest()->getParams();			
		$item_id=$params['item_id'];
		$request_info=$params['request_info'];
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);		
		$quoteitem=$this->_quoteItemFactory->create()->load($item_id,'id');
		$quoteitem->setRequestInfo($request_info);
		$quoteitem->save();          
		$resultJson->setData($result);
		return $resultJson;
	}    
}
