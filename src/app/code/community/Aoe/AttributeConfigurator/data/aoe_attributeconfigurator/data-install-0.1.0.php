<?php
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

// Add Maintainer Flag to eav_attribute
try {
    $installer->run("ALTER TABLE eav_attribute ADD COLUMN is_maintained_by_configurator smallint(5) COMMENT 'Inserted by Aoe_AttributeConfigurator'");
} catch (Exception $e) {
    Mage::logException($e);
    throw $e;
}

$installer->endSetup();
