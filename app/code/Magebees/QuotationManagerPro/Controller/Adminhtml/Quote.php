<?php

namespace Magebees\QuotationManagerPro\Controller\Adminhtml;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session as CustomerSession;

abstract class Quote extends \Magento\Backend\App\Action
{   
    protected $_publicActions = ['view', 'index'];
    protected $_coreRegistry = null;
    protected $_fileFactory;
    protected $_translateInline;
    protected $resultPageFactory;
    protected $resultJsonFactory;
    protected $resultLayoutFactory;
    protected $resultRawFactory;  
    protected $logger;
    
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,        
        LoggerInterface $logger,
		CustomerSession $customerSession,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		\Magebees\QuotationManagerPro\Helper\Admin $backendHelper,
		 \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,	
		\Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quotesession
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_translateInline = $translateInline;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultRawFactory = $resultRawFactory;     
        $this->logger = $logger;
		$this->_localeDate = $localeDate;
		 $this->_customerSession = $customerSession;
		$this->quoteHelper = $quoteHelper;
		  $this->quotesession = $quotesession;
		  $this->backendHelper = $backendHelper;		 
        parent::__construct($context);
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magebees_QuotationManagerPro::grid');
        $resultPage->addBreadcrumb(__('Quotation'), __('Quotation'));
        $resultPage->addBreadcrumb(__('Quotes'), __('Quotes'));
        return $resultPage;
    }

    /**
     * Initialize quote model instance
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     */
    protected function _initQuote()
    {
        $quote_id = $this->getRequest()->getParam('quote_id');
		// \Magento\Backend\Model\Session -> $this->_session
		$this->_session->setCurrentQuoteId($quote_id);
			$this->quotesession->setQuoteId($quote_id);
        try {			
            $quote = $this->quoteHelper->loadQuoteById($quote_id);	
			$customer_id=$quote->getCustomerId();
			/*set customer id for fix issue for get product price include tax*/
			$this->_customerSession->setCustomerId($customer_id);
			$this->backendHelper->getSession()->setCurrencyId($quote->getCurrencyCode());			
			
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError(__('This quote no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addError(__('This quote no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->_coreRegistry->register('quotation_quote', $quote);
        $this->_coreRegistry->register('current_quote', $quote);
        return $quote;
    }

    /**
     * @return bool
     */
    protected function isValidPostRequest()
    {
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        return ($formKeyIsValid && $isPost);
    }
}
