<?php
$installer = $this;

$installer->startSetup();

$role = Mage::getModel("admin/roles")
    ->setName('CallCenterSpecialist')
    ->setRoleType('G')
    ->save();

$role = Mage::getModel("admin/roles")
    ->setName('CallCenterCoordinator')
    ->setRoleType('G')
    ->save();

$installer->endSetup();