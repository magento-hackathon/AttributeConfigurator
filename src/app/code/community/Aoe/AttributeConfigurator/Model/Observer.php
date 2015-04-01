<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Observer
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Observer
{
    /** @var Aoe_AttributeConfigurator_Helper_Data $_helper */
    protected $_helper;

    /** @var Aoe_AttributeConfigurator_Model_Sync_Import $_sync */
    protected $_sync;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('aoe_attributeconfigurator/data');
        $this->_sync = Mage::getModel('aoe_attributeconfigurator/sync_import');
    }

    /**
     * Poll for Changes in XML
     *
     * @return void
     */
    public function controllerActionPredispatchAdminhtml()
    {
        if ($this->_helper->isAttributeXmlNewer()) {
            $this->_sync->import();
        }
    }
}
