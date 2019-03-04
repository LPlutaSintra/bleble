<?php

namespace Magebees\QuotationManagerPro\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class Quote extends \Magento\Framework\DataObject implements SectionSourceInterface
{
   
   
    protected $catalogUrl;
    protected $quote = null;
    protected $checkoutHelper;
    protected $itemPoolInterface;
    protected $summeryCount;
    protected $layout;

   
    public function __construct(
        \Magebees\QuotationManagerPro\Model\Session $quoteSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magebees\QuotationManagerPro\Model\Quote $quote,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        ItemPoolInterface $itemPoolInterface,
        \Magento\Framework\View\LayoutInterface $layout,
		\Magebees\QuotationManagerPro\Helper\Admin $backendHelper,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
        array $data = []
    ) {
        parent::__construct($data);    
        $this->_quoteSession = $quoteSession;
        $this->catalogUrl = $catalogUrl;
        $this->quote = $quote;
        $this->checkoutHelper = $checkoutHelper;
        $this->itemPoolInterface = $itemPoolInterface;
        $this->layout = $layout;
        $this->backendHelper = $backendHelper;
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
		$item_price_arr=$this->getSubtotalInclTax();		
		$quote_id=$this->_quoteSession->getQuoteId();
		$total_data=$this->backendHelper->getDefaultTotalData($quote_id);
		$row_total=$total_data['row_total'];				$row_total_incl_tax=$total_data['row_total_incl_tax'];
		$formated_row_total=$this->quoteHelper->getFormatedPrice($row_total);
		$formated_row_total_incl_tax=$this->quoteHelper->getFormatedPrice($row_total_incl_tax);
		$frontend_config=$this->quoteHelper->getFrontendConfig();
		$tax_config=$this->quoteHelper->getTaxConfig();
		$price_tax=$tax_config['price_tax'];
		//$enable_qprice=$frontend_config['enable_qprice'];
		$enable_qprice=$this->quoteHelper->isShowPriceWithoutProduct();
		$show_price=($enable_qprice==1) ? true:false;
		$subtotal=($enable_qprice==1) ? $formated_row_total:null;
		$subtotal_incl_tax=($enable_qprice==1) ? $formated_row_total_incl_tax:null;
		$show_total_incl_tax=(($price_tax==1)&&($item_price_arr['item_price']!=$item_price_arr['item_price_incl_tax'])) ?true:false;
		 return [
            'summary_count' => $this->getSummaryCount(),
			'subtotal' =>$subtotal,
			'subtotal_incl_tax' =>'Incl.Tax'.$subtotal_incl_tax,
			'display_price_subtotal'=>$show_price,
			 'show_total_incl_tax' => $show_total_incl_tax, 
            'items' => $this->getRecentItems(),
            'extra_actions' => $this->layout->createBlock(\Magento\Catalog\Block\ShortcutButtons::class)->toHtml()
           
        ];
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote =  $this->_quoteSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Get shopping cart items qty based on configuration (summary qty or items qty)
     *
     * @return int|float
     */
    protected function getSummaryCount()
    {
        if (!$this->summeryCount) {
            $this->summeryCount = $this->quote->getItemsCount() ?: 0;
        }
        return $this->summeryCount;
    }

    /**
     * Check if one page checkout is available
     *
     * @return bool
     */
    protected function isPossibleOnepageCheckout()
    {
        return $this->checkoutHelper->canOnepageCheckout() && !$this->getQuote()->getHasError();
    }

    /**
     * Get array of last added items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getRecentItems()
    {
        $items = [];
        if (!$this->getSummaryCount()) {
            return $items;
        }

        foreach (array_reverse($this->getAllQuoteItems()) as $item) {           
            $items[] = $this->itemPoolInterface->getItemData($item);			
        }
        return $items;
    }
	public function getSubtotalInclTax()
	{
		$item_price=array();
		$item_price_incl_tax=array();
		$item_price_arr=array();
		$items=$this->getAllQuoteItems();
		foreach($items as $i)
		{
				$item_price[]=$i->getCalculationPrice();
				$item_price_incl_tax[]=$i->getPriceInclTax();			
		}
		$item_price_arr['item_price']=array_sum($item_price);
		$item_price_arr['item_price_incl_tax']=array_sum($item_price_incl_tax);
		return $item_price_arr;
	}

    /**
     * Return customer quote items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getAllQuoteItems()
    {
        if ($this->getCustomQuote()) {
            return $this->getCustomQuote()->getAllVisibleItems();
        }
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Check if guest checkout is allowed
     *
     * @return bool
     */
    public function isGuestCheckoutAllowed()
    {
        return $this->checkoutHelper->isAllowedGuestCheckout( $this->_quoteSession->getQuote());
    }
}
