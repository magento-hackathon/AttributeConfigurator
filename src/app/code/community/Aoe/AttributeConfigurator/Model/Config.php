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
    /**
     * @param  string $path File Path
     * @return $this
     */
    public function loadCustomConfigXml($path)
    {
        $merge = Mage::getModel('core/config_base');
        $merge->loadFile(Mage::getBaseDir('var') . DS . $path);
        $coreConfig = Mage::getConfig();
        $coreConfig->extend($merge);
        return $this;
    }
}