<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\View;

class Items extends \Magebees\QuotationManagerPro\Block\Adminhtml\Items\AbstractItems
{
  
	protected $_items;
	    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
		\Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem\CollectionFactory $quoteItemFactory,
		\Magebees\QuotationManagerPro\Helper\Admin $backendHelper,
			\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
        array $data = []
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->stockConfiguration = $stockConfiguration;
        $this->_coreRegistry = $registry;
		$this->_quoteItemFactory = $quoteItemFactory; 
			 $this->backendHelper = $backendHelper;
		$this->quoteHelper = $quoteHelper;
        parent::__construct($context,$stockRegistry,$stockConfiguration,$registry,$quoteItemFactory,$backendHelper);
    }
	
	
    public function getColumns()
    {
        $columns = array_key_exists('columns', $this->_data) ? $this->_data['columns'] : [];
        return $columns;
    }

	
   
}
