<?php
/**
 * Class Hackathon_AttributeConfigurator_Helper_Data
 */
class Hackathon_AttributeConfigurator_Test_Config_Main extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testCodePool()
    {
        $this->assertModuleCodePool('community');
    }

    public function testModuleVersion()
    {
        $this->assertModuleVersionGreaterThan('0.0.1');
    }

    public function testSetupRessources()
    {
        $this->assertDataSetupExists();
    }

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

    public function testObserverDefinition()
    {
        $this->assertEventObserverDefined(
            'adminhtml',
            'controller_action_predispatch_adminhtml',
            'hackathon_attributeconfigurator/observer',
            'controllerActionPredispatchAdminhtml'
        );
    }

    public function testConfigContainsXmlLoactionNode()
    {
        $this->assertConfigNodeHasChild('default/catalog/attribute_configurator', 'product_xml_location');
    }
}
