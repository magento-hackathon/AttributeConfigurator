<?php
/**
 * Class Hackathon_AttributeConfigurator_Test_Model_AttributeTest
 */
class Hackathon_AttributeConfigurator_Test_Model_Sync_ImportTest extends EcomDev_PHPUnit_Test_Case
{
    /** @var  Hackathon_AttributeConfigurator_Model_Sync_Import */
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
        $importXmlProperty = $importReflection->getProperty('_xml');
        $importXmlProperty->setAccessible(true);

        //Assert that the property has been set by the getImport Method
        $this->assertInstanceOf(
                'Varien_Simplexml_Config',
                $importXmlProperty->getValue(new Hackathon_AttributeConfigurator_Model_Sync_Import )
        );

        //Set the config property to a new object create with the testfile
        $testConfig = new Varien_Simplexml_Config($this->_testFile);

        $importXmlProperty->setValue($this->_model, $testConfig);

        //Assert Success
        $this->assertInstanceOf(
                'Varien_Simplexml_Config',
                $importXmlProperty->getValue($this->_model)
        );
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Import file can not be loaded
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
        $importXmlProperty = $importReflection->getProperty('_helper');
        $importXmlProperty->setAccessible(true);
        $importXmlProperty->setValue($this->_model, $helperInstance);

        $getImportMethod = $importReflection->getMethod('getImport');
        $getImportMethod->setAccessible(true);
        $getImportMethod->invoke($this->_model);
    }

    /**
     * @test
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage No attributesets found in file
     */
    public function importThrowsExceptionIfNoSetsFound()
    {
        $helperReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Helper_Data');
        $helperInstance = $helperReflection->newInstance();
        $fileNameProperty = $helperReflection->getProperty('_importFilename');
        $fileNameProperty->setAccessible(true);
        $fileNameProperty->setValue( $helperInstance, $this->_testFile);

        //Set up a reflection of the import to inject the helper and invoke getImport
        $importReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Model_Sync_Import');

        $importHelperProperty = $importReflection->getProperty('_helper');
        $importHelperProperty->setAccessible(true);
        $importHelperProperty->setValue($this->_model, $helperInstance);

        $importXmlProperty = $importReflection->getProperty('_xml');
        $importXmlProperty->setAccessible(true);

        $config = $importXmlProperty->getValue($this->_model);
        $config->setNode('attributesetslist', NULL);
        $importXmlProperty->setValue($this->_model, $config);

        $this->_model->import();

    }

    /**
     * @test
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage No attributes found in file
     */
    public function importThrowsExceptionIfNoAttributesFound()
    {
        $importReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Model_Sync_Import');
        $importXmlProperty = $importReflection->getProperty('_xml');
        $importXmlProperty->setAccessible(true);

        //get the config from the object, set the node empty, start import
        $config = $importXmlProperty->getValue($this->_model);
        $config->setNode('attributeslist', NULL);
        $importXmlProperty->setValue($this->_model, $config);

        $this->_model->import();
    }

    public function testGetDataFromXmlPopulatesDataProperties()
    {
        //Create a reflection to set the data to a known state
        $importReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Model_Sync_Import');
        $importXmlProperty = $importReflection->getProperty('_xml');

        $importXmlProperty->setAccessible(true);
        $testConfig = new Varien_Simplexml_Config($this->_testFile);
        $importXmlProperty->setValue($this->_model, $testConfig);

        $this->_model->getDataFromXml();

        $importSetDataProperty = $importReflection->getProperty('_setData');
        $importSetDataProperty->setAccessible(true);

        $attributeSets = $importSetDataProperty->getValue($this->_model);
        $testSet = $attributeSets->xpath("//attributeset[@name='Flöten/Blechbläser']");

        $this->assertNotEmpty($testSet);

        $importAttributeProperty = $importReflection->getProperty('_attrData');
        $importAttributeProperty->setAccessible(true);
        $attributes = $importAttributeProperty->getValue($this->_model);
        $testAttribute = $attributes->xpath("//attribute[@code='hersteller']");

        $this->assertNotEmpty($testAttribute);

    }

    public function testGetAttributesetNames()
    {
        $importReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Model_Sync_Import');
        $importXmlProperty = $importReflection->getProperty('_xml');
        $importXmlProperty->setAccessible(true);

        $testConfig = new Varien_Simplexml_Config($this->_testFile);
        $importXmlProperty->setValue($this->_model, $testConfig);

        $this->_model->getDataFromXml();
        $names = $this->_model->getAttributesetNames();
        $this->assertEquals(
                'Gitarren & Saiteninstrumente',
                $names[0]
        );
    }

    /**
     * @test
     *
     * @expectedException Mage_Adminhtml_Exception
     * @expectedExceptionMessage Attribute 'unittestattribute' is not part of a Attributeset
     *
     */
    public function expceptionThrownIfAttributeIsInNoAttributeSet()
    {
        $importReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Model_Sync_Import');
        $importXmlProperty = $importReflection->getProperty('_xml');
        $importXmlProperty->setAccessible(true);

        $testConfig = new Varien_Simplexml_Config($this->_testFile);
        $importXmlProperty->setValue($this->_model, $testConfig);

        $this->_model->getDataFromXml();

        $importAttributeProperty = $importReflection->getProperty('_attrData');
        $importAttributeProperty->setAccessible(true);
        $attributes = $importAttributeProperty->getValue($this->_model);

        $testAttribute = $attributes->addChild('attribute');
        $testAttribute->addAttribute('code', 'unittestattribute');

        $this->_model->validate();
    }


    /**
     * @test
     *
     * @expectedException Mage_Adminhtml_Exception
     * @expectedExceptionMessage Attributeset 'unittestattributeset' referenced by 'unittestattribute'
     */
    public function exceptionThrownOnUnknownAttributeSet()
    {
        //Create a reflection to set the data to a known state
        $importReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Model_Sync_Import');
        $importXmlProperty = $importReflection->getProperty('_xml');
        $importXmlProperty->setAccessible(true);

        $testConfig = new Varien_Simplexml_Config($this->_testFile);
        $importXmlProperty->setValue($this->_model, $testConfig);

        $this->_model->getDataFromXml();

        $importAttributeProperty = $importReflection->getProperty('_attrData');
        $importAttributeProperty->setAccessible(true);
        $attributes = $importAttributeProperty->getValue($this->_model);


        $testAttribute = $attributes->addChild('attribute');
        $testAttribute->addAttribute('code', 'unittestattribute');
        $testAttributeSets = $testAttribute->addChild('attributesets');
        $testattributeSet = $testAttributeSets->addChild('attributeset');
        $testattributeSet->addAttribute('name', 'unittestattributeset');

        $this->_model->validate();
    }

    /**
     * @test
     *
     * @expectedException Mage_Adminhtml_Exception
     * @expectedExceptionMessage does not contain a attributegroup
     */
    public function expceptionThrownIfNoGroupsInAttributeset()
    {
        //Create a reflection to set the data to a known state
        $importReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Model_Sync_Import');
        $importXmlProperty = $importReflection->getProperty('_xml');
        $importXmlProperty->setAccessible(true);

        $testConfig = new Varien_Simplexml_Config($this->_testFile);
        $importXmlProperty->setValue($this->_model, $testConfig);

        $this->_model->getDataFromXml();

        $importAttributeProperty = $importReflection->getProperty('_attrData');
        $importAttributeProperty->setAccessible(true);
        /** @var Varien_Simplexml_Element $attributes */
        $attributes = $importAttributeProperty->getValue($this->_model);

        //remove the group from the first attributeset
        $sets = $attributes->attribute[0]->attributesets;
        unset($sets->attributeset->attributegroup);

        $this->_model->validate();
    }

    /**
     * @test
     * @expectedException Mage_Adminhtml_Exception
     * @expectedExceptionMessage contains more than one attributegroup
     */
    public function expceptionThrownIfMultipleGroupsInAttributeset()
    {
        //Create a reflection to set the data to a known state
        $importReflection = new ReflectionClass('Hackathon_AttributeConfigurator_Model_Sync_Import');
        $importXmlProperty = $importReflection->getProperty('_xml');
        $importXmlProperty->setAccessible(true);

        $testConfig = new Varien_Simplexml_Config($this->_testFile);
        $importXmlProperty->setValue($this->_model, $testConfig);

        $this->_model->getDataFromXml();

        $importAttributeProperty = $importReflection->getProperty('_attrData');
        $importAttributeProperty->setAccessible(true);
        /** @var Varien_Simplexml_Element $attributes */
        $attributes = $importAttributeProperty->getValue($this->_model);
        $set = $attributes->attribute[0]->attributesets->attributeset;
        $group = $set->addChild('attributegroup');

        $this->_model->validate();

        //remove the group from the first attributeset


    }






}


//        foreach ($this->_setData->children() as $data) {
//            var_dump((string) $data['name']);
//        }

//        foreach ($this->_setData->xpath('//attributeset') as $set) {
//            var_dump($set->asArray());
//        }

//        var_dump($this->_setData->attributeset);
//        var_dump($this->_setData->xpath("//attributeset[@name='Flöten/Blechbläser']"));

///**
// * Parse XML for Attributes
// *
// * @param $attributesets
// * @return array
// */
//protected function _getAttributeFromXml($attributesets)
//{
//    $returnarray = array();
//    foreach ($attributesets->children() as $attributeset) {
//        $returnarray[] = (string) $attributeset['name'];
//    }
//    return $returnarray;
//}
