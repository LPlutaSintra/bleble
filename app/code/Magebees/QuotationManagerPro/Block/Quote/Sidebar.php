<?php

namespace Magebees\QuotationManagerPro\Block\Quote;

use Magento\Store\Model\ScopeInterface;

class Sidebar extends AbstractQuote
{
    protected $imageHelper;    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magebees\QuotationManagerPro\Model\Session $quotationSession,
        \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = []
    ) {
        if (isset($data['jsLayout'])) {
            $this->jsLayout = array_merge_recursive($jsLayoutDataProvider->getData(), $data['jsLayout']);
            unset($data['jsLayout']);
        } else {
            $this->jsLayout = $jsLayoutDataProvider->getData();
        }
        parent::__construct($context, $customerSession, $quotationSession, $data);
        $this->_isScopePrivate = false;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Returns miniquote config  
     */
    public function getConfig()
    {
        return [
            'quoteCartUrl' => $this->getQuoteCartUrl(),
            'checkoutUrl' => $this->getQuoteCartUrl(),
            'updateItemQtyUrl' => $this->getUpdateItemQtyUrl(),
            'removeItemUrl' => $this->getRemoveItemUrl(),
            'imageTemplate' => $this->getImageHtmlTemplate(),
            'baseUrl' => $this->getBaseUrl(),
        ];
    }

    /**
     * Get quotation quote page url
     * @return string
     */
    public function getQuoteCartUrl()
    {
        return $this->getUrl('quotation/quote');
    }
    public function getUpdateItemQtyUrl()
    {
        return $this->getUrl('quotation/sidebar/updateItemQty');
    }

    /**
     * Get remove quote item url    
     */
    public function getRemoveItemUrl()
    {
        return $this->getUrl('quotation/sidebar/removeItem');
    }

    public function getImageHtmlTemplate()
    {
        return $this->imageHelper->getFrame()
            ? 'Magento_Catalog/product/image'
            : 'Magento_Catalog/product/image_with_borders';
    }

    /**
     * Return base url.
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

   
    /**
     * Return totals from custom quote if needed
     * @return array
     */
    public function getTotalsCache()
    {
        if (empty($this->_totals)) {
            $quote = $this->getCustomQuote() ? $this->getCustomQuote() : $this->getQuote();
            $this->_totals = $quote->getTotals();
        }

        return $this->_totals;
    }

    /**
     * Retrieve subtotal block html
     * @return string
     */
    public function getTotalsHtml()
    {
        return $this->getLayout()->getBlock('quotation.quote.miniquote.totals')->toHtml();
    }

   
}
