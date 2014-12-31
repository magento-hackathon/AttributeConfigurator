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
        //price is not maintained
        $this->assertNull($_attribute->getIsMaintainedByConfigurator());
        //now it is
        $_attribute->setIsMaintainedByConfigurator(1);
        $_attribute->save();
        $_attribute = $this->_model->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'price');
        $this->assertEquals(1, $_attribute->getIsMaintainedByConfigurator());
        //clean up
        $_attribute->setIsMaintainedByConfigurator(null)->save();
    }


    public function testConvertAttributeOnlyConvertsValidAttr()
    {
        $validAttributeCode = 'price';
        $invalidAttributeCode = 'invalidcode';
        $entityType = Mage_Catalog_Model_Product::ENTITY;

        //Attribute must exist
        $this->assertFalse(
                $this->_model->convertAttribute($invalidAttributeCode, $entityType, array())
        );
        //Attribute must be maintained by Configurator
        $this->assertFalse(
                $this->_model->convertAttribute($validAttributeCode, $entityType, array())
        );
        //Data array must be given
        $this->assertFalse(
            $this->_model->convertAttribute($validAttributeCode, $entityType)
        );
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Can't import the attribute with an empty label or code
     *
     */
    public function testEmptyLabelOrCodeThrowException()
    {
        $entityType = Mage_Catalog_Model_Product::ENTITY;
        $this->_model->insertAttribute(
                $entityType,
                array(
                        'code' => '',
                        'frontend_label' => 'notempty'
                )
        );
        $this->_model->insertAttribute(
                $entityType,
                array(
                        'code' => 'notempty',
                        'frontend_label' => ''
                )
        );
    }

    public function testTruncateString()
    {
        $teststring = 'teststring';

        $this->assertEquals(
                strlen($teststring),
                strlen($this->_model->truncateString(
                        $teststring,
                        strlen($teststring))
                )
        );
        $this->assertEquals(
                strlen($teststring)-1,
                strlen($this->_model->truncateString(
                                $teststring,
                                strlen($teststring)-1)
                )
        );

    }

    /**
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Attribute already exists.
     *
     */
    public function testInsertAttributeThrowsExceptionIfIdExists()
    {


        /** @var Mage_Catalog_Model_Resource_Category_Attribute_Collection $attributes */
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        $attributeCode = $attributes->getFirstItem()->getAttributeCode();

        if (!empty($attributes)) {
            $this->_model->insertAttribute(
                    $attributes->getFirstItem()->getEntityTypeId(),
                    array(
                            'code' => $attributeCode,
                            'frontend_label' => 'not Empty')
            );
        }
    }
}
