<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Address;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address
{
   
    protected $_template = 'Magebees_QuotationManagerPro::quote/view/address/form.phtml';
    protected $_coreRegistry = null;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $qorderCreate,
        PriceCurrencyInterface $qpriceCurrency,
        \Magento\Framework\Data\FormFactory $qformFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $qdataObjectProcessor,
        \Magento\Directory\Helper\Data $qdirectoryHelper,
        \Magento\Framework\Json\EncoderInterface $qjsonEncoder,
        \Magento\Customer\Model\Metadata\FormFactory $qcustomerFormFactory,
        \Magento\Customer\Model\Options $qoptions,
        \Magento\Customer\Helper\Address $qaddressHelper,
        \Magento\Customer\Api\AddressRepositoryInterface $qaddressService,
        \Magento\Framework\Api\SearchCriteriaBuilder $qcriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $qfilterBuilder,
        \Magento\Customer\Model\Address\Mapper $qaddressMapper,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
        \Magento\Framework\Registry $qregistry,
        array $data = []
    ) {
        $this->_coreRegistry = $qregistry;
		 $this->quoteHelper = $quoteHelper;
        parent::__construct(
            $context,
            $sessionQuote,
            $qorderCreate,
            $qpriceCurrency,
            $qformFactory,
            $qdataObjectProcessor,
            $qdirectoryHelper,
            $qjsonEncoder,
            $qcustomerFormFactory,
            $qoptions,
            $qaddressHelper,
            $qaddressService,
            $qcriteriaBuilder,
            $qfilterBuilder,
            $qaddressMapper,
            $data
        );
    }    
    protected function _getAddress()
    {
        return $this->_coreRegistry->registry('quote_address');
    }
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $this->_form->setId('edit_form');
        $this->_form->setMethod('post');
        $this->_form->setAction(
            $this->getUrl('quotation/quote/addressSave', ['address_id' => $this->_getAddress()->getId()])
        );
        $this->_form->setUseContainer(true);
        return $this;
    }

    public function getFormHeaderText()
    {
        return __('Quote Address Information');
    }

    public function getFormValues()
    {
        return $this->_getAddress()->getData();
    }

    protected function processCountryOptions(
        \Magento\Framework\Data\Form\Element\AbstractElement $countryElement,
        $storeId = null
    ) {
        
        $address = $this->_coreRegistry->registry('quote_address');
        if ($address !== null) {
         $address_data=$address->getData();
		$quote_id=$address_data['quote_id'];
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$storeId=$quote->getStoreId();
        }

        parent::processCountryOptions($countryElement, $storeId);
    }
}
