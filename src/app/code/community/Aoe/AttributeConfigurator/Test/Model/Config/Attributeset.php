<?php
/**
 * Class Aoe_AttributeConfigurator_Test_Model_Config_Attributeset
 *
 * Test class for Aoe_AttributeConfigurator_Model_Config_Attributeset
 *
 * Test for wrapper class for SimpleXML attribute config objects
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Model_Config_Attributeset extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @return Aoe_AttributeConfigurator_Model_Config_Attributeset
     */
    public function checkClass()
    {
        $config = Mage::getModel('aoe_attributeconfigurator/config_attributeset');
        $this->assertInstanceOf('Aoe_AttributeConfigurator_Model_Config_Attributeset', $config);

        return $config;
    }

    /**
     * @test
     * @dataProvider dataProvider
     *
     * @param string $label Code of the data provider expectation
     * @param string $xml   xml string from the data provider
     * @return void
     */
    public function checkXml($label, $xml)
    {
        $attributeSet = $this->_createAttributeSetFromXMl($xml);
        $expected = $this->expected($label);

        $this->assertEquals(
            $expected['is_valid'],
            $attributeSet->validate(),
            'validation is correct'
        );

        if (isset($expected['name'])) {
            $this->assertEquals(
                $expected['name'],
                $attributeSet->getName(),
                'name fetch works'
            );
        }

        if (isset($expected['skeleton'])) {
            $this->assertEquals(
                $expected['skeleton'],
                $attributeSet->getSkeleton(),
                'skeleton fetch works'
            );
        }
    }

    /**
     * Create an attribute config from xml
     *
     * @param string $xml XML data string
     * @return Aoe_AttributeConfigurator_Model_Config_Attributeset
     */
    protected function _createAttributeSetFromXMl($xml)
    {
        $xml = simplexml_load_string($xml);
        return Mage::getModel('aoe_attributeconfigurator/config_attributeset', $xml);
    }
}
