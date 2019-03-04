<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;
use Magento\Framework\Controller\ResultFactory;

class updateRequestInfo extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magebees\QuotationManagerPro\Model\QuoteItemFactory $quoteItemFactory		  
    ) {
    
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		 $this->_quoteItemFactory = $quoteItemFactory;  
		
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

	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_QuotationManagerPro::quotation');
    }
}
