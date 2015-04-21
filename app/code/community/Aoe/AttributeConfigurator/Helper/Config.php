<?php
/**
 * Class Aoe_AttributeConfigurator_Helper_Config
 *
 * Config helper providing access to the module configuration
 *
 * @category Helper
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
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
     * @param Mage_Core_Model_Store|int|null $store Store reference for configuration
     * @return string
     */
    public function getImportFilename($store = null)
    {
        return trim(Mage::getStoreConfig(self::XML_CONFIG_BASE . 'product_xml_location', $store));
    }

    /**
     * Get full path to import file
     *
     * @param Mage_Core_Model_Store|int|null $store Store reference for configuration
     * @return string
     */
    public function getImportFilePath($store = null)
    {
        $filename = $this->getImportFilename($store);

        return Mage::getBaseDir('var') . DS . $filename;
    }

    /**
     * Get Migration Flag
     *
     * @param Mage_Core_Model_Store|int|null $store Store reference for configuration
     * @return string
     */
    public function getMigrateFlag($store = null)
    {
        return (bool) Mage::getStoreConfig(self::XML_CONFIG_BASE . 'migrate_flag', $store);
    }

    /**
     * Get Array with Attribute Codes that should be skipped
     *
     * @param Mage_Core_Model_Store|int|null $store Store reference for configuration
     * @return array
     */
    public function getSkipAttributeCodes($store = null)
    {
        // Complete String with lots of \n
        $configValue = Mage::getStoreConfig(self::XML_CONFIG_BASE . 'skip_attribute_codes', $store);
        // Explode by \n
        $rawCodes = explode(PHP_EOL, $configValue);
        // Trim lines with just spaces to be empty values
        $trimmedCodes = array_map('trim', $rawCodes);
        // Remove empty values
        $cleanedCodes = array_filter($trimmedCodes);
        
        return $cleanedCodes;
    }

    /**
     * Checks File
     *
     * @param string $xmlLocation Location of XML File
     * @return bool
     */
    public function checkFile($xmlLocation)
    {
        if (empty($xmlLocation)) {
            return false;
        }

        // @codingStandardsIgnoreStart
        $fileExists = file_exists($xmlLocation);
        // @codingStandardsIgnoreEnd
        if (!$fileExists) {
            return false;
        }

        return true;
    }
}
