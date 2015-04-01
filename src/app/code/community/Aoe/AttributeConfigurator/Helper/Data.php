<?php

/**
 * Class Aoe_AttributeConfigurator_Helper_Data
 *
 * @category Helper
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_FILENAME = 'catalog/attribute_configurator/product_xml_location';
    const XML_PATH_CURRENT_HASH = 'attributeconfigurator/hashes/current';

    /**
     * Build Import Filename from Store Config
     *
     * @return string
     */
    public function getImportFilename()
    {
        return Mage::getBaseDir() . DS . trim(Mage::getStoreConfig(self::XML_PATH_FILENAME), '/\ ');
    }

    /**
     * Method creates md5 hash of a given file based on its content
     * Intent: We need to figure out when to re-import a file so we have to know when its content changes
     *
     * @param string $file path and filename of Attribute Configuration XML
     *
     * @return bool|string
     */
    public function createFileHash($file)
    {
        // Ignore Coding Standards for forbidden functions
        // @codingStandardsIgnoreStart
        if (file_exists($file)) {
            return md5_file($file);
        }
        // @codingStandardsIgnoreEnd
        return false;
    }

    /**
     * Check if the XML file is newer than the last imported one.
     *
     * @return bool
     */
    public function isAttributeXmlNewer()
    {
        $filename = $this->getImportFilename();
        $currentFileHash = Mage::getStoreConfigFlag(self::XML_PATH_CURRENT_HASH);
        $latestFileHash = $this->createFileHash($filename);
        if ($latestFileHash !== $currentFileHash) {
            return true;
        }
        return false;
    }

    /**
     * Check if Attribute is maintained by extension, return false if not
     * (keep system and third party attributes untouched)
     *
     * @param  Mage_Eav_Model_Entity_Attribute $attribute Attribute Model
     * @return bool
     */
    public function checkAttributeMaintained($attribute)
    {
        if (!$attribute || $attribute->getData('is_maintained_by_configurator') !== 1) {
            return false;
        }
        return true;
    }
}
