<?php
/**
 * Class Hackathon_AttributeConfigurator_Test_Helper_DataTest
 */
class Hackathon_AttributeConfigurator_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{
    /** @var Hackathon_AttributeConfigurator_Helper_Data An instance of the helper*/
    protected $_helper;

    /**
     * @var string Location of the file used for testing
     */
    protected $_testFile;

    protected function setUp()
    {
        $this->_helper = Mage::helper('hackathon_attributeconfigurator');
        $this->_testFile = Mage::getModuleDir('', 'Hackathon_AttributeConfigurator') .
                DS . 'Test' . DS . 'Helper' . DS . 'Fixture' . DS . 'attribute-fixture.xml' ;
        parent::setUp();
    }

    public function testGetImportFilename()
    {
        $this->assertNotNull(Mage::getStoreConfig(Hackathon_AttributeConfigurator_Helper_Data::XML_PATH_FILENAME));
        $this->assertInternalType('int', strpos($this->_helper->getImportFilename(), Mage::getBaseDir() . DS ));
    }

    public function testCreateFileHash()
    {
        $this->assertNotFalse(
                $this->_helper->createFileHash(
                        $this->_testFile
                )
        );

        $this->assertFalse(
                $this->_helper->createFileHash(
                        "notAvalidFile"
                )
        );
    }

    public function testIsAttributeXmlNewer()
    {
        //get the current value of the config - should be null, but will be restored after testing just in case it's different
        $originalConfigValue = Mage::getStoreConfig(Hackathon_AttributeConfigurator_Helper_Data::XML_PATH_CURRENT_HASH);

        //Set up a reflection class to change the filename to an abitrary value
        $helperReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Helper_Data');
        $helperInstance = $helperReflection->newInstance();

        $fileNameProperty = $helperReflection->getProperty('_importFilename');
        $fileNameProperty->setAccessible(true);
        $fileNameProperty->setValue( $helperInstance, $this->_testFile);

        //set the config to the test file's hash
        Mage::getModel('core/config')
                ->saveConfig(
                        Hackathon_AttributeConfigurator_Helper_Data::XML_PATH_CURRENT_HASH,
                        md5_file($this->_testFile)
                );
        //never forget to reset the config after applying changes!!!
        Mage::app()->getStore()->resetConfig();

        $this->assertFalse(
                $helperInstance->isAttributeXmlNewer()
        );

        //set the config to a different value
        Mage::getModel('core/config')
            ->saveConfig(
                Hackathon_AttributeConfigurator_Helper_Data::XML_PATH_CURRENT_HASH,
                "testValue"
        );
        Mage::app()->getStore()->resetConfig();

        $this->assertTrue(
                $helperInstance->isAttributeXmlNewer()
        );

        //revert the config to it's orginal state
        Mage::getModel('core/config')
                ->saveConfig(
                        Hackathon_AttributeConfigurator_Helper_Data::XML_PATH_CURRENT_HASH,
                        $originalConfigValue
                );
        Mage::app()->getStore()->resetConfig();

    }

    public function testcheckAttributeMaintained()
    {
        $attribute = Mage::getModel('catalog/entity_attribute');
        $attribute->setIsMaintainedByConfigurator(1);
        $this->assertTrue($this->_helper->checkAttributeMaintained($attribute));
        $this->assertFalse($this->_helper->checkAttributeMaintained($attribute->setIsMaintainedByConfigurator(0)));
        $this->assertFalse($this->_helper->checkAttributeMaintained(NULL));
    }


}
