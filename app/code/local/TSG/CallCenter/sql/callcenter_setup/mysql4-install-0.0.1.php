<?php
$installer = $this;

$installer->startSetup();

Mage::getModel("admin/roles")
    ->setName('CallCenterSpecialist')
    ->setRoleType('G')
    ->save();

Mage::getModel("admin/roles")
    ->setName('CallCenterCoordinator')
    ->setRoleType('G')
    ->save();

$installer->endSetup();