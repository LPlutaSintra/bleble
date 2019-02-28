<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * Adminhtml sales order create abstract block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractCreate extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * Order create
     *
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected $_orderCreate;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
 
	 public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,		 \Magebees\QuotationManagerPro\Model\Backend\Session\Quote $quoteSession,        \Magebees\QuotationManagerPro\Model\Backend\Quote\Create $quoteCreate,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
       $this->_sessionQuote = $sessionQuote;
        $this->_orderCreate = $orderCreate;
		   $this->_quoteSession = $quoteSession;
        $this->_quoteCreate = $quoteCreate;
        parent::__construct($context,$sessionQuote,$orderCreate,$priceCurrency,$data);
    }

    /**
     * Retrieve create quote model object
     *
    
     */
    public function getCreateQuoteModel()
    {
        return $this->_quoteCreate;
    }

    /**
     * Retrieve quote session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession()
    {
        return $this->_quoteSession;
    }    

}
