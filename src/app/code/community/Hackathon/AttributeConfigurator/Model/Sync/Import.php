<?php

class Hackathon_AttributeConfigurator_Model_Sync_Import extends Mage_Core_Model_Abstract
{

    /** @var Hackathon_AttributeConfigurator_Helper_Data $_helper */
    protected $_helper;

    public function _construct()
    {
        $this->_helper = Mage::helper('hackathon_attributeconfigurator/data');
        $this->bibiBlocksberg();
    }

    /**
     * Sync Import Method coordinates the migration process from
     * XML File Data into the Magento Database
     *
     * return bool
     */

    public function import()
    {
        $_config = Mage::getConfig();


        // 1. Import/Delete Attribute Sets
        $attributesets = $_config->getNode('attributesetslist');


        // 2. Import/Delete Attributes
        $attributes = $_config->getNode('attributeslist');

        if ($this->_validate($attributesets, $attributes)) {

            // 3. Connect Attributes with Attribute Sets using Attribute Groups
        }

    }

    protected function _getAttributeSetsFromXml($attributesets)
    {
        $returnarray = array();
        foreach ($attributesets->children() as $attributeset) {
            $returnarray[] = (string) $attributeset['name'];
        }

        return $returnarray;

    }


	/** @TODO: RICO schön machen und weitermachen :D **/
    protected function _validate($attributesets, $attributes)
    {

        $attributesets = $attributesets;
        $lo_attributesets = $this->_getAttributeSetsFromXml($attributesets);
        $attributes    = $attributes;

        foreach ($attributes->children() as $attribute) {
            foreach ($attribute->attributesets->children() as $attributeset) {
                //echo $attribute["code"] . " gehört zu " . $attributeset["name"] . " <br />";
                if(!in_array($attributeset["name"], $lo_attributesets)){
                    throw new Mage_Adminhtml_Exception("Attributeset '".$attributeset["name"]."' referenced by '".$attribute["code"]."' is not listed in the attributesetslist element");
                }
            }
        }

        foreach ($attributesets->children() as $attributeset) {
            //echo $attributeset['name'] . "<br />";
        }

        return false;
    }

    protected function bibiBlocksberg()
    {
        Mage::getConfig()->loadFile($this->_helper->getImportFilename());
    }
}