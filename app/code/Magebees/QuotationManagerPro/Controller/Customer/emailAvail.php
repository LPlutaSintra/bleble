<?php
namespace Magebees\QuotationManagerPro\Controller\Customer;
use Magento\Framework\Controller\ResultFactory;

class emailAvail extends \Magento\Framework\App\Action\Action
{ 
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Session\SessionManager $session,      	
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper
    ) {
    
        parent::__construct($context);       
		   $this->session =$session;		
		   $this->quoteHelper = $quoteHelper;
		  		   
    }
    public function execute()
    {
		$result = [];
			 $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$params = $this->getRequest()->getParams();
		$email=$params['email'];
		 $avail=$this->quoteHelper->isEmailAvailable($email);	
		$result['avail']=$avail;
			$resultJson->setData($result);
           		return $resultJson;	

    }
}