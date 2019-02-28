<?php
namespace Magebees\QuotationManagerPro\Block\Checkout\Cart\Item;
/**
 * Quote Item Configure block
 * Updates templates to show 'Update Quote' button 
 */
class Configure extends \Magento\Checkout\Block\Cart\Item\Configure
{
    /**
     * Configure product view blocks   
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        // Set custom submit url route for form - to submit updated options to cart
        $block = $this->getLayout()->getBlock('product.info');
        if ($block) {
            $block->setSubmitRouteData(
                [
                    'route' => 'quotation/quote/updateItemOptions',
                    'params' => ['id' => $this->getRequest()->getParam('id')],
                ]
            );
        }

        return $this;
    }
}
