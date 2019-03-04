<?php

namespace Magebees\QuotationManagerPro\Model\Config\Email;

class QuoteProposal implements \Magento\Framework\Option\ArrayInterface
{
	
    public function toOptionArray()
    {
		$email_list_arr=[];
	
			$email_list_arr[0]=__('Disable Email Notification');
			$email_list_arr['magebees_email_quote_proposal_template']=__('New Quote Proposal (Default Template)');
		
		return $email_list_arr;
     
    }
}
