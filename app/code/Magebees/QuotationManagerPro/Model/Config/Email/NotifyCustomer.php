<?php

namespace Magebees\QuotationManagerPro\Model\Config\Email;

class NotifyCustomer implements \Magento\Framework\Option\ArrayInterface
{
	
    public function toOptionArray()
    {
		$email_list_arr=[];	
			$email_list_arr[0]=__('Disable Email Notification');
			$email_list_arr['magebees_email_customer_notify_template']=__('Notify Customer For Update Quote By Admin(Default Template)');		
		return $email_list_arr;
     
    }
}
