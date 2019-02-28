<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;

class Delete extends  \Magento\Backend\App\Action
{
     public function execute()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
		try {
				$quotation = $this->_objectManager->create('Magebees\QuotationManagerPro\Model\Quote')->load($quoteId);
                    $quotation->delete();
                
                 $this->messageManager->addSuccess(
                     __('A quote have been deleted.')
                 );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        
         $this->_redirect('*/*/');
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_QuotationManagerPro::quotation');
    }
}
