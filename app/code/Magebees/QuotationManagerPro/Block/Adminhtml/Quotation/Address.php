<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation;

class Address extends \Magento\Backend\Block\Widget\Form\Container
{
   
    protected $_coreRegistry = null;
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,		\Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteSession,
		\Magebees\QuotationManagerPro\Helper\Admin $backendHelper,	
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
		$this->_quoteSession = $quoteSession; 
		$this->backendHelper = $backendHelper;		
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_quotation';
        $this->_mode = 'address';
        $this->_blockGroup = 'Magebees_QuotationManagerPro';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Quote Address'));
        $this->buttonList->remove('delete');
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $address = $this->_coreRegistry->registry('quote_address');
        $orderId = $address->getOrder()->getIncrementId();
        if ($address->getAddressType() == 'shipping') {
            $type = __('Shipping');
        } else {
            $type = __('Billing');
        }
        return __('Edit Order %1 %2 Address', $orderId, $type);
    }

    /**
     * Back button url getter
     *
     * @return string
     */
    public function getBackUrl()
    {
		//$quote_id=$this->_quoteSession->getQuoteId();
			//$quote_id=$this->backendHelper->getQuote()->getId();
			$quote_id=$this->_backendSession->getCurrentQuoteId();
        $address = $this->_coreRegistry->registry('quote_address');
        return $this->getUrl('quotation/quote/view', ['quote_id' => $quote_id]);
    }
}
