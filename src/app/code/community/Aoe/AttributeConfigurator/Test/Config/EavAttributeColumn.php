<?php
/**
 * Class Aoe_AttributeConfigurator_Test_Config_EavAttributeColumn
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Config_EavAttributeColumn extends EcomDev_PHPUnit_Test_Case_Config
{

    /**
     * Test if the the is_maintained_by_configurator column exists in the eav_attribute table
     *
     * @test
     * @return void
     */
    public function testIsMaintainedByConfiguratorColumnExists()
    {
        /** @var  $dbConnection */
        $dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $query = $dbConnection->query("SHOW COLUMNS FROM eav_attribute LIKE 'is_maintained_by_configurator'");
        $queryResult = $query->fetchAll();

        $this->assertEquals(
            1,
            count($queryResult),
            'column \'is_maintaintd_by_configurator\' exists in \'eav_attribute\' table'
        );
    }
}
