<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_grid'), 'customer_email', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Customer Email'
    ));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_grid'), 'initiator_id', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => true,
        'length'    => 10,
        'after'     => null,
        'comment'   => 'Initiator ID'
    ));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_grid'), 'primary_initiator_id', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => true,
        'length'    => 10,
        'after'     => null,
        'comment'   => 'Primary Initiator ID'
    ));

$installer->endSetup();
