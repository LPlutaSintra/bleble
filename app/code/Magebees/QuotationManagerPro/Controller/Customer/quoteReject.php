<?php
namespace Magebees\QuotationManagerPro\Controller\Customer;
use Magento\Framework\Controller\ResultFactory;
use Magebees\QuotationManagerPro\Model\Quote\Status;

class quoteReject extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magebees\QuotationManagerPro\Helper\Email $emailHelper,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper
    ) {    
        parent::__construct($context);       
	   $this->emailHelper = $emailHelper;
	   $this->quoteHelper = $quoteHelper;			   
    }
    public function execute()
    {
		$post = $this->getRequest()->getPost();
		$quote_id=$post['currentQuoteId'];
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$config=$this->quoteHelper->getConfig();
		$enable_expiration_time=$config['enable_expiration_time'];
		 $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$expiryDate=$quote->getExpiredAt();	
		$expiryDate = $this->quoteHelper->getExpiredDateFormat($expiryDate);
		$inc_id=$quote->getIncrementId();
		if(($enable_expiration_time)&&($expiryDate <= date('Y-m-d')))
		{
			 $resultRedirect->setUrl($this->quoteHelper->getQuoteViewUrl($quote_id));
			//$quote->setStatus(Status::PROPOSAL_EXPIRED);
			$this->messageManager->addNotice('Quotation # '.$inc_id.' had been Expired'); 
		}
		else
		{
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
		$quote->setStatus(Status::PROPOSAL_REJECTED);
		$email_config=$this->quoteHelper->getEmailConfig();
		if($email_config['proposal_reject'])
		{
		$this->emailHelper->sendProposalStatusMail($quote_id,'rejected');
		}
		$this->messageManager->addNotice('Quotation # '.$inc_id.' had been rejected'); 
		}
       
		$quote->save();
		
		  		
		return $resultRedirect;
    }
}
