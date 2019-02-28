<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Header extends \Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\AbstractCreate
{
    
    protected $customerRepository;
    protected $_customerViewHelper;
	 public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,		
		 \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteSession,
       \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,  \Magebees\QuotationManagerPro\Model\Backend\Quote\Create $quoteCreate,
		  \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Helper\View $customerViewHelper,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
       $this->_sessionQuote = $sessionQuote;
        $this->_orderCreate = $orderCreate;
		   $this->_quoteSession = $quoteSession;
        $this->_quoteCreate = $quoteCreate;
		  $this->quoteHelper = $quoteHelper;		  
       $this->customerRepository = $customerRepository;
        $this->_customerViewHelper = $customerViewHelper;  parent::__construct($context,$sessionQuote,$orderCreate,$priceCurrency,$quoteSession,$quoteCreate,$data);
    }


    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
       /* if ($this->_getSession()->getQuote()->getId()) {
            return $this->_getSession()->getQuote()->getId();
        }*/
        $out = $this->_getCreateQuoteTitle();
        return $this->escapeHtml($out);
    }

    /**
     * Generate title for new order creation page.
     *
     * @return string
     */
    protected function _getCreateQuoteTitle()
    {
		$quote_id=$this->_backendSession->getCurrentQuoteId();
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
        $customerId = $this->getCustomerId();
        $storeId = $this->getStoreId();
        $out = '';
		
		if($quote_id)
		{				
			$title=sprintf("Edit Quote #%s",$quote->getIncrementId());
			return $title;
		}
		else
		{
        if ($customerId && $storeId) {
            $out .= __(
                'Create New Quote for %1 in %2',
                $this->_getCustomerName($customerId),
                $this->getStore()->getName()
            );			
            return $out;
        } elseif (!$customerId && $storeId) {
            $out .= __('Create New Quote in %1', $this->getStore()->getName());
            return $out;
        } elseif ($customerId && !$storeId) {
            $out .= __('Create New Quote for %1', $this->_getCustomerName($customerId));
            return $out;
        } elseif (!$customerId && !$storeId) {
            $out .= __('Create New Quote for New Customer');
            return $out;
        }
		}
        return $out;
    }

    /**
     * Get customer name by his ID
     *
     * @param int $customerId
     * @return string
     */
    protected function _getCustomerName($customerId)
    {
        $customerData = $this->customerRepository->getById($customerId);
        return $this->_customerViewHelper->getCustomerName($customerData);
    }
}
