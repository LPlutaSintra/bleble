<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml;

class Quotation extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_quotation';
        $this->_blockGroup = 'Magebees_QuotationManagerPro';
        $this->_headerText = __('Quotations');
        $this->_addButtonLabel = __('Create New Quote');
        parent::_construct();
		$customer_id=$this->getRequest()->getParam('id');
		if($customer_id)
		{
		$this->removeButton('add');
		}
    }
	   public function getCreateUrl()
    {
       return $this->getUrl('quotation/quote_create/start');
       
    }
}
