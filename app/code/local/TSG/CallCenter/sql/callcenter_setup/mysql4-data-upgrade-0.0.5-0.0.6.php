<?php
$installer = $this;

$installer->startSetup();

$modelOrder = Mage::getModel('sales/order');
$ordersCollection = $modelOrder->getCollection();
foreach ($ordersCollection as $order) {
    $installer->run("UPDATE `{$installer->getTable('sales/order_grid')}` SET `customer_email` = '{$order->getCustomerEmail()}' WHERE `entity_id` = {$order->getId()};");
}

$installer->endSetup();
