<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;
use Magento\Framework\App\Filesystem\DirectoryList;

class printPdf extends  \Magento\Backend\App\Action
{
       public function __construct(
        \Magento\Backend\App\Action\Context $context,		 
		    \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
		   \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper		
    ) {
    
        parent::__construct($context);       
		  $this->quoteHelper = $quoteHelper;	
		    $this->_fileFactory = $fileFactory;
    }
	 public function execute()
    {
				$quote_id= $this->getRequest()->getParam('quote_id');				
				$quote = $this->quoteHelper->loadQuoteById($quote_id);	
		
					$pdf = $this->_objectManager->create(\Magebees\QuotationManagerPro\Model\Pdf\Quote::class)->getPdf([$quote]);
				
                $date = $this->_objectManager->get(
                    \Magento\Framework\Stdlib\DateTime\DateTime::class
                )->date('Y-m-d_H-i-s');
              return  $this->_fileFactory->create(
                   'quotation/pdf/#'.$quote->getIncrementId().'.pdf',                  
                    $pdf->render(),
                    DirectoryList::MEDIA,
                    'application/pdf'
                );
	}
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_QuotationManagerPro::quotation');
    }
}
