<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;

class UpdateItemOptions extends \Magebees\QuotationManagerPro\Controller\Quote
{
    /**
     * Update product configuration for a quote item    
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = [];
        }
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $this->quote->getQuote()->getItemById($id);
            if (!$quoteItem) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the quote item.'));
            }

            $item = $this->quote->updateItem($id, new \Magento\Framework\DataObject($params));
			
            if (is_string($item)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item));
            }
            if ($item->getHasError()) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $this->quote->addProductsByIds(explode(',', $related));
            }
         
            if (!$this->_quoteSession->getNoCartRedirect(true)) {
                if (!$this->quote->getQuote()->getHasError()) {
                    $productName = $item->getProduct()->getName();
                    $message = __(
                        '%1 was updated in your quote.',
                        $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($productName)
                    );
                    $this->messageManager->addSuccess($message);
                }
                return $this->_goBack($this->_url->getUrl('quotation/quote'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_quoteSession->getUseNotice(true)) {
                $this->messageManager->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError($message);
                }
            }

            $url = $this->_quoteSession->getRedirectUrl(true);
            if ($url) {
                return $this->resultRedirectFactory->create()->setUrl($url);
            } else {
              
				$cartUrl =$this->_url->getUrl('quotation/quote');
                return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl($cartUrl));
            }
        } catch (\Exception $e) {
		
            $this->messageManager->addException($e, __('We can\'t update the item right now.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->_goBack();
        }
        return $this->resultRedirectFactory->create()->setPath('*/*');
    }
}
