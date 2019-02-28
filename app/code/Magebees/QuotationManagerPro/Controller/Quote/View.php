<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magebees\QuotationManagerPro\Model\Quote\Status;

class View extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quotationHelper,		
		\Magento\Customer\Model\Session $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
		$this->quotationHelper = $quotationHelper;
		 $this->customerSession = $customerSession;
		
        parent::__construct($context);
    }
    public function execute()
    {		
		$resultPage = $this->resultPageFactory->create();
		$config=$this->quotationHelper->getConfig();
		$enable_expiration_time=$config['enable_expiration_time'];
		$quote_id=$this->getRequest()->getParam('quote_id');
		 $current_quote=$this->quotationHelper->loadQuoteById($quote_id);
		
		
			
			$expiryDate=$current_quote->getExpiredAt();				
			$expiryDate = $this->quotationHelper->getExpiredDateFormat($expiryDate);
			$inc_id=$current_quote->getIncrementId();
			 if(($enable_expiration_time)&&($expiryDate <= date('Y-m-d')))
			 {
				 	$this->messageManager->addNotice('Quotation # '.$inc_id.' had been Expired'); 	
				// $current_quote->setStatus(Status::PROPOSAL_EXPIRED);
				 //$current_quote->save();
			 }
			
		
		  if (!$this->customerSession->isLoggedIn()) {			  
				  $url=$this->quotationHelper->getQuoteViewLoginUrl($quote_id);
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);    
		$resultRedirect->setUrl($url);
			  return $resultRedirect;
        }
      
		$_quotes=$this->quotationHelper->getQuoteDataById($quote_id);
		foreach ($_quotes as $_quote): 
		$increment_id=$_quote->getIncrementId();
		$status=$_quote->getStatus();
		endforeach; 
		if(isset($increment_id))
		{
		$quote_cust_id=$_quote->getCustomerId();
		$customer_id=$this->customerSession->getCustomerId();
		if($quote_cust_id!=$customer_id)
		{
			$this->messageManager->addError(__('Not allow to access this Quotation'));
			return $this->_redirect('quotation/customer/quotehistory');
		}

        $resultPage->getConfig()->getTitle()->set(__('#'.$increment_id));
		   $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('quotation/customer/quotehistory');
        }
		if($status==10):
			if((!$enable_expiration_time)&&($expiryDate >= date('Y-m-d')))
			 {
		$this->messageManager->addWarning(__('Quote Request in Process, Please Wait for Price Proposal'));
			 }
		endif;
		}
		else
		{
			$this->messageManager->addError(__('Quotation does not exist'));
			 return $this->_redirect('quotation/customer/quotehistory');
		}
        return $resultPage;
    }

    
}
