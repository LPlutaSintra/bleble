<?php

namespace Magebees\QuotationManagerPro\Block\Checkout\Cart\Item;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;
use Magento\Framework\DataObject\IdentityInterface;

class RenderConfigurable extends \Magebees\QuotationManagerPro\Block\Checkout\Cart\Item\Renderer implements IdentityInterface
{
    
    const CONFIG_THUMBNAIL_SOURCE = 'checkout/cart/configurable_product_image';

    /**
     * Get item configurable child product 
     */
    public function getChildProduct()
    {
        if ($option = $this->getItem()->getOptionByCode('simple_product')) {
            return $option->getProduct();
        }
        return $this->getProduct();
    }

    /**
     * Get item product name    
     */
    public function getProductName()
    {
        return $this->getProduct()->getName();
    }

    /**
     * Get list of all options for product    
     */
    public function getOptionList()
    {		
        return $this->_quoteHelper->getOptions($this->getItem());
    }

    
    public function getProductForThumbnail()
    {
        /**
         * Show parent product thumbnail if it must be always shown according to the related setting in system config
         * or if child thumbnail is not available
         */
        if ($this->_scopeConfig->getValue(
            self::CONFIG_THUMBNAIL_SOURCE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == ThumbnailSource::OPTION_USE_PARENT_IMAGE ||
            !($this->getChildProduct()->getThumbnail() && $this->getChildProduct()->getThumbnail() != 'no_selection')
        ) {
            $qproduct = $this->getProduct();
        } else {
            $qproduct = $this->getChildProduct();
        }
        return $qproduct;
    }

   
   public function getIdentities()
    {
        $identities = parent::getIdentities();
        if ($this->getItem()) {
            $identities = array_merge($identities, $this->getChildProduct()->getIdentities());
        }
        return $identities;
    }
}
