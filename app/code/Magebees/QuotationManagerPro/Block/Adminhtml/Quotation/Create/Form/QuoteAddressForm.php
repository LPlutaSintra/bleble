<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Form;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Pricing\PriceCurrencyInterface;


class QuoteAddressForm extends \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm
{
   
    protected $_qcustomerFormFactory;
    protected $_qjsonEncoder;
    protected $qdirectoryHelper;   
    protected $qoptions;
    protected $qaddressService;
    protected $_qaddressHelper;
    protected $qsearchCriteriaBuilder;
    protected $qfilterBuilder;
    protected $qaddressMapper;
    private $qcountriesCollection;
    private $backendQuoteSession;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $qsessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $qorderCreate,
        PriceCurrencyInterface $qpriceCurrency,
        \Magento\Framework\Data\FormFactory $qformFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $qdataObjectProcessor,
        \Magento\Directory\Helper\Data $qdirectoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
        \Magento\Customer\Model\Options $qoptions,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		\Magebees\QuotationManagerPro\Helper\Admin $backendHelper,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Customer\Api\AddressRepositoryInterface $qaddressService,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $qfilterBuilder,
        \Magento\Customer\Model\Address\Mapper $qaddressMapper,		\Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteSession,
		 \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
		\Magento\Customer\Api\AddressRepositoryInterface $addressRepository,		
        array $data = []
    ) {
        $this->qoptions = $qoptions;
		$this->_quoteSession = $quoteSession;    
        $this->qdirectoryHelper = $qdirectoryHelper;
		$this->quoteHelper = $quoteHelper;
		$this->backendHelper = $backendHelper;
        $this->_qjsonEncoder = $jsonEncoder;
        $this->_qcustomerFormFactory = $customerFormFactory;
        $this->_qaddressHelper = $addressHelper;
        $this->qaddressService = $qaddressService;
        $this->qsearchCriteriaBuilder = $criteriaBuilder;
        $this->qfilterBuilder = $qfilterBuilder;
        $this->qaddressMapper = $qaddressMapper;
		$this->customerRepository = $customerRepository;
		$this->addressRepository = $addressRepository;		
        parent::__construct(
            $context,
            $qsessionQuote,
            $qorderCreate,
            $qpriceCurrency,
            $qformFactory,
            $qdataObjectProcessor,
            $data
        );
    }

	public function getCurrentQuoteId()
	{
		if($this->_backendSession->getCurrentQuoteId())
		{
		return $this->_backendSession->getCurrentQuoteId();	
		}
		else
		{
		return $this->_quoteSession->getQuote()->getId();
		}
	}
	public function getCurrentCustomerId()
	{
		return $this->_quoteSession->getCustomerId();
	}
	public function getCustomer()
	{
		return $this->customerRepository->getById($this->_quoteSession->getCustomerId());
	}
	
	public function getQuoteBillingAddress()
	{
		  try {
            	$quote_id=$this->getCurrentQuoteId();
		return $default_billing=$this->quoteHelper->getDefaultAddressInQuote($quote_id,'billing',$this->getCustomer()->getDefaultBilling());
        } catch (\Exception $e) {
            /** If customer does not exist do nothing. */
        }
	}
	public function getQuoteShippingAddress()
	{
		 try {
		$quote_id=$this->getCurrentQuoteId();
		return $default_shipping=$this->quoteHelper->getDefaultAddressInQuote($quote_id,'shipping',$this->getCustomer()->getDefaultShipping());
			  } catch (\Exception $e) {
            /** If customer does not exist do nothing. */
        }
		//return $this->addressRepository->getById($this->getCustomer()->getDefaultShipping());
	}


    /**
     * Get config
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve current customer address DATA collection.   
     */
    public function getAddressCollection()
    {
	
        if ($this->_quoteSession->getCustomerId()) {
            $filter = $this->qfilterBuilder
                ->setField('parent_id')
                ->setValue($this->_quoteSession->getCustomerId())
                ->setConditionType('eq')
                ->create();
            $this->qsearchCriteriaBuilder->addFilters([$filter]);
            $searchCriteria = $this->qsearchCriteriaBuilder->create();
            $result = $this->qaddressService->getList($searchCriteria);
            return $result->getItems();
        }
        return [];
    }

    /**
     * Return Customer Address Collection as JSON    
     */
    public function getAddressCollectionJson()
    {
        $defaultCountryId = $this->qdirectoryHelper->getDefaultCountry($this->getStore());
        $emptyAddressForm = $this->_qcustomerFormFactory->create(
            'customer_address',
            'adminhtml_customer_address',
            [\Magento\Customer\Api\Data\AddressInterface::COUNTRY_ID => $defaultCountryId]
        );
        $data = [0 => $emptyAddressForm->outputData(\Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON)];
        foreach ($this->getAddressCollection() as $address) {
            $addressForm = $this->_qcustomerFormFactory->create(
                'customer_address',
                'adminhtml_customer_address',
                $this->qaddressMapper->toFlatArray($address)
            );
            $data[$address->getId()] = $addressForm->outputData(
                \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON
            );
        }

        return $this->_qjsonEncoder->encode($data);
    }

    /**
     * Prepare Form and add elements to form     
     */
    protected function _prepareForm()
    {
        $qfieldset = $this->_form->addFieldset('main', ['no_container' => true]);

        $addressForm = $this->_qcustomerFormFactory->create('customer_address', 'adminhtml_customer_address');
        $attributes = $addressForm->getAttributes();
        $this->_addAttributesToForm($attributes, $qfieldset);

        $qprefixElement = $this->_form->getElement('prefix');
        if ($qprefixElement) {
            $qprefixqoptions = $this->qoptions->getNamePrefixOptions($this->getStore());
            if (!empty($qprefixqoptions)) {
                $qfieldset->removeField($qprefixElement->getId());
                $qprefixField = $qfieldset->addField($qprefixElement->getId(), 'select', $qprefixElement->getData(), '^');
                $qprefixField->setValues($qprefixqoptions);
                if ($this->getAddressId()) {
                    $qprefixField->addElementValues($this->getAddress()->getPrefix());
                }
            }
        }

        $qsuffixElement = $this->_form->getElement('suffix');
        if ($qsuffixElement) {
            $qsuffixqoptions = $this->qoptions->getNameSuffixOptions($this->getStore());
            if (!empty($qsuffixqoptions)) {
                $qfieldset->removeField($qsuffixElement->getId());
                $qsuffixField = $qfieldset->addField(
                    $qsuffixElement->getId(),
                    'select',
                    $qsuffixElement->getData(),
                    $this->_form->getElement('lastname')->getId()
                );
                $qsuffixField->setValues($qsuffixqoptions);
                if ($this->getAddressId()) {
                    $qsuffixField->addElementValues($this->getAddress()->getSuffix());
                }
            }
        }

        $qregionElement = $this->_form->getElement('region_id');
        if ($qregionElement) {
            $qregionElement->setNoDisplay(true);
        }

        $this->_form->setValues($this->getFormValues());

        if ($this->_form->getElement('country_id')->getValue()) {
            $qcountryId = $this->_form->getElement('country_id')->getValue();
            $this->_form->getElement('country_id')->setValue(null);
            foreach ($this->_form->getElement('country_id')->getValues() as $qcountry) {
                if ($qcountry['value'] == $qcountryId) {
                    $this->_form->getElement('country_id')->setValue($qcountryId);
                }
            }
        }
        if ($this->_form->getElement('country_id')->getValue() === null) {
            $this->_form->getElement('country_id')->setValue(
                $this->qdirectoryHelper->getDefaultCountry($this->getStore())
            );
        }
        $this->processCountryOptions($this->_form->getElement('country_id'));
        // Set custom renderer for VAT field if needed
        $qvatIdElement = $this->_form->getElement('vat_id');
        if ($qvatIdElement && $this->getDisplayVatValidationButton() !== false) {
            $qvatIdElement->setRenderer(
                $this->getLayout()->createBlock(
                    \Magento\Customer\Block\Adminhtml\Sales\Order\Address\Form\Renderer\Vat::class
                )->setJsVariablePrefix(
                    $this->getJsVariablePrefix()
                )
            );
        }

        return $this;
    }   
    protected function processCountryOptions(
        \Magento\Framework\Data\Form\Element\AbstractElement $qcountryElement,
        $storeId = null
    ) {
        if ($storeId === null) {
            $storeId = $this->getBackendQuoteSession()->getStoreId();
        }
        $qoptions = $this->getCountriesCollection()
            ->loadByStore($storeId)
            ->toOptionArray();

        $qcountryElement->setValues($qoptions);
    }

    /**
     * Retrieve Directiry Countries collection
     * @deprecated 100.1.3
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    private function getCountriesCollection()
    {
        if (!$this->qcountriesCollection) {
            $this->qcountriesCollection = ObjectManager::getInstance()
                ->get(\Magento\Directory\Model\ResourceModel\Country\Collection::class);
        }

        return $this->qcountriesCollection;
    }

    private function getBackendQuoteSession()
    {
        if (!$this->backendQuoteSession) {
            $this->backendQuoteSession = ObjectManager::getInstance()->get(Quote::class);
        }

        return $this->backendQuoteSession;
    }

    protected function _addAdditionalFormElementData(AbstractElement $qelement)
    {
        if ($qelement->getId() == 'region_id') {
            $qelement->setNoDisplay(true);
        }
        return $this;
    }
    
    public function getAddressId()
    {
        return false;
    }

    public function getQuoteAddressAsString(\Magento\Customer\Api\Data\AddressInterface $qaddress)
    {
        $qformatTypeRenderer = $this->_qaddressHelper->getFormatTypeRenderer('oneline');
        $qresult = '';
        if ($qformatTypeRenderer) {
            $qresult = $qformatTypeRenderer->renderArray($this->qaddressMapper->toFlatArray($qaddress));
        }

        return $this->escapeHtml($qresult);
    }
}
