<?php

namespace Magebees\QuotationManagerPro\Model\Config;

class AdminList implements \Magento\Framework\Option\ArrayInterface
{
	 public function __construct(       
         \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper      
    ) {
       $this->quoteHelper = $quoteHelper;
      
    }
    public function toOptionArray()
    {
		$admin_arr=[];
		$all_admin=$this->quoteHelper->getAllUserInfo();
		foreach($all_admin as $admin)
		{
			$user_id=$admin['user_id'];
			$username=$admin['username'];
			$admin_arr[$user_id]=[
                'value' => $user_id,
                'label' => $username
            ];
		}
		return $admin_arr;
     
    }
	
}
