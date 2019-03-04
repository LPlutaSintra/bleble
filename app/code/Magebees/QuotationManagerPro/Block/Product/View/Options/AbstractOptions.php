<?php
namespace Magebees\QuotationManagerPro\Block\Product\View\Options;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;

class AbstractOptions extends \Magento\Framework\View\Element\Template
{
   
    protected $_product;
    protected $_option;
    protected $pricingHelper;
    protected $_catalogHelper;
	protected $_helper;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
		\Magebees\QuotationManagerPro\Helper\Quotation $helper,     
        array $data = []
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->_catalogHelper = $catalogData;		
		$this->_helper = $helper;
        parent::__construct($context, $data);
    }
   
	public function aroundGetFormatedPrice(\Magento\Catalog\Block\Product\View\Options\AbstractOptions $subject,\Closure $proceed)
    {
	 
		if ($option = $subject->getOption()) {
			
			$config=$this->_helper->getConfig();	
			if($config['enable'])
			{
			$product = $subject->getProduct();
			$is_check=$this->_helper->isEnablePriceCustGroupWise($product);	
			$hideprice_config=$this->_helper->getHidePriceConfig();
			$text=$hideprice_config['hide_price_text'];
		if(isset($hideprice_config['hide_price_text_url']))
		{	$text_url=$hideprice_config['hide_price_text_url'];	
		}
			}
			else
			{
				$is_check=true;
			}
            if($is_check){
				
			return $proceed();	
				
			}
			else
			{		
				if(isset($text_url))
				{
					return '<a href='.$text_url.' target=_blank>'.$text.'</a>';
				}
				else					
				{
					 return $text;
				}	
			}
			
        }
        return $proceed();		
    }

}
