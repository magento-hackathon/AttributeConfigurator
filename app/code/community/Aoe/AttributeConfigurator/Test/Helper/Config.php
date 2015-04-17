<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Helper_Config
 *
 * Test class for Aoe_AttributeConfigurator_Helper_Config
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
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

    /**
     * @test
     * @loadFixture testMigrateFlag
     * @return void
     */
    public function testMigrateFlag()
    {
        $this->assertEquals(
            1,
            $this->_helper->getMigrateFlag(),
            'Migration Flag read from config'
        );
    }

    /**
     * @test
     * @return void
     */
    public function testCheckFile()
    {
        $this->assertFalse(
            $this->_helper->checkFile('/var/www/a.nonexistant.file')
        );

        $testFileName = Mage::getModuleDir('', 'Aoe_AttributeConfigurator') .
            DS . 'Test' . DS . 'Helper' . DS . 'Fixture' . DS . 'attribute-dummy.xml' ;
        $this->assertTrue(
            $this->_helper->checkFile($testFileName)
        );
    }
}
