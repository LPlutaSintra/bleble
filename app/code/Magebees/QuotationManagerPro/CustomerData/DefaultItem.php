<?php
namespace Magebees\QuotationManagerPro\CustomerData;

class DefaultItem extends AbstractItem
{
   
    protected $imageHelper;
    protected $msrpHelper;
    protected $urlBuilder;
    protected $proconfigurationPool;
    protected $checkoutHelper;
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $proconfigurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
		\Magebees\QuotationManagerPro\Helper\Quotation $quotehelper
    ) {
        $this->proconfigurationPool = $proconfigurationPool;
        $this->imageHelper = $imageHelper;
        $this->msrpHelper = $msrpHelper;
        $this->urlBuilder = $urlBuilder;
        $this->checkoutHelper = $checkoutHelper;
        $this->quotehelper = $quotehelper;
    }

    protected function doGetItemData()
    {
		$item_price=$this->quotehelper->getConvertedPrice($this->item->getCalculationPrice());
		$item_price_incl_tax=$this->quotehelper->getConvertedPrice($this->item->getPriceInclTax());
        $imageHelper = $this->imageHelper->init($this->item->getProduct(), 'mini_cart_product_thumbnail');
		$frontend_config=$this->quotehelper->getFrontendConfig();
		$tax_config=$this->quotehelper->getTaxConfig();
		$price_tax=$tax_config['price_tax'];		
		//$enable_qprice=$frontend_config['enable_qprice'];
		$_product=$this->quotehelper->loadProduct($this->item->getProduct()->getId());		
		$enable_qprice=$this->quotehelper->isEnablePriceCustGroupWise($_product);
		$product_price=($enable_qprice==1) ? $this->quotehelper->getFormatedPrice($item_price):null;
		$product_price_incl_tax=($enable_qprice==1) ? $this->quotehelper->getFormatedPrice($item_price_incl_tax):null;
		$show_price_incl_tax=(($price_tax==1)&&($item_price!=$item_price_incl_tax)) ?true:false;
		$show_price=($enable_qprice==1) ? true:false;
		$product_price_value=($enable_qprice==1) ? $this->item->getCalculationPrice():null;	
	
        return [
            'options' => $this->getOptionList(),
            'qty' => $this->item->getQty() * 1,
            'item_id' => $this->item->getId(),
            'configure_url' => $this->getConfigureUrl(),
            'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
            'product_id' => $this->item->getProduct()->getId(),
            'product_name' => $this->item->getProduct()->getName(),
            'product_sku' => $this->item->getProduct()->getSku(),
            'product_url' => $this->getProductUrl(),
            'product_has_url' => $this->hasProductUrl(),          
           'product_price' => $product_price,   
           'product_price_incl_tax' =>'Incl.Tax: '.$product_price_incl_tax,   
           'show_price_incl_tax' => $show_price_incl_tax,   
			'display_product_price'=>$show_price,
            'product_price_value' => $product_price_value,
            'product_image' => [
                'src' => $imageHelper->getUrl(),
                'alt' => $imageHelper->getLabel(),
                'width' => $imageHelper->getWidth(),
                'height' => $imageHelper->getHeight(),
            ],
            'canApplyMsrp' => $this->msrpHelper->isShowBeforeOrderConfirm($this->item->getProduct())
                && $this->msrpHelper->isMinimalPriceLessMsrp($this->item->getProduct()),
        ];
    }
   
    protected function getOptionList()
    {
		/*		return $this->proconfigurationPool->getByProductType($this->item->getProductType())->getOptions($this->item);*/
		return $this->quotehelper->getBackendOptions($this->item);
    }

    protected function getConfigureUrl()
    {
        return $this->urlBuilder->getUrl(
            'quotation/quote/configure',
            ['id' => $this->item->getId(), 'product_id' => $this->item->getProduct()->getId()]
        );
    }

    protected function hasProductUrl()
    {
        if ($this->item->getRedirectUrl()) {
            return true;
        }

        $qproduct = $this->item->getProduct();
        $qoption = $this->item->getOptionByCode('product_type');
        if ($qoption) {
            $qproduct = $qoption->getProduct();
        }

        if ($qproduct->isVisibleInSiteVisibility()) {
            return true;
        } else {
            if ($qproduct->hasUrlDataObject()) {
                $data = $qproduct->getUrlDataObject();
                if (in_array($data->getVisibility(), $qproduct->getVisibleInSiteVisibilities())) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function getProductUrl()
    {
        if ($this->item->getRedirectUrl()) {
            return $this->item->getRedirectUrl();
        }

        $qproduct = $this->item->getProduct();
        $qoption = $this->item->getOptionByCode('product_type');
        if ($qoption) {
            $qproduct = $qoption->getProduct();
        }

        return $qproduct->getUrlModel()->getUrl($qproduct);
    }
}
