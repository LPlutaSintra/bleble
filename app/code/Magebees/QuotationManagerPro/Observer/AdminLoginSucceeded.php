<?php

namespace Magebees\QuotationManagerPro\Observer;

use Magento\Framework\Event\ObserverInterface;

class AdminLoginSucceeded implements ObserverInterface
{
	
	public function __construct(
		\Magento\Backend\Model\Auth\Session $authSession, 
		\Magento\Backend\Helper\Data $HelperBackend,
         \Magebees\QuotationManagerPro\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory, 
		 \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_quoteCollectionFactory = $_quoteCollectionFactory;
		$this->authSession = $authSession;
		 $this->HelperBackend = $HelperBackend;
		$this->messageManager = $messageManager;
    }
     public function execute(\Magento\Framework\Event\Observer $observer)
    {
		//$quote_page_url=$this->HelperBackend->getUrl('quotation/quote/index', ['_nosecret' => true]);
		$quote_page_url=$this->HelperBackend->getUrl('quotation/quote/index');
		$current_admin_id=$this->getCurrentUser()->getId();
           $collection = $this->_quoteCollectionFactory->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'is_active',
               0
            )->addFieldToFilter(
                'assign_to',
                $current_admin_id
               // array('like' => '%' .$current_admin_id. '%')
            )->addFieldToFilter(
                'status',
               10
            );
		 $newQuotationsCount=$collection->getSize();
		if($newQuotationsCount>=1)
		{
		 $message = __('Quotation Manager Pro : You have Total '.$newQuotationsCount.' Un-read	Request, Please check it  <a href="%1">Un-read Quotation Request(s)</a>',$quote_page_url);
                    $this->messageManager->addWarning($message);
		}
      
        
    }
	public function getCurrentUser()
	{
		return $this->authSession->getUser();
	}
}
