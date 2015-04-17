<?php

/**
 * Interface Aoe_AttributeConfigurator_Model_Sync_Import_Interface
 *
 * Interface for import tasks
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
interface Aoe_AttributeConfigurator_Model_Sync_Import_Interface
{
    /**
     * Implementation of a data importer
     *
     * @param Aoe_AttributeConfigurator_Model_Config $config Config model containing xml import data
     * @return void
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Exception
     */
    public function run($config);
}
