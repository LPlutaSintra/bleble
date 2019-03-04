<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;

class UpdatePost extends \Magebees\QuotationManagerPro\Controller\Quote
{
    protected function _emptyQuotation()
    {
         try {
            $this->quote->truncate();
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addError($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addException($exception, __('We can\'t update the quote.'));
        }

        return $this->_goBack();
    }
  
    protected function _updateQuotation()
    {		
        try {			
            $quoteData = $this->getRequest()->getParam('quote');
            if (is_array($quoteData)) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                    )->getLocale()]
                );
                foreach ($quoteData as $index => $data) {
                    if (isset($data['qty'])) {
                        $quoteData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                if (!$this->quote->getCustomerSession()->getCustomerId() && $this->quote->getQuote()->getCustomerId()) {
                    $this->quote->getQuote()->setCustomerId(null);
                }

                $quoteData = $this->quote->suggestItemsQty($quoteData);
                $this->quote->updateItems($quoteData)->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(
                $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t update the quote items.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
    }

    /**
     * Update Quotation data action  *
  
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $updateAction = (string)$this->getRequest()->getParam('update_quote_action');

        switch ($updateAction) {
            case 'empty_quote':
                $this->_emptyQuotation();
                break;
            case 'update_qty':
                $this->_updateQuotation();
                break;
            default:
                $this->_updateQuotation();
        }

        return $this->_goBack();
    }
}
