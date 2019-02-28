<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\QuotationManagerPro\Observer;

use Magento\Framework\Event\ObserverInterface;

class UnsetAllObserver implements ObserverInterface
{
    /**
     * @var \Magebees\QuotationManagerPro\Model\Session
     */
    protected $quoteSession;

    /**
     * @param \Magebees\QuotationManagerPro\Model\Session $quoteSession
     * @codeCoverageIgnore
     */
    public function __construct(\Magebees\QuotationManagerPro\Model\Session $quoteSession,
								 \Magento\Framework\Session\SessionManager $session,
								  \Magento\Checkout\Model\Cart $cart,	
							    \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper)
    {
        $this->quoteSession = $quoteSession;
		$this->quoteHelper = $quoteHelper;
		  $this->session =$session;
		 $this->cart = $cart;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @codeCoverageIgnore
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		  $frontend_setting=$this->quoteHelper->getFrontendConfig();
		   $isLockProposal=$frontend_setting['lock_proposal'];
		if($this->session->getQuoteId())
		{
			$this->session->setQuoteId();
		}
		
		   if(($isLockProposal)&&($this->session->getProcessingQuoteId()))
		   {
			   $this->session->setProcessingQuoteId();
			   	$this->cart->truncate();
			   $this->cart->save();
		   }
        $this->quoteSession->clearQuote()->clearStorage();
    }
}
