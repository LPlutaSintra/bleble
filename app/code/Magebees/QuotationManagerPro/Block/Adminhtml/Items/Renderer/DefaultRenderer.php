<?php

namespace Magebees\QuotationManagerPro\Block\Adminhtml\Items\Renderer;
class DefaultRenderer extends \Magebees\QuotationManagerPro\Block\Adminhtml\Items\AbstractItems
{

    public function getItem()
    {
        return $this->_getData('item');
    }
}
