<?php
namespace Magebees\QuotationManagerPro\Controller\Customer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class quoteHistory extends \Magento\Framework\App\Action\Action
{ 
	
    protected $resultPageFactory;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		\Magento\Customer\Model\Session $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
		 $this->customerSession = $customerSession;
        parent::__construct($context);
    }	
    public function execute()
    {	
		$resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Manage Quotations'));    
		  if (!$this->customerSession->isLoggedIn()) {			
			  $this->_redirect('customer/account/'); 
			  $this->messageManager->addSuccess($this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml('You can check the status of your quote by logging into your account.'));
			   
        }
        return $resultPage;
    }
}
