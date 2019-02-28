<?php
/**
 * Quotation page Bundle item render block
 */
namespace Magebees\QuotationManagerPro\Block\Checkout\Cart\Item;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

class RenderBundle extends \Magebees\QuotationManagerPro\Block\Checkout\Cart\Item\Renderer
{
   
    protected $_downloadableProductConfiguration = null;
   
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        InterpretationStrategyInterface $messageInterpretationStrategy,
		\Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory,     
        array $data = []
    ) {
      
		$this->_quoteHelper = $quoteHelper;
		  $this->_quoteFactory = $quoteFactory;
        parent::__construct(
            $context,
            $productConfig,
			$quoteHelper,
            $checkoutSession,
            $imageBuilder,
            $urlHelper,
            $messageManager,
            $priceCurrency,
            $moduleManager,
            $messageInterpretationStrategy,
			$quoteFactory,
            $data
        );
    }

     public function getOptionList()
    {
        return $this->_quoteHelper->getBundleOptions($this->getItem());
    }
}
