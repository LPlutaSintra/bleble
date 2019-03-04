<?php
namespace Magebees\QuotationManagerPro\Block\Adminhtml\Quotation\Create\Search\Grid\Renderer;

class GridProduct extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $colrendered = parent::render($row);
        $isConfigurableProduct = $row->canConfigure();
        $tagstyle = $isConfigurableProduct ? '' : 'disabled';
        $productAttributes = $isConfigurableProduct ? sprintf(
            'list_type = "product_to_add" product_id = %s',
            $row->getId()
        ) : 'disabled="disabled"';
		if($isConfigurableProduct):
        return sprintf(
            '<a href="javascript:void(0)" class="action-configure %s" %s>%s</a>',
            $tagstyle,
            $productAttributes,
            __('Configure')
        ) . $colrendered;
		else:
		return sprintf(
            '<a href="javascript:void(0)" class="action-configure %s" style="display:none;" %s>%s</a>',
            $tagstyle,
            $productAttributes,
            __('Configure')
        ) . $colrendered;
		endif;
    }
}
