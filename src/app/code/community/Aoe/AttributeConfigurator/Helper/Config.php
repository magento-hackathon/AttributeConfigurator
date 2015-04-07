<?php
/**
 * Class Aoe_AttributeConfigurator_Helper_Config
 *
 * Config helper providing access to the module configuration
 *
 * @category Helper
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_CONFIG_BASE = 'catalog/attribute_configurator/';

    /**
     * Build Import Filename from Store Config
     *
     * @param Mage_Core_Model_Store|int $store Store reference for configuration
     * @return string
     */
    public function getImportFilename($store = null)
    {
        return trim(Mage::getStoreConfig(self::XML_CONFIG_BASE . 'product_xml_location', $store));
    }
}
