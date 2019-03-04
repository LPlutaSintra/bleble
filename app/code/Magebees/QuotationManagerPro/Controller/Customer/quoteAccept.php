<?php
namespace Magebees\QuotationManagerPro\Controller\Customer;
use Magento\Framework\Controller\ResultFactory;
use Magebees\QuotationManagerPro\Model\Quote\Status;
use Magento\Catalog\Api\ProductRepositoryInterface;

class quoteAccept extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,       
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,  
		\Magebees\QuotationManagerPro\Helper\Email $emailHelper  
    ) {
    
        parent::__construct($context);       
		   $this->quoteHelper = $quoteHelper;
		   $this->emailHelper = $emailHelper;
		  
		   
    }
    public function execute()
    {
		 $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$config=$this->quoteHelper->getConfig();
		$enable_expiration_time=$config['enable_expiration_time'];
		$post = $this->getRequest()->getParams();
		$quote_id=$post['quote_id'];
		$quote=$this->quoteHelper->loadQuoteById($quote_id);     
		$url=$this->quoteHelper->getQuoteViewUrl($quote_id);
        $resultRedirect->setUrl($url);
		$expiryDate=$quote->getExpiredAt();	
		$expiryDate=$this->quoteHelper->getExpiredDateFormat($expiryDate);
		$inc_id=$quote->getIncrementId();
		if(($enable_expiration_time)&&($expiryDate <= date('Y-m-d')))
		{		
		//	$quote->setStatus(Status::PROPOSAL_EXPIRED);
			//$quote->save();
			$this->messageManager->addNotice('Quotation # '.$inc_id.' had been Expired'); 	
		}
		else
		{		
		$quote->setStatus(Status::PROPOSAL_ACCEPTED);
		$quote->save();
		$email_config=$this->quoteHelper->getEmailConfig();
		if($email_config['proposal_accept'])
		{
			$this->emailHelper->sendProposalStatusMail($quote_id,'accepted');
		}
		}
		return $resultRedirect;
    }
}
