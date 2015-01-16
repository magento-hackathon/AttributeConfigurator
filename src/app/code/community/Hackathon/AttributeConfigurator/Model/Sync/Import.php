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
     * Parse the _xml for Attriubtes and Sets
     *
     * @return $this
     */
    public function getDataFromXml()
    {
        $this->_getAttributeSetsFromXml();
        $this->_getAttributesFromXml();
        return $this;
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
        $this->getDataFromXml();

        // 1. Import/Delete Attribute Sets
        // 2. Import/Delete Attributes

        if ($this->validate($this->_setData, $this->_attrData)) {
            // 3. Connect Attributes with Attribute Sets using Attribute Groups
        }

    }


    /**
     * Validate Attributes and Attributesets
     *
     * @param Varien_Simplexml_Element $attributesets
     * @param Varien_Simplexml_Element $attributes
     *
     * @return $this
     * @throws Mage_Adminhtml_Exception
     */
    public function validate(
        Varien_Simplexml_Element $attributesets = NULL,
        Varien_Simplexml_Element $attributes = NULL
    ) {
        if (is_null($attributesets)) {
            $attributesets = $this->_setData;
        }
        if (is_null($attributes)) {
            $attributes = $this->_attrData;
        }

        $attributsetnames = $this->getAttributesetNames($attributesets);
        //$names = $attributes->xpath("attribute/attributesets/attributeset/@name");

        foreach ($attributes->children() as $attribute) {
            //Check if the attribute is included in at least one attributeset
            if ($attribute->attributesets->count() == 0) {
                throw new Mage_Adminhtml_Exception(
                        "Attribute '".$attribute["code"].
                        "' is not part of a Attributeset"
                );
            }
            /** @var Varien_Simplexml_Element $attributeset */
            foreach ($attribute->attributesets->children() as $attributeset) {
                //Check if the attributeset is in the Config (-> is the xml consistant?)
                if (!in_array($attributeset["name"], $attributsetnames)) {
                    throw new Mage_Adminhtml_Exception(
                            "Attributeset '".$attributeset["name"].
                            "' referenced by '".$attribute["code"]."'
                        is not listed in the attributesetslist element"
                    );
                }
                //Check if the attributeset contains only one group
                if ($attributeset->attributegroup->count() == 0) {
                    throw new Mage_Adminhtml_Exception(
                            "Attributeset '".$attributeset["name"].
                            "' referenced by '".$attribute["code"]."'
                        does not contain a attributegroup"
                    );
                }
                if ($attributeset->attributegroup->count() > 1) {
                    throw new Mage_Adminhtml_Exception(
                            "Attributeset '".$attributeset["name"].
                            "' referenced by '".$attribute["code"]."'
                        contains more than one attributegroup"
                    );
                }

            }


        }
        return $this;
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
     * Gets the attributesets
     *
     * @throws Mage_Core_Exception
     * @return $this
     */

    protected function _getAttributeSetsFromXml()
    {

        /** @var Varien_Simplexml_Element $attributesets */
        $attributesets = $this->_xml->getNode('attributesetslist');
        if (!$attributesets->hasChildren()) {
            Mage::throwException('No attributesets found in file');
        } else {
            $this->_setData = $attributesets;
        }
        return $this;
    }

    /**
     * Gets the attributes
     *
     * @throws Mage_Core_Exception
     * @return $this
     */
    protected function _getAttributesFromXml()
    {

        /** @var Varien_Simplexml_Element $attributes */
        $attributes = $this->_xml->getNode('attributeslist');

        if (!$attributes->hasChildren()) {
            Mage::throwException('No attributes found in file');
        } else {
            $this->_attrData = $attributes;
        }
        return $this;
    }


    /**
     * Parses Attributesetnames
     * @param Varien_Simplexml_Element $attributesets
     *
     * @return array
     */
    public function getAttributesetNames(Varien_Simplexml_Element $attributesets = NULL)
    {
        if (is_null($attributesets)) {
            $attributesets = $this->_setData;
        }
        $returnarray = array();
        $names = $attributesets->xpath('attributeset/@name');
        foreach ($names as $name) {
            $returnarray[] = (string) $name->name;
        }
        return $returnarray;
    }
}
