<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Config_Main
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Config_Main extends EcomDev_PHPUnit_Test_Case_Config
{
    /**
     * @test
     * @return void
     */
    public function testCodePool()
    {
        $this->assertModuleCodePool('community');
    }

    /**
     * @test
     * @return void
     */
    public function testModuleVersion()
    {
        $this->assertModuleVersionGreaterThan('0.0.1');
    }

    /**
     * @test
     * @return void
     */
    public function testSetupRessources()
    {
        $this->assertDataSetupExists();
    }

    /**
     * @test
     * @return void
     */
    public function testClassAliases()
    {
        $this->assertHelperAlias(
            'aoe_attributeconfigurator',
            'Aoe_AttributeConfigurator_Helper_Data'
        );
        $this->assertHelperAlias(
            'aoe_attributeconfigurator/config',
            'Aoe_AttributeConfigurator_Helper_Config'
        );

        $this->assertModelAlias(
            'aoe_attributeconfigurator/attribute',
            'Aoe_AttributeConfigurator_Model_Attribute'
        );
        $this->assertModelAlias(
            'aoe_attributeconfigurator/sync_import',
            'Aoe_AttributeConfigurator_Model_Sync_Import'
        );
        $this->assertModelAlias(
            'aoe_attributeconfigurator/observer',
            'Aoe_AttributeConfigurator_Model_Observer'
        );
    }

    /**
     * @test
     * @return void
     */
    public function testConfigContainsXmlLocationNode()
    {
        $this->assertConfigNodeHasChild(
            'default/catalog/attribute_configurator',
            'product_xml_location'
        );
    }
}
