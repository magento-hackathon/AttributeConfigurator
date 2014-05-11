<?php

class Hackathon_AttributeConfigurator_Test_Helper_DataTest  extends EcomDev_PHPUnit_Test_Case
{
    /** @var Hackathon_AttributeConfigurator_Helper_Data $_helper*/
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = MAGE::helper('hackathon_attributeconfigurator');

        parent::setUp();
    }

    public function testCreateFileHash()
    {
        /** @var string $fileHash */
        $fileHash = '39e261858ae67d3aed716969e449686a';
        /** @var string $testFile */
        $testFileName = Mage::getModuleDir('', 'Hackathon_AttributeConfigurator') .
                DS . 'Test' . DS . 'Helper' . DS . 'Fixture' . DS . 'attribute-dummy.xml' ;

        $this->assertEquals($fileHash, $this->_helper->createFileHash($testFileName));
        $this->assertFalse($this->_helper->createFileHash(''));
        $this->assertFalse($this->_helper->createFileHash('ranD0MsTr1ng'));
    }

    public function testGetImportFilename()
    {
        $this->assertNotNull(Mage::getStoreConfig(Hackathon_AttributeConfigurator_Helper_Data::XML_PATH_FILENAME));
        $this->assertInternalType('int', strpos($this->_helper->getImportFilename(), Mage::getBaseDir() . DS ));
    }

    public function testcheckAttributeMaintained()
    {
        $attribute = Mage::getModel('catalog/entity_attribute');
        $attribute->setIsMaintainedByConfigurator(1);
        $this->assertTrue($this->_helper->checkAttributeMaintained($attribute));
        $this->assertFalse($this->_helper->checkAttributeMaintained(NULL));
    }

}
