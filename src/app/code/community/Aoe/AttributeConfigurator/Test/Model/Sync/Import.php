<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Model_Sync_Import
 *
 * Test class for Aoe_AttributeConfigurator_Model_Sync_Import
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Model_Sync_Import extends Aoe_AttributeConfigurator_Test_Model_Case
{
    /**
     * @test
     *
     * @return Aoe_AttributeConfigurator_Model_Sync
     */
    public function checkClass()
    {
        $model = Mage::getModel('aoe_attributeconfigurator/sync_import');
        $this->assertInstanceOf(
            'Aoe_AttributeConfigurator_Model_Sync_Import',
            $model,
            'class alias works and model can be instantiated'
        );

        return $model;
    }

    /**
     * @test
     *
     * @return void
     */
    public function checkImportMethodsCalled()
    {
        $this->_mockConfigHelperLoadingXml();

        /** @var EcomDev_PHPUnit_Mock_Proxy|Aoe_AttributeConfigurator_Model_Sync_Import $mock */
        $mock = $this->mockModel(
            'aoe_attributeconfigurator/sync_import',
            ['_importAttributeSets', '_importAttributes']
        );

        $mock->expects($this->once())
            ->method('_importAttributeSets');

        $mock->expects($this->once())
            ->method('_importAttributes');

        $mock->import();
    }
}
