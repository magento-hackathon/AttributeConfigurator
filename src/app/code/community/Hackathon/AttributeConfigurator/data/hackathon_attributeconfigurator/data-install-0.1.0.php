<?php
$installer = $this;
$installer->startSetup();

// Add Maintainer Flag to eav_attribute
try{
    $installer->run("ALTER TABLE eav_attribute ADD COLUMN is_maintained_by_configurator smallint(5)");
}catch (Exception $e){
    Mage::exception('hackathon_attributeconfigurator data upgrad exception: '.$e->getMessage());
}

$installer->endSetup();
