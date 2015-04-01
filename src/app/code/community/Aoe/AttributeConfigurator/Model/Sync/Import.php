<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Sync_Import
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Sync_Import extends Mage_Core_Model_Abstract
{
    /** @var Aoe_AttributeConfigurator_Helper_Data $_helper */
    protected $_helper;

    /** @var array $_attributeData Attribute Config Data */
    protected $_attributeData = [];

    /** @var array $_setData Attribute Set Config Data */
    protected $_setData = [];

    /** @var array $_groupData Attribute Group Config Data */
    protected $_groupData = [];

    /** @var Aoe_AttributeConfigurator_Model_Config $_config */
    protected $_config;

    /**
     * Constructor
     * @return void
     */
    public function _construct()
    {
        $this->_helper = Mage::helper('aoe_attributeconfigurator/data');
        $this->loadConfiguration();
    }

    /**
     * Sync Import Method coordinates the migration process from
     * XML File Data into the Magento Database
     *
     * return bool
     * @return void
     */
    public function import()
    {
        $_config = Mage::getConfig();

        // 1. Import/Delete Attribute Sets
        $attributesets = $_config->getNode('attributesetslist');

        // 2. Import/Delete Attributes
        $attributes = $_config->getNode('attributeslist');
        return;
        if ($this->_validate($attributesets, $attributes)) {
            // 3. Connect Attributes with Attribute Sets using Attribute Groups
        }
    }

    /**
     * Get Attribute Set from XML
     *
     * @param  Varien_Simplexml_Element $xml Attribute XML Data
     * @return $this
     */
    public function prepareAttributeSet($xml)
    {
        $this->_setData = json_decode(json_encode($xml->attributesets), true);
        return $this;
    }

    /**
     * Parse XML for Attribute Set
     *
     * @param  Varien_Simplexml_Element $attributesets Attribute Set Data
     * @return array
     */
    protected function _getAttributeSetsFromXml($attributesets)
    {
        $result = [];
        foreach ($attributesets->children() as $attributeset) {
            $result[] = (string) $attributeset['name'];
        }
        return $result;
    }

    /**
     * Fetch Attributes from XML
     *
     * @param  Varien_Simplexml_Element $xml Attribute XML Data
     * @return $this
     */
    public function prepareAttributes($xml)
    {
        $this->_attributeData = json_decode(json_encode($xml->attributes), true);
        return $this;
    }

    /**
     * Validate Attributesets and Attributes
     *
     * @TODO: This is not complete and probably not working at all
     *
     * @param  Varien_Simplexml_Element $attributesets Attribute Set XML Data
     * @param  Varien_Simplexml_Element $attributes    Attributes XML Data
     * @return bool
     * @throws Mage_Adminhtml_Exception
     */
    protected function _validate($attributesets, $attributes)
    {
        $attributesetNames = $this->_getAttributeSetsFromXml($attributesets);

        foreach ($attributes->children() as $attribute) {
            foreach ($attribute->attributesets->children() as $attributeset) {
                echo $attribute["code"] . " gehört zu " . $attributeset["name"] . " <br />";
                if (!in_array($attributeset["name"], $attributesetNames)) {
                    Mage::throwException(sprintf('Attributeset %s referenced by %s is not listed in the attributesetslist element', $attributeset["name"], $attribute["code"]));
                }

            }
        }
        return false;
    }

    /**
     * Fetches Attribute Groups from XML
     *
     * @param  Varien_Simplexml_Element $xml Attribute Group Configuration
     * @return $this
     */
    public function prepareAttributeGroups($xml)
    {
        // Encode/decode to easily get Array Format
        $this->_groupData = json_decode(json_encode($xml->attributegroups), true);
        return $this;
    }

    /**
     * Load XML File via Varien Simplexml to Mage Config
     *
     * @return void
     */
    protected function loadConfiguration()
    {
        $this->_config = Mage::getModel('aoe_attributeconfigurator/config');
        $this->_config->loadCustomConfigXml($this->_helper->getImportFilename());
    }
}
