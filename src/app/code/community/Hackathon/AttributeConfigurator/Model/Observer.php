<?php
/**
 * Class Hackathon_AttributeConfigurator_Model_Observer
 */
class Hackathon_AttributeConfigurator_Model_Observer
{
    /** @var Hackathon_AttributeConfigurator_Helper_Data */
    protected $_helper;

    public function __construct()
    {
        $this->_helper = Mage::helper('hackathon_attributeconfigurator/data');
    }

    /**
     * Poll for Changes in XML
     *
     * @param Varien_Event_Observer $observer
     * @return void
     *
     */
    public function controllerActionPredispatchAdminhtml(Varien_Event_Observer $observer)
    {
        if ($this->_helper->isAttributeXmlNewer()) {
            /** @var Hackathon_AttributeConfigurator_Model_Sync_Import $importer */
            $importer = Mage::getModel('hackathon_attributeconfigurator/sync_import');
            $importer->import();
        }
    }
}
