<?php

/**
 * Class Hackathon_AttributeConfigurator_Test_Config_Main
 *
 * @category Test
 * @package  Hackathon_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/magento-hackathon/AttributeConfigurator
 */
class Hackathon_AttributeConfigurator_Test_Config_Main extends EcomDev_PHPUnit_Test_Case_Config
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
            'hackathon_attributeconfigurator',
            'Hackathon_AttributeConfigurator_Helper_Data'
        );
        $this->assertModelAlias(
            'hackathon_attributeconfigurator/attribute',
            'Hackathon_AttributeConfigurator_Model_Attribute'
        );
        $this->assertModelAlias(
            'hackathon_attributeconfigurator/sync_import',
            'Hackathon_AttributeConfigurator_Model_Sync_Import'
        );
        $this->assertModelAlias(
            'hackathon_attributeconfigurator/observer',
            'Hackathon_AttributeConfigurator_Model_Observer'
        );
    }

    /**
     * @test
     * @return void
     */
    public function testObserverDefinition()
    {
        $this->assertEventObserverDefined(
            'adminhtml',
            'controller_action_predispatch_adminhtml',
            'hackathon_attributeconfigurator/observer',
            'controllerActionPredispatchAdminhtml'
        );
    }

    /**
     * @test
     * @return void
     */
    public function testConfigContainsXmlLoactionNode()
    {
        $this->assertConfigNodeHasChild('default/catalog/attribute_configurator', 'product_xml_location');
    }
}
