<?php
/**
 * Pdf config
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Model\Pdf;

/**
 * Class Config
 */
class Config
{
    /**
     * @var \Magento\Framework\Config\DataInterface
     */
    protected $_dataStorage;

    /**
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     */
    public function __construct(\Magento\Framework\Config\DataInterface $dataStorage)
    {
        $this->_dataStorage = $dataStorage;
    }

    /**
     * Get renderer configuration data by type
     *
     * @param string $pageType
     * @return array
     */
    public function getRenderersPerItem($pageType)
    {
        return $this->_dataStorage->get("renderers/{$pageType}", []);
    }

    /**
     * Get list of settings for showing totals in PDF
     *
     * @return array
     */
    public function getTotals()
    {
        return $this->_dataStorage->get('totals', []);
    }
}
