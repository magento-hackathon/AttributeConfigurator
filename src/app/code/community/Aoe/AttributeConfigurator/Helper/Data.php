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
    const CODE_CURRENT_HASH = 'attributeconfigurator_hash';

    /**
     * @return string
     */
    public function getXmlImportPath()
    {
        return self::XML_PATH_FILENAME;
    }

    /**
     * Build Import Filename from Store Config
     *
     * @return string
     */
    public function getImportFilename()
    {
        return trim(Mage::getStoreConfig($this->getXmlImportPath()));
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
     * Writes Values with given code to the core_flag Table (underutilized feature)
     *
     * @param string $code  flag_code for core_flag table
     * @param string $value value to write
     * @return void
     */
    public function setFlagValue($code, $value)
    {
        /** @var Mage_Core_Model_Flag $flagModel */
        $flagModel = Mage::getModel('core/flag', ['flag_code' => $code])->loadSelf();
        $flagModel->setFlagData($value);
        $flagModel->save();
    }

    /**
     * @param  string $code flag_code for core_flag table
     * @return stdClass
     */
    public function getFlagValue($code)
    {
        /** @var Mage_Core_Model_Flag $flagModel */
        $flagModel = Mage::getModel('core/flag', ['flag_code' => $code])->loadSelf();
        return $flagModel->getFlagData();
    }

    /**
     * Check if the XML file is newer than the last imported one.
     *
     * @return bool
     */
    public function isAttributeXmlNewer()
    {
        $filename = $this->getImportFilename();
        $currentFileHash = $this->getFlagValue(self::CODE_CURRENT_HASH);
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