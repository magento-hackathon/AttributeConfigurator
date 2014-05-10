<?php


class Hackathon_AttributeConfigurator_Test_Helper_DataTest  extends EcomDev_PHPUnit_Test_Case
{
    protected $_helper = NULL;

    protected function setUp()
    {
        $this->_helper = MAGE::helper('hackathon_attributeconfigurator');

        parent::setUp();
    }

    public function testCreateFileHash()
    {
        //$this->assertTrue(false);
        $path = Mage::getModuleDir('Test', 'Hackathon_AttributeConfigurator') .
                DS . 'Test' . DS . 'Helper' . DS . 'Fixture';
        $helperMock = $this->getHelperMock('Hackathon_AttributeConfigurator');
    }


}
