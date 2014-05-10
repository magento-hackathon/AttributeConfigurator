<?php

/**
 * Class Hackathon_AttributeConfigurator_Model_Observer
 */
class Hackathon_AttributeConfigurator_Model_Observer
{
    const XML_PATH_FILENAME = 'catalog/attribute_configurator/product_xml_location';
    const XML_PATH_CURRENT_HASH = 'attributeconfigurator/hashes/current';

    protected $_helper;

    public function __construct()
    {
        $this->_helper = Mage::helper('hackathon_attributeconfigurator');
    }

    /**
     * 
     *
     * @param Varien_Event_Observer $observer
     *
     */
    public function controllerActionPredispatchAdminhtml(Varien_Event_Observer $observer)
    {
        if($this->isAttributeXmlNewer()) {
            Mage::getModel('hackathon_attributeconfigurator/sync_import')->import();
        }
    }

    /**
     * Check if the XML file is newer than the last imported one.
     *
     * return bool
     */
    protected function isAttributeXmlNewer()
    {
        $filename        = Mage::getStoreConfig(self::XML_PATH_FILENAME);
        $currentFileHash = Mage::getStoreConfigFlag(self::XML_PATH_CURRENT_HASH);
        $latestFileHash  = $this->_helper->createFileHash($filename);

        if ($latestFileHash !== $currentFileHash) {
            return true;
        }

        return false;
    }
}
