<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Block\Quote\Email\Items;
use Magebees\QuotationManagerPro\Model\QuoteItem as QuoteItem;
class DefaultItems extends \Magento\Framework\View\Element\Template
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
    public function getSku($item)
    {
        if ($item->getQuoteItem()->getProductOptionByCode('simple_sku')) {
            return $item->getQuoteItem()->getProductOptionByCode('simple_sku');
        } else {
            return $item->getSku();
        }
    }
	 public function getFormatedOptionValue($optionValue)
    {
        /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
        $helper = $this->_productConfig;
        $params = [
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
        ];
        return $helper->getFormattedOptionValue($optionValue, $params);
    }

    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }

    public function getItemPrice($item)
    {
        $block = $this->getLayout()->getBlock('item_price');
        $block->setItem($item);
        return $block->toHtml();
    }
}
