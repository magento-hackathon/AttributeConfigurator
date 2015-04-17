<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Helper_Data
 *
 * Test class for Aoe_AttributeConfigurator_Helper_Data
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
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
        $fileHash = '030db930ec1f620eb9b975af9284cfe0';
        /** @var string $testFile */
        $testFileName = Mage::getModuleDir('', 'Aoe_AttributeConfigurator') .
                DS . 'Test' . DS . 'Helper' . DS . 'Fixture' . DS . 'attribute-dummy.xml' ;

        $this->assertEquals(
            $fileHash,
            $this->_helper->createFileHash($testFileName),
            'file content hash is correct'
        );

        $this->assertFalse(
            $this->_helper->createFileHash(''),
            'Return false for an empty filename'
        );

        $this->assertFalse(
            $this->_helper->createFileHash('ranD0MsTr1ng'),
            'Return false for a not existing filename'
        );
    }

    /**
     * @test
     * @return void
     */
    public function testCheckAttributeMaintained()
    {
        /** @var Mage_Catalog_Model_Entity_Attribute $attribute */
        $attribute = Mage::getModel('catalog/entity_attribute');

        $attribute->getData(Aoe_AttributeConfigurator_Helper_Data::EAV_ATTRIBUTE_MAINTAINED);
        $this->assertFalse(
            $this->_helper->checkAttributeMaintained($attribute),
            'default maintained status is \'false\''
        );

        $attribute->setData(Aoe_AttributeConfigurator_Helper_Data::EAV_ATTRIBUTE_MAINTAINED, true);
        $this->assertTrue(
            $this->_helper->checkAttributeMaintained($attribute),
            'enabling maintaining status works'
        );

        $this->assertFalse($this->_helper->checkAttributeMaintained(null));
    }

    /**
     * @test
     * @return void
     */
    public function testCheckExtensionInstallStatus()
    {
        $this->assertInternalType(
            'bool',
            $this->_helper->checkExtensionInstallStatus()
        );
    }
}
