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
class Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset extends Mage_Core_Model_Abstract
{
    /** @var Aoe_AttributeConfigurator_Helper_Data $_helper */
    protected $_helper;

    /** @var Varien_Simplexml_Element $_config */
    protected $_config;

    /**
     * Constructor
     *
     * @param Varien_Simplexml_Element $config Config Data
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
        foreach ($this->_config->children() as $childConfig) {
            try {
                //$this->validate($config);
                //$this->createAttributeSet($config);
                return;
            } catch (Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Validation_Exception $e) {
                $this->_helper->log('Attribute Set validation exception.', $e);
            } catch (Exception $e) {
                $this->_helper->log('Attribute Set was not created, skipping', $e);
            }
        }
    }

    /**
     * @param  Mage_Core_Model_Config_Element $config Single Attribute Set Config
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Validation_Exception
     * @return void
     */
    private function validate($config)
    {
        if (!isset($config['name']) || !trim($config['name'])) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Validation_Exception();
        }
        if (!isset($config['skeleton']) || !trim($config['skeleton'])) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Validation_Exception();
        }
    }

    /**
     * Create Attribute Set
     *
     * @param  Mage_Core_Model_Config_Element $config Single Attribute Set Config
     * @return void
     */
    private function createAttributeSet($config)
    {
        /** @var Mage_Eav_Model_Entity_Attribute_Set $setModel */
        $setModel = Mage::getModel('eav/entity_attribute_set');
        $setModel->setData('name', trim($config['name']));
    }
}
