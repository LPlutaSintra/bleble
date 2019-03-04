<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;

use Magento\Framework\Controller\ResultFactory;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressSave extends \Magento\Backend\App\Action
{
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,  		\Magebees\QuotationManagerPro\Model\QuoteAddressFactory $quoteAddressFactory,		\Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteSession,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper
    ) {
    
        parent::__construct($context);
        $this->_quoteAddressFactory = $quoteAddressFactory;
		 $this->quoteHelper = $quoteHelper;
		$this->_quoteSession = $quoteSession;  
		
    }
    public function execute()
    {
		$params=$this->getRequest()->getParams();
        $addressId = $this->getRequest()->getParam('address_id');
		$quote_id=$this->_quoteSession->getQuoteId();
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		if($addressId)
		{
			$address=$this->_quoteAddressFactory->create()->load($addressId,'address_id');
		}
		else
		{
			$address=$this->_quoteAddressFactory->create();
		}
		
        $data = $this->getRequest()->getPostValue();   
		$firstname=isset($data['firstname'])?$data['firstname']:null;
		$lastname=isset($data['lastname'])?$data['lastname']:null;
		$city=isset($data['city'])?$data['city']:null;
		$country_id=isset($data['country_id'])?$data['country_id']:null;
		$region=isset($data['region'])?$data['region']:null;
		$region_id=isset($data['region_id'])?$data['region_id']:null;
		$postcode=isset($data['postcode'])?$data['postcode']:null;
		$telephone=isset($data['telephone'])?$data['telephone']:null;
		$street=is_array($data['street']) ? implode("\n", $data['street']):null;
		//$vat_id=$data['vat_id']?$data['vat_id']:null;
		//$fax=$data['fax']?$data['fax']:null;
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
          //  $address->addData($data);
            try {
				
	$address->setFirstname($firstname);
	$address->setLastname($lastname);	
	$address->setStreet($street);				
	$address->setCity($city);		
	$address->setCountryId($country_id);
	$address->setPostcode($postcode);		
	$address->setTelephone($telephone);
	$address->setRegion($region);
	$address->setRegionId($region_id);
	$address->setQuoteId($quote_id);	
	//$address->setAddressType();	
	$address->setCustomerId($quote->getCustomerId());	
    $address->save();
               
                $this->messageManager->addSuccess(__('You updated the quote address.'));
                return $resultRedirect->setPath('quotation/quote/view', ['quote_id' => $address->getQuoteId()]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t update the order address right now.'));
            }
			if($addressId)
			{
           // return $resultRedirect->setPath('quotation/*/address', ['address_id' => $address->getId()]);
				return $resultRedirect->setPath('quotation/quote/view', ['quote_id' => $address->getQuoteId()]);
			}
			else
			{
			return $resultRedirect->setPath('quotation/*/');
			}
        } else {
            return $resultRedirect->setPath('quotation/*/');
        }
    }

}
