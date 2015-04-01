<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Config
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Config extends Mage_Core_Model_Config
{
    const CONFIG_ATTRIBUTE_SETS = 'aoe_attributeconfigurator/attributesets';
    const CONFIG_ATTRIBUTES = 'aoe_attributeconfigurator/attributes';

    /** @var Mage_Core_Model_Config_Base $_config */
    private $_config;

    /**
     * @param  string $path File Path
     * @return $this
     */
    public function loadCustomConfigXml($path)
    {
        $this->_config = Mage::getModel('core/config_base');
        $this->_config->loadFile(Mage::getBaseDir('var') . DS . $path);
    }

    /**
     * @return Varien_Simplexml_Element
     */
    public function getAttributeSets()
    {
        return $this->_config->getNode(self::CONFIG_ATTRIBUTE_SETS);
    }

    /**
     * @return Varien_Simplexml_Element
     */
    public function getAttributes()
    {
        return $this->_config->getNode(self::CONFIG_ATTRIBUTES);
    }
}
