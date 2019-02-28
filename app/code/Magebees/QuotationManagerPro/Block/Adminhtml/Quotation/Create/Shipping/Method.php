<?php
namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Shipping;

class Method extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
   
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quotation_quote_create_shipping_method');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Shipping Method');
    }

    /**
     * Get header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-shipping-method';
    }
}
