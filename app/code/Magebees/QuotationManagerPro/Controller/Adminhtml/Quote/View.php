<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;

use Magento\Backend\App\Action;

class View extends \Magebees\QuotationManagerPro\Controller\Adminhtml\Quote
{
     
    public function execute()
    {
        $quote = $this->_initQuote();
		 $resultRedirect = $this->resultRedirectFactory->create();
		$assign_adminid=$quote->getAssignTo();
		$assign_adminid_arr=explode(',',$assign_adminid);
		$admin_id=$this->backendHelper->getCurrentUser()->getData('user_id');	
		$config=$this->quoteHelper->getConfig();
		$access_user=$config['access_quote_user'];
		if($access_user==1)
		{
		if(!in_array($admin_id,$assign_adminid_arr))
		{
			  $this->messageManager->addError(__('Not allow to access this Quotation'));
                $resultRedirect->setPath('quotation/quote/index');
                return $resultRedirect;
		}
		}
       
        if ($quote) {
            try {
                $resultPage = $this->_initAction();
                $resultPage->getConfig()->getTitle()->prepend(__('Quotes'));
            } catch (\Exception $e) {
				
                $this->logger->critical($e);
                $this->messageManager->addError(__('Exception occurred during quote load'));
                $resultRedirect->setPath('quotation/quote/index');
                return $resultRedirect;
            }
			$quote_status=$quote->getStatus();
			$update_time=$this->_localeDate->formatDate(
     $quote->getUpdatedAt(),
    \IntlDateFormatter::MEDIUM,
    true
);
			$allow_change=$this->backendHelper->checkStatusAllowForProposal($quote_status);
			if($allow_change)
			{
				$title=sprintf("#%s",$quote->getIncrementId()).' | '.$update_time;
			}
			else
			{
				$title=sprintf("#%s",$quote->getIncrementId()).' | '.$update_time.' [Locked]';
			}
            $resultPage->getConfig()->getTitle()->prepend($title);
            return $resultPage;
        }
        $resultRedirect->setPath('quotation/*/');
        return $resultRedirect;
    }
}
