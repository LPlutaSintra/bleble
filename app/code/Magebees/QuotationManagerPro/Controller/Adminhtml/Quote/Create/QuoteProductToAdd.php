<?php

namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote\Create;

class QuoteProductToAdd extends \Magebees\QuotationManagerPro\Controller\Adminhtml\Quote\Create
{
    public function execute()
    {
        
        $qproductId = (int)$this->getRequest()->getParam('id');
        $configureProductResult = new \Magento\Framework\DataObject();
        $configureProductResult->setOk(true);
        $configureProductResult->setProductId($qproductId);
        $quote_session = $this->_objectManager->get(\Magento\Backend\Model\Session\Quote::class);
        $configureProductResult->setCurrentStoreId($quote_session->getStore()->getId());
        $configureProductResult->setCurrentCustomerId($quote_session->getCustomerId());
        $helper = $this->_objectManager->get(\Magento\Catalog\Helper\Product\Composite::class);
        return $helper->renderConfigureResult($configureProductResult);
    }
}
