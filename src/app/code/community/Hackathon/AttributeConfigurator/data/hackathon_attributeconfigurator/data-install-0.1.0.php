<?php
/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

//$io = new Varien_Io_File();
//$io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'importexport'.DS.'product_attributes');

$installer->getConnection()->addColumn(
        $installer->getTable('eav/attribute'),
        'is_maintained_by_configurator',
        'SMALLINT(5)'
);

$installer->endSetup();
