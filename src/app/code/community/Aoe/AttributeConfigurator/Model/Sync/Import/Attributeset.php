<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Attributeset
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset extends Mage_Eav_Model_Entity_Attribute_Set
{
    /** @var Aoe_AttributeConfigurator_Helper_Data $_helper */
    protected $_helper;

    /** @var Mage_Core_Model_Config_Base $_config */
    protected $_config;

    /**
     * Constructor
     *
     * @param Mage_Core_Model_Config_Base $config Config Data
     */
    public function __construct($config)
    {
        parent::_construct();
        $this->_helper = Mage::helper('aoe_attributeconfigurator/data');
        $this->_config = $config;
    }

    /**
     * Cycle through Attributesets
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->_config as $config) {
            try {
                $this->validate($config);
                $this->createAttributeSet($config);
            } catch (Exception $e) {
                $this->_helper->log('Attribute Set was not created, skipping', $e);
            }
        }
    }

    /**
     * @param   $config Single Attribute Set Config
     * @returns boolean
     */
    private function validate($config)
    {

    }
}
