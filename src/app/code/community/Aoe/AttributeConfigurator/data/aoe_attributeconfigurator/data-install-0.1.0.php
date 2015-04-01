<?php
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

// Add Maintainer Flag to eav_attribute
try {
    $installer->run(
        'ALTER TABLE eav_attribute ADD COLUMN'
        . ' `' . Aoe_AttributeConfigurator_Helper_Data::EAV_ATTRIBUTE_MAINTAINED . '` '
        . ' smallint(5) COMMENT \'Inserted by Aoe_AttributeConfigurator\'');
} catch (Exception $e) {
    Mage::logException($e);
    throw $e;
}

$installer->endSetup();
