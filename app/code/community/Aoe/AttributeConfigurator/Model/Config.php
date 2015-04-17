<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Config
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Config extends Mage_Core_Model_Config
{
    const CONFIG_ATTRIBUTE_SETS = 'aoe_attributeconfigurator/attributesets',
          CONFIG_ATTRIBUTES     = 'aoe_attributeconfigurator/attributes';

    /**
     * Lazy loaded config
     *
     * @var Varien_Simplexml_Element $_xml
     */
    protected $_xml;

    /**
     * Get attributesets xml config node
     *
     * @return Varien_Simplexml_Element
     * @throws Aoe_AttributeConfigurator_Model_Exception
     */
    public function getAttributeSets()
    {
        $result = $this->_getXml()
            ->descend(self::CONFIG_ATTRIBUTE_SETS);

        if (false === $result) {
            throw new Aoe_AttributeConfigurator_Model_Exception(
                sprintf(
                    'config xml does not contain \'%s\'.',
                    self::CONFIG_ATTRIBUTE_SETS
                )
            );
        }

        return $result;
    }

    /**
     * Get attributes xml config node
     *
     * @return Varien_Simplexml_Element
     * @throws Aoe_AttributeConfigurator_Model_Exception
     */
    public function getAttributes()
    {
        $result = $this->_getXml()
            ->descend(self::CONFIG_ATTRIBUTES);

        if (false === $result) {
            throw new Aoe_AttributeConfigurator_Model_Exception(
                sprintf(
                    'config xml does not contain \'%s\'.',
                    self::CONFIG_ATTRIBUTES
                )
            );
        }

        return $result;
    }

    /**
     * Lazy getter for the config xml
     *
     * @return Varien_Simplexml_Element
     * @throws Aoe_AttributeConfigurator_Model_Exception
     */
    protected function _getXml()
    {
        if (isset($this->_xml)) {
            return $this->_xml;
        }

        $xml = $this->_loadXml();
        $this->_xml = $xml;

        return $this->_xml;
    }

    /**
     * Load the xml config file
     *
     * @return Varien_Simplexml_Element
     * @throws Aoe_AttributeConfigurator_Model_Exception
     */
    protected function _loadXml()
    {
        $filePath = $this->_getConfigHelper()->getImportFilePath();

        // @codingStandardsIgnoreStart
        if (!is_readable($filePath)) {
            throw new Aoe_AttributeConfigurator_Model_Exception(
                sprintf(
                    'configured import file \'%s\' is not readable.',
                    $filePath
                )
            );
        }
        // @codingStandardsIgnoreEnd

        $xml = simplexml_load_file($filePath, 'Varien_Simplexml_Element');
        if (false === $xml) {
            throw new Aoe_AttributeConfigurator_Model_Exception(
                sprintf(
                    'unable to load xml file \'%s\'.',
                    $filePath
                )
            );
        }

        return $xml;
    }

    /**
     * Get the module config helper
     *
     * @return Aoe_AttributeConfigurator_Helper_Config
     */
    protected function _getConfigHelper()
    {
        return Mage::helper('aoe_attributeconfigurator/config');
    }
}
