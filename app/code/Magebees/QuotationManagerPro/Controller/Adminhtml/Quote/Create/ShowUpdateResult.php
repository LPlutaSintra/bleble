<?php

namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote\Create;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;

class ShowUpdateResult extends \Magebees\QuotationManagerPro\Controller\Adminhtml\Quote\Create
{
    
    protected $resultRawFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        RawFactory $resultRawFactory,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		\Magebees\QuotationManagerPro\Helper\Email $emailHelper
    ) {
        $this->resultRawFactory = $resultRawFactory;
		$this->emailHelper = $emailHelper;
		$this->quoteHelper = $quoteHelper;
        parent::__construct(
            $context,
            $productHelper,
            $escaper,
            $resultPageFactory,
            $resultForwardFactory,
			$quoteHelper,
			$emailHelper
        );
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $session = $this->_objectManager->get(\Magento\Backend\Model\Session::class);
        if ($session->hasUpdateResult() && is_scalar($session->getUpdateResult())) {
            $resultRaw->setContents($session->getUpdateResult());
        }
        $session->unsUpdateResult();
        return $resultRaw;
    }
}
