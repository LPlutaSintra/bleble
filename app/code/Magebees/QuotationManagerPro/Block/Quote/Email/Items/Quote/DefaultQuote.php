<?php

namespace Magebees\QuotationManagerPro\Block\Quote\Email\Items\Quote;

use  Magebees\QuotationManagerPro\Model\QuoteItem as QuoteItem;

class DefaultQuote extends \Magento\Framework\View\Element\Template
{
	
	 public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,			 
        array $data = []
    ) {
        
      
		$this->_productConfig = $productConfig;
				 
        parent::__construct(
            $context,          
            $data
        );
    }
   
    public function getQuote()
    {
        return $this->getItem()->getQuote();
    }

    /**
     * @return array
     */
    public function getItemOptions()
    {
        $qitemresult = [];
        if ($qitemoptions = $this->getItem()->getProductOptions()) {
            if (isset($qitemoptions['options'])) {
                $qitemresult = array_merge($qitemresult, $qitemoptions['options']);
            }
            if (isset($qitemoptions['additional_options'])) {
                $qitemresult = array_merge($qitemresult, $qitemoptions['additional_options']);
            }
            if (isset($qitemoptions['attributes_info'])) {
                $qitemresult = array_merge($qitemresult, $qitemoptions['attributes_info']);
            }
        }

        return $qitemresult;
    }

    
    public function getSku($qitem)
    {
        if ($qitem->getProductOptionByCode('simple_sku')) {
            return $qitem->getProductOptionByCode('simple_sku');
        } else {
            return $qitem->getSku();
        }
    }

    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }

    public function getItemPrice(QuoteItem $qitem)
    {
        $qblock = $this->getLayout()->getBlock('item_price');
        $qblock->setItem($qitem);
        return $qblock->toHtml();
    }
	 public function getFormatedOptionValue($itemoptValue)
    {
        /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
        $helper = $this->_productConfig;
        $params = [
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
        ];
        return $helper->getFormattedOptionValue($itemoptValue, $params);
    }
}
