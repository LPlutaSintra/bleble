<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\View;

class Info extends \Magento\Backend\Block\Widget
{
	 public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,  
		  \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		    \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
  
        $this->_coreRegistry = $registry;
		 $this->quoteHelper = $quoteHelper;
		  $this->groupRepository = $groupRepository;
		 $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    public function getQuoteFiles()
	{
		$quote_id=$this->getQuote()->getId();
		return $this->quoteHelper->getQuoteFiles($quote_id);
	}
    public function getQuote()
    {
        return $this->_coreRegistry->registry('current_quote');
    }
 public function getQuoteDataJson()
    {
        $data = [];
        if ($this->getCustomerId()) {
            $data['customer_id'] = $this->getCustomerId();
            $data['addresses'] = [];

            $quoteaddresses = $this->customerRepository->getById($this->getCustomerId())->getAddresses();

            foreach ($quoteaddresses as $quoteaddress) {
                $quoteaddressForm = $this->_customerFormFactory->create(
                    'customer_address',
                    'adminhtml_customer_address',
                    $this->addressMapper->toFlatArray($quoteaddress)
                );
                $data['addresses'][$quoteaddress->getId()] = $quoteaddressForm->outputData(
                    \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON
                );
            }
        }
        if ($this->getStoreId() !== null) {
            $data['store_id'] = $this->getStoreId();
            $currency = $this->_localeCurrency->getCurrency($this->getStore()->getCurrentCurrencyCode());
            $symbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
            $data['currency_symbol'] = $symbol;
          
        }

        return $this->_jsonEncoder->encode($data);
    }
	 public function getLoadBlockUrl($quoteId)
    {
        return $this->getUrl('quotation/quote_create/loadBlock', ['quote_id' => $quoteId]);
    }

    /**
     * Retrieve source model instance *    
     */
    public function getSource()
    {
        return $this->getQuote();
    }
    /**
     * Get items html
     *
     * @return string
     */
    public function getItemsHtml()
    {
        return $this->getChildHtml('items');
    }

     public function getCustomerViewUrl()
    {
        if ( !$this->getQuote()->getCustomerId()) {
            return '';
        }

        return $this->getUrl('customer/index/edit', ['id' => $this->getQuote()->getCustomerId()]);
    }
	 public function getCustomerGroupName()
    {
        if ($this->getQuote()) {
            $customerId = $this->getQuote()->getCustomerId();
			$customer=$this->quoteHelper->loadCustomerById($customerId);
			$customerGroupId=$customer->getGroupId();
            try {
                if ($customerGroupId !== null) {
                    return $this->groupRepository->getById($customerGroupId)->getCode();
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return '';
            }
        }

        return '';
    }
	  public function getQuoteStoreName()
    {
        if ($this->getQuote()) {
            $storeId = $this->getQuote()->getStoreId();
            if ($storeId === null) {
                $deleted = __(' [deleted]');
                return nl2br($this->getQuote()->getStoreName()) . $deleted;
            }
            $store = $this->_storeManager->getStore($storeId);
            $name = [$store->getWebsite()->getName(), $store->getGroup()->getName(), $store->getName()];
            return implode('<br/>', $name);
        }

        return null;
    }
  /*public function getAddressEditLink($quoteaddressId, $label = '')
    {
      
            if (empty($label)) {
                $label = __('Edit');
            }
            $url = $this->getUrl('sales/order/address', ['address_id' => $quoteaddressId]);
            return '<a href="' . $this->escapeUrl($url) . '">' . $this->escapeHtml($label) . '</a>';
        

        return '';
    }*/
    public function getQuoteCreateAdminDate($createdAt)
    {
        return $this->_localeDate->date(new \DateTime($createdAt));
    }
	 public function getTimezoneForStore($storeId)
    {
		 $store = $this->_storeManager->getStore($storeId);
        return $this->_localeDate->getConfigTimezone(
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }
  
    public function getViewUrl($quoteId)
    {
        return $this->getUrl('quotation/*/*', ['quote_id' => $quoteId]);
    }

   
}
