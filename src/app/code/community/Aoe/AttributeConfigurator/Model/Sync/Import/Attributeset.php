<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset implements Aoe_AttributeConfigurator_Model_Sync_Import_Interface
{
    /**
     * Lazy fetched entity type id for product attributes
     *
     * @var int $_entityTypeId
     */
    protected $_entityTypeId;

    /**
     * Import attributesets
     *
     * @param Aoe_AttributeConfigurator_Model_Config $config Config model
     * @return void
     */
    public function run($config)
    {
        $iterator = Mage::getModel(
            'aoe_attributeconfigurator/config_attributeset_iterator',
            $config->getAttributeSets()
        );
        foreach ($iterator as $_attributeSetConfig) {
            /** @var Aoe_AttributeConfigurator_Model_Config_Attributeset $_attributeSetConfig */
            try {
                $this->_processAttributeSet($_attributeSetConfig);
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
     * Process a single attributeset config
     *
     * @param Aoe_AttributeConfigurator_Model_Config_Attributeset $attributeSetConfig AttributeSet config
     * @return void
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Validation_Exception
     */
    protected function _processAttributeSet($attributeSetConfig)
    {
        if (!$attributeSetConfig->validate()) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Validation_Exception(
                'Validation errors on attributeset: \n'
                . implode('\n', $attributeSetConfig->getValidationMessages())
            );
        }

        $attributeSet = $this->_loadAttributeSetByName($attributeSetConfig->getName());
        if ($attributeSet->getId()) {
            // attribute set already exists
            return;
        }

        $this->_createAttributeSet($attributeSetConfig);
    }

    /**
     * Create Attribute Set
     *
     * @param  Aoe_AttributeConfigurator_Model_Config_Attributeset $attributeSetConfig Attribute Set Config
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Creation_Exception
     * @return void
     */
    protected function _createAttributeSet($attributeSetConfig)
    {
        $skeletonAttributeSet = $this->_loadAttributeSetByName($attributeSetConfig->getSkeleton());
        if (!$skeletonAttributeSet->getId()) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Creation_Exception(
                sprintf(
                    'Skeleton attribute set \'%s\' does not exist',
                    $attributeSetConfig->getSkeleton()
                )
            );
        }

        /** @var Mage_Eav_Model_Entity_Attribute_Set $newAttributeSet */
        $newAttributeSet = Mage::getModel('eav/entity_attribute_set');

        $newAttributeSet->setEntityTypeId($this->_getEntityTypeId())
            ->setAttributeSetName(trim($attributeSetConfig->getName()));

        try {
            $newAttributeSet->validate();
        } catch (Mage_Eav_Exception $validationException) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Validation_Exception(
                sprintf(
                    'Validation error on attribute set \'%s\': %s',
                    $attributeSetConfig->getName(),
                    $validationException->getMessage()
                )
            );
        }
        try {
            $newAttributeSet->save();
        } catch (Exception $saveException) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Creation_Exception(
                sprintf(
                    'Creation error on attribute set \'%s\': %s',
                    $attributeSetConfig->getName(),
                    $saveException->getMessage()
                )
            );
        }
    }

    /**
     * @param string $name Attribut set name
     * @return Mage_Eav_Model_Entity_Attribute_Set
     */
    protected function _loadAttributeSetByName($name)
    {
        /** @var Mage_Eav_Model_Entity_Attribute_Set $result */
        $result = Mage::getModel('eav/entity_attribute_set');
        $result->setEntityTypeId($this->_getEntityTypeId())
            ->load($name, 'attribute_set_name');

        return $result;
    }

    /**
     * Get the entity type id for product attributes
     *
     * @return int
     */
    protected function _getEntityTypeId()
    {
        if (isset($this->_entityTypeId)) {
            return $this->_entityTypeId;
        }

        $entityTypeId = Mage::getModel('eav/entity')
            ->setType(Mage_Catalog_Model_Product::ENTITY)
            ->getTypeId();
        $this->_entityTypeId = $entityTypeId;

        return $entityTypeId;
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
