<?php
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

// Add Maintainer Flag to eav_attribute
try {
    /** @var Aoe_AttributeConfigurator_Helper_Data $helper */
    $helper = Mage::helper('aoe_attributeconfigurator/data');
    $directory = Mage::getStoreConfig($helper->getXmlImportPath());
    // @codingStandardsIgnoreStart
    mkdir($directory);
    // @codingStandardsIgnoreEnd
} catch (Exception $e) {
    Mage::logException($e);
    throw $e;
}

$installer->endSetup();
