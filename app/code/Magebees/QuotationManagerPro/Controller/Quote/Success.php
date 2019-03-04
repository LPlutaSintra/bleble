<?php

namespace Magebees\QuotationManagerPro\Controller\Quote;

class Success extends \Magento\Framework\App\Action\Action
{
    /**
     * Quote success action
     */
	public function __construct(
        \Magento\Framework\App\Action\Context $context,     
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper		
    ) {
          
		   $this->quoteHelper = $quoteHelper;	
		$this->resultPageFactory = $resultPageFactory;
		   parent::__construct($context);
		 
    }
    public function execute()
    {     
		 $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Thank You for Quote Request..!!'));
        return $resultPage;
    }
}
