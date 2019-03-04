<?php

namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
	protected $_publicActions = ['index'];

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
    
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magebees_QuotationManagerPro::grid');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Quotations'));
        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_QuotationManagerPro::quotation');
    }
}
