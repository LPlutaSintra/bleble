<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Form extends \Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\AbstractCreate
{
  
	 public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
		 \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteSession,
        \Magebees\QuotationManagerPro\Model\Backend\Quote\Create $quoteCreate,
		 \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
		  \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
		    \Magento\Framework\Json\EncoderInterface $jsonEncoder,
		    \Magento\Customer\Model\Address\Mapper $quoteaddressMapper,
		  \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        array $quotedata = []
    ) {
        $this->priceCurrency = $priceCurrency;
       $this->_sessionQuote = $sessionQuote;
        $this->_orderCreate = $orderCreate;
		   $this->_quoteSession = $quoteSession;
        $this->_quoteCreate = $quoteCreate;
		  $this->customerRepository = $customerRepository;
		  $this->_localeCurrency = $localeCurrency;
		  $this->_jsonEncoder = $jsonEncoder;
		  $this->addressMapper = $quoteaddressMapper;
         $this->_qcustomerFormFactory = $customerFormFactory;  parent::__construct($context,$sessionQuote,$orderCreate,$priceCurrency,$quoteSession,$quoteCreate,$quotedata);
    }
	
    
  
 public function getQuoteDataJson()
    {
        $quotedata = [];
        if ($this->getCustomerId()) {
            $quotedata['customer_id'] = $this->getCustomerId();
            $quotedata['addresses'] = [];

            $quoteaddresses = $this->customerRepository->getById($this->getCustomerId())->getAddresses();

            foreach ($quoteaddresses as $quoteaddress) {
                $quoteaddressForm = $this->_qcustomerFormFactory->create(
                    'customer_address',
                    'adminhtml_customer_address',
                    $this->addressMapper->toFlatArray($quoteaddress)
                );
                $quotedata['addresses'][$quoteaddress->getId()] = $quoteaddressForm->outputData(
                    \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON
                );
            }
        }
        if ($this->getStoreId() !== null) {
            $quotedata['store_id'] = $this->getStoreId();
            $qcurrency = $this->_localeCurrency->getCurrency($this->getStore()->getCurrentCurrencyCode());
            $symbol = $qcurrency->getSymbol() ? $qcurrency->getSymbol() : $qcurrency->getShortName();
            $quotedata['currency_symbol'] = $symbol;
        
        }

        return $this->_jsonEncoder->encode($quotedata);
    }
	 public function getLoadBlockUrl()
    {
        return $this->getUrl('quotation/quote_create/loadBlock');
    }
	  public function getQuoteStoreSelectorDisplay()
    {
        $qstoreId = $this->getStoreId();
        $qcustomerId = $this->getCustomerId();
        if ($qcustomerId !== null && !$qstoreId) {
            return 'block';
        }
        return 'none';
    }
	 public function getQuoteDataSelectorDisplay()
    {
		 $qstoreId = $this->getStoreId();
        $qcustomerId = $this->getCustomerId();
        if ($qcustomerId !== null && $qstoreId) {
            return 'block';
        }
        return 'none';
	}
	 public function getQuoteCustomerSelectorDisplay()
    {
        $qcustomerId = $this->getCustomerId();
        if ($qcustomerId === null) {
            return 'block';
        }
        return 'none';
    }
}
