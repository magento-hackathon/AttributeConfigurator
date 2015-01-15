<?php
/**
 * Class Hackathon_AttributeConfigurator_Model_Sync_Import
 */
class Hackathon_AttributeConfigurator_Model_Sync_Import extends Mage_Core_Model_Abstract
{
    /** @var Hackathon_AttributeConfigurator_Helper_Data $_helper */
    protected $_helper;

    /**
     * Attribute Data
     * @var array
     */
    protected $_attributeData = array();

    /**
     * Attribute-Set Data
     * @var Varien_Simplexml_Element
     */
    protected $_setData;

    /**
     * Attribute Data
     * @var Varien_Simplexml_Element
     */
    protected $_attrData;

    /**
     * Group Data
     * $_groupData
     * @var array
     */
    protected $_groupData = array();

    /**
     * The XML to import
     * @var Varien_Simplexml_Config
     */
    protected $_xml;

    public function _construct()
    {
        $this->_helper = Mage::helper('hackathon_attributeconfigurator/data');
        $this->getImport();
    }


    /**
     * Load XML File via Varien Simplexml to Mage Config
     */
    protected function getImport()
    {
        if (!is_readable($this->_helper->getImportFilename())) {
            Mage::throwException('Import file can not be loaded');
        }
        $this->_xml = new Varien_Simplexml_Config($this->_helper->getImportFilename());
    }

    /**
     * Sync Import Method coordinates the migration process from
     * XML File Data into the Magento Database
     *
     * return bool
     */
    public function import()
    {

        //Get the data
        $this->_getAttributeSetsFromXml();
        $this->_getAttributesFromXml();



        // 1. Import/Delete Attribute Sets
        // 2. Import/Delete Attributes
        /** @var Mage_Core_Model_Config_Element $attributes */

        if ($this->_validate($this->_setData, $this->_attrData)) {
            // 3. Connect Attributes with Attribute Sets using Attribute Groups
        }

    }

    /**
     * Validate Attributesets and Attributes
     * @param $attributesets
     * @param $attributes
     *
     * @return bool
     */
    public function _validate($attributesets, $attributes)
    {


//        foreach ($attributes->children() as $attribute) {
//            foreach ($attribute->attributesets->children() as $attributeset) {
//                echo $attribute["code"] . " gehört zu " . $attributeset["name"] . " <br />";
//                }
//        }
//
//
//        if (!in_array($attributeset["name"], $lo_attributesets)) {
//            throw new Mage_Adminhtml_Exception(
//                    "Attributeset '".$attributeset["name"].
//                    "' referenced by '".$attribute["code"]."'
//                    is not listed in the attributesetslist element");
//        }

//        foreach ($attributesets->children() as $attributeset) {
//            echo $attributeset['name'] . "<br />";
//        }
        return false;
    }

    /**
     * Gets the attributesets
     *
     * @throws Mage_Core_Exception
     * @return void
     */

    protected function _getAttributeSetsFromXml()
    {

        /** @var Mage_Core_Model_Config_Element $attributesets */
        $attributesets = $this->_xml->getNode('attributesetslist');
        if (!$attributesets->hasChildren()) {
            Mage::throwException('No attributesets found in file');
        } else {
            $this->_setData = $attributesets;
        }
    }

    /**
     * Gets the attributes
     *
     * @throws Mage_Core_Exception
     * @return void
     */
    protected function _getAttributesFromXml()
    {

        /** @var Mage_Core_Model_Config_Element $attributes */
        $attributes = $this->_xml->getNode('attributeslist');

        if (!$attributes->hasChildren()) {
            Mage::throwException('No attributes found in file');
        } else {
            $this->_attrData = $attributes;
        }
    }


}
