<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;

class ajaxLogin extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
         \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		 AccountManagementInterface $accountManagement,
		  \Magento\Customer\Model\Session $customerSession
		
    ) {
       $this->accountManagement = $accountManagement;
		   $this->customerSession = $customerSession;
		     $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
       
    }
    public function execute()
    {
		 $response = [];
		$params = $this->getRequest()->getParams();	
		$email=$params['email'];
		$password=$params['password'];
		try
		{
   	 	$customer = $this->accountManagement->authenticate(
               $email,
               $password
            );
			 $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->customerSession->regenerateId();
		}
		 catch (EmailNotConfirmedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
			 $this->messageManager->addError($this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage()));
        } catch (InvalidEmailOrPasswordException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
			  $this->messageManager->addError($this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage()));
        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
			  $this->messageManager->addError($this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage()));
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => __('Invalid login or password.')
            ];
			  $this->messageManager->addError($this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml('Invalid login or password.'));
        }
		
		$resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
		

    }
}
