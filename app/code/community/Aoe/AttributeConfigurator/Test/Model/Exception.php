<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Model_Exception
 *
 * Test class for Aoe_AttributeConfigurator_Model_Exception
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Model_Exception extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage test exception
     * @throws Aoe_AttributeConfigurator_Model_Exception
     * @return void
     */
    public function checkExceptionClass()
    {
        throw new Aoe_AttributeConfigurator_Model_Exception('test exception');
    }
}
