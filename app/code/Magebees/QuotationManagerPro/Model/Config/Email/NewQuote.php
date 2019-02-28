<?php

namespace Magebees\QuotationManagerPro\Model\Config\Email;

class NewQuote implements \Magento\Framework\Option\ArrayInterface
{
	
    public function toOptionArray()
    {
		$email_list_arr=[];
	
			$email_list_arr[0]=__('Disable Email Notification');
			$email_list_arr['magebees_email_new_quote_template']=__('New Quotation Submit (Default Template)');
		
		return $email_list_arr;
     
    }
}
