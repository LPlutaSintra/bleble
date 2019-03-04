<?php
namespace Magebees\QuotationManagerPro\Block\Checkout\Cart\Item\Renderer\Actions;

class Edit extends \Magebees\QuotationManagerPro\Block\Checkout\Cart\Item\Renderer\Actions\Generic
{
    /**
     * Get item configure url
     * @return string
     */
    public function getConfigureUrl()
    {
        return $this->getUrl(
            'quotation/quote/configure',
            [
                'id' => $this->getItem()->getId(),
                'product_id' => $this->getItem()->getProduct()->getId(),
            ]
        );
    }
}
