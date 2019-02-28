<?php
namespace Magebees\QuotationManagerPro\Model\Pdf;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Quote PDF abstract model
 * */
abstract class QuoteAbstractPdf extends \Magento\Framework\DataObject
{
   
    public $y;

    /**
     * Item renderers with render type key  
     */
    protected $_qrenderers = [];

    /**
     * Predefined constants
     */
    const XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID = 'sales_pdf/invoice/put_order_id';
	
    protected $_pdf;

    abstract public function getPdf();

    protected $qstring;
    protected $_localeDate;
    protected $_scopeConfig;
    protected $_mediaDirectory;
    protected $_rootDirectory;
    protected $_pdfConfig;
    protected $_qpdfTotalFactory;
    protected $_qpdfItemsFactory;
    protected $qinlineTranslation;
    protected $qaddressRenderer;

    public function __construct(
     
        \Magento\Framework\Stdlib\StringUtils $qstring,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        Config $pdfConfig,
        \Magebees\QuotationManagerPro\Model\Pdf\Total\Factory $qpdfTotalFactory,
        \Magebees\QuotationManagerPro\Model\Pdf\ItemsFactory $qpdfItemsFactory,      
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $qinlineTranslation,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
        array $data = []
    ) {        
        $this->_localeDate = $localeDate;
		 $this->_storeManager = $storeManager;
        $this->string = $qstring;
        $this->_scopeConfig = $scopeConfig;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->_pdfConfig = $pdfConfig;
        $this->_qpdfTotalFactory = $qpdfTotalFactory;
        $this->quoteHelper = $quoteHelper;
        $this->_qpdfItemsFactory = $qpdfItemsFactory;
        $this->qinlineTranslation = $qinlineTranslation;
        parent::__construct($data);
    }

    public function widthForFontSize($qstring, $pdffont, $pdffontSize)
    {
        $qdrawingString = '"libiconv"' == ICONV_IMPL ? iconv(
            'UTF-8',
            'UTF-16BE//IGNORE',
            $qstring
        ) : @iconv(
            'UTF-8',
            'UTF-16BE',
            $qstring
        );

        $characters = [];
        for ($i = 0; $i < strlen($qdrawingString); $i++) {
            $characters[] = ord($qdrawingString[$i++]) << 8 | ord($qdrawingString[$i]);
        }
        $pdfglyphs = $pdffont->glyphNumbersForCharacters($characters);
        $fontwidths = $pdffont->widthsForGlyphs($pdfglyphs);
        $qstringWidth = array_sum($fontwidths) / $pdffont->getUnitsPerEm() * $pdffontSize;
        return $qstringWidth;
    }

   
    public function getTextAlignRight($qstring, $x, $pdfcolumnWidth, \Zend_Pdf_Resource_Font $pdffont, $pdffontSize, $padding = 5)
    {
        $imgwidth = $this->widthForFontSize($qstring, $pdffont, $pdffontSize);
        return $x + $pdfcolumnWidth - $imgwidth - $padding;
    }

    public function getTextAlignCenter($qstring, $x, $pdfcolumnWidth, \Zend_Pdf_Resource_Font $pdffont, $pdffontSize)
    {
        $imgwidth = $this->widthForFontSize($qstring, $pdffont, $pdffontSize);
        return $x + round(($pdfcolumnWidth - $imgwidth) / 2);
    }

    /**
     * Insert logo to pdf page    
     */
    protected function insertQuoteLogo(&$pdfpage, $store = null)
    {
        $this->y = $this->y ? $this->y : 815;
        $logoimage = $this->_scopeConfig->getValue(
            'sales/identity/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($logoimage) {
            $logoimagePath = '/sales/store/logo/' . $logoimage;
            if ($this->_mediaDirectory->isFile($logoimagePath)) {
                $logoimage = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($logoimagePath));
                $top = 830;
                //top border of the page
                $imgwidthLimit = 270;
                //half of the page width
                $imgheightLimit = 270;
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

                //coordinates after transformation are rounded by Zend
                $pdfpage->drawImage($logoimage, $x1, $y1, $x2, $y2);

                $this->y = $y1 - 10;
            }
        }
    }

   
    protected function insertQuoteAddress(&$pdfpage, $store = null)
    {
        $pdfpage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $pdffont = $this->_setFontRegular($pdfpage, 10);
        $pdfpage->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $top = 815;
        foreach (explode(
                     "\n",
                     $this->_scopeConfig->getValue(
                         'sales/identity/address',
                         \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                         $store
                     )
                 ) as $value) {
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $pdfpage->drawText(
                        trim(strip_tags($_value)),
                        $this->getTextAlignRight($_value, 130, 440, $pdffont, 10),
                        $top,
                        'UTF-8'
                    );
                    $top -= 10;
                }
            }
        }
        $this->y = $this->y > $top ? $top : $this->y;
    }

    /**
     * Format address   
     */
    protected function _formatQuoteAddress($qaddress)
    {
        $return = [];
        foreach (explode('|', $qaddress) as $str) {
            foreach ($this->string->split($str, 45, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    /**
     * Calculate address height     
     */
    protected function _calcQuoteAddressHeight($qaddress)
    {
        $y = 0;
        foreach ($qaddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 55, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $y += 15;
                }
            }
        }
        return $y;
    }

    /**
     * Insert quote to pdf page   
     */
    protected function insertQuote(&$pdfpage, $obj, $putOrderId = true)
    {
		$config=$this->quoteHelper->getConfig();
		$enable_expiration_time=$config['enable_expiration_time'];
        if ($obj instanceof \Magebees\QuotationManagerPro\Model\Quote) {
            $shipment = null;
            $quote = $obj;
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $pdfpage->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $pdfpage->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $pdfpage->drawRectangle(25, $top, 570, $top -75);
        $pdfpage->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
		if($enable_expiration_time)
		{
		$this->setDocHeaderCoordinates([25, $top, 570, $top - 45]);
		}
		else
		{
		$this->setDocHeaderCoordinates([25, $top, 570, $top - 35]);
		}
		
        
        $this->_setFontRegular($pdfpage, 10);
		 $pdfpage->drawText(
            __('Quote Created Date: ') .
			$update_time=$this->_localeDate->formatDate(
				 $quote->getCreatedAt(),
				\IntlDateFormatter::MEDIUM,
				true
			),
            35,
            $top -= 30,
            'UTF-8'
        );
		$top +=15;
		 $pdfpage->drawText(
            __('Quote Updated Date: ') .
			$update_time=$this->_localeDate->formatDate(
				 $quote->getUpdatedAt(),
				\IntlDateFormatter::MEDIUM,
				true
			),
            35,
            $top -= 30,
            'UTF-8'
        );
		
		if($enable_expiration_time)
		{		
		$top +=15;
		 $pdfpage->drawText(
            __('Quote Expiry Date: ') .
			$update_time=$this->_localeDate->formatDate(
				 $quote->getExpiredAt(),
				\IntlDateFormatter::MEDIUM,
				true
			),
            35,
            $top -= 30,
            'UTF-8'
        );
		}
		

        if ($putOrderId) {
        
            $top +=15;
        }

        $top -=30;
      
        $top -= 10;
        $pdfpage->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $pdfpage->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $pdfpage->setLineWidth(0.5);
        $pdfpage->drawRectangle(25, $top, 275, $top - 25);
        $pdfpage->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
		$avail_billing=$this->quoteHelper->IsCustomBillAddressAvail($quote->getId());
if($avail_billing['is_default_address']!=0)
{	
		$billing_address=$this->quoteHelper->renderCustomAddress($avail_billing,'pdf');	
}
else
{
	//$billing_address= $this->quoteHelper->getFormattedAddress($quote->getbillAddressId());
	$default_billing=$this->quoteHelper->getDefaultAddressInQuote($quote->getId(),'billing',$quote->getbillAddressId());
	if($default_billing)
	{
	$billing_address=$this->quoteHelper->renderCustomAddress($default_billing,'pdf');
	}
	else
	{
		$billing_address='';
	}
}
        $qbillingAddress = $this->_formatQuoteAddress($billing_address);      
        /* Shipping Address and Method */
        if (!$quote->getIsVirtual()) {
            /* Shipping Address */
			$avail_shipping=$this->quoteHelper->IsCustomShipAddressAvail($quote->getId());

if($avail_shipping['is_default_address']!=0)
{	
		$shipping_address=$this->quoteHelper->renderCustomAddress($avail_shipping,'pdf');	
}
else
{
	//$shipping_address=$this->quoteHelper->getFormattedAddress($quote->getshipAddressId());
	$default_shipping=$this->quoteHelper->getDefaultAddressInQuote($quote->getId(),'shipping',$quote->getshipAddressId());
	if($default_shipping)
	{
	$shipping_address=$this->quoteHelper->renderCustomAddress($default_shipping.'pdf');
	}
	else
	{
		$shipping_address='';
	}
	
}	
            $qshippingAddress = $this->_formatQuoteAddress($shipping_address);           
            $shippingMethod = $quote->getShippingDescription();
        }

        $pdfpage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($pdfpage, 12);
        $pdfpage->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');
		$pdfpage->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        

        $qaddressesHeight = $this->_calcQuoteAddressHeight($qbillingAddress);
        if (isset($qshippingAddress)) {
            $qaddressesHeight = max($qaddressesHeight, $this->_calcQuoteAddressHeight($qshippingAddress));
        }

        $pdfpage->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $pdfpage->drawRectangle(25, $top - 25, 570, $top - 33 - $qaddressesHeight);
        $pdfpage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($pdfpage, 10);
        $this->y = $top - 40;
        $qaddressesStartY = $this->y;

        foreach ($qbillingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $pdfpage->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $qaddressesEndY = $this->y;

        if (!$quote->getIsVirtual()) {
            $this->y = $qaddressesStartY;
            foreach ($qshippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $pdfpage->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $qaddressesEndY = min($qaddressesEndY, $this->y);
            $this->y = $qaddressesEndY;


            $this->y -= 10;
            $pdfpage->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($pdfpage, 10);
            $pdfpage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $qaddressesStartY;
            $paymentLeft = 285;
        }

            // replacement of Shipments-Payments rectangle block
            $yPayments = min($qaddressesEndY, $yPayments);
			$this->y = $yPayments - 15;
        
    }

    /**
     * Insert title and number for concrete document type  
     */
    public function insertDocumentNumber(\Zend_Pdf_Page $pdfpage, $text)
    {
        $pdfpage->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($pdfpage, 10);
        $docHeader = $this->getDocHeaderCoordinates();
        $pdfpage->drawText($text, 35, $docHeader[1] - 15, 'UTF-8');
    }

    /**
     * Sort totals list    
     */
    protected function _sortQuoteTotalsList($a, $b)
    {
        if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
            return 0;
        }

        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }

        return $a['sort_order'] > $b['sort_order'] ? 1 : -1;
    }

    /**
     * Return total list    
     */
    protected function _getQuoteTotalsList($quote)
    {
        $quotetotals = $this->_pdfConfig->getTotals();
        usort($quotetotals, [$this, '_sortQuoteTotalsList']);
        $qtotalModels = [];
        foreach ($quotetotals as $qtotalInfo) {
			
            $class = empty($qtotalInfo['model']) ? null : $qtotalInfo['model'];
            $totalModel = $this->_qpdfTotalFactory->create($class);
            $totalModel->setData($qtotalInfo);
            $qtotalModels[$qtotalInfo['source_field']] = $totalModel;
			$pdf_config=$this->quoteHelper->getPdfConfig();
			$tax_config=$this->quoteHelper->getTaxConfig();
			$enable_shipping=$tax_config['enable_shipping'];
			$is_include_tax=$tax_config['price_tax'];
			$currentUrl=$this->_storeManager->getStore()->getCurrentUrl();
			
			/* hide quote adjustment from pdf */
			if(!$pdf_config['show_quote_adjustment'])
			{
				if($qtotalInfo['source_field']=='adjustment')
				{
					unset( $qtotalModels[$qtotalInfo['source_field']]);
				}
			}
			else
			{
				$isadmin=$this->quoteHelper->isAdmin();
				if (!($isadmin))
			{
					if($quote->getStatus()<20)
					{
						if($qtotalInfo['source_field']=='adjustment')
					{
						unset( $qtotalModels[$qtotalInfo['source_field']]);
					}
					}
			}
			}
				/* hide tax detail from pdf */
			if(!$is_include_tax)
			{
				if($qtotalInfo['source_field']=='tax')
				{
					unset( $qtotalModels[$qtotalInfo['source_field']]);
				}
			}
				/* hide shipping detail from pdf */
			if(!$enable_shipping)
			{
				if($qtotalInfo['source_field']=='shipping')
				{
					unset( $qtotalModels[$qtotalInfo['source_field']]);
				}
			}
			$isadmin=$this->quoteHelper->isAdmin();
				if (!($isadmin))
				{
					if($quote->getStatus()<20)
						{
							if($qtotalInfo['source_field']=='quote_total')
						{
							unset( $qtotalModels[$qtotalInfo['source_field']]);
						}
						}
				}
			
        }

        return $qtotalModels;
    }

    /**
     * Insert totals to pdf page    
     */
    protected function insertQuoteTotals($pdfpage, $quote)
    {
      
        $quotetotals = $this->_getQuoteTotalsList($quote);
        $pdflineBlock = ['lines' => [], 'height' => 18];
        foreach ($quotetotals as $total) {
            $total->setQuote($quote)->setSource($quote);

            if ($total->canDisplay($quote)) {
                $total->setFontSize(10);
                foreach ($total->getQuoteTotalsForDisplay($quote) as $qtotalData) {
                    $pdflineBlock['lines'][] = [
                        [
                            'text' => $qtotalData['label'],
                            'feed' => 475,
                            'align' => 'right',
                            'font_size' => $qtotalData['font_size'],
                            'font' => 'bold',
                        ],
                        [
                            'text' => $qtotalData['amount'],
                            'feed' => 565,
                            'align' => 'right',
                            'font_size' => $qtotalData['font_size'],
                            'font' => 'bold'
                        ],
                    ];
                }
            }
        }

        $this->y -= 10;
		$pdf_config=$this->quoteHelper->getPdfConfig();
		if($pdf_config['enable_remark'])
		{
        $pdfpage = $this->drawLineBlocks($pdfpage, [$pdflineBlock],['table_content' => true]);
		}
		else
		{
			 $pdfpage = $this->drawLineBlocks($pdfpage, [$pdflineBlock]);
		}
        return $pdfpage;
    }
	protected function insertQuoteRemarks($pdfpage, $quote)
    {
		/* Add for remark */ 
		$pdflineBlock = ['lines' => [], 'height' => 18];
		$pdf_config=$this->quoteHelper->getPdfConfig();
		if($pdf_config['enable_remark'])
		{
		if($quote->getQuoteRequestInfo())
		{
			$quote_remark=$quote->getQuoteRequestInfo();
		
			 if ($quote_remark !== '') {
                    $quoteremarktext = [];
                   // $quoteremarktext[] = 'Remarks With Quote';
                    foreach ($this->string->split($quote_remark, 120, true, true) as $quote_remark) {
                        $quoteremarktext[] = $quote_remark;
                    }
				 	$pdflineBlock['lines'][0] = [
                        [
                            'text' =>'Remarks With Quote',
                            'feed' => 40,  
			 				'align' => 'left',
                            'font' => 'bold',
                        ]
                    ];
                    foreach ($quoteremarktext as $part) {
						$pdflineBlock['lines'][] = [
                        [
                            'text' =>strip_tags(ltrim($part)),
                            'feed' => 40,  
			 				'align' => 'left'
                            
                        ]
                    ];
                       
                    }
                }
			$this->y -= 10;
		$this->drawLineBlocks($pdfpage, [$pdflineBlock],['table_content' => true]);
		}		
		 $this->y -= 10;
		$pdflineBlock = ['lines' => [], 'height' => 18];
		
			$remark=$pdf_config['remark'];
		
			 if ($remark !== '') {
                    $remarktext = [];
                    foreach ($this->string->split($remark, 120, true, true) as $remark) {
                        $remarktext[] = $remark;
                    }
                    foreach ($remarktext as $part) {
						$pdflineBlock['lines'][] = [
                        [
                            'text' =>strip_tags(ltrim($part)),
                            'feed' => 40,  
			 				'align' => 'left',
                           
                        ]
                    ];
                       
                    }
                }
			
		}
		$pdfpage = $this->drawLineBlocks($pdfpage, [$pdflineBlock]);
        return $pdfpage;
		
	}

    /**
     * Parse item description   
     */
    protected function _parseItemDescription($item)
    {
        $matches = [];
        $description = $item->getDescription();
        if (preg_match_all('/<li.*?>(.*?)<\/li>/i', $description, $matches)) {
            return $matches[1];
        }

        return [$description];
    }

    /**
     * Before getPdf processing  
     */
    protected function _beforeGetPdf()
    {
        $this->qinlineTranslation->suspend();
    }

    /**
     * After getPdf processing   
     */
    protected function _afterGetPdf()
    {
        $this->qinlineTranslation->resume();
    }

    
    /**
     * Initialize renderer process   
     */
    protected function _initPdfRenderer($type)
    {
        $pdfrendererData = $this->_pdfConfig->getRenderersPerItem($type);
        foreach ($pdfrendererData as $qproductType => $pdfrenderer) {
            $this->_qrenderers[$qproductType] = ['model' => $pdfrenderer, 'renderer' => null];
        }
    }

    /**
     * Retrieve renderer model    
     */
    protected function _getPdfRenderer($type)
    {
        if (!isset($this->_qrenderers[$type])) {
            $type = 'default';
        }

        if (!isset($this->_qrenderers[$type])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We found an invalid renderer model.'));
        }

        if (is_null($this->_qrenderers[$type]['renderer'])) {
            $this->_qrenderers[$type]['renderer'] = $this->_qpdfItemsFactory->get($this->_qrenderers[$type]['model']);
        }

        return $this->_qrenderers[$type]['renderer'];
    }

    /**
     * Public method of protected @see _getPdfRenderer()   
     */
    public function getRenderer($type)
    {
        return $this->_getPdfRenderer($type);
    }

    /**
     * Draw Item process    
     */
    protected function _drawItem(\Magebees\QuotationManagerPro\Model\QuoteItem $item, \Zend_Pdf_Page $pdfpage, \Magebees\QuotationManagerPro\Model\Quote $quote,$top)
    {
        $type = $item->getProductType();
        $pdfrenderer = $this->_getPdfRenderer($type);
        $pdfrenderer->setQuote($quote);
        $pdfrenderer->setItem($item);
        $pdfrenderer->setPdf($this);
        $pdfrenderer->setPage($pdfpage);
        $pdfrenderer->setRenderedModel($this);

        $pdfrenderer->draw($top);

        return $pdfrenderer->getPage();
    }
	 

    /**
     * Set font as regular    
     */
    protected function _setFontRegular($object, $size = 7)
    {
        $pdffont = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
        );
        $object->setFont($pdffont, $size);
        return $pdffont;
    }

    /**
     * Set font as bold   
     */
    protected function _setFontBold($object, $size = 7)
    {
        $pdffont = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
        );
        $object->setFont($pdffont, $size);
        return $pdffont;
    }

    /**
     * Set font as italic    
     */
    protected function _setFontItalic($object, $size = 7)
    {
        $pdffont = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_It-2.8.2.ttf')
        );
        $object->setFont($pdffont, $size);
        return $pdffont;
    }

    /**
     * Set PDF object    
     */
    protected function _setPdf(\Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Retrieve PDF object   
     */
    protected function _getPdf()
    {
        if (!$this->_pdf instanceof \Zend_Pdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please define the Quote PDF object before using.'));
        }

        return $this->_pdf;
    }

    /**
     * Create new page and assign to PDF object   
     */
    public function newPage(array $settings = [])
    {
        $pdfpageSize = !empty($settings['page_size']) ? $settings['page_size'] : \Zend_Pdf_Page::SIZE_A4;
        $pdfpage = $this->_getPdf()->newPage($pdfpageSize);
        $this->_getPdf()->pages[] = $pdfpage;
        $this->y = 800;

        return $pdfpage;
    }

   
    public function drawLineBlocks(\Zend_Pdf_Page $pdfpage, array $draw, array $pdfpageSettings = [])
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We don\'t recognize the draw line data. Please define the "lines" array.')
                );
            }
            $lines = $itemsProp['lines'];
            $imgheight = isset($itemsProp['height']) ? $itemsProp['height'] : 20;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
						                       

                        $lineSpacing = !empty($column['height']) ? $column['height'] : $imgheight;
                        if (!is_array($column['text'])) {
                            $column['text'] = [$column['text']];
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    
					}
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < 15) {
                $pdfpage = $this->newPage($pdfpageSettings);
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $pdffontSize = empty($column['font_size']) ? 10 : $column['font_size'];
                    if (!empty($column['font_file'])) {
                        $pdffont = \Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $pdfpage->setFont($pdffont, $pdffontSize);
                    } else {
                        $pdffontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        switch ($pdffontStyle) {
                            case 'bold':
                                $pdffont = $this->_setFontBold($pdfpage, $pdffontSize);
                                break;
                            case 'italic':
                                $pdffont = $this->_setFontItalic($pdfpage, $pdffontSize);
                                break;
                            default:
                                $pdffont = $this->_setFontRegular($pdfpage, $pdffontSize);
                                break;
                        }
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = [$column['text']];
                    }

                    $lineSpacing = !empty($column['height']) ? $column['height'] : $imgheight;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        if ($this->y - $lineSpacing < 15) {
                            $pdfpage = $this->newPage($pdfpageSettings);
                        }

                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $imgwidth = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($imgwidth) {
                                    $feed = $this->getTextAlignRight($part, $feed, $imgwidth, $pdffont, $pdffontSize);
                                } else {
                                    $feed = $feed - $this->widthForFontSize($part, $pdffont, $pdffontSize);
                                }
                                break;
                            case 'center':
                                if ($imgwidth) {
                                    $feed = $this->getTextAlignCenter($part, $feed, $imgwidth, $pdffont, $pdffontSize);
                                }
                                break;
                            default:
                                break;
                        }
						if(!(preg_match('/media/', $part)))
						{
                        $pdfpage->drawText($part, $feed, $this->y - $top, 'UTF-8');
						}
						else
						{if(array_key_exists('is_image', $column) && !is_null($column['text'][0])){
                           $image = \Zend_Pdf_Image::imageWithPath($column['text'][0]);
                           $feed = $column['feed'];
                	    //   $pdfpage->drawImage($image, $feed, $this->y-20, $feed+70, $this->y+10);
							
											   $pdfpage->drawImage($image, $feed, $this->y-20, $feed+30, $this->y+10);
                         //  $maxHeight = 65;
						}
						}
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $this->y -= $maxHeight;
            }
        }
		if(isset($pdfpageSettings['table_content']))
		{
$pdfpage->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
$pdfpage->setLineWidth(0.5);
$pdfpage->drawLine(25, $this->y+12.5, 570, $this->y+12.5);
		}
        return $pdfpage;
    }
}
