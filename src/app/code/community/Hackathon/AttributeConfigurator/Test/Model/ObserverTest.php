<?php

/**
 * Class Hackathon_AttributeConfigurator_Test_Model_ObserverTest
 *
 * @category Test
 * @package  Hackathon_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/magento-hackathon/AttributeConfigurator
 */
class Hackathon_AttributeConfigurator_Test_Model_ObserverTest extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @return void
     */
    public function testConstructorSetsHelper()
    {
        $observerReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Model_Observer');
        $observerHelperProp = $observerReflection->getProperty('_helper');
        $observerHelperProp->setAccessible(true);
        $this->assertInstanceOf(
            'Hackathon_AttributeConfigurator_Helper_Data',
            $observerHelperProp->getValue(new Hackathon_AttributeConfigurator_Model_Observer)
        );
    }
}
