<?php

namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;

class MassDelete extends \Magento\Backend\App\Action
{

    public function execute()
    {
        $quoteIds = $this->getRequest()->getParam('quotation');
        if (!is_array($quoteIds) || empty($quoteIds)) {
            $this->messageManager->addError(__('Please select quote(s).'));
        } else {
            try {
                $count=0;
                 $count=count($quoteIds);
                foreach ($quoteIds as $quoteId) {
                    $quotation = $this->_objectManager->create('Magebees\QuotationManagerPro\Model\Quote')->load($quoteId);
                    $quotation->delete();
                }
                 $this->messageManager->addSuccess(
                     __('A total of   '.$count .'  quote(s) have been deleted.', count($quoteId))
                 );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
         $this->_redirect('*/*/');
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_QuotationManagerPro::quotation');
    }
}
