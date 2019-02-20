<?php


namespace Nikal\Products\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{

    private $eavSetupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), "1.0.1", "<")) {

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'size',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Size',
                    'input' => 'select',
                    'class' => '',
                    'source' => '',
                    'global' => 0,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => null,
                    'searchable' => false,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'system' => 0,
                    'group' => 'General',
                    'option' => array('values' => array(""))
                ]
            );
        }

        if (version_compare($context->getVersion(), "1.0.2", "<")) {

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'gender',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Gender',
                    'input' => 'select',
                    'class' => '',
                    'source' => '',
                    'global' => 0,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => null,
                    'searchable' => false,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'system' => 0,
                    'group' => 'General',
                    'option' => array('values' => array("Male", "Female", "Boy", "Girl", "Unisex"))
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'material',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Material',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => 0,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => null,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'system' => 0,
                    'group' => 'General',
                    'option' => array('values' => array(""))
                ]
            );

            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY,
                'barcode',
                'system',
                0);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY,
                'color_filter',
                'system',
                0);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY,
                'collection',
                'system',
                0);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY,
                'missions',
                'system',
                0);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY,
                'author',
                'system',
                0);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY,
                'customizable',
                'system',
                0);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY,
                'expiration_date',
                'system',
                0);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY,
                'concept',
                'system',
                0);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY,
                'size',
                'system',
                0);

        }
    }
}