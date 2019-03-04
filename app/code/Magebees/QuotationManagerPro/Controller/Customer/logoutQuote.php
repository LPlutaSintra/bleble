<?php
namespace Magebees\QuotationManagerPro\Controller\Customer;
use Magento\Framework\Controller\ResultFactory;


class logoutQuote extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Session\SessionManager $session,
       \Magento\Checkout\Model\Cart $cart,		
       \Magento\Checkout\Model\Session $checkoutSession,		
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper
    ) {
    
        parent::__construct($context);       
		   $this->session =$session;
		  $this->cart = $cart;
		  $this->checkoutSession = $checkoutSession;
		   $this->quoteHelper = $quoteHelper;
		  		   
    }
    public function execute()
    {
		 $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_url->getUrl('quotation/quote'));
      //  $resultRedirect->setUrl($this->_redirect->getRefererUrl());
		$this->session->setProcessingQuoteId(null);		
		$this->session->unsProcessingQuoteId();
		$this->cart->truncate();	
		$this->checkoutSession->clearQuote();
		$quote = $this->checkoutSession->getQuote();
		$quote->delete();        
		$this->messageManager->addNotice(__('Successfully log-out from the current quote confirmation mode.'));   		

    }
}
