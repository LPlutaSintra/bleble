<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Model\Pdf\Total;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Default total model
     *
     * @var string
     */
    protected $_defaultTotalModel = \Magebees\QuotationManagerPro\Model\Pdf\Total\DefaultTotal::class;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create instance of a total model
     *
     * @param string|null $class
     * @param array $arguments
     * @return \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($class = null, $arguments = [])
    {
        $class = $class ?: $this->_defaultTotalModel;
        if (!is_a($class, \Magebees\QuotationManagerPro\Model\Pdf\Total\DefaultTotal::class, true)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'The PDF total model %1 must be or extend \Magebees\QuotationManagerPro\Model\Pdf\Total\DefaultTotal.',
                    $class
                )
            );
        }
        return $this->_objectManager->create($class, $arguments);
    }
}
