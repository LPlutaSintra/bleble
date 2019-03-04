<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Api\AccountManagementInterface;

class customerLogin extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		 AccountManagementInterface $accountManagement
		
    ) {
       $this->accountManagement = $accountManagement;
        parent::__construct($context);
       
    }
    public function execute()
    {
		 $result = [];
		$params = $this->getRequest()->getParams();	
		$email=$params['email'];
   		$response=!$this->accountManagement->isEmailAvailable($email, null);
		$result['isEmailAvail']=$response;
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;

    }
}
