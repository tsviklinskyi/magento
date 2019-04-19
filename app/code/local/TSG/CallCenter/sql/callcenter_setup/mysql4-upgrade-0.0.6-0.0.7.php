<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addForeignKey(
    $installer->getFkName('callcenter/queue', 'user_id', 'admin/user', 'user_id'),
    $installer->getTable('callcenter/queue'),
    'user_id',
    $installer->getTable('admin/user'),
    'user_id'
);

$installer->endSetup();
