<?php

namespace Magebees\QuotationManagerPro\Model\Config\Email;

class ProposalReject implements \Magento\Framework\Option\ArrayInterface
{
	
    public function toOptionArray()
    {
		$email_list_arr=[];	
			$email_list_arr[0]=__('Disable Email Notification');
			$email_list_arr['magebees_email_proposal_rejected_template']=__('Proposal Rejected (Default Template)');		
		return $email_list_arr;
     
    }
}
