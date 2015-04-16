<?php

/**
 * Class Aoe_AttributeConfigurator_Helper_Data
 *
 * @category Helper
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CODE_CURRENT_HASH = 'attributeconfigurator_hash';
    const EAV_ATTRIBUTE_MAINTAINED = 'is_maintained_by_configurator';
    const FILENAME_LOGFILE = 'aoe_attributeconfigurator.log';

    /**
     * Custom Logging
     *
     * @param string    $message   Text
     * @param Exception $exception Optional Exception
     * @param integer   $level     Zend Debug Level
     * @return void
     */
    public function log($message, $exception = null, $level = null)
    {
        $exceptionMessage = '';
        if (!is_null($exception)) {
            Mage::logException($exception);
            $exceptionMessage = $exception->getMessage();
        }
        Mage::log(sprintf('%s %s', $message, $exceptionMessage), $level, self::FILENAME_LOGFILE);
    }

    /**
     * Method creates md5 hash of a given file based on its content.
     * Returns false if no md5 could be generated for a file.
     *
     * @param string $filePath Full path to a file
     *
     * @return bool|string
     */
    public function createFileHash($filePath)
    {
        // @codingStandardsIgnoreStart
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return false;
        }
        // @codingStandardsIgnoreEnd

        return md5_file($filePath);
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
        $filename = $this->_getConfigHelper()->getImportFilename();
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
        if ($attribute && $attribute->getData(self::EAV_ATTRIBUTE_MAINTAINED)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if Column has been added to the eav_attribute Table,
     * the extension won´t work correctly if it is missing.
     *
     * @return bool
     */
    public function checkExtensionInstallStatus()
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $attributeTable = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
        $query = "SHOW COLUMNS FROM " .
            $attributeTable .
            " LIKE :maintainerflag";
        $binds = [
            'maintainerflag' => self::EAV_ATTRIBUTE_MAINTAINED
        ];
        $columnConfig = $read->query($query, $binds)->fetch();
        if ($columnConfig) {
            return true;
        }
        return false;
    }

    /**
     * Returns Property from Constant
     *
     * @return string
     */
    public function getFlagColumnName()
    {
        return self::EAV_ATTRIBUTE_MAINTAINED;
    }

    /**
     * Get the config helper
     *
     * @return Aoe_AttributeConfigurator_Helper_Config
     */
    protected function _getConfigHelper()
    {
        return Mage::helper('aoe_attributeconfigurator/config');
    }
}
