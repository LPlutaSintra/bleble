<?php

namespace Magebees\QuotationManagerPro\Block\Quote;

use Magebees\QuotationManagerPro\Model\Quote;

class AbstractQuote extends \Magento\Framework\View\Element\Template
{
    /**
     * Block alias fallback
     */
    const DEFAULT_TYPE = 'default';

    protected $_quote = null;

    protected $_totals;

    protected $_itemRenders = [];
   
    protected $_customerSession;

    protected $_quotationSession;

    protected $visibilityEnabled;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magebees\QuotationManagerPro\Model\Session $quotationSession,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_quotationSession = $quotationSession;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    public function getItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    public function getQuote()
    {
        if (null === $this->_quote) {
            $this->_quote = $this->_quotationSession->getQuote();
        }
        return $this->_quote;
    }

    public function getItemHtml(\Magebees\QuotationManagerPro\Model\QuoteItem $item)
    {		
        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        return $renderer->toHtml();
    }

    public function getItemRenderer($type = null)
    {
        if ($type === null) {
            $type = self::DEFAULT_TYPE;
        }
        $rendererList = $this->_getRendererList();
        if (!$rendererList) {
            throw new \RuntimeException('Renderer list for block "' . $this->getNameInLayout() . '" is not defined');
        }
        $overriddenTemplates = $this->getOverriddenTemplates() ?: [];
        $template = isset($overriddenTemplates[$type]) ? $overriddenTemplates[$type] : $this->getRendererTemplate();
        return $rendererList->getRenderer($type, self::DEFAULT_TYPE, $template);
    }

    protected function _getRendererList()
    {
        return $this->getRendererListName() ? $this->getLayout()->getBlock(
            $this->getRendererListName()
        ) : $this->getChildBlock(
            'renderer.list'
        );
    }

    public function getTotals()
    {
        return $this->getTotalsCache();
    }

    public function getTotalsCache()
    {
        if (empty($this->_totals)) {
            $this->_totals = $this->getQuote()->getTotals();
        }
        return $this->_totals;
    }

   
}
