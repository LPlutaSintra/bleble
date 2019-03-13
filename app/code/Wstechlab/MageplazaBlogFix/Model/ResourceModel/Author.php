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
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Wstechlab\MageplazaBlogFix\Model\ResourceModel;


/**
 * Class Author
 * @package Mageplaza\Blog\Model\ResourceModel
 */
class Author extends \Mageplaza\Blog\Model\ResourceModel\Author
{
    /**
     * @inheritdoc
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUrlKey(
            $this->helperData->generateUrlKey($this, $object, $object->getUrlKey() ?: $object->getName())
        );
        // die('123');
        // $now = new DateTime();
        if (!$object->isObjectNew()) {
        	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$date = $objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime');
        	// $date = Zend_Date::now();
			$timeStamp = $date->gmtDate();	
        	// \Zend_Debug::dump($timeStamp);die('aaa');
            $object->setUpdatedAt($timeStamp);
        }

        return $this;
    }
}
