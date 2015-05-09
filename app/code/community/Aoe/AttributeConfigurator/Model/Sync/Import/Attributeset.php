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
class Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset
    implements Aoe_AttributeConfigurator_Model_Sync_Import_Interface
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
        if (!$attributeSet->getId()) {
            // Attribute Set does not exist, create
            $attributeSet = $this->_createAttributeSet($attributeSetConfig);
        }
        if ($attributeSet->getId()) {
            // Update Groups on Attribute Set
            $this->_updateAttributeGroups($attributeSetConfig, $attributeSet->getId(), $attributeSet->getData('attribute_set_name'));
        }
    }

    /**
     * Create Attribute Set
     *
     * @param  Aoe_AttributeConfigurator_Model_Config_Attributeset $attributeSetConfig Attribute Set Config
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Creation_Exception
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Validation_Exception
     * @return Mage_Eav_Model_Entity_Attribute_Set
     */
    protected function _createAttributeSet($attributeSetConfig)
    {
        /** @var Mage_Eav_Model_Entity_Attribute_Set $skeletonAttributeSet */
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

        $newName = trim($attributeSetConfig->getName());

        $newAttributeSet->setEntityTypeId($this->_getEntityTypeId())
            ->setAttributeSetName($newName);

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
        $this->_getHelper()->log(sprintf('Attribute Set \'%s\' Data has been validated', $attributeSetConfig->getName()));

        try {
            $newAttributeSet->save();
            $this->_getHelper()->log(sprintf('Attribute Set \'%s\' created.', $attributeSetConfig->getName()));
            // Initialize from Skeleton
            $newAttributeSet->initFromSkeleton($skeletonAttributeSet->getId());
            $this->_getHelper()->log(sprintf('Attribute Set \'%s\' initialized from skeleton %s.', $attributeSetConfig->getName(), $attributeSetConfig->getSkeleton()));
            $newAttributeSet->save();
            $this->_getHelper()->log(sprintf('Attribute Set \'%s\' updated from skeleton.', $attributeSetConfig->getName()));
        } catch (Exception $saveException) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attributeset_Creation_Exception(
                sprintf(
                    'Creation error on attribute set \'%s\': %s',
                    $attributeSetConfig->getName(),
                    $saveException->getMessage()
                )
            );
        }
        $this->_getHelper()->log(sprintf('Attribute Set \'%s\' has been created/updated', $attributeSetConfig->getName()));

        return $newAttributeSet;
    }

    /**
     * Create Attribute Set Groups
     *
     * @param  Aoe_AttributeConfigurator_Model_Config_Attributeset $attributeSetConfig Attribute Set Config
     * @param  int                                                 $attributeSetId     Attribute Set Id
     * @param  string                                              $attributeSetName   Attribute Set Name
     * @return void
     */
    protected function _updateAttributeGroups($attributeSetConfig, $attributeSetId, $attributeSetName)
    {
        /** @var Mage_Eav_Model_Entity_Setup $setup */
        $setup = Mage::getModel('eav/entity_setup', 'core_setup');

        /** @var array $groups */
        $groups = $attributeSetConfig->getAttributeGroups();

        /** @var SimpleXMLIterator $group */
        foreach ($groups as $group) {
            $xmlAttr = current($group->attributes());

            $groupName = trim($xmlAttr['name']);

            try {
                $setup->addAttributeGroup(
                    $this->_getEntityTypeId(),
                    $attributeSetId,
                    $groupName
                );
                $this->_getHelper()->log(sprintf('Added/Updated: Group \'%s\' to Attribute Set \'%s\'.', $groupName, $attributeSetName));
            } catch (Exception $e) {
                $this->_getHelper()->log(sprintf('Error: While adding Group \'%s\' to Attribute Set \'%s\'.', $groupName, $attributeSetName), $e);
            }
        }
    }

    /**
     * @param string $name Attribut set name
     * @return Mage_Eav_Model_Entity_Attribute_Set
     */
    protected function _loadAttributeSetByName($name)
    {
        /** @var Mage_Eav_Model_Entity_Attribute_Set $attributeSetModel */
        $result = Mage::getModel('eav/entity_attribute_set')
            ->getCollection()
            ->addFieldToFilter('attribute_set_name', $name)
            ->addFieldToFilter('entity_type_id', $this->_getEntityTypeId())
            ->getFirstItem();

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
