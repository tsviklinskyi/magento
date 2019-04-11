<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE ".$installer->getTable('admin/user')."
    ADD COLUMN `orders_type` INT(5) NOT NULL DEFAULT 0,
    ADD COLUMN `products_type` INT(5) NOT NULL DEFAULT 0;
    ");

$installer->endSetup();
