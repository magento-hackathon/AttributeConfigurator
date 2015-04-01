<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Model_ObserverTest
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Model_ObserverTest extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @return void
     */
    public function testConstructorSetsHelper()
    {
        $observerReflection = new ReflectionClass('Aoe_AttributeConfigurator_Model_Observer');
        $observerHelperProp = $observerReflection->getProperty('_helper');
        $observerHelperProp->setAccessible(true);
        $this->assertInstanceOf(
            'Aoe_AttributeConfigurator_Helper_Data',
            $observerHelperProp->getValue(new Aoe_AttributeConfigurator_Model_Observer)
        );
    }
}
