<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Model_Observer
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @return Aoe_AttributeConfigurator_Model_Observer
     */
    public function checkClass()
    {
        $observer = Mage::getModel('aoe_attributeconfigurator/observer');
        $this->assertInstanceOf(
            'Aoe_AttributeConfigurator_Model_Observer',
            $observer,
            'Observer can be instantiated'
        );

        return $observer;
    }

    /**
     * @test
     * @depends checkClass
     * @param Aoe_AttributeConfigurator_Model_Observer $observer Observer model
     * @return void
     */
    public function getHelperReturnsModuleHelper($observer)
    {

        $reflection = new ReflectionClass('Aoe_AttributeConfigurator_Model_Observer');
        $method = $reflection->getMethod('_getHelper');
        $method->setAccessible(true);

        $this->assertInstanceOf(
            'Aoe_AttributeConfigurator_Helper_Data',
            $method->invoke($observer)
        );
    }

    /**
     * @test
     * @depends checkClass
     * @param Aoe_AttributeConfigurator_Model_Observer $observer Observer model
     * @return void
     */
    public function getSyncReturnsModel($observer)
    {
        $reflection = new ReflectionClass('Aoe_AttributeConfigurator_Model_Observer');
        $method = $reflection->getMethod('_getSyncModel');
        $method->setAccessible(true);

        $this->assertInstanceOf(
            'Aoe_AttributeConfigurator_Model_Sync_Import',
            $method->invoke($observer)
        );
    }
}
