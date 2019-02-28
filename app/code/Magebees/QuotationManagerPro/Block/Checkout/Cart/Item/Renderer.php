<?php
namespace Magebees\QuotationManagerPro\Block\Checkout\Cart\Item;

use Magebees\QuotationManagerPro\Block\Checkout\Cart\Item\Renderer\Actions;
use Magebees\QuotationManagerPro\Model\QuoteItem;

class Renderer extends \Magento\Framework\View\Element\Template implements
    \Magento\Framework\DataObject\IdentityInterface
{
   
   
    protected $_quoteFactory;
	 protected $_strictQtyMode = true;
	  protected $_ignoreProductUrl = false;
	 protected $_qproductUrl;
	   protected $qmessageManager;
	protected $_productConfig = null;
   
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $qproductConfig,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Message\ManagerInterface $qmessageManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $qmessageInterpretationStrategy,      
        \Magebees\QuotationManagerPro\Model\QuoteFactory $quoteFactory,		 
        array $data = []
    ) {
        
        $this->_quoteFactory = $quoteFactory;
		 $this->imageBuilder = $imageBuilder;
		$this->_productConfig = $qproductConfig;
		$this->_quoteHelper = $quoteHelper;
		$this->messageManager = $qmessageManager;		 
        parent::__construct(
            $context,          
            $data
        );
    }
    
    public function canAccept()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        $quote = $this->_quoteFactory->create()->load($quoteId);
return true;
       
    }
  
    public function getDeleteUrl($tierItemId)
    {
        $quoteId = $this->getRequest()->getParam('quote_id');

        return $this->getUrl('quotation/quote/deleteTierItem', ['id' => $tierItemId, 'quote_id' => $quoteId]);
    }
	 public function getIdentities()
    {
        $identities = [];
        if ($this->getItem()) {
            $identities = $this->getProduct()->getIdentities();
        }
        return $identities;
    }
	 public function setItem(QuoteItem $item)
    {
        $this->_item = $item;
        return $this;
    }
	 public function getImage($qproduct, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($qproduct)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }
	  public function getProductForThumbnail()
    {
        return $this->getProduct();
    }

    /**
     * Get quote item    
     */
    public function getItem()
    {
        return $this->_item;
    }
	 public function getProduct()
    {
        return $this->getItem()->getProduct();
    }
	 public function hasProductUrl()
    {
        if ($this->_ignoreProductUrl) {
            return false;
        }

        if ($this->_qproductUrl || $this->getItem()->getRedirectUrl()) {
            return true;
        }

        $qproduct = $this->getProduct();
        $itemoption = $this->getItem()->getOptionByCode('product_type');
        if ($itemoption) {
            $qproduct = $itemoption->getProduct();
        }

        if ($qproduct->isVisibleInSiteVisibility()) {
            return true;
        } else {
            if ($qproduct->hasUrlDataObject()) {
                $data = $qproduct->getUrlDataObject();
                if (in_array($data->getVisibility(), $qproduct->getVisibleInSiteVisibilities())) {
                    return true;
                }
            }
        }
        return false;
    }
	public function getProductUrl()
    {
        if ($this->_qproductUrl !== null) {
            return $this->_qproductUrl;
        }
        if ($this->getItem()->getRedirectUrl()) {
            return $this->getItem()->getRedirectUrl();
        }

        $qproduct = $this->getProduct();
        $itemoption = $this->getItem()->getOptionByCode('product_type');
        if ($itemoption) {
            $qproduct = $itemoption->getProduct();
        }

        return $qproduct->getUrlModel()->getUrl($qproduct);
    }
	 public function getProductName()
    {
        if ($this->hasProductName()) {
            return $this->getData('product_name');
        }
        return $this->getProduct()->getName();
    }   
	 public function getUnitPriceHtml(QuoteItem $item)
    {
        /** @var Renderer $qblock */
        $qblock = $this->getLayout()->getBlock('checkout.item.price.unit');
        $qblock->setItem($item);		
        return $qblock->toHtml();
    }
	public function getActions(QuoteItem $item)
    {
        /** @var Actions $qblock */
        $qblock = $this->getChildBlock('actions');
        if ($qblock instanceof Actions) {
            $qblock->setItem($item);
            return $qblock->toHtml();
        } else {
            return '';
        }
    }
	 public function getQty()
    {
        if (!$this->_strictQtyMode && (string)$this->getItem()->getQty() == '') {
            return '';
        }
        return $this->getItem()->getQty() * 1;
    }
	
   public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }
	  public function getOptionList()
    {
        return $this->getProductOptions();
    }
	 public function getProductOptions()
    {
   		$helper =$this->_quoteHelper;		
		return $helper->getCustomOptions($this->getItem());
		
    }
	 public function getFormatedOptionValue($itemoptionVal)
    {
        /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
        $helper = $this->_productConfig;
        $params = [
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
        ];
        return $helper->getFormattedOptionValue($itemoptionVal, $params);
    }
	
	 public function getMessages()
    {
        $qmessages = [];
        $mquoteItem = $this->getItem();
		$has_error=$this->getItem()->getQuote()->getData('has_error');
        // Add basic messages occurring during this page load
        $baseMessages = $mquoteItem->getMessage(false);
        if ($baseMessages) {
            foreach ($baseMessages as $qmessage) {
                $qmessages[] = ['text' => $qmessage, 'type' => $mquoteItem->getHasError() ? 'error' : 'notice'];
            }
        }

        /* @var $qcollection \Magento\Framework\Message\Collection */
        $qcollection = $this->messageManager->getMessages(true, 'quote_item' . $mquoteItem->getId());
        if ($qcollection) {
            $qadditionalMessages = $qcollection->getItems();
            foreach ($qadditionalMessages as $qmessage) {
                /* @var $qmessage \Magento\Framework\Message\MessageInterface */
                $qmessages[] = [
                    'text' => $this->messageInterpretationStrategy->interpret($qmessage),
                    'type' => $qmessage->getType()
                ];
            }
        }
        $this->messageManager->getMessages(true, 'quote_item' . $mquoteItem->getId())->clear();

        return $qmessages;
    }

}
