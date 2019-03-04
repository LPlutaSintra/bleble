<?php

namespace Magebees\QuotationManagerPro\Block\Quote;
use \Magento\Framework\App\ObjectManager;
use \Magebees\QuotationManagerPro\Model\Quote\Status;

class History extends \Magento\Framework\View\Element\Template
{   

    
    protected $_quoteCollectionFactory;   
    protected $_customerSession;  
    protected $quotes;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magebees\QuotationManagerPro\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory,		
        \Magento\Customer\Model\Session $customerSession,	       
        array $data = []
    ) {
        $this->_quoteCollectionFactory = $_quoteCollectionFactory;
        $this->_customerSession = $customerSession;
		$this->_localeDate = $context->getLocaleDate();
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Quotations'));
    }	
	public function isCustomerLogin()
	{
		 if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
		return true;
	}
	public function formatQuoteDate($date)
	{
		$update_time=$this->_localeDate->formatDate(
				 $date,
				\IntlDateFormatter::MEDIUM,
				true
			);
		return $update_time;
	}
    public function getQuotes()
    {
			
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->quotes) {
            $this->quotes = $this->_quoteCollectionFactory->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'customer_id',
               $customerId
            )->addFieldToFilter(
                'is_active',
               0
            )->addFieldToFilter(
                'is_backend',
               1
            )->setOrder(
                'created_at',
                'desc'
            );
        }
        return $this->quotes;
    }
   
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuotes()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'customer.quote.history.pager'
            )->setCollection(
                $this->getQuotes()
            );
            $this->setChild('pager', $pager);
            $this->getQuotes()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getViewUrl($quote)
    {
        return $this->getUrl('quotation/quote/view', ['quote_id' => $quote->getId()]);
    }
   
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
