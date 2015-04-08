<?php
/**
 * Class Aoe_AttributeConfigurator_Model_Config_Attribute
 *
 * Wrapper class for SimpleXML attribute config objects
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Config_Attribute extends Aoe_AttributeConfigurator_Model_Config_Abstract
{

    const ENTITY_TYPE_CODE = 'catalog_product';

    /**
     * Lazy settings array
     *
     * @var array
     */
    protected $_settingsArray;

    /**
     * Lazy attributesets array
     *
     * @var Aoe_AttributeConfigurator_Model_Config_Attribute_Attributeset[]
     */
    protected $_attributeSets;

    /**
     * @return string
     */
    public function getCode()
    {

        return (string) $this->_xmlElement['code'];
    }

    /**
     * @return string
     */
    public function getEntityTypeId()
    {
        return (string) $this->_getSettingsNode('entity_type_id');
    }

    /**
     * Set the entity type id on the data model
     *
     * @param int $entityTypeId Entity type id
     * @return $this
     */
    protected function _setEntityTypeId($entityTypeId)
    {
        $this->_setSettingsNode('entity_type_id', $entityTypeId);
        return $this;
    }

    /**
     * If the entity type is is missing it can be created using the attribute
     * default entity type code 'product'
     *
     * Throws an exception if the required entity type does not exist.
     *
     * @return this
     * @throws Aoe_AttributeConfigurator_Model_Exception
     */
    protected function _createDefaultEntityTypeId()
    {
        /** @var Mage_Eav_Model_Entity_Type $entityType */
        $entityType = Mage::getModel('eav/entity_type');
        $entityType->loadByCode(self::ENTITY_TYPE_CODE);

        if (!$entityType->getEntityTypeId()) {
            throw new Aoe_AttributeConfigurator_Model_Exception(
                sprintf(
                    'entity type with code \'%s\' does not exist',
                    self::ENTITY_TYPE_CODE
                )
            );
        }

        $typeId = $entityType->getEntityTypeId();
        $this->_setEntityTypeId($typeId);

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return (int) $this->_getSettingsNode('sort_order');
    }

    /**
     * Get the settings as key-value array
     *
     * @return array
     */
    public function getSettingsAsArray()
    {
        if (isset($this->_settingsArray)) {
            return $this->_settingsArray;
        }

        /** @var SimpleXMLElement $settings */
        $settingsNode = $this->_xmlElement->{'settings'};
        $settings = [];
        foreach ($settingsNode->children() as $_setting) {
            /** @var SimpleXmlElement $_setting */

            // pseudocast the parsed xml node values
            $value = (string) $_setting;
            if (is_numeric($value)) {
                $value = (int) $value;
            } else if ('NULL' == $value || 'null' == $value) {
                $value = null;
            }
            $settings[$_setting->getName()] = $value;
        }

        $this->_settingsArray = $settings;

        return $settings;
    }

    /**
     * @return Aoe_AttributeConfigurator_Model_Config_Attribute_Attributeset[]
     */
    public function getAttributeSets()
    {
        if (isset($this->_attributeSets)) {
            return $this->_attributeSets;
        }

        /** @var SimpleXmlElement $attributeSetsNode */
        $attributeSetsNode = $this->_xmlElement->{'attributesets'};

        $iterator = Mage::getModel(
            'aoe_attributeconfigurator/config_attribute_attributeset_iterator',
            $attributeSetsNode
        );

        $result = [];
        foreach ($iterator as $_attributeSet) {
            /** @var Aoe_AttributeConfigurator_Model_Config_Attribute_Attributeset $_attributeSet */
            $result[] = $_attributeSet;
        }

        $this->_attributeSets = $result;
        return $result;
    }

    /**
     * Validate the wrapped xml item.
     * Add validation messages for each error that is found
     *
     * @return void
     */
    protected function _validateXml()
    {
        if (!$this->getCode()) {
            $this->_addValidationMessage('code missing');
        }

        $settingsArray = $this->getSettingsAsArray();
        if (!is_array($settingsArray) || empty($settingsArray)) {
            $this->_addValidationMessage('settings node missing or empty');
        }

        if (!$this->getEntityTypeId()) {
            $this->_createDefaultEntityTypeId();
            $this->_addInfoMessage('Entity type id created: ' . $this->getEntityTypeId());
        }

        /** @var SimpleXMLElement $attributeSetsNode */
        $attributeSetsNode = $this->_xmlElement->{'attributesets'};
        if (!$attributeSetsNode || !$attributeSetsNode->count()) {
            $this->_addValidationMessage('not attributesets defined');
        } else {
            foreach ($this->getAttributeSets() as $_attributeSet) {
                if (!$_attributeSet->validate()) {
                    $this->_mergeValidation($_attributeSet->getValidationMessages());
                }
            }
        }
    }

    /**
     * Get content of the settings node
     *
     * @param string $nodeName Node name to fetch
     * @return SimpleXMLElement
     */
    protected function _getSettingsNode($nodeName)
    {
        $settingsNode = $this->_xmlElement->{'settings'};

        return $settingsNode->{$nodeName};
    }

    /**
     * Set $data on settings node $nodeName
     *
     * @param string     $nodeName Node name inside setting
     * @param string|ing $data     Data to set on $nodeName
     * @return SimpleXmlElement
     */
    protected function _setSettingsNode($nodeName, $data)
    {
        /** @var SimpleXmlElement $settingsNode */
        $settingsNode = $this->_xmlElement->{'settings'};

        $settingsNode->{$nodeName} = $data;

        return $this->_getSettingsNode($nodeName);
    }
}
