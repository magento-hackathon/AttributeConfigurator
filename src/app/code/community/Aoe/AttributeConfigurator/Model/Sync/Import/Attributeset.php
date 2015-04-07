<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset implements Aoe_AttributeConfigurator_Model_Sync_Import_Interface
{

    /**
     * Import attributesets
     *
     * @param Aoe_AttributeConfigurator_Model_Config $config Config model
     * @return void
     */
    public function run($config)
    {
        $xml = $config->getAttributeSets();
        foreach ($xml->children() as $childConfig) {
            try {
                $this->validate($childConfig);
                $this->createAttributeSet($childConfig);
            } catch (Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Validation_Exception $e) {
                $this->_getHelper()->log('Attribute Set validation exception.', $e);
            } catch (Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Creation_Exception $e) {
                $this->_getHelper()->log('Attribute Set could not be saved.', $e);
            } catch (Exception $e) {
                $this->_getHelper()->log('Unexpected Attribute Set Error, skipping.', $e);
            }
        }
    }

    /**
     * @param  SimpleXMLElement $config Single Attribute Set Config
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Validation_Exception
     * @return void
     */
    private function validate($config)
    {
        $name = (string) $config['name'];
        $skeleton = (string) $config['skeleton'];
        if (!isset($name) || !trim($name)) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Validation_Exception();
        }
        if (!isset($skeleton) || !trim($skeleton)) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Validation_Exception();
        }
    }

    /**
     * Create Attribute Set
     *
     * @param  SimpleXMLElement $config Attribute Set Config
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Creation_Exception
     * @return void
     */
    private function createAttributeSet($config)
    {
        $name = trim((string) $config['name']);
        $skeleton = trim((string) $config['skeleton']);
        // Get Product Entity Id
        $productEntityId = Mage::getModel('catalog/product')->getResource()->getTypeId();
        // Retrieve Id of Skeleton Attribute Set to use for the new Attribute Set
        $skeletonAttributeSet = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter($productEntityId)
            ->addFilter('attribute_set_name', $skeleton);
        $skeletonId = $skeletonAttributeSet->getData()[0]['attribute_set_id'];
        /** @var Mage_Eav_Model_Entity_Attribute_Set $setModel */
        $setModel = Mage::getModel('eav/entity_attribute_set');
        // Set required Data to new Attribute Set
        $setModel->setEntityTypeId($productEntityId);
        $setModel->setData('attribute_set_name', trim($name));
        if ($setModel->validate()) {
            $setModel->save();
            $setModel->initFromSkeleton($skeletonId);
            $setModel->save();
        } else {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Creation_Exception();
        }
    }

    /**
     * Get the modules data helper
     *
     * @return Aoe_AttributeConfigurator_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('aoe_attributeconfigurator');
    }
}
