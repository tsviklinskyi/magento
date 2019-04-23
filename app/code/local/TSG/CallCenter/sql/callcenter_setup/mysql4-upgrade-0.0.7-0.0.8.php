<?php
$installer = $this;

$installer->startSetup();

$entities = array(
    'quote_item',
    'order_item'
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'custom_product_type', $options);
}

$installer->endSetup();
