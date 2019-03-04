<?php

namespace Magebees\QuotationManagerPro\Observer;

use Magento\Framework\Event\ObserverInterface;

class checkoutConfigure implements ObserverInterface
{
	
	 public function __construct(
      
        \Magento\Framework\App\ResponseFactory $responseFactory,
		   \Magento\Framework\Message\ManagerInterface $messageManager,
		  \Magento\Framework\Session\SessionManager $session,
		   \Magento\Framework\App\Response\RedirectInterface $redirect,
		 	 \Magento\Framework\App\ActionFlag $actionFlag,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		 \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->responseFactory = $responseFactory;
		 $this->messageManager = $messageManager;
		 $this->quoteHelper = $quoteHelper;
        $this->url = $url;
		  $this->_actionFlag = $actionFlag;
		   $this->redirect = $redirect;
		   $this->jsonHelper = $jsonHelper;
		  $this->session =$session;
    }

	   public function execute(\Magento\Framework\Event\Observer $observer) {
		 
		   $controller = $observer->getControllerAction();		
		   $frontend_setting=$this->quoteHelper->getFrontendConfig();
		   $isLockProposal=$frontend_setting['lock_proposal'];
		   if(($isLockProposal)&&($this->session->getProcessingQuoteId()))
		   {
           $quoteLogoutUrl = $this->url->getUrl('quotation/customer/logoutQuote');
		   $redirectUrl = $this->redirect->getRedirectUrl();
			
			   $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
			    $opcmessage = __("Action is blocked due to quotation proposal is locked.To update item in the Shopping cart <a class=logout_quote href='%1'>log out</a> from Quotation confirmation mode.", $quoteLogoutUrl);
			   if($controller->getRequest()->getActionName()=='add')
			   {				  
			   $result['html_popup']=$opcmessage;
			   $result['messages']=$opcmessage;
			    return $controller->getResponse()->representJson($this->jsonHelper->jsonEncode($result)); 
			   }
			   if($controller->getRequest()->getActionName()=='delete')
			   {
				   if($controller->getRequest()->isAjax())
				   {
					     $result['html_popup']=$opcmessage;
			   $result['messages']=$opcmessage;
			    return $controller->getResponse()->representJson($this->jsonHelper->jsonEncode($result)        ); 
				   }

			   }

			    $this->messageManager->addError(
                __('Action is blocked due to quotation proposal is locked')
            );
			   $message = __("To update item in the Shopping cart <a class=logout_quote href='%1'>log out</a> from Quotation confirmation mode.", $quoteLogoutUrl);
            $this->messageManager->addNotice($message);
			  
     //   $this->responseFactory->create()->setRedirect($redirectUrl)->sendResponse();
 	
			   
          //  $this->redirect->redirect($controller->getResponse(), 'checkout/cart/index');
           $this->redirect->redirect($controller->getResponse(), $redirectUrl);
		   }
        return $this;
        }
}
