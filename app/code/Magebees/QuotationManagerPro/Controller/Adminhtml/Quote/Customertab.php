<?php

namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;
 
class Customertab extends \Magento\Customer\Controller\Adminhtml\Index
{
    public function execute()
    {
       
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}