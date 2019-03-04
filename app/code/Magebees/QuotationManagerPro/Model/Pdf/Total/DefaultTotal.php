<?php

namespace Magebees\QuotationManagerPro\Model\Pdf\Total;

class DefaultTotal extends \Magento\Framework\DataObject
{
   
    
    public function __construct(
       \Magebees\QuotationManagerPro\Helper\Admin $backendHelper,   \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		 \Magento\Store\Model\StoreManagerInterface $storeManager,  
        array $data = []
    ) {
        $this->backendHelper = $backendHelper;
       $this->quoteHelper = $quoteHelper;
		 $this->_storeManager = $storeManager;
        parent::__construct($data);
    }

    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     *
     * @return array
     */
    public function getQuoteTotalsForDisplay($quote)
    {
		
        $amount = $this->getQuote()->formatPriceTxt($this->getAmount($quote));
       // $amount =$totals_data['orig_price'];
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        $title = __($this->getTitle());       
        $label = $title . ':';
        

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => $amount, 'label' => $label, 'font_size' => $fontSize];
        return [$total];
    }

    

    /**
     * Check if we can display total information in PDF
     *
     * @return bool
     */
    public function canDisplay($quote)
    {
        $amount = $this->getAmount($quote);
        return $this->getDisplayZero() === 'true' || $amount != 0;
    }

    /**
     * Get Total amount from source
     *
     * @return float
     */
    public function getAmount($quote)
    {
		$quote_id=$quote->getId();
		$is_show_price=$this->quoteHelper->isShowPriceBeforeProposal($quote->getStatus());	
		$totals_data=$this->backendHelper->getDefaultTotalData($quote_id);
		$tax_config=$this->quoteHelper->getTaxConfig();
$enable_shipping=$tax_config['enable_shipping'];
$is_include_tax=$tax_config['price_tax'];
		$currentUrl=$this->_storeManager->getStore()->getCurrentUrl();
		$tax=$totals_data['tax'];
		$isadmin=$this->quoteHelper->isAdmin();
		if ($isadmin)
		{
			$grand_total=$totals_data['row_total'];
		}
		else
		{
		if($quote->getStatus()>20)
		{
			$grand_total=$totals_data['row_total'];
		}
		else
		{
			$grand_total=$totals_data['orig_price'];
		}
		}
		
		$shipping_handling_excl_tax=$totals_data['shipping_handling_excl_tax'];
$shipping_handling_incl_tax=$totals_data['shipping_handling_incl_tax'];
		if($is_include_tax)
{
	$grand_total+=$tax;
}
if($enable_shipping)
{
	if($is_include_tax)
	{
	$grand_total+=$shipping_handling_incl_tax;
	}
	else
	{
	$grand_total+=$shipping_handling_excl_tax;
	}
}
		$isadmin=$this->quoteHelper->isAdmin();
		if ($isadmin)
		{
			$totaldata_orig_price=$totals_data['orig_price'];
		$totaldata_adjustment_quote=$totals_data['adjustment_quote'];
		$totaldata_tax=$totals_data['tax'];
		$totaldata_row_total=$totals_data['row_total'];
		$totaldata_shipping_handling_incl_tax=$totals_data['shipping_handling_incl_tax'];
		}
		else
		{
		if($is_show_price)
		{
		$totaldata_orig_price=$totals_data['orig_price'];
		$totaldata_adjustment_quote=$totals_data['adjustment_quote'];
		$totaldata_tax=$totals_data['tax'];
		$totaldata_row_total=$totals_data['row_total'];
		$totaldata_shipping_handling_incl_tax=$totals_data['shipping_handling_incl_tax'];
		}
		else
		{
		$totaldata_orig_price='--';
		$totaldata_adjustment_quote='--';
		$totaldata_tax='--';
		$totaldata_row_total='--';
		$totaldata_shipping_handling_incl_tax='--';
		$grand_total='--';
		}
		}
		
		
		if($this->getSourceField()=='subtotal_orig')
		{
			return $totaldata_orig_price;
		}
		elseif($this->getSourceField()=='adjustment')
		{
			return $totaldata_adjustment_quote;
		}
		elseif($this->getSourceField()=='tax')
		{
			return $totaldata_tax;
		}
		elseif($this->getSourceField()=='quote_total')		
		{
			return $totaldata_row_total;
		}
		elseif($this->getSourceField()=='shipping')		
		{
			return $totaldata_shipping_handling_incl_tax;
		}
		elseif($this->getSourceField()=='grand_total')		
		{
			return $grand_total;
		}
		

        
    }

    /**
     * Get title description from source
     *
     * @return mixed
     */
    public function getTitleDescription()
    {
        return $this->getSource()->getData($this->getTitleSourceField());
    }
}
