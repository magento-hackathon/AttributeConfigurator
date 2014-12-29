<?php
/**
 * Class Hackathon_AttributeConfigurator_Test_Model_AttributeTest
 */
class Hackathon_AttributeConfigurator_Test_Model_AttributeTest extends EcomDev_PHPUnit_Test_Case
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

    public function testIsMaintainedByConfigurator()
    {
        $_attribute = $this->_model->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'price');
        $this->assertNull($_attribute->getIsMaintainedByConfigurator());
        $_attribute->setIsMaintainedByConfigurator(1);
        $_attribute->save();
        $_attribute = $this->_model->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'price');
        $this->assertEquals(1, $_attribute->getIsMaintainedByConfigurator());
        $_attribute->setIsMaintainedByConfigurator(null)->save();
    }


//    /**
//     * @test
//     */
//    public function insertAttributeThrowsExceptionIfIdExists()
//    {
//        /** @var Mage_Catalog_Model_Resource_Category_Attribute_Collection $attributes */
//        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
//        $attributeCode = $attributes->getFirstItem()->getCode();
//        if (!empty($attributes)) {
//            $this->_model->insertAttribute(array('data' => $attributeCode));
//        }
//    }
}
