<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\Controller\ResultFactory;

class shippingRate extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magento\Shipping\Model\Config $shipconfig,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper
    ) {
    
        parent::__construct($context);
		  $this->quoteHelper=$quoteHelper;
		  $this->shipconfig=$shipconfig;
		   $this->scopeConfig = $scopeConfig;
      
    }
    public function execute()
    {
		
        	$result = [];
			$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        	$params = $this->getRequest()->getParams();		
		 	
			if($params['shipping_add_id'])
			{
			
			$shipping_add_id=$params['shipping_add_id'];
					//print_r($shipping_add_id);die;
			$shipping_add=$this->quoteHelper->getAddressObjFromId($shipping_add_id);	
			$shipping_rate=$shipping_add->getAllShippingRates();
				$result['shipping_rate']=$shipping_rate;
			}
		  $activeCarriers = $this->shipconfig->getActiveCarriers();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            foreach($activeCarriers as $carrierCode => $carrierModel)
            {
               $options = array();
               if( $carrierMethods = $carrierModel->getAllowedMethods() )
               {
                   foreach ($carrierMethods as $methodCode => $method)
                   {
                        $code= $carrierCode.'_'.$methodCode;
                        $options[]=array('value'=>$code,'label'=>$method);

                   }
                   $carrierTitle =$this->scopeConfig->getValue('carriers/'.$carrierCode.'/title');

               }
                $methods[]=array('value'=>$options,'label'=>$carrierTitle);
            }
		$result['methods']=$methods;
			$resultJson->setData($result);
           		return $resultJson;	
    }
}
