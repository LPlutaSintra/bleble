<?php
namespace Magebees\QuotationManagerPro\Block;

class Quote extends \Magebees\QuotationManagerPro\Block\Quote\AbstractQuote
{
      
    protected $catalogUrlBuilder;
    protected $httpContext;
    protected $cartHelper;
    protected $customerUrl;
    protected $visibilityEnabled;
    protected $fullFormSet = false;
   
    public function __construct(
       
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magebees\QuotationManagerPro\Model\Session $quotationSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,       
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $quotationSession, $data);       
        $this->catalogUrlBuilder = $catalogUrlBuilder;
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
        $this->customerUrl = $customerUrl;
      
    }

    public function hasError()
    {
        return $this->getQuote()->getHasError();
    }
    
    public function getContinueShoppingUrl()
    {
        $url = $this->getData('continue_shopping_url');
        if ($url === null) {
            $url = $this->_quotationSession->getContinueShoppingUrl(true);
            if (!$url) {
                $url = $this->_urlBuilder->getUrl();
            }
            $this->setData('continue_shopping_url', $url);
        }

        return $url;
    }

    public function getItemsCount()
    {
        return $this->getQuote()->getItemsCount();
    }

    public function isCustomerLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    public function getLoginUrl()
    {
        return $this->customerUrl->getLoginUrl();
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_customerSession->setAfterAuthUrl($this->getUrl('quotation/quote'));     
    }

    public function getItems()
    {
     
        return $this->getQuote()->getQuotePageItemsCollection();
    }
	public function getQuoteId()
	{
		return $this->getQuote()->getId();
	}
    /**
     * Get the request for quote form
     *
     * @return string
     */
    public function getForm()
    {
        $form = $this->getChildHtml('checkout.root');
        $this->fullFormSet = (bool) $this->getEnableForm();

        return $form;
    }
}
