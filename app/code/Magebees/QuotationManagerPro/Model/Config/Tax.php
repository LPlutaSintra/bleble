<?php

namespace Magebees\QuotationManagerPro\Model\Config;

class Tax implements \Magento\Framework\Option\ArrayInterface
{
	 public function __construct(       
         \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper      
    ) {
       $this->quoteHelper = $quoteHelper;
      
    }
    public function toOptionArray()
    {
		$tax_arr=[];		
		$tax_arr[0]=__('Excluding Tax');
		$tax_arr[1]=__('Including Tax');
		$tax_arr[2]=__('Including and Excluding Tax');		
		return $tax_arr;
			
     
    }
	
}
