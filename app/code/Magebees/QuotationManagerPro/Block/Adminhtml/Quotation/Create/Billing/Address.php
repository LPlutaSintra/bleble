<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Billing;

class Address extends \Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Form\QuoteAddressForm
{
    
    public function getHeaderText()
    {
        return __('Billing Address');
    }

    public function getHeaderCssClass()
    {
        return 'head-billing-address';
    }

    protected function _prepareForm()
    {
        $this->setJsVariablePrefix('billingAddress');
        parent::_prepareForm();

        $this->_form->addFieldNameSuffix('quote[billing_address]');
        $this->_form->setHtmlNamePrefix('quote[billing_address]');
        $this->_form->setHtmlIdPrefix('quote-billing_address_');

        return $this;
    }

    public function getFormValues()
    {
		$address=$this->getAddress();		
		return $address;
    }   
    public function getAddressId()
    {
    
		$address=$this->getAddress();
		$id=isset($address['customer_address_id'])?$address['customer_address_id']:'';
		 return $id;
    }

    public function getAddress()
    {		
		return $this->getQuoteBillingAddress();		
    }
}
