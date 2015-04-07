<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Model_Attribute
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Model_Sync_Import_Attribute extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @test
     *
     * @return Aoe_AttributeConfigurator_Model_Sync_Import_Attribute
     */
    public function checkClass()
    {
        $model = Mage::getModel('aoe_attributeconfigurator/sync_import_attribute');
        $this->assertInstanceOf(
            'Aoe_AttributeConfigurator_Model_Sync_Import_Attribute',
            $model
        );

        return $model;
    }

    /**
     * @test
     * @depends checkClass
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage Data validation: no code set on attribute data array.
     *
     * @param Aoe_AttributeConfigurator_Model_Sync_Import_Attribute $model Attribute import model
     * @return void
     */
    public function insertAttributeWithoutCodeThrowsException($model)
    {
        $model->insertAttribute([]);
    }

    /**
     * @test
     * @depends checkClass
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no 'settings' section.
     *
     * @param Aoe_AttributeConfigurator_Model_Sync_Import_Attribute $model Attribute import model
     * @return void
     */
    public function insertAttributeWithoutSettingsThrowsException($model)
    {
        $attribute = $this->_loadAttribute();
        $model->insertAttribute(
            [
                'code' => $attribute->getAttributeCode()
            ]
        );
    }

    /**
     * @test
     * @depends checkClass
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no frontend label.
     *
     * @param Aoe_AttributeConfigurator_Model_Sync_Import_Attribute $model Attribute import model
     * @return void
     */
    public function insertAttributeWithoutFrontendLabelThrowsException($model)
    {
        $attribute = $this->_loadAttribute();
        $model->insertAttribute(
            [
                'code'     => $attribute->getAttributeCode(),
                'settings' => []
            ]
        );
    }

    /**
     * @test
     * @depends checkClass
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no attribute set config.
     *
     * @param Aoe_AttributeConfigurator_Model_Sync_Import_Attribute $model Attribute import model
     * @return void
     */
    public function insertAttributeWithoutAttributeSetThrowsException($model)
    {
        $attribute = $this->_loadAttribute();
        $model->insertAttribute(
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
     * @depends checkClass
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no entity type id.
     *
     * @param Aoe_AttributeConfigurator_Model_Sync_Import_Attribute $model Attribute import model
     * @return void
     */
    public function insertAttributeWithoutEntityTypeIdThrowsException($model)
    {
        $attribute = $this->_loadAttribute();
        $model->insertAttribute(
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
     * @depends checkClass
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no group.
     *
     * @param Aoe_AttributeConfigurator_Model_Sync_Import_Attribute $model Attribute import model
     * @return void
     */
    public function insertAttributeWithoutGroupThrowsException($model)
    {
        $attribute = $this->_loadAttribute();
        $model->insertAttribute(
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
     * @depends checkClass
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no attribute code.
     *
     * @param Aoe_AttributeConfigurator_Model_Sync_Import_Attribute $model Attribute import model
     * @return void
     */
    public function insertAttributeWithoutAttributeCodeThrowsException($model)
    {
        $attribute = $this->_loadAttribute();
        $model->insertAttribute(
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
     * @depends checkClass
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage contains no sort order.
     *
     * @param Aoe_AttributeConfigurator_Model_Sync_Import_Attribute $model Attribute import model
     * @return void
     */
    public function insertAttributeWithoutSortOrderThrowsException($model)
    {
        $attribute = $this->_loadAttribute();
        $model->insertAttribute(
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
     * @depends checkClass
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessage already exists.
     *
     * @param Aoe_AttributeConfigurator_Model_Sync_Import_Attribute $model Attribute import model
     * @return void
     */
    public function insertAttributeThrowsExceptionIfIdExists($model)
    {
        $attribute = $this->_loadAttribute();
        $model->insertAttribute(
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
