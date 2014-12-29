<?php
/**
 * Class Hackathon_AttributeConfigurator_Test_Model_AttributeTest
 */
class Hackathon_AttributeConfigurator_Test_Model_Attribute extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var Hackathon_AttributeConfigurator_Model_Attribute
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Mage::getModel('hackathon_attributeconfigurator/attribute');
        parent::setUp();
    }
    /**
     * @test
     */
    public function testDa()
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     */
    public function insertAttributeThrowsExceptionIfIdExists()
    {
        /** @var Mage_Catalog_Model_Resource_Category_Attribute_Collection $attributes */
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        $attributeCode = $attributes->getFirstItem()->getCode();
        if (!empty($attributes)) {
            $this->_model->insertAttribute(array('data' => $attributeCode));
        }
    }
}
