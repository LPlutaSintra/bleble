<?php

namespace Magebees\QuotationManagerPro\Model\Config;

class AccessAdminList implements \Magento\Framework\Option\ArrayInterface
{
	 public function __construct(       
         \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper      
    ) {
       $this->quoteHelper = $quoteHelper;
      
    }
    public function toOptionArray()
    {
		$btn_arr=[];		
		$btn_arr[0]=__('All Admin');
		$btn_arr[1]=__('Only Assigned Admin To Quote');		
		return $btn_arr;
     
    }
}
