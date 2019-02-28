<?php

namespace Magebees\QuotationManagerPro\Model\Config;

class Addtoquote implements \Magento\Framework\Option\ArrayInterface
{
	 public function __construct(       
         \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper      
    ) {
       $this->quoteHelper = $quoteHelper;
      
    }
    public function toOptionArray()
    {
		$btn_arr=[];
		
		$btn_arr[0]=__('All Products');
		$btn_arr[1]=__('Product in which "Apply Add To Quote" attribute applied');
		
		return $btn_arr;
     
    }
}
