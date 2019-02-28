<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Customer;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    
    protected $_salesConfig;
    protected $_quoteSession;
    protected $_catalogConfig;    
    protected $_customerFactory;
 	protected $_template = 'Magebees_QuotationManagerPro::widget/grid/extended.phtml';
   
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\CustomerFactory $productFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $sessionQuote,     
        array $data = []
    ) {
        $this->_customerFactory = $productFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->_quoteSession = $sessionQuote;      
        parent::__construct($context, $backendHelper, $data);
    }

   
    protected function _construct()
    {
        parent::_construct();		
        $this->setId('quotation_quote_create_customer_grid');  
       $this->setRowClickCallback('quote.selectCustomer.bind(quote)');  
		$entityid='entity_id';
        $this->setDefaultSort($entityid);
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }
    
    public function getQuote()
    {
        return $this->_quoteSession->getQuote();
    }

    protected function _prepareCollection()
    {
        $cust_collection = $this->_customerFactory->create()->getCollection();
        $cust_collection->addAttributeToSelect(
            'customer_id'
        );
        $this->setCollection($cust_collection);
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
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'index' => 'entity_id'
            ]
        );
        $first_name=$this->addColumn(
            'firstname',
            [
                'header' => __('First Name'),
				'sortable' => true,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',             
                'index' => 'firstname'
            ]
        );
		$last_name=$this->addColumn(
            'lastname',
            [
                'header' => __('Last Name'),
				'sortable' => true,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',             
                'index' => 'lastname'
            ]
        );
		  $email=$this->addColumn(
            'email',
            [
                'header' => __('Email'),
				'sortable' => true,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',             
                'index' => 'email'
            ]
        );
		 $created_in=$this->addColumn(
            'created_in',
            [
                'header' => __('Signed-up Point'),
				'sortable' => true,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',             
                'index' => 'created_in'
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
            ['block' => 'customer_grid', '_current' => true, 'collapse' => null]
        );
    }

  
   
}
