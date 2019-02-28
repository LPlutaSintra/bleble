<?php

namespace Magebees\QuotationManagerPro\Model\Pdf\Items;
use Magento\Framework\App\Filesystem\DirectoryList;

abstract class PdfAbstractItems extends \Magento\Framework\Model\AbstractModel
{
    protected $_order;

    protected $_qsource;

    protected $_qitem;

    protected $_qpdf;

    protected $_qpdfPage;

    protected $_taxData;

    protected $_rootDirectory;

    protected $filterManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->filterManager = $filterManager;
        $this->quoteHelper = $quoteHelper;
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    
    public function setQuote(\Magebees\QuotationManagerPro\Model\Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    public function setSource(\Magento\Framework\Model\AbstractModel $source)
    {
        $this->_qsource = $source;
        return $this;
    }

    public function setItem(\Magebees\QuotationManagerPro\Model\QuoteItem $qitem)
    {
        $this->_qitem = $qitem;
        return $this;
    }

    public function setPdf(\Magebees\QuotationManagerPro\Model\Pdf\QuoteAbstractPdf $qpdf)
    {
        $this->_qpdf = $qpdf;
        return $this;
    }

    /**
     * Set current page   
     */
    public function setPage(\Zend_Pdf_Page $pdfpage)
    {
        $this->_qpdfPage = $pdfpage;
        return $this;
    }

    /**
     * Retrieve order object   
     */
    public function getQuote()
    {
        if (null === $this->_quote) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The quote object is not specified.'));
        }
        return $this->_quote;
    }

    /**
     * Retrieve source object   
     */
    public function getSource()
    {
        if (null === $this->_qsource) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The source object is not specified.'));
        }
        return $this->_qsource;
    }

    /**
     * Retrieve item object   
     */
    public function getItem()
    {
        if (null === $this->_qitem) {
            throw new \Magento\Framework\Exception\LocalizedException(__('An quote item object is not specified.'));
        }
        return $this->_qitem;
    }

    /**
     * Retrieve Pdf model   
     */
    public function getPdf()
    {
        if (null === $this->_qpdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('A Quote PDF object is not specified.'));
        }
        return $this->_qpdf;
    }

    /**
     * Retrieve Pdf page object 
     */
    public function getPage()
    {
        if (null === $this->_qpdfPage) {
            throw new \Magento\Framework\Exception\LocalizedException(__('A Quote PDF page object is not specified.'));
        }
        return $this->_qpdfPage;
    }

    /**
     * Draw item line
     *
     * @return void
     */
    abstract public function draw($top);

   
    public function getItemPricesForDisplay($req_qty_price,$req_qty_price_incl_tax,$req_qty)
    {
        $quote = $this->getQuote();
        $qitem = $this->getItem();
      $subtotal=$req_qty_price*$req_qty;
      $subtotal_incl_tax=$req_qty_price_incl_tax*$req_qty;
		$currentUrl=$this->_storeManager->getStore()->getCurrentUrl();
		$isadmin=$this->quoteHelper->isAdmin();
		if ($isadmin)
		{
			$product_tax=$quote->formatPriceTxt($subtotal_incl_tax-$subtotal);
		}
		else
		{
		if($quote->getStatus()<20)
		{
			$product_tax=$quote->formatPriceTxt(($qitem->getPriceInclTax()*$req_qty)-($qitem->getPrice()*$req_qty));
		}
		else
		{
		$product_tax=$quote->formatPriceTxt($subtotal_incl_tax-$subtotal);
		}
		}
            $prices = [
                [
                    'price' => $quote->formatPriceTxt($qitem->getPrice()),
                    'price_incl_tax' => $quote->formatPriceTxt($qitem->getPriceInclTax()),
                    'proposal_price' => $quote->formatPriceTxt($req_qty_price),
                    'proposal_price_incl_tax' => $quote->formatPriceTxt($req_qty_price_incl_tax),
                    'subtotal' => $quote->formatPriceTxt($req_qty_price*$req_qty),
                    'subtotal_incl_tax' => $quote->formatPriceTxt($req_qty_price_incl_tax*$req_qty),
					'tax'=>$product_tax
				
                ],
            ];
        
        return $prices;
    }

    /**
     * Retrieve item options
     *
     * @return array
     */
    public function getItemOptions()
    {
		 $options = $this->quoteHelper->getBackendOptions($this->getItem());
		return $options;     
    }

    /**
     * Set font as regular
     *
     * @param  int $fontsize
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($fontsize = 7)
    {
        $pdffont = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
        );
        $this->getPage()->setFont($pdffont, $fontsize);
        return $pdffont;
    }

    /**
     * Set font as bold
     *
     * @param  int $fontsize
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($fontsize = 7)
    {
        $pdffont = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
        );
        $this->getPage()->setFont($pdffont, $fontsize);
        return $pdffont;
    }

    /**
     * Set font as italic
     *
     * @param  int $fontsize
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($fontsize = 7)
    {
        $pdffont = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_It-2.8.2.ttf')
        );
        $this->getPage()->setFont($pdffont, $fontsize);
        return $pdffont;
    }

    /**
     * Return item Sku
     *
     * @param mixed $qitem
     * @return mixed
     */
    public function getSku($qitem)
    {
        if ($qitem->getProductOptionByCode('simple_sku')) {
            return $qitem->getProductOptionByCode('simple_sku');
        } else {
            return $qitem->getSku();
        }
    }
}
