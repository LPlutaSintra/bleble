<?php
namespace Magebees\QuotationManagerPro\Block\Adminhtml;

class DefaultFrontend extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {    
        return '<textarea rows="6" style="background:#efefef;border:1px solid #d8d8d8;padding:10px;margin-bottom:10px;" onclick="this.focus();this.select()">&lt?php echo $block->getLayout()->createBlock("Magento\Framework\View\Element\Template")->setProduct($_item)->setTemplate("Magebees_QuotationManagerPro::quote_button.phtml")->toHtml();?&gt;</textarea>';
    }
}
