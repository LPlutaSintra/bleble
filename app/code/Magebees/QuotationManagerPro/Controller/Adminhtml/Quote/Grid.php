<?php
namespace  Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;

class Grid extends \Magento\Backend\App\Action
{
    public function execute()
    {
        
            $this->getResponse()->setBody(
                $this->_view->getLayout()->
                createBlock('Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Grid')->toHtml()
            );
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_QuotationManagerPro::quotation');
    }
}
