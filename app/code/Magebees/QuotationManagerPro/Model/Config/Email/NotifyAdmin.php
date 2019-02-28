<?php

namespace Magebees\QuotationManagerPro\Model\Config\Email;

class NotifyAdmin implements \Magento\Framework\Option\ArrayInterface
{
	
    public function toOptionArray()
    {
		$email_list_arr=[];	
			$email_list_arr[0]=__('Disable Email Notification');
			$email_list_arr['magebees_email_admin_notify_template']=__('Notify Admin For Update Quote By Customer(Default Template)');		
		return $email_list_arr;
     
    }
}
