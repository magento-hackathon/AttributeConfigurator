<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Shell
 *
 * Container for all Shell related Methods
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Shell extends Mage_Core_Model_Abstract
{
    const PARAM_RUN_ALL = 'runAll';

    /**
     * Init Shell Settings
     *
     * @param array $args Arguments
     * @return void
     */
    public function setIni(array $args)
    {
        foreach ($args as $arg => $value) {
            ini_set($arg, $value);
        }
    }

    /**
     * Validation of Shell Run Context
     *
     * @param Aoe_AttributeConfigurator_Shell_Command $shell Shell Script Model
     * @return void
     */
    public function validate($shell)
    {
        $config = $this->_checkConfig();
        if (!$config) {
            $this->exitConfigurator($this->_configError());
        }

        $install = $this->_checkInstall();
        if (!$install) {
            $this->exitConfigurator($this->_installError());
        }

        $runAll = $shell->getArg(self::PARAM_RUN_ALL);

        if (!$runAll) {
            $this->exitConfigurator($this->_usageHelp());
        }

        if ($runAll) {
            $this->_runAll();
            return;
        }
        $this->exitConfigurator($this->_usageHelp());
    }

    /**
     * Prints Exit Message while ending the Shell Script
     *
     * @param string $msg Exit Message
     * @return void
     */
    protected function exitConfigurator($msg)
    {
        // @codingStandardsIgnoreStart
        die($msg);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Check if System Setting is correct
     *
     * @return string
     */
    protected function _checkConfig()
    {
        /** @var Aoe_AttributeConfigurator_Helper_Config $helper */
        $helper = Mage::helper('aoe_attributeconfigurator/config');
        $configFilePath = $helper->getImportFilePath();
        return $helper->checkFile($configFilePath);
    }

    /**
     * Return Error Message
     *
     * @return string
     */
    protected function _configError()
    {
        return <<<USAGE
Error: System Config Settings missing or XML File could not be read.

USAGE;
    }

    /**
     * Check if Installation is correct
     *
     * @return string
     */
    protected function _checkInstall()
    {
        /** @var Aoe_AttributeConfigurator_Helper_Data $helper */
        $helper = Mage::helper('aoe_attributeconfigurator/data');
        return $helper->checkExtensionInstallStatus();
    }

    /**
     * Return Error Message
     *
     * @return string
     */
    protected function _installError()
    {
        return <<<USAGE
Error: Aoe_Attributeconfigurator has not been installed correctly. Check your System.

USAGE;
    }

    /**
     * Retrieve usage help message
     *
     * @return string
     */
    public function _usageHelp()
    {
        return <<<USAGE
Usage:  php aoe_attributeconfigurator.php -- <options>

  Options:
  --runAll                                      Run complete Import
  help                                          This help

USAGE;
    }

    /**
     * runAll Hook
     *
     * @return void
     */
    protected function _runAll()
    {
        /** @var Aoe_AttributeConfigurator_Model_Observer $observer */
        $observer = Mage::getModel('aoe_attributeconfigurator/observer');
        $observer->runAll();
    }
}
