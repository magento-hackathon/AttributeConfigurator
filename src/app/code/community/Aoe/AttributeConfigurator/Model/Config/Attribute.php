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
            $this->_addValidationMessage('entity type id missing');
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
}
