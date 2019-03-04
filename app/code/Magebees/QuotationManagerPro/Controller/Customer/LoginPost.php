<?php
namespace Magebees\QuotationManagerPro\Controller\Customer;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\App\Config\ScopeConfigInterface;


class LoginPost extends \Magento\Customer\Controller\AbstractAccount
{
    
    protected $qcustomerAccountManagement;
    protected $qformKeyValidator;
    protected $accountRedirect;
    protected $qsession;
    private $qscopeConfig;
    private $qcookieMetadataFactory;
    private $qcookieMetadataManager;
    public function __construct(
        Context $context,
        Session $qcustomerSession,
        AccountManagementInterface $qcustomerAccountManagement,
        CustomerUrl $qcustomerHelperData,
        Validator $qformKeyValidator,
        AccountRedirect $accountRedirect
    ) {
        $this->qsession = $qcustomerSession;
        $this->qcustomerAccountManagement = $qcustomerAccountManagement;
        $this->customerUrl = $qcustomerHelperData;
        $this->qformKeyValidator = $qformKeyValidator;
        $this->accountRedirect = $accountRedirect;
        parent::__construct($context);
    }

    private function getScopeConfig()
    {
        if (!($this->qscopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->qscopeConfig;
        }
    }
   
    private function getQuoteCookieManager()
    {
        if (!$this->qcookieMetadataManager) {
            $this->qcookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->qcookieMetadataManager;
    }

    private function getCookiedataFactory()
    {
        if (!$this->qcookieMetadataFactory) {
            $this->qcookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->qcookieMetadataFactory;
    }

    public function execute()
    {
		
        if ($this->qsession->isLoggedIn() || !$this->qformKeyValidator->validate($this->getRequest())) {			
            /** @var \Magento\Framework\Controller\Result\Redirect $qresultRedirect */
            $qresultRedirect = $this->resultRedirectFactory->create();
            $qresultRedirect->setPath('*/*/');
            return $qresultRedirect;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->qcustomerAccountManagement->authenticate($login['username'], $login['password']);
                    $this->qsession->setCustomerDataAsLoggedIn($customer);
                    $this->qsession->regenerateId();
                    if ($this->getQuoteCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookiedataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getQuoteCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }
                    $qredirectUrl = $this->accountRedirect->getRedirectCookie();
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $qredirectUrl) {
                        $this->accountRedirect->clearRedirectCookie();
                        $qresultRedirect = $this->resultRedirectFactory->create();
                        // URL is checked to be internal in $this->_redirect->success()
                        $qresultRedirect->setUrl($this->_redirect->success($qredirectUrl));
                        return $qresultRedirect;
                    }
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                    $this->messageManager->addError($message);
                    $this->qsession->setUsername($login['username']);
                } catch (UserLockedException $e) {
                    $message = __(
                        'You did not sign in correctly or your account is temporarily disabled.'
                    );
                    $this->messageManager->addError($message);
                    $this->qsession->setUsername($login['username']);
                } catch (AuthenticationException $e) {
                    $message = __('You did not sign in correctly or your account is temporarily disabled.');
                    $this->messageManager->addError($message);
                    $this->qsession->setUsername($login['username']);
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                    $this->messageManager->addError($message);
                    $this->qsession->setUsername($login['username']);
                } catch (\Exception $e) {
					
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $this->messageManager->addError(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
                }
            } else {
                $this->messageManager->addError(__('A login and a password are required.'));
            }
        }

        return $this->accountRedirect->getRedirect();
    }
}
