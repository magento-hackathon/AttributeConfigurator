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

    /** @var Aoe_AttributeConfigurator_Model_Config $_config */
    protected $_config;

    /**
     * Constructor
     *
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
        // 1. Create/Update Attribute Sets
        /** @var Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset $attributeSetModel */
        $attributeSetModel = Mage::getModel('aoe_attributeconfigurator/sync_import_attributeset', $this->_config->getAttributeSets());
        $attributeSetModel->run();

        // 2. Create/Update Attributes
        /** @var Aoe_AttributeConfigurator_Model_Attribute $attributeModel */
        $attributeModel = Mage::getModel('aoe_attributeconfigurator/attribute', $this->_config->getAttributes());
        $attributeModel->run();

        // TODO: Refactor this into the attribute model
        //if ($this->_validate($attributesets, $attributes)) {
            // 3. Connect Attributes with Attribute Sets using Attribute Groups
        //}
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
     * Load Config Element
     *
     * @return void
     */
    protected function loadConfiguration()
    {
        /** @var Aoe_AttributeConfigurator_Model_Config _config */
        $this->_config = Mage::getModel('aoe_attributeconfigurator/config');
        $this->_config->loadCustomConfigXml($this->_helper->getImportFilename());
    }
}
