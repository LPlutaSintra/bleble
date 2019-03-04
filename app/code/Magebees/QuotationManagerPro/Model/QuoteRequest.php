<?php

namespace Magebees\QuotationManagerPro\Model;
use Magento\Framework\Api\AttributeValueFactory;

class QuoteRequest extends \Magento\Framework\Model\AbstractExtensibleModel
{
	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,  
		AttributeValueFactory $customAttributeFactory,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,	   
        array $data = []       
    ) {    
		 $this->quoteHelper = $quoteHelper;		
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,  
			$customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }
    protected function _construct()
    {
        $this->_init('Magebees\QuotationManagerPro\Model\ResourceModel\QuoteRequest');
    }
	public function afterSave()
    {
		/* update entry for quote updated time */
		$quote_id=$this->getQuoteId();
		if($quote_id)
		{
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$quote->setUpdatedAt($this->quoteHelper->getGmtTime());
		$quote->save();      
		}
        return parent::afterSave();
    }
}
