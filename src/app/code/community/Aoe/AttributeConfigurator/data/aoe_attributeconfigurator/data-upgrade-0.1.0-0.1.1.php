<?php
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

// Add Maintainer Flag to eav_attribute
try {
    $directory = Mage::getBaseDir('var') . DS . 'importexport';
    // Ignore Coding Standards for forbidden functions
    // @codingStandardsIgnoreStart
    mkdir($directory);
    mkdir($directory . DS . 'product_attributes');
    // @codingStandardsIgnoreEnd
} catch (Exception $e) {
    Mage::logException($e);
    throw $e;
}

$installer->endSetup();
