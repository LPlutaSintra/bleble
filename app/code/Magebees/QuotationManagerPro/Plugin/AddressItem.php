<?php
namespace Magebees\QuotationManagerPro\Plugin;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
//use Magento\Framework\App\ObjectManager;

class AddressItem
{
	
	 public function __construct(
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
		 \Magento\Framework\App\Response\RedirectInterface $redirect,
		  JoinProcessorInterface $extensionAttributesJoinProcessor,
		\Magebees\QuotationManagerPro\Helper\Quotation $helper
    ) {
		
		$this->_helper = $helper;	
		$this->redirect = $redirect;	
		   $this->_quoteItemCollectionFactory = $quoteItemCollectionFactory;
		   $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        
    }
	   public function beforeGetAllItems(\Magento\Quote\Model\Quote\Address $subject)
    { 
	
		 // We calculate item list once and cache it in three arrays - all items
        $key = 'cached_items_all';
		$quoteItems=array();
		 $objectManager =ObjectManager::getInstance();  
		$qrequestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
		$actionName= $qrequestInterface->getActionName();	
        if (!$subject->hasData($key)) {			
		$redirectUrl= $this->redirect->getRedirectUrl();		
		if ((preg_match('/quotation/', $redirectUrl)) ) 	
		{
			$pos=strpos($redirectUrl,'quotation/quote/view');	
			$isadmin=$this->_helper->isAdmin();
			if (!($isadmin) && ($pos === false) ) {
				$qItems = $this->_quoteItemCollectionFactory->create();
				if(($actionName!='guestQuoteProcess'))
				{
		 $qItems->addFieldToFilter('is_magebees_item',1);
				}
				else
				{
					 $qItems->addFieldToFilter('is_magebees_item',0);
				}
		  $this->extensionAttributesJoinProcessor->process($qItems);
	   $quoteItems=$qItems->setQuote($subject->getQuote());
			
		}
		else
		{
			$qItems = $subject->getQuote()->getItemsCollection();
			foreach($qItems as $i)
			{
				if(!$i->getIsMagebeesItem())
				 {
					 $quoteItems[] = $i;
				}

			}
		
		}
		}
		else
		{
 			$qItems = $subject->getQuote()->getItemsCollection();
			foreach($qItems as $i)
			{
				if(!$i->getIsMagebeesItem())
				 {
					 $quoteItems[] = $i;
				}

			}
		}
          //  $qItems = $subject->getQuote()->getItemsCollection();
			
            $addressItems = $subject->getItemsCollection();

            $items = [];
            if ($subject->getQuote()->getIsMultiShipping() && $addressItems->count() > 0) {
                foreach ($addressItems as $aItem) {
                    if ($aItem->isDeleted()) {
                        continue;
                    }

                    if (!$aItem->getQuoteItemImported()) {
                        $qItem = $subject->getQuote()->getItemById($aItem->getQuoteItemId());
                        if ($qItem) {
                            $aItem->importQuoteItem($qItem);
                        }
                    }
                    $items[] = $aItem;
                }
            } else {
                /*
                 * For virtual quote we assign items only to billing address, otherwise - only to shipping address
                 */
                $addressType = $subject->getAddressType();
                $canAddItems = $subject->getQuote()->isVirtual()
                    ? $addressType == AbstractAddress::TYPE_BILLING
                    : $addressType == AbstractAddress::TYPE_SHIPPING;

                if ($canAddItems) {
                    foreach ($quoteItems as $qItem) {
                        if ($qItem->isDeleted()) {
                            continue;
                        }
                        $items[] = $qItem;
                    }
                }
            }

            // Cache calculated lists
            $subject->setData('cached_items_all', $items);
        }

        $items = $subject->getData($key);

        return $items;
	}
}
