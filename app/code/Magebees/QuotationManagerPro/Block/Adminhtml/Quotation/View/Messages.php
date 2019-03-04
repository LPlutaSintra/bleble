<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\View;

class Messages extends \Magento\Framework\View\Element\Messages
{
   
    protected function _prepareLayout()
    {
        $this->addMessages($this->messageManager->getMessages(true));
        parent::_prepareLayout();
    }
}
