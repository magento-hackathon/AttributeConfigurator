<?php
/**
 * Class Hackathon_AttributeConfigurator_Test_Model_AttributeTest
 */
class Hackathon_AttributeConfigurator_Test_Model_Sync_ImportTest extends EcomDev_PHPUnit_Test_Case
{
    /** @var  Hackathon_AttributeConfigurator_Helper_Data */
    protected $_model;

    /** @var  string */
    protected $_testFile;

    protected function setUp()
    {
        $this->_model = Mage::getModel('hackathon_attributeconfigurator/sync_import');
        $this->_testFile = Mage::getModuleDir('', 'Hackathon_AttributeConfigurator') .
                DS . 'Test' . DS . 'Helper' . DS . 'Fixture' . DS . 'attribute-dummy.xml' ;

        parent::setUp();
    }

    public function testDataLoadedToConfig()
    {
        //Create a reflection to make the config property accessible
        $importReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Model_Sync_Import');
        $importConfigProperty = $importReflection->getProperty('_config');
        $importConfigProperty->setAccessible(true);

        //Assert that the property has been set by the getImport Method
        $this->assertInstanceOf(
                'Mage_Core_Model_Config',
                $importConfigProperty->getValue(new Hackathon_AttributeConfigurator_Model_Sync_Import )
        );

        //Set the config property to a new object create with the testfile
        $testConfig = Mage::getModel('core/config');

        $this->assertNotFalse(
                $testConfig->loadFile($this->_testFile)
        );
        $importConfigProperty->setValue($this->_model, $testConfig);

        //Assert Success
        $this->assertInstanceOf(
                'Mage_Core_Model_Config',
                $importConfigProperty->getValue($this->_model)
        );
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Import File can not be loaded
     *
     */

    public function testExceptionFiredIfFileNotFound()
    {
        //Set up a reflection of the helper to change the filename to an abitrary value
        $helperReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Helper_Data');
        $helperInstance = $helperReflection->newInstance();

        $fileNameProperty = $helperReflection->getProperty('_importFilename');
        $fileNameProperty->setAccessible(true);
        $fileNameProperty->setValue( $helperInstance, 'fileNotFound');

        //Set up a reflection of the import to inject the helper and invoke getImport
        $importReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Model_Sync_Import');

        $importConfigProperty = $importReflection->getProperty('_helper');
        $importConfigProperty->setAccessible(true);
        $importConfigProperty->setValue($this->_model, $helperInstance);

        $getImportMethod = $importReflection->getMethod('getImport');
        $getImportMethod->setAccessible(true);
        $getImportMethod->invoke($this->_model);
    }

    public function testImport()
    {

    }


}

