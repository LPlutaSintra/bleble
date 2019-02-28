<?php

namespace Magebees\QuotationManagerPro\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
//use Magento\Quote\Api\Data\CartInterface;
use Magebees\QuotationManagerPro\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magebees\QuotationManagerPro\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magebees\QuotationManagerPro\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;

class QuoteRepository 
{
    
    protected $quotesById = [];
    protected $quotesByCustomerId = [];
    protected $quoteFactory;
    protected $storeManager;
    protected $quoteCollection;
 

   
    
    public function __construct(
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager,
        \Magebees\QuotationManagerPro\Model\ResourceModel\Quote\Collection $quoteCollection 
      
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;  
      
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId, array $sharedStoreIds = [])
    {
        if (!isset($this->quotesById[$cartId])) {
            $quote = $this->loadQuote('loadByIdWithoutStore', 'QuoteId', $cartId, $sharedStoreIds); 
            $this->quotesById[$cartId] = $quote;
        }
        return $this->quotesById[$cartId];
    }

    /**
     * {@inheritdoc}
     */
    public function getForCustomer($customerId, array $sharedStoreIds = [])
    {
        if (!isset($this->quotesByCustomerId[$customerId])) {
            $quote = $this->loadQuote('loadByCustomer', 'customerId', $customerId, $sharedStoreIds); 
            $this->quotesById[$quote->getId()] = $quote;
            $this->quotesByCustomerId[$customerId] = $quote;
        }
		
        return $this->quotesByCustomerId[$customerId];
    }

    /**
     * {@inheritdoc}
     */
    public function getActive($cartId, array $sharedStoreIds = [])
    {
        $quote = $this->get($cartId, $sharedStoreIds);
        if (!$quote->getIsActive()) {
			
           // throw NoSuchEntityException::singleField('QuoteId', $cartId);
        }
        return $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveForCustomer($customerId, array $sharedStoreIds = [])
    {
        $quote = $this->getForCustomer($customerId, $sharedStoreIds);
        if (!$quote->getIsActive()) {
           // throw NoSuchEntityException::singleField('customerId', $customerId);
        }
        return $quote;
    }
    
    public function delete(\Magebees\QuotationManagerPro\Model\Quote $quote)
    {
        $quoteId = $quote->getId();
        $customerId = $quote->getCustomerId();
        $quote->delete();
        unset($this->quotesById[$quoteId]);
        unset($this->quotesByCustomerId[$customerId]);
    }
   
    protected function loadQuote($loadMethod, $loadField, $identifier, array $sharedStoreIds = [])
    {
        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        if ($sharedStoreIds) {
            $quote->setSharedStoreIds($sharedStoreIds);
        }	
        $quote->$loadMethod($identifier)->setStoreId($this->storeManager->getStore()->getId());
        if (!$quote->getId()) {
           // throw NoSuchEntityException::singleField($loadField, $identifier);
        }
        return $quote;
    }    
}
