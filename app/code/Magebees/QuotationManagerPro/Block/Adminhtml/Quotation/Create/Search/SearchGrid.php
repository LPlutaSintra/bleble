<?php
namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Search;

class SearchGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_salesConfig;
    protected $_quoteSession;
    protected $_productConfig;
    protected $_productCollFactory;
	
 	protected $_template = 'Magebees_QuotationManagerPro::widget/grid/extended.phtml';
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $sessionQuote,     
        array $data = []
    ) {
        $this->_productCollFactory = $productFactory;
        $this->_productConfig = $catalogConfig;
        $this->_quoteSession = $sessionQuote;      
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();		
        $this->setId('quotation_quote_create_search_grid');   
       $this->setRowClickCallback('quote.productGridRowClick.bind(quote)');
        $this->setCheckboxCheckCallback('quote.productGridCheckboxCheck.bind(quote)');
        $this->setRowInitCallback('quote.productGridRowInit.bind(quote)');
		$entityid='entity_id';
        $this->setDefaultSort($entityid);
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    public function getStore()
    {
        return $this->_quoteSession->getStore();
    }

    public function getQuote()
    {
        return $this->_quoteSession->getQuote();
    }

    protected function _addColumnFilterToCollection($gridcol)
    {
        // Set custom filter for in product flag
        if ($gridcol->getId() == 'in_products') {
            $qproductIds = $this->_getSelectedProducts();
            if (empty($qproductIds)) {
                $qproductIds = 0;
            }
            if ($gridcol->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $qproductIds]);
            } else {
                if ($qproductIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $qproductIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($gridcol);
        }
        return $this;
    }

    /**
     * Prepare collection to be displayed in the grid
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $productattributes = $this->_productConfig->getProductAttributes();
        /* @var $productcollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productcollection = $this->_productCollFactory->create()->getCollection();
        $productcollection->setStore(
            $this->getStore()
        )->addAttributeToSelect(
            $productattributes
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'gift_message_available'
        );

        $this->setCollection($productcollection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $entity_id=$this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'header_css_class' => 'col-id id',
                'column_css_class' => 'col-id id',
                'index' => 'entity_id'
            ]
        );
        $name=$this->addColumn(
            'name',
            [
                'header' => __('Product Name'),
                'renderer' => \Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Search\Grid\Renderer\GridProduct::class,
                'index' => 'name'
            ]
        );
        $this->addColumn('sku',
						 [
							'header' => __('SKU'),
							'index' => 'sku'
						 ]
						);
		$current_currency_code=$this->getStore()->getCurrentCurrencyCode();
		$currency_rate=$this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode());
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'column_css_class' => 'price',
                'type' => 'currency',
                'currency_code' => $current_currency_code,
                'rate' => $currency_rate,
                'index' => 'price',
                'renderer' => \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price::class
            ]
        );
		$selected_products=$this->_getSelectedProducts();
        $this->addColumn(
            'in_products',
            [
                'header' => __('Select'),
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $selected_products,
                'index' => 'entity_id',
                'sortable' => false,
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select'
            ]
        );

        $this->addColumn(
            'qty',
            [
                'filter' => false,
                //'sortable' => false,
                'header' => __('Quantity'),
                'renderer' => \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Qty::class,
                'name' => 'qty',
                'inline_css' => 'qty',
                'type' => 'input',
                'validate_class' => 'validate-number',
                'index' => 'qty'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
             'quotation/quote_create/loadBlock',
            ['block' => 'search_grid', '_current' => true, 'collapse' => null]
        );
    }

    /**
     * Get selected products
     *
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('products', []);

        return $products;
    }

    /**
     * Add custom options to product collection
     *
     * @return $this
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->addOptionsToResult();
        return parent::_afterLoadCollection();
    }
}
