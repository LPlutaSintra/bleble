<?php

namespace Magebees\QuotationManagerPro\Block\Checkout\Cart\Item\Renderer\Actions;
use Magebees\QuotationManagerPro\Helper\Quotation;
use Magento\Framework\View\Element\Template;

class Remove extends \Magebees\QuotationManagerPro\Block\Checkout\Cart\Item\Renderer\Actions\Generic
{
   
    protected $cartHelper;
   
    public function __construct(
        Template\Context $context,  
		 Quotation $quoteHelper,
        array $data = []
    ) {
         $this->quoteHelper = $quoteHelper;
        parent::__construct($context, $data);
    }

   
    public function getDeletePostJson()
    {
        return $this->quoteHelper->getDeletePostJson($this->getItem());
    }
}
