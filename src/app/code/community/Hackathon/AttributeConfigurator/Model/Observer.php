<?php
/**
 * Class Hackathon_AttributeConfigurator_Model_Observer
 */
class Hackathon_AttributeConfigurator_Model_Observer
{
    protected $_helper;

    public function __construct()
    {
        $this->_helper = Mage::helper('hackathon_attributeconfigurator/data');
    }

    /**
     * Poll for Changes in XML
     *
     * @param Varien_Event_Observer $observer
     *
     */
    public function controllerActionPredispatchAdminhtml(Varien_Event_Observer $observer)
    {
        if($this->_helper->isAttributeXmlNewer()) {
            Mage::getModel('hackathon_attributeconfigurator/sync_import')->import();
        }
    }
}
