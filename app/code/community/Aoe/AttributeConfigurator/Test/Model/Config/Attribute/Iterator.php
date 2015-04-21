<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Model_Config_Attribute_Iterator
 *
 * Test class for Aoe_AttributeConfigurator_Model_Config_Attribute_Iterator
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Model_Config_Attribute_Iterator extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @return Aoe_AttributeConfigurator_Model_Config_Attribute_Iterator
     */
    public function checkClass()
    {
        $config = Mage::getModel('aoe_attributeconfigurator/config_attribute_iterator', '<attributes></attributes>');
        $this->assertInstanceOf('Aoe_AttributeConfigurator_Model_Config_Attribute_iterator', $config);

        return $config;
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param string $label     Code to the data provider expectation
     * @param string $xmlString xml string from the data provider
     * @return void
     */
    public function checkIterator($label, $xmlString)
    {
        $iterator = $this->_createIteratorFromFixture($xmlString);
        $count = 0;
        foreach ($iterator as $_element) {
            $count++;
        }

        $expected = $this->expected($label);
        $this->assertEquals(
            $expected->getCount(),
            $count,
            'no items iterated'
        );
    }

    /**
     * Create an attribute iterator from xml
     *
     * @param string $xml XML data string
     * @return Aoe_AttributeConfigurator_Model_Config_Attribute_Iterator
     */
    protected function _createIteratorFromFixture($xml)
    {
        $xml = simplexml_load_string($xml);

        return Mage::getModel('aoe_attributeconfigurator/config_attribute_iterator', $xml);
    }
}
