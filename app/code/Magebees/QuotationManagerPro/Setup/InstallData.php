<?php
namespace Magebees\QuotationManagerPro\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory /* For Attribute create  */;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
class InstallData implements InstallDataInterface
{

private $eavSetupFactory;

public function __construct(EavSetupFactory $eavSetupFactory)
{
    $this->eavSetupFactory = $eavSetupFactory;
}

public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
{
          
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
       	    $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'magebees_quote',
                [
                    'group' => 'General',
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Apply Add To Quote',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '1',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => 'simple,configurable,virtual,bundle,downloadable'
                ]
            );



    }
}