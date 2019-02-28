<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Form;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Account extends \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm
{
    
    protected $_metadataFormFactory;
    protected $qcustomerRepository;
    protected $_extensibleDataObjectConverter;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $qorderCreate,
        PriceCurrencyInterface $qpriceCurrency,
        \Magento\Framework\Data\FormFactory $qformFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $qdataObjectProcessor,
        \Magento\Customer\Model\Metadata\FormFactory $qmetadataFormFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $qcustomerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $qextensibleDataObjectConverter,		\Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteSession,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,	
        array $data = []
    ) {
        $this->_metadataFormFactory = $qmetadataFormFactory;
        $this->customerRepository = $qcustomerRepository;
        $this->_extensibleDataObjectConverter = $qextensibleDataObjectConverter;
		$this->_quoteSession = $quoteSession; 	
		$this->quoteHelper = $quoteHelper;
        parent::__construct(
            $context,
            $sessionQuote,
            $qorderCreate,
            $qpriceCurrency,
            $qformFactory,
            $qdataObjectProcessor,
            $data
        );
    }

    
    public function getAccountHeaderCssClass()
    {
        return 'head-account';
    }

    public function getAccountHeaderText()
    {
        return __('Account Information');
    }

    protected function _prepareForm()
    {
       
        $qcustomerForm = $this->_metadataFormFactory->create('customer', 'adminhtml_checkout');
        $qattributes = [];

        // add system required attributes
        foreach ($qcustomerForm->getSystemAttributes() as $qattribute) {
            if ($qattribute->isRequired()) {
                $qattributes[$qattribute->getAttributeCode()] = $qattribute;
            }
        }

        if ($this->getQuote()->getCustomerIsGuest()) {
            unset($qattributes['group_id']);
        }

        // add user defined attributes
        foreach ($qcustomerForm->getUserAttributes() as $qattribute) {
            $qattributes[$qattribute->getAttributeCode()] = $qattribute;
        }

        $qfieldset = $this->_form->addFieldset('main', []);

        $this->_addAttributesToForm($qattributes, $qfieldset);

        $this->_form->addFieldNameSuffix('quote[account]');
        $this->_form->setValues($this->getFormValues());

        return $this;
    }

    /**
     * Add additional data to form element
     *
     * @param AbstractElement $element
     * @return $this
     */
    protected function _addAdditionalFormElementData(AbstractElement $qelement)
    {
        switch ($qelement->getId()) {
            case 'email':
                $qelement->setRequired(0);
                $qelement->setClass('validate-email admin__control-text');
                break;
        }
        return $this;
    }
	public function getCustomerId()
	{
		return $this->_quoteSession->getCustomerId();
	}
    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        try {
            $qcustomer = $this->customerRepository->getById($this->_quoteSession->getCustomerId());
        } catch (\Exception $e) {
            /** If customer does not exist do nothing. */
        }
		
        $data = isset($qcustomer) ? $this->_extensibleDataObjectConverter->toFlatArray($qcustomer, [], \Magento\Customer\Api\Data\CustomerInterface::class) : [];
        foreach ($this->getQuote()->getData() as $key => $value) {
            if (strpos($key, 'customer_') === 0) {
                $data[substr($key, 9)] = $value;
            }
        }

      //  if ($this->getQuote()->getCustomerEmail()) {			
        if ($this->_quoteSession->getCustomerId()) {			
            $data['email'] = $qcustomer->getEmail();
        }
		else
		{
			if($this->_backendSession->getCurrentQuoteId())
			{
			$quote_id=$this->_backendSession->getCurrentQuoteId();
				if($quote_id)
				{
					$quote_customer=$this->quoteHelper->loadQuoteCustomerByQuoteId($quote_id);
					$email=$quote_customer->getEmail();
					 $data['email'] =$email;
				}
			}
		}

        return $data;
    }
}
