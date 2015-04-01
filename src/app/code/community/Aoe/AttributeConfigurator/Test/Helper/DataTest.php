<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Helper_DataTest
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{
    /** @var Aoe_AttributeConfigurator_Helper_Data $_helper*/
    protected $_helper;

    /**
     * Setup Method
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_helper = Mage::helper('aoe_attributeconfigurator');
        parent::setUp();
    }

    /**
     * @test
     * @return void
     */
    public function testCreateFileHash()
    {
        /** @var string $fileHash */
        $fileHash = '39e261858ae67d3aed716969e449686a';
        /** @var string $testFile */
        $testFileName = Mage::getModuleDir('', 'Aoe_AttributeConfigurator') .
                DS . 'Test' . DS . 'Helper' . DS . 'Fixture' . DS . 'attribute-dummy.xml' ;

        $this->assertEquals($fileHash, $this->_helper->createFileHash($testFileName));
        $this->assertFalse($this->_helper->createFileHash(''));
        $this->assertFalse($this->_helper->createFileHash('ranD0MsTr1ng'));
    }

    /**
     * @test
     * @return void
     */
    public function testGetImportFilename()
    {
        $this->assertNotNull(Mage::getStoreConfig(Aoe_AttributeConfigurator_Helper_Data::XML_PATH_FILENAME));
        $this->assertInternalType('int', strpos($this->_helper->getImportFilename(), Mage::getBaseDir() . DS));
    }

    /**
     * @test
     * @return void
     */
    public function testcheckAttributeMaintained()
    {
        /** @var Mage_Catalog_Model_Entity_Attribute $attribute */
        $attribute = Mage::getModel('catalog/entity_attribute');

        $attribute->getData('is_maintained_by_configurator');
        $this->assertTrue($this->_helper->checkAttributeMaintained($attribute));

        $attribute->setData('is_maintained_by_configurator', 0);
        $this->assertFalse($this->_helper->checkAttributeMaintained($attribute));
        $this->assertFalse($this->_helper->checkAttributeMaintained(null));
    }
}
