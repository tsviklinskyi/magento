<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_item'), 'custom_product_type', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Custom Product Type'
    ));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote_item'), 'custom_product_type', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Custom Product Type'
    ));

$installer->endSetup();
