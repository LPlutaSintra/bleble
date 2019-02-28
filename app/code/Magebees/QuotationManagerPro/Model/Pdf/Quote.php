<?php

namespace Magebees\QuotationManagerPro\Model\Pdf;

/**
 * Quote PDF model
 */
class Quote extends QuoteAbstractPdf
{
   
    protected $_storeManager;

    protected $_localeResolver;

    public function __construct(
       
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        Config $pdfConfig,
        \Magebees\QuotationManagerPro\Model\Pdf\Total\Factory $pdfTotalFactory,
        \Magebees\QuotationManagerPro\Model\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
          \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_localeResolver = $localeResolver;
        parent::__construct(
          
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
			$storeManager,
            $quoteHelper,
            $data
        );
    }

    /**
     * Draw header for item table     
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
		$tax_config=$this->quoteHelper->getTaxConfig();
$enable_shipping=$tax_config['enable_shipping'];
$is_include_tax=$tax_config['price_tax'];
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 35];
        $lines[0][] = ['text' => __('Price'), 'feed' => 360, 'align' => 'right'];       
        $lines[0][] = ['text' => __('Proposal Price'), 'feed' => 440, 'align' => 'right'];       
 		$lines[0][] = ['text' => __('Qty'), 'feed' => 480, 'align' => 'right'];
		if($is_include_tax):
 		$lines[0][] = ['text' => __('Tax'), 'feed' => 520, 'align' => 'right'];
		endif;
        $lines[0][] = ['text' => __('Subtotal'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 7];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document    
     */
    public function getPdf($quotes = [])
    {
		
        $this->_beforeGetPdf();
        $this->_initPdfRenderer('quotation');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($quotes as $quote) {
            if ($quote->getStoreId()) {
                $this->_localeResolver->emulate($quote->getStoreId());
                $this->_storeManager->setCurrentStore($quote->getStoreId());
            }
            $page = $this->newPage();
           
            /* Add image */
            $this->insertQuoteLogo($page, $quote->getStore());
            /* Add address */
            $this->insertQuoteAddress($page, $quote->getStore());
            /* Add head */
            $this->insertQuote(
                $page,
                $quote,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $quote->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Quote # ') . $quote->getIncrementId());
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
			$top=600;
            foreach ($quote->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
				$top=$top-45;
                /* Draw item */
                $this->_drawItem($item, $page, $quote,$top);
               
                $page = end($pdf->pages);
			//	break;
            }
            /* Add totals */
            $this->insertQuoteTotals($page, $quote);
            if ($quote->getStoreId()) {
                $this->_localeResolver->revert();
            }
			 $this->insertQuoteRemarks($page, $quote);
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Create new page and assign to PDF object   
     */
    public function newPage(array $settings = [])
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }
}
