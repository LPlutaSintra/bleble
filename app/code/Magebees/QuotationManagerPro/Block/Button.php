<?php
namespace Magebees\QuotationManagerPro\Block;

class Button extends \Magento\Framework\View\Element\Template
{
   
    protected $_coreRegistry;
    protected $urlHelper;    
    protected $quotationCartHelper;
    protected $resultForwardFactory;
    protected $_visibilityEnabled;
    protected $_customerSession;

    public function __construct(
        \Magebees\QuotationManagerPro\Helper\Quotation $quotationHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,     
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->quotationHelper = $quotationHelper;
        $this->urlHelper = $urlHelper;       
        $this->_coreRegistry = $context->getRegistry();
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    } 

	  public function getProduct()
    {
        //get product
        if ($this->_coreRegistry->registry('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        } 
        return $this->getData('product');
    }
    /**
     * Get post parameters
     * @param \Magento\Catalog\Model\Product $qproduct
     * @return string
     */
    public function getAddToQuoteParams(\Magento\Catalog\Model\Product $qproduct)
    {
        $url = $this->getAddToQuoteUrl($qproduct);

        return [
            'action' => $url,
            'data' => [
                'product' => $qproduct->getEntityId(),
                \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options      
     */
    public function getAddToQuoteUrl($qproduct, $additionaldata = [])
    {
     //   if ($qproduct->getTypeInstance()->hasRequiredOptions($qproduct)) {
		if (!$qproduct->getTypeInstance()->isPossibleBuyFromList($qproduct)) {
            if (!isset($additionaldata['_escape'])) {
                $additionaldata['_escape'] = true;
            }
            if (!isset($additionaldata['_query'])) {
                $additionaldata['_query'] = [];
            }
            $additionaldata['_query']['options'] = 'quote';

            return $this->getProductUrl($qproduct, $additionaldata);
        }

        return $this->quotationHelper->getAddUrl($qproduct, $additionaldata);
    }

   
    public function getProductUrl($qproduct, $additionaldata = [])
    {
        if ($this->hasProductUrl($qproduct)) {
            if (!isset($additionaldata['_escape'])) {
                $additionaldata['_escape'] = true;
            }

            return $qproduct->getUrlModel()->getUrl($qproduct, $additionaldata);
        }

        return '#';
    }

    /**
     * Check Product has URL
     * @param \Magento\Catalog\Model\Product $qproduct
     * @return bool
     */
    public function hasProductUrl($qproduct)
    {
        if ($qproduct->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($qproduct->hasUrlDataObject()) {
            if (in_array($qproduct->hasUrlDataObject()->getVisibility(), $qproduct->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }
    
}
