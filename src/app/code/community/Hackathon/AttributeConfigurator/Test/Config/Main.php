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

    public function testClassAliases()
    {
        $this->assertHelperAlias('hackathon_attributeconfigurator', 'Hackathon_AttributeConfigurator_Helper_Data');
    }

}