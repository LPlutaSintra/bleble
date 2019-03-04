<?php
namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Shipping\Method;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Form extends \Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\AbstractCreate
{
   
    protected $_rates;

    protected $_taxData = null;

    protected $priceCurrency;

    public function __construct(
		\Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
		 \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteSession,
        \Magebees\QuotationManagerPro\Model\Backend\Quote\Create $quoteCreate,   
		  \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		 ProductRepositoryInterface $productRepository,
        \Magento\Tax\Helper\Data $taxData,
        array $data = []
    ) {
        $this->_taxData = $taxData;
		 $this->productRepository = $productRepository;
		  $this->quoteHelper = $quoteHelper;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency,$quoteSession,$quoteCreate,$data);		 
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quotation_quote_create_shipping_method_form');
    }
protected function _getSession()
    {
        return $this->_sessionQuote;
    }
    protected function _getOrderCreateModel()
    {
        return $this->_orderCreate;
    }
    public function getQuoteAddress()
    {
				
		$quote_id= $this->getRequest()->getParam('quote_id');			$this->_getOrderCreateModel()->collectShippingRates();
		$this->_getOrderCreateModel()->saveQuote();
		return $this->_getSession()->getQuote()->getShippingAddress();
    }

    public function getQuoteShippingRates()
    {
        if (empty($this->_rates)) {
		//	print_r(get_class($this->getQuoteAddress()));die;
            $this->_rates = $this->getQuoteAddress()->getGroupedAllShippingRates();
        }
		
		$magebees_quote_id= $this->getRequest()->getParam('quote_id');				
	$mquote = $this->quoteHelper->loadQuoteById($magebees_quote_id);	
        $qrates = $this->_rates;
        if (is_array($qrates)) {
            foreach ($qrates as $qgroup) {
                foreach ($qgroup as $qrate) {
                    if ($qrate->getCode() == $mquote->getShippingMethod()) {
						
						$_excl_without_currency = $this->getQuoteShippingPriceNotConvert($qrate->getPrice(),$this->_taxData->displayShippingPriceIncludingTax());  $_incl_without_currency = $this->getQuoteShippingPriceNotConvert($qrate->getPrice(), true); 
						$mquote->setShippingRateInclTax($_incl_without_currency);
						$mquote->setShippingRateExclTax($_excl_without_currency);
						$mquote->save();
                       
                    }
                }
            }
        }
        return $this->_rates;
    }
   
    public function getQuoteCarrierName($qcarrierCode)
    {
        if ($qname = $this->_scopeConfig->getValue(
            'carriers/' . $qcarrierCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        )
        ) {
            return $qname;
        }
        return $qcarrierCode;
    }

    
    public function getShippingMethod()
    {
        return $this->getQuoteAddress()->getShippingMethod();
    }

   
    public function isMethodActive($qcode)
    {
		$magebees_quote_id= $this->getRequest()->getParam('quote_id');				
	$mquote = $this->quoteHelper->loadQuoteById($magebees_quote_id);		
        return $qcode === $mquote->getShippingMethod();
    }

   
    public function getActiveMethodRate()
    {
		$magebees_quote_id= $this->getRequest()->getParam('quote_id');				
	$mquote = $this->quoteHelper->loadQuoteById($magebees_quote_id);	
        $qrates = $this->getShippingRates();
        if (is_array($qrates)) {
            foreach ($qrates as $qgroup) {
                foreach ($qgroup as $qrate) {
                    if ($qrate->getCode() == $mquote->getShippingMethod()) {
						$_excl_without_currency = $this->getQuoteShippingPriceNotConvert($qrate->getPrice(),$this->_taxData->displayShippingPriceIncludingTax());  $_incl_without_currency = $this->getQuoteShippingPriceNotConvert($qrate->getPrice(), true); 
						$mquote->setShippingRateInclTax($_incl_without_currency);
						$mquote->setShippingRateExclTax($_excl_without_currency);
						$mquote->save();
                        return $qrate;
                    }
                }
            }
        }
        return false;
    }

    public function getIsQuoteRateRequest()
    {
        return $this->getRequest()->getParam('collect_shipping_rates');
    }

    public function getQuoteShippingPrice($qprice, $flag)
    {
        return $this->priceCurrency->convertAndFormat(
            $this->_taxData->getShippingPrice(
                $qprice,
                $flag,
                $this->getQuoteAddress(),
                null,
                $this->getQuoteAddress()->getQuote()->getStore()
            ),
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        );
    }
	 public function getQuoteShippingPriceNotConvert($qprice, $flag)
    {
        return $this->_taxData->getShippingPrice(
                $qprice,
                $flag,
                $this->getQuoteAddress(),
                null,
                $this->getQuoteAddress()->getQuote()->getStore()
            );
    }
}
