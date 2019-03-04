<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class printPdf extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		\Magento\Framework\Registry $coreRegistry,
		\Magebees\QuotationManagerPro\Model\Pdf\Quote $qpdfModel,
		\Magento\Framework\Stdlib\DateTime\DateTime $datetime,
		  \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quotationHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
		$this->quoteHelper = $quotationHelper;
		  $this->_fileFactory = $fileFactory;
		  $this->_datetime = $datetime;
		  $this->_qpdfModel = $qpdfModel;
		 $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }
    public function execute()
    {		
		
        $resultPage = $this->resultPageFactory->create();
	//	$quote_id=$this->getRequest()->getPost('currentQuoteId');
		$quote_id=$this->getRequest()->getParam('id');
		$quote = $this->quoteHelper->loadQuoteById($quote_id);	
	 	$this->_coreRegistry->register('current_quote', $quote);		
		$pdf = $this->_qpdfModel->getPdf([$quote]);				
                $date = $this->_datetime->date('Y-m-d_H-i-s');
              return  $this->_fileFactory->create(
                   'quotation/pdf/#'.$quote->getIncrementId().'.pdf',                   
                    $pdf->render(),
                    DirectoryList::MEDIA,
                    'application/pdf'
                );		
    }
    
}
