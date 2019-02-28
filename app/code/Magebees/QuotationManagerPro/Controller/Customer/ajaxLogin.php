<?php
namespace Magebees\QuotationManagerPro\Controller\Customer;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

class ajaxLogin extends \Magento\Framework\App\Action\Action
{ 
    protected $session;
    protected $qcustomerAccountManagement;
    protected $helper;
    protected $qresultJsonFactory;
    protected $qresultRawFactory;
    protected $qaccountRedirect;
    protected $qscopeConfig;
    private $cookieManager;
    private $cookieMetadataFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $qcustomerSession,
        \Magento\Framework\Json\Helper\Data $helper,
        AccountManagementInterface $qcustomerAccountManagement,
        \Magento\Framework\Controller\Result\JsonFactory $qresultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $qresultRawFactory,
        CookieManagerInterface $cookieManager = null,
        CookieMetadataFactory $cookieMetadataFactory = null
    ) {
        parent::__construct($context);
        $this->customerSession = $qcustomerSession;
        $this->helper = $helper;
        $this->customerAccountManagement = $qcustomerAccountManagement;
        $this->resultJsonFactory = $qresultJsonFactory;
        $this->resultRawFactory = $qresultRawFactory;
        $this->cookieManager = $cookieManager ?: ObjectManager::getInstance()->get(
            CookieManagerInterface::class
        );
        $this->cookieMetadataFactory = $cookieMetadataFactory ?: ObjectManager::getInstance()->get(
            CookieMetadataFactory::class
        );
    }

    protected function getAccountRedirect()
    {
        if (!is_object($this->qaccountRedirect)) {
            $this->qaccountRedirect = ObjectManager::getInstance()->get(AccountRedirect::class);
        }
        return $this->qaccountRedirect;
    }   
    public function setAccountRedirect($value)
    {
        $this->qaccountRedirect = $value;
    }
    protected function getScopeConfig()
    {
        if (!is_object($this->qscopeConfig)) {
            $this->qscopeConfig = ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        }
        return $this->qscopeConfig;
    }

    public function setScopeConfig($value)
    {
        $this->qscopeConfig = $value;
    }
    public function execute()
    {
        $qcredentials = null;
        $qhttpBadRequestCode = 400;

       
        $qresultRaw = $this->resultRawFactory->create();
        try {
         //   $qcredentials = $this->helper->jsonDecode($this->getRequest()->getContent());
            $params = $this->getRequest()->getParams();
			$qcredentials=$params['login'];
			
        } catch (\Exception $e) {
            return $qresultRaw->setHttpResponseCode($qhttpBadRequestCode);
        }
        if (!$qcredentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $qresultRaw->setHttpResponseCode($qhttpBadRequestCode);
        }

        $qresponse = [
            'errors' => false,
            'message' => __('Login successful.')
        ];
        try {
            $qcustomer = $this->customerAccountManagement->authenticate(
                $qcredentials['username'],
                $qcredentials['password']
            );
            $this->customerSession->setCustomerDataAsLoggedIn($qcustomer);
            $this->customerSession->regenerateId();
            $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
            if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }
            if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
                $qresponse['redirectUrl'] = $this->_redirect->success($redirectRoute);
                $this->getAccountRedirect()->clearRedirectCookie();
            }
        } catch (EmailNotConfirmedException $e) {
            $qresponse = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (InvalidEmailOrPasswordException $e) {
            $qresponse = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (LocalizedException $e) {
            $qresponse = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $qresponse = [
                'errors' => true,
                'message' => __('Invalid login or password.')
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $qresultJson */
        $qresultJson = $this->resultJsonFactory->create();
        return $qresultJson->setData($qresponse);
    }
}