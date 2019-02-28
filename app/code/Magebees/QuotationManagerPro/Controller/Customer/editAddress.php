<?php
namespace Magebees\QuotationManagerPro\Controller\Customer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class editAddress extends \Magento\Framework\App\Action\Action
{ 
	
    protected $resultPageFactory;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,		
		\Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory,	\Magebees\QuotationManagerPro\Model\ResourceModel\QuoteAddress\CollectionFactory $quoteAddressCollFactory,	 
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper		
    ) {
        $this->resultPageFactory = $resultPageFactory;	
		 $this->_quoteFactory = $quoteFactory;
		  $this->_quoteAddressCollFactory = $quoteAddressCollFactory;
		$this->quoteHelper=$quoteHelper;
        parent::__construct($context);
    }	
    public function execute()
    {	
		
		   $result = [];
			$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);		 
			$params=$this->getRequest()->getParams();	
			$address_id=$params['address_id'];
			$quoteAddress= $this->_quoteAddressCollFactory->create(); 
			$quoteAddress->addFieldToFilter('address_id',$address_id);
			$quoteAddress_data=$quoteAddress->getData();
			if(count($quoteAddress_data))
			{
				$result['address_data']=$quoteAddress_data[0];
				$street=explode("\n",$quoteAddress_data[0]['street']);
				$result['street']=$street;
			}
			$resultJson->setData($result);
           		return $resultJson;	
    }
}
