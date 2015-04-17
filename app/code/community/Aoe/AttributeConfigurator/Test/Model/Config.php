<?php

/**
 * Class Aoe_AttributeConfigurator_Test_Model_Config
 *
 * Test class for Aoe_AttributeConfigurator_Model_Config
 *
 * @category Test
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Test_Model_Config extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test model instantiation
     *
     * @test
     * @return Aoe_AttributeConfigurator_Model_Config
     */
    public function checkClass()
    {
        $model = Mage::getModel('aoe_attributeconfigurator/config');
        $this->assertInstanceOf(
            'Aoe_AttributeConfigurator_Model_Config',
            $model
        );

        return $model;
    }

    /**
     * @test
     * @depends checkClass
     * @param Aoe_AttributeConfigurator_Model_Config $model Config model
     * @return void
     */
    public function checkGetConfigHelper($model)
    {
        $reflection = new ReflectionClass(get_class($model));
        $method = $reflection->getMethod('_getConfigHelper');
        $method->setAccessible(true);

        $this->assertInstanceOf(
            'Aoe_AttributeConfigurator_Helper_Config',
            $method->invoke($model)
        );
    }

    /**
     * @test
     * @depends checkClass
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessageRegExp /configured import file '.*invalid_testfile.xml' is not readable/
     * @param Aoe_AttributeConfigurator_Model_Config $model Config model
     * @return void
     */
    public function checkLoadInvalidXml($model)
    {
        // mock helper to return invalid filepath
        $mockedHelper = $this->getHelperMock(
            'aoe_attributeconfigurator/config',
            ['getImportFilePath']
        );
        $mockedHelper->expects($this->once())
            ->method('getImportFilePath')
            ->will($this->returnValue('invalid_testfile.xml'));

        $this->replaceByMock(
            'helper',
            'aoe_attributeconfigurator/config',
            $mockedHelper
        );

        $reflection = new ReflectionClass(get_class($model));
        $method = $reflection->getMethod('_loadXml');
        $method->setAccessible(true);

        $method->invoke($model);
    }

    /**
     * @test
     * @depends checkClass
     * @expectedException Aoe_AttributeConfigurator_Model_Exception
     * @expectedExceptionMessageRegExp /unable to load xml file '.*checkLoadBrokenXml.xml'/
     * @param Aoe_AttributeConfigurator_Model_Config $model Config model
     * @return void
     */
    public function checkLoadBrokenXml($model)
    {
        // mock helper to return fixture of broken xml file
        $mockedHelper = $this->getHelperMock(
            'aoe_attributeconfigurator/config',
            ['getImportFilePath']
        );

        $brokenXmlFilePath = implode(
            DS,
            [
                Mage::getModuleDir('', 'Aoe_AttributeConfigurator'),
                'Test',
                'Model',
                'Config',
                'fixtures',
                'checkLoadBrokenXml.xml'
            ]
        );

        $mockedHelper->expects($this->once())
            ->method('getImportFilePath')
            ->will($this->returnValue($brokenXmlFilePath));

        $this->replaceByMock(
            'helper',
            'aoe_attributeconfigurator/config',
            $mockedHelper
        );

        $reflection = new ReflectionClass(get_class($model));
        $method = $reflection->getMethod('_loadXml');
        $method->setAccessible(true);

        $method->invoke($model);
    }

    /**
     * @test
     * @depends checkClass
     * @param Aoe_AttributeConfigurator_Model_Config $model Config model
     * @return void
     */
    public function checkLoadValidXml($model)
    {
        $this->_mockConfigHelperLoadingXml();

        $reflection = new ReflectionClass(get_class($model));
        $method = $reflection->getMethod('_loadXml');
        $method->setAccessible(true);

        $xml = $method->invoke($model);
        $this->assertInstanceOf(
            'Varien_Simplexml_Element',
            $xml,
            'parsed xml successful'
        );
    }

    /**
     * @test
     * @depends checkClass
     * @param Aoe_AttributeConfigurator_Model_Config $model Config model
     * @return void
     */
    public function checkGetAttributeSet($model)
    {
        $this->_mockConfigHelperLoadingXml();
        $attributeSets = $model->getAttributeSets();
        $this->assertInstanceOf('Varien_Simplexml_Element', $attributeSets);
    }

    /**
     * @test
     * @depends checkClass
     * @param Aoe_AttributeConfigurator_Model_Config $model Config model
     * @return void
     */
    public function checkGetAttributes($model)
    {
        $this->_mockConfigHelperLoadingXml();
        $attributes = $model->getAttributes();
        $this->assertInstanceOf('Varien_Simplexml_Element', $attributes);
    }

    /**
     * Mock the config helper to force loading of a specified fixture xml
     *
     * @param string $fixture     Fixture file name
     * @param bool   $replaceMock Also call replaceByMock for the mocked helper
     * @return EcomDev_PHPUnit_Mock_Proxy|Aoe_AttributeConfigurator_Model_Config
     */
    protected function _mockConfigHelperLoadingXml($fixture = 'validXml.xml', $replaceMock = true)
    {
        $mockedHelper = $this->getHelperMock(
            'aoe_attributeconfigurator/config',
            ['getImportFilePath']
        );

        $validXmlDFilePath = implode(
            DS,
            [
                Mage::getModuleDir('', 'Aoe_AttributeConfigurator'),
                'Test',
                'Model',
                'Config',
                'fixtures',
                $fixture
            ]
        );

        $mockedHelper->expects($this->any())
            ->method('getImportFilePath')
            ->will($this->returnValue($validXmlDFilePath));

        if ($replaceMock) {
            $this->replaceByMock(
                'helper',
                'aoe_attributeconfigurator/config',
                $mockedHelper
            );
        }

        return $mockedHelper;
    }
}
