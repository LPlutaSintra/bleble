<?php
namespace Magebees\QuotationManagerPro\Model;
use Magento\Framework\App\ObjectManager;

class DefaultQuote extends \Magento\Quote\Model\Quote
{	
	  public function getAllVisibleItems()
    {
        $qitems = [];
        foreach ($this->getItemsCollection() as $qitem) {
            if (!$qitem->isDeleted() && !$qitem->getParentItemId() && !$qitem->getParentItem() && !$qitem->getIsMagebeesItem()) {
                $qitems[] = $qitem;
            }
        }
        return $qitems;
    }

	 public function getAllItems()
    {
        $qitems = [];
        foreach ($this->getItemsCollection() as $qitem) {
            /** @var \Magento\Quote\Model\ResourceModel\Quote\Item $qitem */
            if (!$qitem->isDeleted() && !$qitem->getIsMagebeesItem()) {
                $qitems[] = $qitem;
            }
        }
        return $qitems;
    }
	 public function getItemsCollection($useCache = true)
    {
	
		$objectManager =ObjectManager::getInstance();           
		$qrequestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
		$routeName= $qrequestInterface->getRouteName();		
        if ($this->hasItemsCollection()) {
            return $this->getData('items_collection');
        }
        if (null === $this->_items) {
            
		if($routeName=='checkout')
		{			
			$this->_items = $this->_quoteItemCollectionFactory->create();
			 $this->_items->addFieldToFilter('is_magebees_item',0);
		}
		else if($routeName=='quotation')
		{
			
			$this->_items = $this->_quoteItemCollectionFactory->create();
			//$this->_items->addFieldToFilter('is_magebees_item',0);
		}
		else
		{			
		$objectManager =ObjectManager::getInstance();           
		$redirectInterface = $objectManager->get('\Magento\Framework\App\Response\RedirectInterface');
		$redirectUrl= $redirectInterface->getRedirectUrl();
		$currentUrl=$this->_storeManager->getStore()->getCurrentUrl();
		$this->_items = $this->_quoteItemCollectionFactory->create();
			 if ((preg_match('/quotation/', $redirectUrl)) ) {			
		//	$this->_items->addFieldToFilter('is_magebees_item',0);
			 }
			else if(preg_match('/checkout/', $redirectUrl))
			{
			$this->_items->addFieldToFilter('is_magebees_item',0);
			}
			else
			{
			$this->_items->addFieldToFilter('is_magebees_item',0);
			}			
			
		}
			
            $this->extensionAttributesJoinProcessor->process($this->_items);
            $this->_items->setQuote($this);
        }
		
        return $this->_items;
    }
	 public function addProduct(
        \Magento\Catalog\Model\Product $qproduct,
        $qrequest = null,
        $quoteProcessMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ) {
		  $objectManager =ObjectManager::getInstance();           
		$qrequestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
		$routeName= $qrequestInterface->getRouteName();	           
		$actionName= $qrequestInterface->getActionName();	           
		$redirectInterface = $objectManager->get('\Magento\Framework\App\Response\RedirectInterface');
		$redirectUrl= $redirectInterface->getRedirectUrl();
        if ($qrequest === null) {
            $qrequest = 1;
        }
        if (is_numeric($qrequest)) {
            $qrequest = $this->objectFactory->create(['qty' => $qrequest]);
        }
        if (!$qrequest instanceof \Magento\Framework\DataObject) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

        if (!$qproduct->isSalable()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Product that you are trying to add is not available.')
            );
        }

        $quoteCandidates = $qproduct->getTypeInstance()->prepareForCartAdvanced($qrequest, $qproduct, $quoteProcessMode);

        /**
         * Error message
         */
        if (is_string($quoteCandidates) || $quoteCandidates instanceof \Magento\Framework\Phrase) {
            return strval($quoteCandidates);
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($quoteCandidates)) {
            $quoteCandidates = [$quoteCandidates];
        }

        $qparentItem = null;
        $errors = [];
        $qitem = null;
        $qitems = [];
        foreach ($quoteCandidates as $qcandidate) {
            // Child items can be sticked together only within their parent
            $stickWithinParent = $qcandidate->getParentProductId() ? $qparentItem : null;
            $qcandidate->setStickWithinParent($stickWithinParent);

            $qitem = $this->getItemByProduct($qcandidate);
            if (!$qitem) {
				
                $qitem = $this->itemProcessor->init($qcandidate, $qrequest);
                $qitem->setQuote($this);
                $qitem->setOptions($qcandidate->getCustomOptions());
                $qitem->setProduct($qcandidate);
                // Add only item that is not in quote already
                $this->addItem($qitem);
            }
			else
			{
				
				if($routeName=='quotation')
				{
					
					$qitem = $this->itemProcessor->init($qcandidate, $qrequest);
                $qitem->setQuote($this);
                $qitem->setOptions($qcandidate->getCustomOptions());
                $qitem->setProduct($qcandidate);
                // Add only item that is not in quote already
                $this->addItem($qitem);
				}
			}
			if($routeName=='quotation')
			{
				
				$quoteHelper = $objectManager->get('Magebees\QuotationManagerPro\Helper\Quotation');
				$pos=strpos($redirectUrl,'quotation/quote/view');
				$isadmin=$quoteHelper->isAdmin();
				if (!($isadmin) && ($pos === false)&& ($actionName!='guestQuoteProcess')) {				
				$qitem->setIsMagebeesItem(1);
				}
				else
				{
					$qitem->setIsMagebeesItem(0);
				}
			}
            $qitems[] = $qitem;

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$qparentItem) {
                $qparentItem = $qitem;
            }
            if ($qparentItem && $qcandidate->getParentProductId() && !$qitem->getParentItem()) {
                $qitem->setParentItem($qparentItem);
            }

            $this->itemProcessor->prepare($qitem, $qrequest, $qcandidate);

            // collect errors instead of throwing first one
            if ($qitem->getHasError()) {
                foreach ($qitem->getMessage(false) as $message) {
                    if (!in_array($message, $errors)) {
                        // filter duplicate messages
                        $errors[] = $message;
                    }
                }
            }
        }
        if (!empty($errors)) {
            throw new \Magento\Framework\Exception\LocalizedException(__(implode("\n", $errors)));
        }

        $this->_eventManager->dispatch('sales_quote_product_add_after', ['items' => $qitems]);
        return $qparentItem;
    }

}