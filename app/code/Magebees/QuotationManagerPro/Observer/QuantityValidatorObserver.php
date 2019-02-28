<?php
/**
 * Product inventory data validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Observer;

use Magento\Framework\Event\ObserverInterface;

class QuantityValidatorObserver implements ObserverInterface
{
    /**
     * @var \Magebees\QuotationManagerPro\Model\Quote\Item\QuantityValidator $quantityValidator
     */
    protected $quantityValidator;

    /**
     * @param \Magebees\QuotationManagerPro\Model\Quote\Item\QuantityValidator $quantityValidator
     */
    public function __construct(
        \Magebees\QuotationManagerPro\Model\Quote\Item\ItemQuantityValidator $quantityValidator
    ) {
        $this->quantityValidator = $quantityValidator;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->quantityValidator->validate($observer);
    }
}
