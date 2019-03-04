<?php
namespace Magebees\QuotationManagerPro\Helper;

class Address extends \Magento\Framework\App\Helper\AbstractHelper
{
   
	 public function __construct(  
		\Magento\Framework\App\Helper\Context $context,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,		 \Magebees\QuotationManagerPro\Model\QuoteAddressFactory $quoteAddressFactory,
		  \Magento\Customer\Model\AddressFactory $addressFactory		 
    ) {      
		$this->quoteHelper = $quoteHelper;
		 $this->_addressFactory = $addressFactory;
		 $this->_quoteAddressFactory = $quoteAddressFactory;
        parent::__construct($context);
    }
	
	public function saveAddressInBook($quote,$addressData)
	{
		//echo $quote->getCustomerId();die;
		$address = $this->_addressFactory->create();
 if(isset($addressData['region']))
	{
		$region=$addressData['region'];
		 $address->setRegion($region);
	}
	if(isset($addressData['region_id']))
	{
		$region_id=$addressData['region_id'];
		$address->setRegionId($region_id);
	}
$address->setCustomerId($quote->getCustomerId())
->setFirstname($addressData['firstname'])
->setLastname($addressData['lastname'])
->setCountryId($addressData['country_id'])
->setPostcode($addressData['postcode'])
->setCity($addressData['city'])
->setTelephone($addressData['telephone'])
->setStreet($addressData['street']);
$address->setSaveInAddressBook(1);	
$address->save();	
		$address_id=$address->getId();
		return $address_id;
	}
	public function saveCustomAddress($address_id,$quote,$address_type)
	{
		$customer_id=$quote->getCustomerId();
		$quote_id=$quote->getQuoteId();
		$customerData=$this->quoteHelper->loadCustomerById($customer_id);
		$addressArr=$this->quoteHelper->getAddressArray($address_id);
		$street=is_array($addressArr['street']) ? implode("\n", $addressArr['street']):null;
	$quote_address=$this->_quoteAddressFactory->create();		
	$quote_address->setQuoteId($quote_id);				
	$quote_address->setFirstname($addressArr['firstname']);
	$quote_address->setLastname($addressArr['lastname']);		
	$quote_address->setCustomerId($customer_id);	
	$quote_address->setCustomerAddressId($address_id);	
	$quote_address->setSaveInAddressBook(0);
	$quote_address->setAddressType($address_type);
	$quote_address->setEmail($customerData->getEmail());		
	$quote_address->setStreet($street);				
	$quote_address->setCity($addressArr['city']);		
	$quote_address->setCountryId($addressArr['country_id']);
	$quote_address->setPostcode($addressArr['postcode']);		
	$quote_address->setTelephone($addressArr['telephone']);		
	$quote_address->setIsDefaultAddress(1);		
	//$quote_address->setIsCustom(0);		
	$quote_address->setRegion($addressArr['region']);		
	$quote_address->setRegionId($addressArr['region_id']);	
			
	$quote_address->save();
		
	}
	
}
