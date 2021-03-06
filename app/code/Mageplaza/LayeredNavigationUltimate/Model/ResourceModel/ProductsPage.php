<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Model\ResourceModel;

/**
 * Class ProductsPage
 * ProductsList resource
 * @package Mageplaza\LayeredNavigationUltimate\Model\ResourceModel
 */
class ProductsPage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	/**
	 * Initialize resource
	 *
	 * @return void
	 */
	public function _construct()
	{
		$this->_init('layered_product_pages', 'page_id');
	}

	/**
	 * @param \Magento\Framework\Model\AbstractModel $object
	 * @return $this
	 */
	protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
	{
		parent::_beforeSave($object);

		if (is_array($object->getStoreIds())) {
			$object->setStoreIds(implode(',', $object->getStoreIds()));
		}

		if (is_array($object->getPosition())) {
			$object->setPosition(implode(',', $object->getPosition()));
		}

		return $this;
	}
}
