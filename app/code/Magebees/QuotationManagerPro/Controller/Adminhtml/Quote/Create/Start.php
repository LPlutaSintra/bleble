<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote\Create;

use Magento\Backend\App\Action;

class Start extends \Magebees\QuotationManagerPro\Controller\Adminhtml\Quote\Create
{
    /**
     * Start quote create action    
     */
    public function execute()
    {
        $this->_getSession()->clearStorage();	
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('quotation/*', ['customer_id' => $this->getRequest()->getParam('customer_id')]);
    }
}
