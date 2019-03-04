<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Store;

/**
 * Adminhtml sales quote create select store block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Select extends \Magento\Backend\Block\Store\Switcher
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sc_store_select');
    }
}
