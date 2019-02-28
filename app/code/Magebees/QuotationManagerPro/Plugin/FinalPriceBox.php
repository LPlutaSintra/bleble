<?php
namespace Magebees\QuotationManagerPro\Plugin;
use Magento\Framework\App\Http\Context;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
class FinalPriceBox
{
	
	protected $_customercontext;
	protected $_context;
	protected $_customerSession;
	public function __construct(		
		\Magento\Customer\Model\Context $customercontext,
		\Magento\Framework\App\Http\Context $context,		
		\Magento\Customer\Model\Session $customerSession,
		\Magebees\QuotationManagerPro\Helper\Quotation $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
		$this->_context = $context;
		$this->_helper = $helper;		
		$this->_customercontext = $customercontext;
		$this->_customerSession = $customerSession;
		
    }
	function aroundToHtml($subject, callable $proceed)
	{
			$config=$this->_helper->getConfig();
			if($config['enable'])
			{			
			$_product = $subject->getSaleableItem();		
			$enable_price=$this->_helper->isEnablePriceCustGroupWise($_product);	
			$hideprice_config=$this->_helper->getHidePriceConfig();
			$text=$hideprice_config['hide_price_text'];
		if(isset($hideprice_config['hide_price_text_url']))
		{$text_url=$hideprice_config['hide_price_text_url'];
		}
			if($enable_price)
			{
				  return $proceed();
			}
			else
			{
				if(isset($text_url))
				{
					return '<a href='.$text_url.' target=_blank>'.$text.'</a>';
				}
				else					
				{
					 return $text;
				}
				
			}
			}
			else
			{
				return $proceed();
			}
	}
}
