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

    /**
     * @incomplete
     */
    public function testCreateFileHash()
    {
        /** @var string $fileHash */
        $fileHash = '39e261858ae67d3aed716969e449686a';
        /** @var string $testFile */
        $testFileName = Mage::getModuleDir('', 'Hackathon_AttributeConfigurator') .
                DS . 'Test' . DS . 'Helper' . DS . 'Fixture' . DS . 'attribute-dummy.xml' ;
        $docRoot = Mage::getBaseDir();


        $testFileLocation = str_replace($docRoot, '', $testFileName);
        $this->assertEquals($fileHash, $this->_helper->createFileHash($testFileLocation));
        //TODO: Check if passing empty strings is a problem -> currently returns a hash, not false
        $this->assertFalse($this->_helper->createFileHash('zxcv'));


    }


}
