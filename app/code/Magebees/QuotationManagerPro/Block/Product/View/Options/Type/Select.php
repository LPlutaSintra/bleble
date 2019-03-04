<?php
namespace Magebees\QuotationManagerPro\Block\Product\View\Options\Type;

class Select extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
	protected $scopeConfig;
	protected $_helper;
	public function __construct(
		\Magebees\QuotationManagerPro\Helper\Quotation $helper
    ) {
        
		$this->_helper = $helper;
    }
   
	public function afterGetValuesHtml(\Magento\Catalog\Block\Product\View\Options\Type\Select $subject)
	{
		$config=$this->_helper->getConfig();	
		if($config['enable'])
		{				
		$product = $subject->getProduct();
		$is_check=$this->_helper->isEnablePriceCustGroupWise($product);
		}
		else
		{
			$is_check=true;
		}
	  $input_option = $subject->getOption();
        $configValue = $subject->getProduct()->getPreconfiguredValues()->getData('options/' . $input_option->getId());
        $store = $subject->getProduct()->getStore();

        $subject->setSkipJsReloadPrice(1);
        // Remove inline prototype onclick and onchange events

        if ($input_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN ||
            $input_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE
        ) {
            $require = $input_option->getIsRequire() ? ' required' : '';
            $extraParams = '';
            $select = $subject->getLayout()->createBlock(
                'Magento\Framework\View\Element\Html\Select'
            )->setData(
                [
                    'id' => 'select_' . $input_option->getId(),
                    'class' => $require . ' product-custom-option admin__control-select'
                ]
            );
            if ($input_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN) {
                $select->setName('options[' . $input_option->getid() . ']')->addOption('', __('-- Please Select --'));
            } else {
                $select->setName('options[' . $input_option->getid() . '][]');
                $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');
            }
            foreach ($input_option->getValues() as $opt_value) {
               if($is_check){
			   $priceStr = $subject->_formatPrice(
                    [
                        'is_percent' => $opt_value->getPriceType() == 'percent',
                        'pricing_value' => $opt_value->getPrice($opt_value->getPriceType() == 'percent'),
                    ],
                    false
                );
                $select->addOption(
                    $opt_value->getOptionTypeId(),
                    $opt_value->getTitle() . ' ' . strip_tags($priceStr) . '',
                    ['price' => $subject->pricingHelper->currencyByStore($opt_value->getPrice(true), $store, false)]
                );
			   }else{
			   
			   $priceStr = null;
                $select->addOption(
                    $opt_value->getOptionTypeId(),
                    $opt_value->getTitle() . ' ' . strip_tags($priceStr) . '');
			   }
				
            }
            if ($input_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
            if (!$subject->getSkipJsReloadPrice()) {
                $extraParams .= ' onchange="opConfig.reloadPrice()"';
            }
            $extraParams .= ' data-selector="' . $select->getName() . '"';
            $select->setExtraParams($extraParams);

            if ($configValue) {
                $select->setValue($configValue);
            }

            return $select->getHtml();
        }

        if ($input_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO ||
            $input_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX
        ) {
            $selectOptHtml = '<div class="options-list nested" id="options-' . $input_option->getId() . '-list">';
            $require = $input_option->getIsRequire() ? ' required' : '';
            $arraySign = '';
            switch ($input_option->getType()) {
                case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO:
                    $input_type = 'radio';
                    $input_class = 'radio admin__control-radio';
                    if (!$input_option->getIsRequire()) {
                        $selectOptHtml .= '<div class="field choice admin__field admin__field-option">' .
                            '<input type="radio" id="options_' .
                            $input_option->getId() .
                            '" class="' .
                            $input_class .
                            ' product-custom-option" name="options[' .
                            $input_option->getId() .
                            ']"' .
                            ' data-selector="options[' . $input_option->getId() . ']"' .
                            ($subject->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                            ' value="" checked="checked" /><label class="label admin__field-label" for="options_' .
                            $input_option->getId() .
                            '"><span>' .
                            __('None') . '</span></label></div>';
                    }
                    break;
                case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX:
                    $input_type = 'checkbox';
                    $input_class = 'checkbox admin__control-checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 1;
            foreach ($input_option->getValues() as $opt_value) {
                $count++;
	
                $priceStr = $subject->_formatPrice(
                    [
                        'is_percent' => $opt_value->getPriceType() == 'percent',
                        'pricing_value' => $opt_value->getPrice($opt_value->getPriceType() == 'percent'),
                    ]
                );

                $htmlValue = $opt_value->getOptionTypeId();
                if ($arraySign) {
                    $checked = is_array($configValue) && in_array($htmlValue, $configValue) ? 'checked' : '';
                } else {
                    $checked = $configValue == $htmlValue ? 'checked' : '';
                }

                $dataSelector = 'options[' . $input_option->getId() . ']';
                if ($arraySign) {
                    $dataSelector .= '[' . $htmlValue . ']';
                }
				 if($is_check){
				 $selectOptHtml .= '<div class="field choice admin__field admin__field-option' .
                    $require .
                    '">' .
                    '<input type="' .
                    $input_type .
                    '" class="' .
                    $input_class .
                    ' ' .
                    $require .
                    ' product-custom-option"' .
                    ($subject->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                    ' name="options[' .
                    $input_option->getId() .
                    ']' .
                    $arraySign .
                    '" id="options_' .
                    $input_option->getId() .
                    '_' .
                    $count .
                    '" value="' .
                    $htmlValue .
                    '" ' .
                    $checked .
                    ' data-selector="' . $dataSelector . '"' .
                    ' price="' .
                    $subject->pricingHelper->currencyByStore($opt_value->getPrice(true), $store, false) .
                    '" />' .
                    '<label class="label admin__field-label" for="options_' .
                    $input_option->getId() .
                    '_' .
                    $count .
                    '"><span>' .
                    $opt_value->getTitle() .
                    '</span> ' .
                    $priceStr .
                    '</label>';
				 }else{
				 $selectOptHtml .= '<div class="field choice admin__field admin__field-option' .
                    $require .
                    '">' .
                    '<input type="' .
                    $input_type .
                    '" class="' .
                    $input_class .
                    ' ' .
                    $require .
                    ' product-custom-option"' .
                    ($subject->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                    ' name="options[' .
                    $input_option->getId() .
                    ']' .
                    $arraySign .
                    '" id="options_' .
                    $input_option->getId() .
                    '_' .
                    $count .
                    '" value="' .
                    $htmlValue .
                    '" ' .
                    $checked .
                    ' data-selector="' . $dataSelector . '"' .
                    ' />' .
                    '<label class="label admin__field-label" for="options_' .
                    $input_option->getId() .
                    '_' .
                    $count .
                    '"><span>' .
                    $opt_value->getTitle() .
                    '</span> </label>';
				 }
                
                $selectOptHtml .= '</div>';
            }
            $selectOptHtml .= '</div>';

            return $selectOptHtml;
        }
	}
   
}
