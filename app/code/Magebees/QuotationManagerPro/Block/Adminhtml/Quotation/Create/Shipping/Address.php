<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Shipping;

class Address extends \Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Form\QuoteAddressForm
{
   
    public function getHeaderText()
    {
        return __('Shipping Address');
    }

    public function getHeaderCssClass()
    {
        return 'head-shipping-address';
    }

    protected function _prepareForm()
    {
        $this->setJsVariablePrefix('shippingAddress');
        parent::_prepareForm();

        $this->_form->addFieldNameSuffix('quote[shipping_address]');
        $this->_form->setHtmlNamePrefix('quote[shipping_address]');
        $this->_form->setHtmlIdPrefix('quote-shipping_address_');

        return $this;
    }
    
    public function getIsShipping()
    {
        return true;
    }

    public function getIsAsBilling()
    {
        return $this->getCreateOrderModel()->getShippingAddress()->getSameAsBilling();
    }

    public function getDontSaveInAddressBook()
    {
        return $this->getIsAsBilling();
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
		return $this->getQuoteShippingAddress();
    }
   
    public function getIsDisabled()
    {
        return $this->getQuote()->isVirtual();
    }
}
