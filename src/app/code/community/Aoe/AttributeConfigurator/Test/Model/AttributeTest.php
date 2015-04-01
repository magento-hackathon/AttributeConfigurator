<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Model_AttributeTest
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Model_AttributeTest extends EcomDev_PHPUnit_Test_Case
{
    /** @var Aoe_AttributeConfigurator_Model_Attribute $_model */
    protected $_model;

    /**
     * Setup Method
     * @return void
     */
    protected function setUp()
    {
        $this->_model = Mage::getModel('aoe_attributeconfigurator/attribute');
        parent::setUp();
    }

    /**
     * @test
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage Data validation: no code set on attribute data array.
     *
     * @return void
     */
    public function insertAttributeWithoutCodeThrowsException()
    {
        $this->_model->insertAttribute([]);
    }

    /**
     * @test
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no 'settings' section.
     *
     * @return void
     */
    public function insertAttributeWithoutSettingsThrowsException()
    {
        $attribute = $this->_loadAttribute();
        $this->_model->insertAttribute(
            [
                'code' => $attribute->getAttributeCode()
            ]
        );
    }

    /**
     * @test
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no frontend label.
     *
     * @return void
     */
    public function insertAttributeWithoutFrontendLabelThrowsException()
    {
        $attribute = $this->_loadAttribute();
        $this->_model->insertAttribute(
            [
                'code'     => $attribute->getAttributeCode(),
                'settings' => []
            ]
        );
    }

    /**
     * @test
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no attribute set config.
     *
     * @return void
     */
    public function insertAttributeWithoutAttributeSetThrowsException()
    {
        $attribute = $this->_loadAttribute();
        $this->_model->insertAttribute(
            [
                'code'     => $attribute->getAttributeCode(),
                'settings' => [
                    'frontend_label' => $attribute->getFrontendLabel()
                ]
            ]
        );
    }

    /**
     * @test
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no entity type id.
     *
     * @return void
     */
    public function insertAttributeWithoutEntityTypeIdThrowsException()
    {
        $attribute = $this->_loadAttribute();
        $this->_model->insertAttribute(
            [
                'code'           => $attribute->getAttributeCode(),
                'settings'       => [
                    'frontend_label' => $attribute->getFrontendLabel()
                ],
                'attribute_set'  => [
                    '0' => 'default'
                ]
            ]
        );
    }

    /**
     * @test
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no group.
     *
     * @return void
     */
    public function insertAttributeWithoutGroupThrowsException()
    {
        $attribute = $this->_loadAttribute();
        $this->_model->insertAttribute(
            [
                'code'           => $attribute->getAttributeCode(),
                'settings'       => [
                    'frontend_label' => $attribute->getFrontendLabel()
                ],
                'attribute_set'  => [
                    '0' => 'default'
                ],
                'entity_type_id' => $attribute->getEntityTypeId()
            ]
        );
    }

    /**
     * @test
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no attribute code.
     *
     * @return void
     */
    public function insertAttributeWithoutAttributeCodeThrowsException()
    {
        $attribute = $this->_loadAttribute();
        $this->_model->insertAttribute(
            [
                'code'           => $attribute->getAttributeCode(),
                'settings'       => [
                    'frontend_label' => $attribute->getFrontendLabel()
                ],
                'attribute_set'  => [
                    '0' => 'default'
                ],
                'entity_type_id' => $attribute->getEntityTypeId(),
                'group'          => 'default'
            ]
        );
    }

    /**
     * @test
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no sort order.
     *
     * @return void
     */
    public function insertAttributeWithoutSortOrderThrowsException()
    {
        $attribute = $this->_loadAttribute();
        $this->_model->insertAttribute(
            [
                'code'           => $attribute->getAttributeCode(),
                'settings'       => [
                    'frontend_label' => $attribute->getFrontendLabel()
                ],

                'attribute_set'  => [
                    '0' => 'default'
                ],
                'entity_type_id' => $attribute->getEntityTypeId(),
                'group'          => 'default',
                'attribute_code' => $attribute->getAttributeCode()
            ]
        );
    }

    /**
     * @test
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage already exists.
     * @return void
     */
    public function insertAttributeThrowsExceptionIfIdExists()
    {
        $attribute = $this->_loadAttribute();
        $this->_model->insertAttribute(
            [
                'code'           => $attribute->getAttributeCode(),
                'settings'       => [
                    'frontend_label' => $attribute->getFrontendLabel()
                ],
                'attribute_set'  => [
                    '0' => 'default'
                ],
                'entity_type_id' => $attribute->getEntityTypeId(),
                'group'          => 'default',
                'attribute_code' => $attribute->getAttributeCode(),
                'sort_order'     => 0
            ]
        );
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected function _loadAttribute()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Attribute_Collection $attributes */
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');

        $this->assertGreaterThan(0, $attributes->getSize(), 'attributes configured in the system');

        return $attributes->getFirstItem();
    }
}
