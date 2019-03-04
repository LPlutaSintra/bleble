<?php
namespace Magebees\QuotationManagerPro\Controller;
use Magento\Catalog\Controller\Product\View\ViewInterface;

abstract class Quote extends \Magento\Framework\App\Action\Action implements ViewInterface
{
   
    protected $_scopeConfig;
    protected $_quoteSession;
    protected $_storeManager;
    protected $_formKeyValidator;
    protected $quote;
    protected $_quoteFactory;
    protected $resultPageFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magebees\QuotationManagerPro\Model\CustomerQuote $quote,
        \Magebees\QuotationManagerPro\Model\Session $quotationSession,
        \Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->quote = $quote;
        $this->_quoteSession = $quotationSession;
        $this->_quoteFactory = $quoteFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function getQuotationSession()
    {
        return $this->_quoteSession;
    }

    /**
     * Set back redirect url to response   
     */
    protected function _goBack($backUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($backUrl || $backUrl = $this->getBackUrl($this->_redirect->getRefererUrl())) {
            $resultRedirect->setUrl($backUrl);
        }

        return $resultRedirect;
    }

    /**
     * Get resolved back url  
     */
    protected function getBackUrl($defaultUrl = null)
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl && $this->_isInternalUrl($returnUrl)) {
            $this->messageManager->getMessages()->clear();

            return $returnUrl;
        }
        //use magento quote settings
        $shouldRedirectToCart = $this->_scopeConfig->getValue(
            'checkout/quote/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($shouldRedirectToCart || $this->getRequest()->getParam('in_quote')) {
            if ($this->getRequest()->getActionName() == 'add' && !$this->getRequest()->getParam('in_quote')) {
                $this->_quoteSession->setContinueShoppingUrl($this->_redirect->getRefererUrl());
            }
            return $this->_url->getUrl('quotation/quote');
        }
        return $defaultUrl;
    }

    /**
     * Check if URL corresponds store
     * @param string $url
     * @return bool
     */
    protected function _isInternalUrl($url)
    {
        if (strpos($url, 'http') === false) {
            return false;
        }

        /**
         * Url must start from base secure or base unsecure url
         */
        /** @var $store \Magento\Store\Model\Store */
        $store = $this->_storeManager->getStore();
        $unsecure = strpos($url, $store->getBaseUrl()) === 0;
        $secure = strpos($url, $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true)) === 0;

        return $unsecure || $secure;
    }

    /**
     * Set success redirect url to response
     * @param null $successUrl
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _successPage($successUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($successUrl === null) {
            $successUrl = $this->_url->getUrl(
                'quotation/quote/success',
                [
                    'id' => $this->_quoteSession->getLastQuoteId()
                ]
            );
        }
        $resultRedirect->setUrl($successUrl);

        return $resultRedirect;
    }
}
