<?php

namespace Magebees\QuotationManagerPro\Model\Pdf\Items\Quote;
use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Quote Pdf default items renderer
 */
class DefaultQuote extends \Magebees\QuotationManagerPro\Model\Pdf\Items\PdfAbstractItems
{
   
    protected $string;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		 \Magento\Store\Model\StoreManagerInterface $storeManager,   
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->string = $string;
		$this->quoteHelper = $quoteHelper;
		 $this->_storeManager = $storeManager;      
		$this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        parent::__construct(
            $context,
            $registry,
            $quoteHelper,
            $filesystem,
            $filterManager,
            $resource,
            $resourceCollection,
            $data
        );
    }
 protected function insertImage($item,$page,$top)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProduct()->getId());

   $logoimagePath = '/catalog/product' .$product->getSmallImage();
		//print_r($logoimagePath);die;
		 if ($this->_mediaDirectory->isFile($logoimagePath)) {
		$logoimage = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($logoimagePath));
			 return $this->_mediaDirectory->getAbsolutePath($logoimagePath);
			  //$top = 830;
                //top border of the page
                $imgwidthLimit = 30;
                //half of the page width
                $imgheightLimit = 30;
                //assuming the image is not a "skyscraper"
                $imgwidth = $logoimage->getPixelWidth();
                $imgheight = $logoimage->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $imgwidth / $imgheight;
                if ($ratio > 1 && $imgwidth > $imgwidthLimit) {
                    $imgwidth = $imgwidthLimit;
                    $imgheight = $imgwidth / $ratio;
                } elseif ($ratio < 1 && $imgheight > $imgheightLimit) {
                    $imgheight = $imgheightLimit;
                    $imgwidth = $imgheight * $ratio;
                } elseif ($ratio == 1 && $imgheight > $imgheightLimit) {
                    $imgheight = $imgheightLimit;
                    $imgwidth = $imgwidthLimit;
                }

                $y1 = $top - $imgheight;
                $y2 = $top;
                $x1 = 25;
                $x2 = $x1 + $imgwidth;
		  $page->drawImage($logoimage, $x1, $y1, $x2, $y2);
		 }
		
	}
    public function draw($top)
    {
        $quote = $this->getQuote();
        $item = $this->getItem();
		$productId=$item->getProduct()->getId();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];
		$tax_config=$this->quoteHelper->getTaxConfig();
$enable_shipping=$tax_config['enable_shipping'];
$is_include_tax=$tax_config['price_tax'];
		// draw product image in pdf
		//$this->insertImage($item,$page,$top);
		   	$productImage = $this->insertImage($item, $page,$top);
        // draw Product name
        $lines[0] = [['text' => $this->string->split($item->getName(),30), 'feed' => 35,'font' => 'bold']];
		$lines[0][] = array(
           'text'  => $productImage,
           'is_image'  => 1,
           'feed'  => 200
   	);
        $lines[1] = [['text' => $this->string->split("SKU:".$this->getSku($item),30), 'feed' => 35]];

        // draw SKU
       /* $lines[0][] = [
            'text' => $this->string->split($this->getSku($item), 17),
            'feed' => 230,
            'align' => 'right',
        ];*/
		
		// draw Comment
      /*  $lines[0][] = [
            'text' => $this->string->split($item->getRequestInfo(), 15),
            'feed' => 250,
            'align' => 'justify',
        ];*/
		
$quotationtierQty=$this->quoteHelper->getDynamicQuoteQty($item->getId(),$quote->getId(),$productId);
        // draw QTY
		 $i = 0;
		foreach($quotationtierQty as $qty):	
		if($i==0)
		{
       $lines[$i][] = ['text' => $qty->getRequestQty() * 1, 'feed' => 480, 'align' => 'right', 'font' => 'bold'];
		}
		else
		{
			 $lines[$i][] = ['text' => $qty->getRequestQty() * 1, 'feed' => 480, 'align' => 'right'];
		}
		 $i++;
endforeach;
        // draw item Prices
        $i = 0;		
		foreach($quotationtierQty as $qty):	
        $prices = $this->getItemPricesForDisplay($qty->getRequestQtyPrice(),$qty->getReqQtyPriceInclTax(),$qty->getRequestQty());			 
        $feedPrice = 360;
        $feedProposalPrice = $feedPrice+60;
       
		if($is_include_tax):
		$feedtax = $feedProposalPrice+100;
        $feedSubtotal = $feedtax + 50;
		else:
		$feedSubtotal = $feedProposalPrice + 140;
		endif;
		$currentUrl=$this->_storeManager->getStore()->getCurrentUrl();
		//print_r($currentUrl);die;
		
		$is_show_price=$this->quoteHelper->isShowPriceBeforeProposal($quote->getStatus());	
        foreach ($prices as $priceData) {
		$isadmin=$this->quoteHelper->isAdmin();
		if ($isadmin)
		{
			$price=$priceData['price'];	
			$proposal_price=$priceData['proposal_price'];
			$tax=$priceData['tax'];
			$subtotal=$priceData['subtotal'];
		}
		else
		{
			if($is_show_price)
			{
			$price=$priceData['price'];		
			}
			else
			{
			$price='--';			
			}
			if($quote->getStatus()<20)
			{
				$proposal_price='--';
				$tax='--';
				$subtotal='--';
				
			}
			else
			{
				$proposal_price=$priceData['proposal_price'];
				$tax=$priceData['tax'];
				$subtotal=$priceData['subtotal'];
			}
		}
			
			
			
			
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedPrice, 'align' => 'right'];
				 // draw Proposal Price label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedProposalPrice, 'align' => 'right'];
				 // draw Tax label
				if($is_include_tax):
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedtax, 'align' => 'right'];
				endif;
                // draw Subtotal label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'right'];
                $i++;
            }
            
			if($i==0)
			{
				// draw Price
            $lines[$i][] = [
                'text' => $price,
                'feed' => $feedPrice,
                'font' => 'bold',
                'align' => 'right',
            ];
				// draw Price
            $lines[$i][] = [
                'text' => $proposal_price,
                'feed' => $feedProposalPrice,
                'font' => 'bold',
                'align' => 'right',
            ];
				
			// draw Tax
				if($is_include_tax):
            $lines[$i][] = [
                'text' => $tax,
                'feed' => $feedtax,
                'font' => 'bold',
                'align' => 'right',
            ];
				endif;
            // draw Subtotal
            $lines[$i][] = [
                'text' => $subtotal,
                'feed' => $feedSubtotal,
                'font' => 'bold',
                'align' => 'right',
            ];
			}
			else
			{
				// draw Price
				$lines[$i][] = [
                'text' => $price,
                'feed' => $feedPrice,                
                'align' => 'right',
            ];
				// draw Proposal Price
				$lines[$i][] = [
                'text' => $proposal_price,
                'feed' => $feedProposalPrice,                
                'align' => 'right',
            ];
				// draw Tax
				if($is_include_tax):
            $lines[$i][] = [
                'text' => $tax,
                'feed' => $feedtax,               
                'align' => 'right',
            ];
				endif;
            // draw Subtotal
            $lines[$i][] = [
                'text' => $subtotal,
                'feed' => $feedSubtotal,               
                'align' => 'right',
            ];
			}
            $i++;
			
        }
 endforeach;
     
        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                ];

                if ($option['value']) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
						if(is_array($option['value']))
						{
							$opt_val=$option['value'][0];
						}
						else
						{
							$opt_val=$option['value'];
						}
                        $printValue = $this->filterManager->stripTags($opt_val);
                    }
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines[][] = ['text' => $this->string->split($value, 30, true, true), 'feed' => 40];
                    }
                }
            }
        }
		/*** Add for display comment below product name */
		if($item->getRequestInfo())
		{
		$lines[][] = [
            'text' => "Comment:",
           'feed' => 30,  
			'font' => 'bold',
			'align' => 'left',
        ];
		$lines[][] = [
            'text' => $this->string->split($item->getRequestInfo(), 70, true, true),
           'feed' => 30,  
			'align' => 'left',
        ];
		}
		
		/**End for display comment below product name */
        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_content' => true]);
        $this->setPage($page);
    }
}
