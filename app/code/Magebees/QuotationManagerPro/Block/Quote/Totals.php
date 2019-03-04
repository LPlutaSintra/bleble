<?php

namespace Magebees\QuotationManagerPro\Block\Quote;

use Magebees\QuotationManagerPro\Model\Quote;

class Totals extends \Magento\Framework\View\Element\Template
{
   
    protected $_totals;

    protected $_quote = null;

    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

   
    public function getQuote()
    {
        if ($this->_quote === null) {
            if ($this->hasData('quote')) {
                $this->_quote = $this->_getData('quote');
            } elseif ($this->_coreRegistry->registry('current_quote')) {
                $this->_quote = $this->_coreRegistry->registry('current_quote');
            } elseif ($this->getParentBlock()->getQuote()) {
                $this->_quote = $this->getParentBlock()->getQuote();
            }
        }
        return $this->_quote;
    }

  
    public function setQuote($quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    public function getSource()
    {
        return $this->getQuote();
    }

   
}
