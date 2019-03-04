<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote\Create;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;

class LoadBlock extends \Magebees\QuotationManagerPro\Controller\Adminhtml\Quote\Create
{
    
    protected $qresultRawFactory;
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $qresultPageFactory,
        ForwardFactory $qresultForwardFactory,
        RawFactory $qresultRawFactory,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		\Magebees\QuotationManagerPro\Helper\Email $emailHelper,
		\Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quotesession
		
    ) {
        $this->resultRawFactory = $qresultRawFactory;
        $this->quotesession = $quotesession;
        $this->quoteHelper = $quoteHelper;
		$this->emailHelper = $emailHelper;
        parent::__construct(
            $context,
            $productHelper,
            $escaper,
            $qresultPageFactory,
            $qresultForwardFactory,$quoteHelper,$emailHelper
        );
    }

    public function execute()
    {
		
		$quoteId = $this->getRequest()->getParam('quote_id');
		$this->quotesession->setQuoteId($quoteId);
		 $quote=$this->quoteHelper->loadQuoteById($quoteId);
		$store_id=$quote->getStoreId();		
		$this->quotesession->setStrId($store_id);
		$this->quotesession->setCurrencyId($quote->getCurrencyCode());
		$this->quotesession->setCustomerId($quote->getCustomerId() ?: false);       
        $quoterequest = $this->getRequest();
		 $qasJson = $quoterequest->getParam('json');
        $qblock = $quoterequest->getParam('block');
        try {
            $this->_initSession()->_processData();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
         
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
           
            $this->messageManager->addException($e, $e->getMessage());
        }
		$qasJson = $quoterequest->getParam('json');
        $qblock = $quoterequest->getParam('block');

        /** @var \Magento\Framework\View\Result\Page $qresultPage */
        $qresultPage = $this->resultPageFactory->create();
        if ($qasJson) {
            $qresultPage->addHandle('sales_order_create_load_block_json');
        } else {
            $qresultPage->addHandle('sales_order_create_load_block_plain');
        }

        if ($qblock) {
            $qblocks = explode(',', $qblock);
            if ($qasJson && !in_array('message', $qblocks)) {
                $qblocks[] = 'message';
            }
            foreach ($qblocks as $qblock) {				
                $qresultPage->addHandle('quotation_quote_create_load_block_' . $qblock);
            }			
        }
        $qresult = $qresultPage->getLayout()->renderElement('content');
        if ($quoterequest->getParam('as_js_varname')) {
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setUpdateResult($qresult);
            return $this->resultRedirectFactory->create()->setPath('quotation/*/showUpdateResult');
        }
        return $this->resultRawFactory->create()->setContents($qresult);
       
    }
	
}
