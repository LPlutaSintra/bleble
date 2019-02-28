<?php
namespace Magebees\QuotationManagerPro\Block\Product\View;

class OptionPrice extends \Magento\Framework\View\Element\Template
{
	protected $scopeConfig;
	protected $_helper;
	  public function __construct(     
		   \Magento\Framework\Json\EncoderInterface $jsonEncoder,
		  \Magebees\QuotationManagerPro\Helper\Quotation $helper
    ) {
       	 	$this->_helper = $helper;    
		  $this->_jsonEncoder = $jsonEncoder;
    }

	 public function aroundGetJsonConfig(\Magento\Catalog\Block\Product\View\Options $subject,\Closure $proceed)
    {
		
		$general_config=$this->_helper->getConfig();	
		if($general_config['enable'])
		{
			
		$product = $subject->getProduct();
		$enable_price=$this->_helper->isEnablePriceCustGroupWise($product);	
		$hideprice_config=$this->_helper->getHidePriceConfig();
		$text=$hideprice_config['hide_price_text'];
			if(isset($hideprice_config['hide_price_text_url']))
		{
		$text_url=$hideprice_config['hide_price_text_url'];
		}
	
		
			if($enable_price)
			{
				 return $proceed();			
			}
			else
			{
				 $opt_config = [];
        foreach ($subject->getOptions() as $product_option) {
            /* @var $product_option \Magento\Catalog\Model\Product\Option */
            if ($product_option->getGroupByType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_SELECT) {
                $tmpOptPriceValues = [];
                foreach ($product_option->getValues() as $optValueId => $opt_value) {
					
					$tmpOptPriceValues[$optValueId] = null;
					       
                  
                }
                $optPriceValue = $tmpOptPriceValues;
            } else {
				
					 $optPriceValue = null;
					
             
               
            }
            $opt_config[$product_option->getId()] = $optPriceValue;
        }

        $opt_configObj = new \Magento\Framework\DataObject(
            [
                'config' => $opt_config,
            ]
        );

        //pass the return array encapsulated in an object for the other modules to be able to alter it eg: weee
        $subject->_eventManager->dispatch('catalog_product_option_price_configuration_after', ['configObj' => $opt_configObj]);

        $opt_config=$opt_configObj->getConfig();

        return $this->_jsonEncoder->encode($opt_config);
			}
		}
		else
		{
			 return $proceed();	
		}
		
       
    }

   
}
