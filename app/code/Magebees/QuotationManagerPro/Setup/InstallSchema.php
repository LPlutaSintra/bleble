<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magebees\QuotationManagerPro\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'magebees_quote'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magebees_quote')
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Magebees Quote Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Magebees Store Id'
        )->addColumn(
            'expired_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Magebees Quote Expired At'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Magebees Quote Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
            'Magebees Quote Updated At'
        )->addColumn(
            'is_quote',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '1'],
            'Is Quote'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '1'],
            'Magebees Quote Is Active'
        )->addColumn(
              'status',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              null,
              ['unsigned' => true,'nullable' => false],
              'Magebees Quote Status'
          )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Magebees Quote Customer Id'
        )->addColumn(
            'ship_address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Magebees Quote Shipping Address Id'
        )->addColumn(
            'bill_address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Magebees Quote Billing Address Id'
        )->addColumn(
              'increment_id',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              null,
              ['unsigned' => true],
              'Magebees Quote Increment Id'
          )->addColumn(
              'assign_to',
              \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
              null,
              ['unsigned' => true],
              'Magebees Quote Assign To'
          )->addColumn(
              'currency_code',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              null,
              ['unsigned' => true],
              'Magebees Quote Currency Code'
          )->addColumn(
              'base_currency_code',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              null,
              ['unsigned' => true],
              'Magebees Quote Base Currency Code'
          )->addColumn(
            'shipping_rate_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Magebees Quote Shipping Rate Include Tax'
        )->addColumn(
            'shipping_rate_excl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Magebees Quote Shipping Rate Exclude Tax'
        )->addColumn(
            'shipping_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Magebees Quote Shipping Method'
        )->addColumn(
              'is_backend',
              \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
              null,
              ['unsigned' => true],
              'Magebees Quote Is Backend'
          )->addColumn(
            'quote_request_info',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Magebees Quote Request Information'
        );
        $installer->getConnection()->createTable($table);

         /**
         * Create table 'magebees_quote_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magebees_quote_item')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Quote Item Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Magebees Quote Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Magebees Quote Product Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Magebees Quote Store Id'
        )->addColumn(
            'parent_item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Magebees Quote Parent Item Id'
        )->addColumn(
            'qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Magebees Quote Item Qty'
        )->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Magebees Quote Item Price'
        )->addColumn(
            'price_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Magebees Quote Item Price Include Tax'
        )->addColumn(
            'product_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Magebees Quote Product Type'
        )->addColumn(
            'has_options',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '1'],
            'Magebees Quote Has Options'
        )->addColumn(
            'request_info',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Magebees Quote Item Request Information'
        )->addIndex(
            $installer->getIdxName('magebees_quote_item', ['quote_id']),
            ['quote_id']
        )->addIndex(
            $installer->getIdxName('magebees_quote_item', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('magebees_quote_item', 'quote_id', 'magebees_quote', 'quote_id'),
            'quote_id',
            $installer->getTable('magebees_quote'),
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('magebees_quote_item', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        );
        $installer->getConnection()->createTable($table);
		
		
		  /**
         * Create table 'magebees_quote_item_option'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magebees_quote_item_option')
        )->addColumn(
            'option_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Magebees Quote Item Option Id'
        )->addColumn(
            'item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Magebees Quote Item Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Magebees Quote Product Id'
        )->addColumn(
            'code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Magebees Quote Option Code'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Magebees Quote Option Value'
        )->addIndex(
            $installer->getIdxName('magebees_quote_item_option', ['item_id']),
            ['item_id']
        )->addForeignKey(
            $installer->getFkName('magebees_quote_item_option', 'item_id', 'magebees_quote_item', 'id'),
            'item_id',
            $installer->getTable('magebees_quote_item'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);
		
		
		
		 /**
         * Create table 'magebees_quote_request_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magebees_quote_request_item')
        )->addColumn(
            'request_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Magebees Quote Request Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Magebees Quote Id'
        )->addColumn(
            'item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Magebees Quote Item Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Magebees Quote Product Id'
        )->addColumn(
            'request_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Magebees Quote Item Request Qty'
        )->addColumn(
            'request_qty_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Magebees Quote Item Request Qty Price'
        )->addColumn(
            'req_qty_price_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Magebees Quote Item Request Qty Price Include Tax'
        )->addColumn(
            'cost_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Magebees Quote Item Cost Price'
        )->addColumn(
            'is_default',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '1'],
            'Magebees Quote Is Default'
        )->addIndex(
            $installer->getIdxName('magebees_quote_request_item', ['item_id']),
            ['item_id']
        )->addForeignKey(
            $installer->getFkName('magebees_quote_request_item', 'item_id', 'magebees_quote_item', 'id'),
            'item_id',
            $installer->getTable('magebees_quote_item'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);
		
		
		 /**
         * Create table 'magebees_quote_message'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magebees_quote_message')
        )->addColumn(
            'message_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Magebees Quote Message Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Magebees Quote Id'
        )->addColumn(
            'communication',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Magebees Quote Communication'
        )->addIndex(
            $installer->getIdxName('magebees_quote_message', ['quote_id']),
            ['quote_id']
        )->addForeignKey(
            $installer->getFkName('magebees_quote_message', 'quote_id', 'magebees_quote', 'quote_id'),
            'quote_id',
            $installer->getTable('magebees_quote'),
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);
		
		
		 /**
         * Create table 'magebees_quote_files'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magebees_quote_files')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Magebees Quote File Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Magebees Quote Id'
        )->addColumn(
            'file_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Magebees Quote File Name'
        )->addColumn(
            'file_path',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Magebees Quote File Path'
        )->addIndex(
            $installer->getIdxName('magebees_quote_files', ['quote_id']),
            ['quote_id']
        )->addForeignKey(
            $installer->getFkName('magebees_quote_files', 'quote_id', 'magebees_quote', 'quote_id'),
            'quote_id',
            $installer->getTable('magebees_quote'),
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);
		
		
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
                $installer->getTable('quote_item'),
                'is_magebees_item',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '5',   
			 		'default'=>0,
                    'comment' => 'check if quote item is added for shipping rate of magebees quotation item'
                ]
            );
        $installer->endSetup();
    }
}
