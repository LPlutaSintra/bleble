<?php
namespace Magebees\QuotationManagerPro\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
   
     public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {	$installer = $setup;

        $installer->startSetup();
        if (version_compare($context->getVersion(), "1.0.0", "<")) {
       
        }
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('magebees_quote'),
                'quote_request_info',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64k',
                    'nullable' => true,
                    'comment' => 'Magebees Quote Request Information'
                ]
            );
        }
	 	if (version_compare($context->getVersion(), '1.1.0', '<')) {
			
		/**
         * Create table 'magebees_quote_customer'
         */
			$table = $installer->getConnection()->newTable(
            $installer->getTable('magebees_quote_customer')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Magebees Quote Customer Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Magebees Quote Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Magebees Quote Customer Id'
        )->addColumn(
              'email',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              null,
              ['unsigned' => true,'nullable' => false],
              'Magebees Customer Email'
          )->addColumn(
              'fname',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              null,
              ['unsigned' => true,'nullable' => false],
              'Magebees Customer Firstname'
          )->addColumn(
              'lname',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              null,
              ['unsigned' => true,'nullable' => false],
              'Magebees Customer Lastname'
          )->addIndex(
            $installer->getIdxName('magebees_quote_customer', ['quote_id']),
            ['quote_id']
        )->addForeignKey(
            $installer->getFkName('magebees_quote_customer', 'quote_id', 'magebees_quote', 'quote_id'),
            'quote_id',
            $installer->getTable('magebees_quote'),
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);
			
			
		/**
         * Create table 'magebees_quote_address'
         */
		$table = $installer->getConnection()->newTable(
            $installer->getTable('magebees_quote_address')
        )->addColumn(
            'address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Address Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Quote Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Id'
        )->addColumn(
            'save_in_address_book',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['default' => '0'],
            'Save In Address Book'
        )->addColumn(
            'customer_address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Address Id'
        )->addColumn(
            'address_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            [],
            'Address Type'
        )->addColumn(
            'email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Email'
        )->addColumn(
            'firstname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Firstname'
        )->addColumn(
            'middlename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Middlename'
        )->addColumn(
            'lastname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Lastname'
        )->addColumn(
            'street',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Street'
        )->addColumn(
            'city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'City'
        )->addColumn(
            'region',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Region'
        )->addColumn(
            'region_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Region Id'
        )->addColumn(
            'postcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Postcode'
        )->addColumn(
            'country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            30,
            [],
            'Country Id'
        )->addColumn(
            'telephone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Phone Number'
        )->addColumn(
            'same_as_billing',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Same As Billing'
        )->addColumn(
            'is_default_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '1'],
            'Magebees Quote Address Is Default'
        )->addIndex(
            $installer->getIdxName('magebees_quote_address', ['quote_id']),
            ['quote_id']
        )->addForeignKey(
            $installer->getFkName('magebees_quote_address', 'quote_id', 'quote', 'quote_id'),
            'quote_id',
            $installer->getTable('magebees_quote'),
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Magebees Quote Address'
        );
        $installer->getConnection()->createTable($table);	
			
          $installer->getConnection()->addColumn(
                $installer->getTable('magebees_quote_item'),
                'price_incl_tax',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
			  		'default' => '0.0000',
                    'comment' => 'Magebees Quote Item Price Include Tax'
                ]
            );	
			
			 $installer->getConnection()->addColumn(
                $installer->getTable('magebees_quote_request_item'),
                'req_qty_price_incl_tax',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64k',                    
                    'comment' => 'Magebees Quote Item Request Qty Price Include Tax'
                ]
            );	
			
			 $installer->getConnection()->addColumn(
                $installer->getTable('magebees_quote'),
                'shipping_rate_excl_tax',
                [
                    'type' =>\Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',                    
                    'comment' => 'Magebees Quote Shipping Rate Exclude Tax'
                ]
            ); 
			$installer->getConnection()->addColumn(
                $installer->getTable('magebees_quote'),
                'shipping_rate_incl_tax',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',                    
                    'comment' => 'Magebees Quote Shipping Rate Include Tax'
                ]
            );
			$installer->getConnection()->addColumn(
                $installer->getTable('magebees_quote'),
                'expired_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,				'default'=>\Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,   'comment' => 'Magebees Quote Expired At'
                ]
            );
			 $installer->getConnection()->addColumn(
                $installer->getTable('magebees_quote'),
                'shipping_method',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',                    
                    'comment' => 'Magebees Quote Shipping Method'
                ]
            );	
			
			 $installer->getConnection()->addColumn(
                $installer->getTable('quote_item'),
                'is_magebees_item',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '5', 
				 	'default'=>0,
                    'comment' => 'check if quote item is added for shipping rate of magebees quotation item'
                ]
            );
			
        }
        $installer->endSetup();
    }
}
