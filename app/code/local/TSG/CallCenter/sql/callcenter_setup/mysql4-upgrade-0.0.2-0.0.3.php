<?php
$installer = $this;

$installer->startSetup();

$entityTypeId = Mage::getModel('catalog/product')
    ->getResource()
    ->getEntityType()
    ->getId();

$attributeSet = Mage::getModel('eav/entity_attribute_set')
    ->setEntityTypeId($entityTypeId)
    ->setAttributeSetName('TSG');

$attributeSet->validate();
$attributeSet->save();

$attributeSet->initFromSkeleton($entityTypeId)->save();

/* @var TSG_CallCenter_Model_Queue $callcenterQueue */
$callcenterQueue = Mage::getModel('callcenter/queue');

$installer->addAttribute('catalog_product', 'custom_product_type', array(
    'group'                 => '',
    'label'                 => 'Custom Product Type',
    'input'                 => 'select',
    'type'                  => 'varchar',
    'required'              => 1,
    'visible_on_front'      => false,
    'filterable'            => 0,
    'filterable_in_search' => 0,
    'searchable'            => 0,
    'used_in_product_listing' => true,
    'visible_in_advanced_search' => false,
    'comparable'      => 0,
    'user_defined'    => 1,
    'is_configurable' => 0,
    'global'          => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'option'          => ['values' => $callcenterQueue->getProductTypes()],
    'note'            => ''
));

$attributeSetId = $this->getAttributeSetId($entityTypeId, 'TSG');
$this->addAttributeToSet($entityTypeId, $attributeSetId, 'General', 'custom_product_type', 10);

$installer->endSetup();
