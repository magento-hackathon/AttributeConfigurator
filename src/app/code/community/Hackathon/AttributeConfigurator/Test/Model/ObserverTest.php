<?php
/**
 * Class Hackathon_AttributeConfigurator_Test_Model_ObserverTest
 */
class Hackathon_AttributeConfigurator_Test_Model_ObserverTest extends EcomDev_PHPUnit_Test_Case
{
    public function testConstructorSetsHelper()
    {
        $observerReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Model_Observer');
        $observerHelperProperty = $observerReflection->getProperty('_helper');
        $observerHelperProperty->setAccessible(true);
        $this->assertInstanceOf(
            'Hackathon_AttributeConfigurator_Helper_Data',
            $observerHelperProperty->getValue(new Hackathon_AttributeConfigurator_Model_Observer)
        );
    }
}
