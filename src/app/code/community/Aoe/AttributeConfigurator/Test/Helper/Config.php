<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Helper_Config
 *
 * Test class for Aoe_AttributeConfigurator_Helper_Config
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Helper_Config extends EcomDev_PHPUnit_Test_Case
{
    /** @var Aoe_AttributeConfigurator_Helper_Config $_helper*/
    protected $_helper;

    /**
     * Setup Method
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_helper = Mage::helper('aoe_attributeconfigurator/config');
        parent::setUp();
    }

    /**
     * @test
     * @loadFixture
     *
     * @return void
     */
    public function testGetImportFilename()
    {
        $this->assertStringEndsWith(
            'test/file/path.xml',
            $this->_helper->getImportFilename(),
            'Import filename read from config'
        );
    }
}
