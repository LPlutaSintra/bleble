<?php

namespace Magebees\QuotationManagerPro\Controller\Sidebar;

class RemoveItem extends \Magento\Framework\App\Action\Action
{  
    protected $logger;
    protected $jsonHelper;
    protected $resultPageFactory;
    private $formKeyValidator;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magebees\QuotationManagerPro\Model\CustomerQuote $quote,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->quote = $quote;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->getFormKeyValidator()->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('quotation/quote/');
        }
        $itemId = (int)$this->getRequest()->getParam('item_id');
        try {
            $this->quote->removeItem($itemId)->save();
            return $this->jsonResponse();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    protected function jsonResponse($error = '')
    {
       
        if (empty($error)) {
            $response = [
                'success' => true,
            ];
        } else {
            $response = [
                'success' => false,
                'error_message' => $error,
            ];
        } 

        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    private function getFormKeyValidator()
    {
        if (!$this->formKeyValidator) {
            $this->formKeyValidator = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Data\Form\FormKey\Validator::class);
        }
        return $this->formKeyValidator;
    }
}
