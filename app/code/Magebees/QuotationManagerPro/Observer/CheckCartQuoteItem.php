<?php
namespace Magebees\QuotationManagerPro\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


class CheckCartQuoteItem implements ObserverInterface
{
   protected $redirect;   
    public function __construct(
       \Magento\Quote\Model\Quote\ItemFactory $itemFactory,
		    \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\Response\RedirectInterface $redirect

    ) {

       $this->cart = $cart;
		$this->itemFactory = $itemFactory;
        $this->redirect = $redirect;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $actionName = $observer->getEvent()->getRequest()->getFullActionName();
        $routeName = $observer->getEvent()->getRequest()->getRouteName();
        $controller = $observer->getControllerAction();      
        if ($routeName!='quotation' && $controller != 'quote' && $actionName != 'addressData') {
			
		$default_quote_id=$this->cart->getQuote()->getId();
		$quote_items=$this->cart->getQuote()->getItemsCollection();
		foreach($quote_items as $q)
		{
			$itemId=$q->getId();
			 $model =$this->itemFactory->create();
            $model->load($itemId);
            $model->delete();
		
			//$this->cart->getQuote()->removeItem($itemId);
			//$this->cart->save();
			//echo 
		}
            //return $this; //if in allowed actions do nothing.
        }
      

    }
}