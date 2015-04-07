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
    /**
     * Lazy fetched data helper
     *
     * @var Aoe_AttributeConfigurator_Helper_Data $_helper
     */
    protected $_helper;

    /**
     * Lazy created import sync model
     * @var Aoe_AttributeConfigurator_Model_Sync_Import $_sync
     */
    protected $_sync;

    /**
     * Poll for Changes in XML
     * TODO: we need to change this to a more useful event without interrupting the admin interface
     *
     * @return void
     */
    public function controllerActionPredispatchAdminhtml()
    {
        if ($this->_helper->isAttributeXmlNewer()) {
            $this->_sync->import();
        }
    }

    /**
     * @return Aoe_AttributeConfigurator_Helper_Data
     */
    protected function _getHelper()
    {
        if (isset($this->_helper)) {
            return $this->_helper;
        }

        $helper = Mage::helper('aoe_attributeconfigurator');
        $this->_helper = $helper;

        return $helper;
    }

    /**
     * @return Aoe_AttributeConfigurator_Model_Sync_Import
     */
    protected function _getSyncModel()
    {
        if (isset($this->_sync)) {
            return $this->_sync;
        }

        $syncModel = Mage::getModel('aoe_attributeconfigurator/sync_import');
        $this->_sync = $syncModel;

        return $syncModel;
    }
}
