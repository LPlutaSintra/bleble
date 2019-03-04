<?php

namespace  Magebees\QuotationManagerPro\Block\Adminhtml\Quotation;

use Magento\Store\Model\Store;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
		\Magento\Backend\Model\Auth\Session $authSession, 
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
          \Magebees\QuotationManagerPro\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory,  \Magento\Framework\App\ResourceConnection $resourceConnection,
        array $data = []
    ) {
        
         $this->_quoteCollectionFactory = $_quoteCollectionFactory;
		$this->authSession = $authSession;
		$this->quoteHelper = $quoteHelper;
		 $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $backendHelper, $data);
    }
    protected function _construct()
    {
        parent::_construct();
        $this->setId('QuotationGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    
    protected function _prepareCollection()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
		$customer_id=$this->getRequest()->getParam('id');
		
		$current_admin_id=$this->getCurrentUser()->getId();
	
           $collection = $this->_quoteCollectionFactory->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'main_table.is_active',
               0
            );
		$config=$this->quoteHelper->getConfig();
		$access_user=$config['access_quote_user'];
		if($access_user==1)
		{
			$collection->addFieldToFilter(
                'main_table.assign_to',
             	$current_admin_id
              //array('like' => '%' .$current_admin_id. '%')
            );
		}
		
		if($customer_id)
		{
			$collection->addFieldToFilter(
                'main_table.customer_id',
             	$customer_id
            );
		}
        $this->setCollection($collection);
		
		 /*$collection->getSelect()
       ->joinLeft(
           array('customer' => $this->resourceConnection->getTableName('customer_entity')),
           'main_table.customer_id = customer.entity_id',
           array('lastname' => 'customer.lastname','firstname' => 'customer.firstname','email' => 'customer.email')
       );*/
		 $collection->getSelect()
       ->joinLeft(
           array('customer' => $this->resourceConnection->getTableName('magebees_quote_customer')),
           'main_table.quote_id = customer.quote_id',
           array('lastname' => 'customer.lname','firstname' => 'customer.fname','email' => 'customer.email')
       );
    	$collection->addFilterToMap('lastname', 'customer.lname');
    	$collection->addFilterToMap('email', 'customer.email');
    	$collection->addFilterToMap('firstname', 'customer.fname');
    	$collection->addFilterToMap('quote_id', 'main_table.increment_id');
    	$collection->addFilterToMap('created_at', 'main_table.created_at');
    	$collection->addFilterToMap('store_id', 'main_table.store_id');
    	$collection->addFilterToMap('status', 'main_table.status');
		
        return parent::_prepareCollection();
    }
    
    
    protected function _prepareMassaction()
    {
        $customer_id=$this->getRequest()->getParam('id');
		if(!$customer_id){
        $this->setMassactionIdField('quote_id');
        $this->getMassactionBlock()->setFormFieldName('quotation');
    
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                        'label' => __('Delete'),
                        'url' => $this->getUrl('quotation/*/massDelete'),
                        'confirm' => __('Are you sure want to delete?')
                ]
        );
		}
        
        return $this;
    }
    protected function _prepareColumns()
    {
		$customer_id=$this->getRequest()->getParam('id');
        $this->addColumn(
            'quote_id',
            [
                        'header' => __('ID'),
                        'type' => 'text',
                        'sortable' =>true,
                        'index' => 'quote_id',
			 			'frame_callback'=>[$this,'getIncrementId'],
                        'header_css_class' => 'col-id',
                        'column_css_class' => 'col-id'
                ]
        );
		$this->addColumn(
            'quote_assign_to',
            [
                        'header' => __('Quote Assign To'),
                        'type' => 'text',
                        'sortable' =>true,
                        'index' => 'assign_to',
			 			'frame_callback'=>[$this,'getUserDetail'],
                        'header_css_class' => 'col-id',
                        'column_css_class' => 'col-id'
                ]
        );
		if(!$customer_id)
		{
		$this->addColumn(
            'customer_first_name',
            [
                        'header' => __('Customer First Name'),
                        'type' => 'text',                      
                        'index' => 'firstname',			 			
                        'header_css_class' => 'col-id',
                        'column_css_class' => 'col-id'
                ]
        );
		$this->addColumn(
            'customer_last_name',
            [
                        'header' => __('Customer Last Name'),
                        'type' => 'text',                       
                        'index' => 'lastname',			 			
                        'header_css_class' => 'col-id',
                        'column_css_class' => 'col-id'
                ]
        );
				$this->addColumn(
            'customer_email',
            [
                        'header' => __('Email'),
                        'type' => 'text',                       
                        'index' => 'email',			 			
                        'header_css_class' => 'col-id',
                        'column_css_class' => 'col-id'
                ]
        );
		}
	
			$this->addColumn(
            'created_at',
            [
                        'header' => __('Quote Requested At'),
                        'type' => 'datetime',                       
                        'index' => 'created_at',			 			
                        'header_css_class' => 'col-id',
                        'column_css_class' => 'col-id'
                ]
        );
		
        	$this->addColumn(
                    'store_id',
                    [
                        'header' => __('Store Views'),
                        'index' => 'store_id',                        
                        'type' => 'store',
                        'store_all' => true,
                        'store_view' => true,
                        'renderer'=>  'Magento\Backend\Block\Widget\Grid\Column\Renderer\Store',
                        'filter_condition_callback' => [$this, '_filterStoreCondition']
                    ]
                );
      
        $this->addColumn(
            'status',
            [
                        'header' => __('Status'),
                        'type' => 'options',
                        'index' => 'status',
                        'sortable' =>true,
                        'header_css_class' => 'col-id',
                        'column_css_class' => 'col-id',                      
                        'options' => [ 
									  '0'=>'ALL',
									  '10' => 'NEW REQUESTED',
									  '11' => 'NEW REQUESTED ACTION FOR STORE OWNER',
									  '12' => 'NEW REQUESTED ACTION FOR CUSTOMER',
									  '20' => 'PROPOSAL PROCESSING',
									  '21' => 'PROPOSAL PROCESSING ACTION STORE OWNER',
									  '22' => 'PROPOSAL PROCESSING ACTION CUSTOMER',
									  '30' => 'PROPOSAL CREATED',
									  '40' => 'PROPOSAL SENT',
									  '41' => 'PROPOSAL SENT ACTION STORE OWNER',
									  '42' => 'PROPOSAL SENT ACTION CUSTOMER',
									  '50' => 'PROPOSAL CANCELLED',
									  '51' => 'PROPOSAL CANCELLED OUTOF STOCK',
									  '60' => 'PROPOSAL REJECTED',
									  '70' => 'PROPOSAL ACCEPTED',									 
									  '80' => 'PROPOSAL ORDERED'
									  ],
			 		'filter_condition_callback' => [$this, '_filterStatusCondition']
                ]
        );
	
      
         $this->addColumn(
             'edit',
             [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View Quotation'),
                        'url' => [
                            'base' => '*/*/view',
                            'params' => ['store' => $this->getRequest()->getParam('store')]
                        ],
                        'field' => 'quote_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
             ]
         );

        return parent::_prepareColumns();
    }
	
	 public function getIncrementId($value, $row, $column, $isExport)
    {        
     	$quote_id=$value;			
		$quote_arr=$this->getQuoteData($quote_id);
        return $quote_arr['increment_id'];
    }
	 public function getUserDetail($value, $row, $column, $isExport)
    {        
     	$user_ids=$value;	
		$user_ids_arr=explode(',',$user_ids);
		$name_arr=[];
		foreach($user_ids_arr as $user_id)
		{			
		$user_data=$this->quoteHelper->getUserInfoById($user_id);
		$name_arr[]=$user_data->getUserName();
		}
		$admins=implode(",",$name_arr);
		return $admins;
       
    }	
	
	public function getQuoteData($quote_id)
	{
		$quote=$this->quoteHelper->getQuoteDataById($quote_id);
		$quote_data=$quote->getData();
		$quote_arr=reset($quote_data);
		return $quote_arr;
	}
    public function getGridUrl()
    {
        return $this->getUrl('quotation/quote/grid', ['_current' => true]);
    }
	protected function _filterStoreCondition($collection, $column){

         if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addFieldToFilter('store_id', array('finset' => $value));
    }
	protected function _filterStatusCondition($collection, $column){

         if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addFieldToFilter('status', array('finset' => $value));
    }
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'quotation/*/view',
            ['store' => $this->getRequest()->getParam('store'), 'quote_id' => $row->getId()]
        );
    }
	public function getCurrentUser()
	{
		return $this->authSession->getUser();
	}
}
