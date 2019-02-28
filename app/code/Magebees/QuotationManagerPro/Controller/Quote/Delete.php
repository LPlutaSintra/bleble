<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
class Delete extends \Magebees\QuotationManagerPro\Controller\Quote
{   
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {			
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {			
            try {
                $this->quote->removeItem($id)->save();
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t remove the item.'));
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }
        }
        $defaultUrl = $this->_objectManager->create(\Magento\Framework\UrlInterface::class)->getUrl('*/*');
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl($defaultUrl));
    }
}
