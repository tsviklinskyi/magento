<?php
$installer = $this;

$installer->startSetup();

/* @var Mage_Sales_Model_Order $modelOrder */
$modelOrder = Mage::getModel('sales/order');
$ordersCollection = $modelOrder->getCollection();
foreach ($ordersCollection as $order) {
    $installer->run("UPDATE `{$installer->getTable('sales/order_grid')}` SET `customer_email` = '{$order->getCustomerEmail()}' WHERE `entity_id` = {$order->getId()};");
}

$installer->endSetup();
