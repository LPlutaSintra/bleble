<?php

namespace Magebees\QuotationManagerPro\Block\Product;

use Magento\Downloadable\Model\Link;
use Magento\Framework\Json\EncoderInterface;

class Linked extends \Magento\Catalog\Block\Product\AbstractProduct
{
    
    protected $pricingHelper;
    protected $encoder;
    public function __construct(
       
		\Magebees\QuotationManagerPro\Helper\Quotation $helper
    ) {
		
		$this->_helper = $helper;		
        
    }

    public function aroundGetLinkPrice(\Magento\Downloadable\Block\Catalog\Product\Links $subject,\Closure $proceed,$link)
    { 
		
			$config=$this->_helper->getConfig();
			if($config['enable'])
			{			
			$_product = $subject->getProduct();
			$enable_price=$this->_helper->isEnablePriceCustGroupWise($_product);			
			if($enable_price)
			{
				 return $proceed($link);			
			}
			else
			{
				 return '';
			}
			}
			else
			{
				 return $proceed($link);				
			}
       
    }

}
