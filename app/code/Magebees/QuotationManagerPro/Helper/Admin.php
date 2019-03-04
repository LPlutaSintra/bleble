<?php
namespace Magebees\QuotationManagerPro\Helper;
use \Magebees\QuotationManagerPro\Model\Quote\Status;

class Admin extends \Magento\Framework\App\Helper\AbstractHelper
{
   
    protected $_storeManager;
    protected $priceCurrency;
    protected $escaper;
    protected $_items;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,     
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Escaper $escaper,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		 \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteSession,
		\Magebees\QuotationManagerPro\Model\ResourceModel\QuoteItem\CollectionFactory $quoteItemCollFactory,
		 \Magento\Framework\Registry $registry,
		\Magento\Backend\Model\Auth\Session $authSession, 
		\Magento\Framework\Session\SessionManager $default_session
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManager;       
        $this->escaper = $escaper;
		$this->_coreRegistry = $registry;
		$this->_quoteItemCollFactory = $quoteItemCollFactory; 
		$this->quoteHelper = $quoteHelper;
		$this->_session = $quoteSession;
		$this->default_session = $default_session;
		  $this->authSession = $authSession;
        parent::__construct($context);
    }
 
     public function getQuote()
    {
       
        if ($this->_coreRegistry->registry('current_quote')) {
            return $this->_coreRegistry->registry('current_quote');
        }
        elseif ($this->_coreRegistry->registry('quote')) {
            return $this->_coreRegistry->registry('quote');
        }
		else
		{
			return $this->_session->getQuote();
		}

        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t get the quote instance right now.'));
    }
	public function getSession()
	{
		return $this->default_session;
	}
	 public function getItemsCollection($quote_id=null)
    {
		if(!$quote_id)
		{
        $quote_id=$this->_session->getQuoteId();
		}
		 if (!$this->_items) {
		  	$this->_items = $this->_quoteItemCollFactory->create();   		
			$this->_items->addFieldToFilter('quote_id',$quote_id);
			$this->_items->addFieldToFilter('parent_item_id', ['null' => true]);	
		 }
        	return $this->_items;
    }
 	public function checkStatusAllowForProposal($status)
	{
		$not_allow_for_proposal = array();
		$not_allow_for_proposal[] = Status::PROCESSING_ACTION_CUSTOMER;	
		$not_allow_for_proposal[] =Status::PROPOSAL_SENT_ACTION_CUSTOMER;
		$not_allow_for_proposal[] =Status::PROPOSAL_ACCEPTED;
		$not_allow_for_proposal[] =Status::PROPOSAL_ACCEPTED_NOT_ORDERED;
		$not_allow_for_proposal[] = Status::PROPOSAL_ORDERED;	
		$not_allow_for_proposal[] = Status::PROPOSAL_REJECTED;
		$not_allow_for_proposal[] = Status::PROPOSAL_ORDERED;
		return !in_array($status, $not_allow_for_proposal);
		
		
	}
	public function checkAllowForBackendButton($status)
	{
		$not_allow_for_proposal = array();
		$not_allow_for_proposal[] = Status::PROPOSAL_ACCEPTED;	
		$not_allow_for_proposal[] =Status::PROPOSAL_ACCEPTED_NOT_ORDERED;
		$not_allow_for_proposal[] =Status::PROPOSAL_ORDERED;
		$not_allow_for_proposal[] =Status::PROPOSAL_CANCELLED;
		$not_allow_for_proposal[] = Status::PROPOSAL_CANCELLED_OUTOF_STOCK;	
		$not_allow_for_proposal[] = Status::PROPOSAL_REJECTED;	
		return !in_array($status, $not_allow_for_proposal);
	}
	public function getDefaultTotalData($quote_id=null)
	{
		if(!$quote_id)
		{
		 $quote_id=$this->_session->getQuoteId();
		}
		$currentUrl=$this->_storeManager->getStore()->getCurrentUrl();
		
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$orig_price=[];
		$cost_price=[];
		$row_price=[];
		$total_data=[];
		$orig_price_incl_tax=[];
		$row_incl_tax_price=[];
		$_items=$this->getItemsCollection($quote_id);
		foreach ($_items as $_item):   
		$item_id=$_item->getId();
		$request_qty_data=$this->quoteHelper->getDefaultRequestQty($quote_id,$item_id);
		$default_qty=$request_qty_data['request_qty'];
		$item_orig_price=$_item->getCalculationPrice();      
		$item_orig_price_incl_tax=$_item->getPriceInclTax();      
		$item_cost_price=$request_qty_data['cost_price']; 
		$request_qty_price=$request_qty_data['request_qty_price'];    
		/*start for add request quantity price include tax*/
		//$req_price_incl_tax=$this->quoteHelper->getProductPriceInclTax($_item->getProduct(),$request_qty_price,true);		
		$req_price_incl_tax=$request_qty_data['req_qty_price_incl_tax'];		
		/*end for add request quantity price include tax*/
		$row_price[]=$request_qty_price*$default_qty;
		$row_incl_tax_price[]=$req_price_incl_tax*$default_qty;
		$cost_price[]=$item_cost_price*$default_qty;
		$orig_price[]=$item_orig_price*$default_qty;
		$orig_price_incl_tax[]=$item_orig_price_incl_tax*$default_qty;
        endforeach;
		$shipping_handling_incl_tax=$quote->getShippingRateInclTax();
		$shipping_handling_excl_tax=$quote->getShippingRateExclTax();
		$total_data['orig_price']=array_sum($orig_price);
		$total_data['orig_price_incl_tax']=array_sum($orig_price_incl_tax);
		$total_data['cost_price']=array_sum($cost_price);
		$total_data['row_total']=array_sum($row_price);
		$total_data['row_total_incl_tax']=array_sum($row_incl_tax_price);
		$total_data['adjustment_quote']=array_sum($row_price)-array_sum($orig_price);		
		$total_data['adjustment_quote_incl_tax']=array_sum($row_incl_tax_price)-array_sum($orig_price_incl_tax);		
		$total_orig_price=$total_data['orig_price'];
		$total_cost_price=$total_data['cost_price'];
		$isadmin=$this->quoteHelper->isAdmin();
		if ($isadmin)
		{
			$tax=$total_data['row_total_incl_tax']-$total_data['row_total'];
		}
		else
		{
		if($quote->getStatus()<20)
		{
			$tax=$total_data['orig_price_incl_tax']-$total_data['orig_price'];
		}
		else
		{
		$tax=$total_data['row_total_incl_tax']-$total_data['row_total'];
		}
		}
		
		$total_data['tax']=$tax;
		$total_data['shipping_handling_incl_tax']=$shipping_handling_incl_tax;
		$total_data['shipping_handling_excl_tax']=$shipping_handling_excl_tax;
		$profitamount = $total_orig_price - $total_cost_price;
		if($profitamount!=$total_orig_price)
		{
		$profitpercentage = ($profitamount/$total_orig_price)*100;
		$total_data['margin']=number_format($profitpercentage,2)."%";
		}
		return $total_data;
	}
	public function getCurrentUser()
	{
		return $this->authSession->getUser();
	}
}
