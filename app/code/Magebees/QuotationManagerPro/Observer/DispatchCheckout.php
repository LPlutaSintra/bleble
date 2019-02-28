<?php

namespace Magebees\QuotationManagerPro\Observer;

use Magento\Framework\Event\ObserverInterface;

class DispatchCheckout implements ObserverInterface
{
	
	 public function __construct(
        \Magento\Checkout\Model\Session $session,
		 \Magento\Framework\Session\SessionManager $default_session ,
        \Magento\Quote\Api\Data\AddressInterface $address,
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $shippingInformation,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		  \Magebees\QuotationManagerPro\Helper\Address $addressHelper,
        \Magento\Framework\Url $url,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\App\Response\Http $http
    )
    {
        $this->session = $session;
        $this->default_session = $default_session;
        $this->address = $address;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
        $this->quoteRepository = $quoteRepository;       
        $this->url = $url;
        $this->messageManager = $messageManager;
        $this->http = $http;
		 $this->quoteHelper = $quoteHelper;
			$this->addressHelper = $addressHelper;
    }
	   public function execute(\Magento\Framework\Event\Observer $observer) {		
		  
		    $this->saveShippingInformation();       	
        }
	 public function saveShippingInformation()
    {
       $cartId = $this->session->getQuote()->getId();
                $shippingAddress = $this->getShippingAddressInformation();
         $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingAddress);
            
    }
    public function getShippingAddressInformation() {
        
		 $quote_id=$this->default_session->getProcessingQuoteId();
        $collectionPointResponse=$this->quoteHelper->IsCustomShipAddressAvail($quote_id);
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
	//	$address_id=$this->addressHelper->saveAddressInBook($quote,$collectionPointResponse);
        $shippingAddress = $this->prepareShippingAddress($collectionPointResponse);
        $address = $this->shippingInformation->setShippingAddress($shippingAddress)
			//->setCustomerAddressId($address_id)
            ->setShippingCarrierCode('flatrate')
            ->setShippingMethodCode('flatrate');
        return $address;
    }

    /* prepare shipping address from your custom shipping address */
    protected function prepareShippingAddress($quoteAddress) {
		
        
        $firstName = $quoteAddress['firstname'];
        $lastName = $quoteAddress['lastname'];
        $countryId = $quoteAddress['country_id'];
        $pincode = $quoteAddress['postcode'];
        $region = $quoteAddress['region'];
        $street = $quoteAddress['street'];
        $city = $quoteAddress['city'];
        $telephone = $quoteAddress['telephone'];
        $regionId =  $quoteAddress['region_id'];
        $address = $this->address
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setStreet($street)
            ->setCity($city)
            ->setCountryId($countryId)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setPostcode($pincode)
            ->setTelephone($telephone)          
           // ->setSaveInAddressBook(1)            
            ->setSameAsBilling(0);
        return $address;
    }

    


   
}
