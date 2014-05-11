<?php

class Hackathon_AttributeConfigurator_Model_Sync_Import extends Mage_Core_Model_Abstract {

    /**
     * Attribute Data
     * @var array
     */
    protected $_attributeData = array();

    /**
     * Attribute-Set Data
     * @var array
     */
    protected $_setData = array();

    protected $_groupData = array();


    public function _construct(){
        $this->bibiBlocksberg();
    }

    /**
     * Sync Import Method coordinates the migration process from
     * XML File Data into the Magento Database
     *
     * return bool
     */

    public function import(){
        // 1. Import/Delete Attribute Sets
        $_config = Mage::getConfig();

        $attributesets = $_config->getNode('attributesetslist');


        // 2. Import/Delete Attributes

        // 3. Connect Attributes with Attribute Sets using Attribute Groups
    }

    private function bibiBlocksberg(){
        Mage::getConfig()->loadFile(Hackathon_AttributeConfigurator_Model_Observer::XML_PATH_FILENAME);
    }

    public function prepareAttributeSet($xml)
    {
        $this->_setData = json_decode(json_encode($xml->attributesets), true);
        return $this;
    }


    /**
     * @param $xml
     * @return $this
     */
    public function prepareAttributes($xml)
    {
        $this->_attributeData = json_decode(json_encode($xml->attributes), true);
        return $this;
    }

    public function prepareAttributeGroups($xml)
    {
        $this->_groupData = json_decode(json_encode($xml->attributegroups), true);
        return $this;
    }
}