<?php
/**
 * Class Hackathon_AttributeConfigurator_Test_Model_AttributeTest
 */
class Hackathon_AttributeConfigurator_Test_Model_Sync_ImportTest extends EcomDev_PHPUnit_Test_Case
{
    /** @var  Hackathon_AttributeConfigurator_Helper_Data */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Mage::getModel('hackathon_attributeconfigurator/sync_import');
        parent::setUp();
    }


}

