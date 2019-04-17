<?php
define('MAGENTO_ROOT', getcwd());
$mageFilename = MAGENTO_ROOT . '/app/Mage.php';
require MAGENTO_ROOT . '/app/bootstrap.php';
require_once $mageFilename;
Mage::init();
$model = Mage::getModel('callcenter/observer_queue_handler');
$model->queueDistribution();
exit('end');