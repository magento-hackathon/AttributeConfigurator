<?php


class Hackathon_AttributeConfigurator_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{
    protected $_helper = NULL;

    protected function setUp()
    {
        $this->_helper = MAGE::helper('hackathon_attributeconfigurator');
        parent::setUp();
    }


}
