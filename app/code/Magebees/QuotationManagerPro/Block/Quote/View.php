<?php
namespace Magebees\QuotationManagerPro\Block\Quote;

class View extends \Magento\Framework\View\Element\Template
{
   
    protected $_coreRegistry;
    protected $urlHelper;    
    protected $quotationCartHelper;
    protected $resultForwardFactory;
    protected $_visibilityEnabled;
    protected $_customerSession;
	protected $_items;
	const DEFAULT_TYPE = 'default';

    public function __construct(
        \Magebees\QuotationManagerPro\Helper\Quotation $quotationHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,     
        \Magento\Catalog\Block\Product\Context $context,
		\Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem\CollectionFactory $quoteItemFactory, 
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->quotationHelper = $quotationHelper;
        $this->urlHelper = $urlHelper;       
        $this->_coreRegistry = $context->getRegistry();
        $this->_customerSession = $customerSession;
		$this->_quoteItemFactory = $quoteItemFactory; 
        parent::__construct($context, $data);
    } 
	public function getQuoteId()
	{
		$quote_id=$this->getRequest()->getParam('quote_id');
		return $quote_id;
	}

	public function getItems()
	{
		 if (!$this->_items) {
		  	$this->_items = $this->_quoteItemFactory->create();         	 	
			$quote_id=$this->getQuoteId();
			$this->_items->addFieldToFilter('quote_id',$quote_id);
			$this->_items->addFieldToFilter('parent_item_id', ['null' => true]);	
		 }
        	return $this->_items;
	}
	 protected function _prepareLayout()
    {
		 if ($this->getItems()) {
			 /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('quote_view_item_pager');
        if ($pagerBlock) {	
            $pagerBlock->setCollection($this->getItems());		
        }			 
		 }

        return parent::_prepareLayout();
    }
	
	  public function isPagerDisplayed()
    {
       
       // return true;
        return false;
    }
	 public function getPagerHtml()
    {
        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('quote_view_item_pager');
        return $pagerBlock ? $pagerBlock->toHtml() : '';
    }
	   public function getItemHtml(\Magebees\QuotationManagerPro\Model\QuoteItem $item)
    {
		
        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        return $renderer->toHtml();
    }

    /**
     * Retrieve item renderer block    
     */
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

    
}
