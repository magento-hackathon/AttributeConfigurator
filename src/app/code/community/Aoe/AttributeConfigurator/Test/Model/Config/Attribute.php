<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Model_Config_Attribute
 *
 * Test for wrapper class for SimpleXML attribute config objects
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Model_Config_Attribute extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @return Aoe_AttributeConfigurator_Model_Config_Attribute
     */
    public function checkClass()
    {
        $config = Mage::getModel('aoe_attributeconfigurator/config_attribute');
        $this->assertInstanceOf('Aoe_AttributeConfigurator_Model_Config_Attribute', $config);

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
        $attribute = $this->_createAttributeFromXMl($xml);
        $expected = $this->expected($label);

        $this->assertEquals(
            $expected['is_valid'],
            $attribute->validate(),
            'validation is correct'
        );

        if (isset($expected['code'])) {
            $this->assertEquals(
                $expected['code'],
                $attribute->getCode(),
                'code fetch works'
            );
        }

        if (isset($expected['entity_type_id'])) {
            $this->assertEquals(
                $expected['entity_type_id'],
                $attribute->getEntityTypeId(),
                'entity type id fetch works'
            );
        }

        if (isset($expected['settings'])) {
            $this->assertEquals(
                $expected['settings'],
                $attribute->getSettingsAsArray(),
                'settings array fetch works'
            );
        }

        if (isset($expected['attribute_sets'])) {
            $attributeSets = $attribute->getAttributeSets();
            foreach ($attributeSets as $_attributeSet) {
                /** @var Aoe_AttributeConfigurator_Model_Config_Attribute_Attributeset @_attributeSet */
                $this->assertArrayHasKey(
                    $_attributeSet->getName(),
                    $expected['attribute_sets'],
                    'attribute set mentioned in expectations'
                );
                $this->assertEquals(
                    $expected['attribute_sets'][$_attributeSet->getName()]['groups'],
                    $_attributeSet->getAttributeGroups(),
                    'all groups in attribute set'
                );
            }
        }
    }

    /**
     * Create an attribute config from xml
     *
     * @param string $xml XML data string
     * @return Aoe_AttributeConfigurator_Model_Config_Attribute
     */
    protected function _createAttributeFromXMl($xml)
    {
        $xml = simplexml_load_string($xml);
        return Mage::getModel('aoe_attributeconfigurator/config_attribute', $xml);
    }
}
