<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create;

/**
 * Adminhtml sales quote create search block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Customer extends \Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\AbstractCreate
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
       // $this->setId('sales_order_create_search');
        $this->setId('quotation_quote_create_customer');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Please select a customer');
    }

    public function getButtonsHtml()
    {      
       if ($this->_authorization->isAllowed('Magento_Customer::manage')) {
            $addButtonData = [
                'label' => __('Create New Customer'),
                'onclick' => 'quote.setCustomerId(false)',
                'class' => 'primary',
            ];
            return $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
                ->setData($addButtonData)
                ->toHtml();
        }
        return '';
    }
	 public function getHeaderCssClass()
    {
        return 'head-catalog-product';
    }
}
