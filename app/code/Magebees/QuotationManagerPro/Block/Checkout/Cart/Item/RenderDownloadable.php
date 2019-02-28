<?php

namespace Magebees\QuotationManagerPro\Block\Checkout\Cart\Item;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

class RenderDownloadable extends \Magebees\QuotationManagerPro\Block\Checkout\Cart\Item\Renderer
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

    /**
     * Retrieves item links options    
     */
    public function getLinks()
    {
        if (!$this->getItem()) {
            return [];
        }
        return $this->_quoteHelper->getLinks($this->getItem());
    }

    /**
     * Return title of links section   
     */
    public function getLinksTitle()
    {
        return $this->_quoteHelper->getLinksTitle($this->getProduct());
    }

    /**
     * Get list of all options for product    
     */
    public function getOptionList()
    {
		
        return $this->_quoteHelper->getDownloadableOptions($this->getItem());
    }

    /**
     * Get list of all options for product 
     */
    public function getOption($item)
    {
        return $this->_quoteHelper->getOptions($item);
    }
}
