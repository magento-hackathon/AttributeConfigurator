<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Model_Shell
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Model_Shell extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test model instantiation
     *
     * @test
     * @return Aoe_AttributeConfigurator_Model_Shell
     */
    public function checkClass()
    {
        $model = Mage::getModel('aoe_attributeconfigurator/shell');
        $this->assertInstanceOf(
            'Aoe_AttributeConfigurator_Model_Shell',
            $model
        );

        return $model;
    }

    /**
     * Test configError
     *
     * @test
     * @param Aoe_AttributeConfigurator_Model_Shell $model Shell Model
     * @depends checkClass
     * @return void
     */
    public function testConfigError($model)
    {
        $reflection = new ReflectionClass('Aoe_AttributeConfigurator_Model_Shell');
        $method = $reflection->getMethod('_configError');
        $method->setAccessible(true);

        $this->assertStringStartsWith(
            'Error',
            $method->invoke($model)
        );

        $this->assertInternalType(
            'string',
            $method->invoke($model)
        );
    }

    /**
     * Test configError
     *
     * @test
     * @param Aoe_AttributeConfigurator_Model_Shell $model Shell Model
     * @depends checkClass
     * @return void
     */
    public function testInstallError($model)
    {
        $reflection = new ReflectionClass('Aoe_AttributeConfigurator_Model_Shell');
        $method = $reflection->getMethod('_installError');
        $method->setAccessible(true);

        $this->assertStringStartsWith(
            'Error',
            $method->invoke($model)
        );

        $this->assertInternalType(
            'string',
            $method->invoke($model)
        );
    }

    /**
     * Test configError
     *
     * @test
     * @param Aoe_AttributeConfigurator_Model_Shell $model Shell Model
     * @depends checkClass
     * @return void
     */
    public function testUsageHelp($model)
    {
        $reflection = new ReflectionClass('Aoe_AttributeConfigurator_Model_Shell');
        $method = $reflection->getMethod('_usageHelp');
        $method->setAccessible(true);

        $this->assertStringStartsWith(
            'Usage',
            $method->invoke($model)
        );

        $this->assertInternalType(
            'string',
            $method->invoke($model)
        );
    }
}
