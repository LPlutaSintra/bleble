<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Items;

/**
 * Abstract quote items renderer
 * 
 */
class AbstractItems extends \Magento\Backend\Block\Template
{
   
    const DEFAULT_TYPE = 'default';    
    protected $_canEditQty;
    protected $_coreRegistry;
    protected $stockRegistry;
    protected $stockConfiguration;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,		
		\Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem\CollectionFactory $quoteItemFactory,
		\Magebees\QuotationManagerPro\Helper\Admin $backendHelper,
        array $data = []
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->stockConfiguration = $stockConfiguration;
        $this->_coreRegistry = $registry;
        $this->backendHelper = $backendHelper;
		$this->_quoteItemFactory = $quoteItemFactory; 
        parent::__construct($context, $data);
    }

	 public function getItemsCollection()
    {
       return $this->backendHelper->getItemsCollection();
    }
  

    /**
     * Retrieve item renderer block    
     */
    public function getItemRenderer($type)
    {
      
        $renderer = $this->getChildBlock($type) ?: $this->getChildBlock(self::DEFAULT_TYPE);
        if (!$renderer instanceof \Magento\Framework\View\Element\BlockInterface) {
            throw new \RuntimeException('Renderer for type "' . $type . '" does not exist.');
        }
        $renderer->setColumnRenders($this->getLayout()->getGroupChildNames($this->getNameInLayout(), 'column'));

        return $renderer;
    }
 
    /**
     * Retrieve rendered item html content     
     */
    public function getItemHtml(\Magebees\QuotationManagerPro\Model\QuoteItem $item)
    {
        if ($item->getOrderItem()) {
            $type = $item->getOrderItem()->getProductType();
        } else {
            $type = $item->getProductType();			
        }

        return $this->getItemRenderer($type)->setItem($item)->toHtml();
    }

    /**
     * Retrieve available Quote   
     */
    public function getQuote()
    {
       return $this->backendHelper->getQuote();
    }

   

    


}
