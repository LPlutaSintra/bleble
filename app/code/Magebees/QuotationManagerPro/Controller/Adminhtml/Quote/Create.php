<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

abstract class Create extends \Magento\Backend\App\Action
{
   
    protected $escaper;
    protected $resultPageFactory;
    protected $resultForwardFactory;
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,			
        ForwardFactory $resultForwardFactory,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		\Magebees\QuotationManagerPro\Helper\Email $emailHelper
    ) {
        parent::__construct($context);
        $productHelper->setSkipSaleableCheck(true);
        $this->escaper = $escaper;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
		$this->quoteHelper = $quoteHelper;
			$this->emailHelper = $emailHelper;
    }

    
    protected function _getSession()
    {		
       return $this->_objectManager->get(\Magebees\QuotationManagerPro\Model\Backend\Session\Quote::class);
    }
    protected function _getQuote()
    {
        return $this->_getSession()->getQuote();
    }
    protected function _getQuoteCreateModel()
    {
        return $this->_objectManager->get(\Magebees\QuotationManagerPro\Model\Backend\Quote\Create::class);
    }
	 protected function _getOrderCreateModel()
    {
        return $this->_objectManager->get(\Magento\Sales\Model\AdminOrder\Create::class);
    }
    protected function _initSession()
    {
        /**
         * Identify customer
         */
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            $this->_getSession()->setCustomerId((int)$customerId);
        }

        /**
         * Identify store
         */
        if ($storeId = $this->getRequest()->getParam('store_id')) {
            $this->_getSession()->setStoreId((int)$storeId);
        }

        /**
         * Identify currency
         */
        if ($currencyId = $this->getRequest()->getParam('currency_id')) {
            $this->_getSession()->setCurrencyId((string)$currencyId);
            $this->_getQuoteCreateModel()->setRecollect(true);
        }
        return $this;
    }
 	protected function _getGiftmessageSaveModel()
    {
        return $this->_objectManager->get(\Magento\GiftMessage\Model\Save::class);
    }	
    /**
     * Processing request data
     *
     * @return $this
     */
    protected function _processData()
    {
        return $this->_processActionData();
    }

     protected function _processActionData($action = null)
    {
		$quoteId=$this->_getSession()->getQuoteId();
		$quote=$this->quoteHelper->loadQuoteById($quoteId);
        $eventData = [
            'quote_create_model' => $this->_getQuoteCreateModel(),
            'request_model' => $this->getRequest(),
            'session' => $this->_getSession(),
        ];   
		
		 if ($this->getRequest()->getPost(
            'collect_shipping_rates'
        )
        ) {
            $this->_getOrderCreateModel()->collectShippingRates();
        }
		if ($data = $this->getRequest()->getPost('quote')) {
          if(isset($data['shipping_method']))
		  {
			  $quote->setShippingMethod($data['shipping_method']);
			  $quote->setShippingRateInclTax($data['applied_rate_incl_tax']);
			  $quote->setShippingRateExclTax($data['applied_rate_excl_tax']);
			  $quote->save();
		  }
        }
        /**
         * Adding product to quote from shopping cart, wishlist etc.
         */
        if ($productId = (int)$this->getRequest()->getPost('add_product')) {
			
            $this->_getQuoteCreateModel()->addProduct($productId, $this->getRequest()->getPostValue());
        }

        /**
         * Adding products to quote from special grid
         */
        if ($this->getRequest()->has('item') && !$this->getRequest()->getPost('update_items') && !($action == 'save')
        ) {
			
			if(!$quote->getIsBackend())
			{
				$send_mail=true;
			}
			else
			{
				$send_mail=false;
			}
            $items = $this->getRequest()->getPost('item');      
			 $items = $this->_processFiles($items);
            $this->_getQuoteCreateModel()->addQuoteProducts($items);
			
			/**Start for set the flag when product is added to new generated quote,so quote is not empty and will be display in frontend **/
			
			$quote->setIsBackend(true);	
			$quote->save();
			if($send_mail)
			{
				$email_config=$this->quoteHelper->getEmailConfig();
				if($email_config['quote_request'])
				{
				// send mail to customer after create new quote and add product to new quote
					$this->emailHelper->sendQuoteCreateMail($quoteId);
				}
			}
			/**End for set the flag when product is added to new generated quote***/
        }

        /**
         * Update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
			
            $items = $this->getRequest()->getPost('item', []);
            $items = $this->_processFiles($items);
            $this->_getQuoteCreateModel()->updateQuoteItemsData($items);			
        }

        $eventData = [
            'quote_create_model' => $this->_getQuoteCreateModel(),
            'request' => $this->getRequest()->getPostValue(),
        ];


        /**
         * Importing gift message allow items from specific product grid
         */
        if ($data = $this->getRequest()->getPost('add_products')) {
            $this->_getGiftmessageSaveModel()->importAllowQuoteItemsFromProducts(
                $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonDecode($data)
            );
        }

        /**
         * Importing gift message allow items on update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', []);
            $this->_getGiftmessageSaveModel()->importAllowQuoteItemsFromItems($items);
        }     
        return $this;
    }
	  protected function _processFiles($items)
    {
        /* @var $productHelper \Magento\Catalog\Helper\Product */
        $productHelper = $this->_objectManager->get(\Magento\Catalog\Helper\Product::class);
        foreach ($items as $id => $item) {
            $buyRequest = new \Magento\Framework\DataObject($item);
            $params = ['files_prefix' => 'item_' . $id . '_'];
            $buyRequest = $productHelper->addParamsToBuyRequest($buyRequest, $params);
            if ($buyRequest->hasData()) {
                $items[$id] = $buyRequest->toArray();
            }
        }
        return $items;
    }
   
}
