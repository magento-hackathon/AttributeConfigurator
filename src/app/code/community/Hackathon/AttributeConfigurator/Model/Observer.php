<?php

/**
 * Class Hackathon_AttributeConfigurator_Model_Observer
 *
 * @category Model
 * @package  Hackathon_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/magento-hackathon/AttributeConfigurator
 */
class Hackathon_AttributeConfigurator_Model_Observer
{
    /** @var Hackathon_AttributeConfigurator_Helper_Data $_helper */
    protected $_helper;

    /** @var Hackathon_AttributeConfigurator_Model_Sync_Import $_sync */
    protected $_sync;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('hackathon_attributeconfigurator/data');
        $this->_sync = Mage::getModel('hackathon_attributeconfigurator/sync_import');
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
