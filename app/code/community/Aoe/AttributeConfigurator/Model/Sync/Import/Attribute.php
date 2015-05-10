<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Sync_Import_Attribute
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Sync_Import_Attribute
    extends Mage_Eav_Model_Entity_Attribute
    implements Aoe_AttributeConfigurator_Model_Sync_Import_Interface
{
    /**
     * Lazy fetched entity type id for product attributes
     *
     * @var int $_entityTypeId
     */
    protected $_entityTypeId;

    /** @var array */
    protected $_attributesToSkip;

    /** @var array Attribute Properties that cannot be changed */
    protected $_fixedProps = [
        'attribute_id', // Internal ID
        'entity_type_id', // Can't toggle Attributes between entities, i´m afraid
        'attribute_code', // Obviously
        'attribute_model', // Makes no sense
        'is_unique', // Unique is already streched over all entities, hard to roll back
        'is_maintained_by_configurator', // Fixed for this module
        'frontend_input', // Will be set dependent on backend_type
    ];

    /** @var array Attribute Properties that can be changed without problems */
    protected $_changeableProps = [
        'is_required',
        'is_user_defined',
        'note'
    ];

    /** @var array Attribute Properties boolean validation */
    protected $_booleanValidation = [
        'is_required',
        'is_user_defined'
    ];

    /** @var array Attribute Properties that need to be migrated if changed */
    protected $_migratableProps = [
        'attribute_model',
        'backend_model',
        'backend_type',
        'frontend_model',
        'frontend_label',
        'frontend_class',
        'source_model',
        'default_value'
    ];

    /** @var array Possible Frontend Input Types for different Backend Types, first value is the preferred */
    protected $_frontendMappping = [
        'varchar' => ['text', 'textarea'],
        'datetime' => ['date'],
        'int' => ['text', 'select', 'hidden'],
        'text' => ['textarea', 'text', 'multiline', 'multiselect'],
        'decimal' => ['text', 'price', 'weight']
    ];

    /**
     * Run the attribute update/import
     *
     * @param Aoe_AttributeConfigurator_Model_Config $config Config model
     * @return void
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Exception
     */
    public function run($config)
    {
        /** @var Aoe_AttributeConfigurator_Model_Config_Attribute_Iterator $iterator */
        $iterator = Mage::getModel(
            'aoe_attributeconfigurator/config_attribute_iterator',
            $config->getAttributes()
        );

        $this->_attributesToSkip = $this->_getConfigHelper()->getSkipAttributeCodes();

        foreach ($iterator as $_attributeConfig) {
            /** @var Aoe_AttributeConfigurator_Model_Config_Attribute $_attributeConfig */
            try {
                $this->_processAttribute($_attributeConfig);
            } catch (Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Skipped_Exception $skippedException) {
                $this->_getHelper()->log(
                    $skippedException->getMessage(),
                    null,
                    Zend_Log::INFO
                );
            } catch (Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Exception $attributeException) {
                $this->_getHelper()->log(
                    '-',
                    $attributeException,
                    Zend_Log::WARN
                );
            } catch (Exception $e) {
                $this->_getHelper()->log(
                    'error during attribute import',
                    $e,
                    Zend_Log::ERR
                );
            }
        }
    }

    /**
     * Process a single attribute config
     *
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config to process
     * @return void
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Validation_Exception
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Skipped_Exception
     */
    protected function _processAttribute($attributeConfig)
    {
        if (!$attributeConfig->validate()) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Validation_Exception(
                'Validation errors on attribute: \n'
                . implode('\n', $attributeConfig->getValidationMessages())
            );
        }

        if (!$this->_checkSkipAttribute($attributeConfig->getCode())) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Skipped_Exception(
                sprintf('Skipped: Attribute \'%s\' (see System Config).', $attributeConfig->getCode())
            );
        }

        $attribute = $this->_loadAttributeByCode($attributeConfig->getCode());
        if ($attribute->getId()) {
            $this->_updateOrMigrateAttribute($attribute, $attributeConfig);
        } else {
            $this->_createAttribute($attribute, $attributeConfig);
        }
    }

    /**
     * Checks if Attribute is set to be Skipped in System Config
     *
     * @param string $code Attribute Code
     * @return bool
     */
    protected function _checkSkipAttribute($code)
    {
        if (in_array($code, $this->_attributesToSkip)) {
            return false;
        }

        return true;
    }

    /**
     * Decide to upgrade or migrate an attribute and trigger the required methods
     *
     * @param Mage_Catalog_Model_Entity_Attribute $attribute Attribute to update
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Skipped_Exception
     * @return void
     */
    protected function _updateOrMigrateAttribute($attribute, $attributeConfig)
    {
        if (!$this->_getHelper()->checkAttributeMaintained($attribute)) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Skipped_Exception(
                sprintf('Not maintained: Attribute \'%s\'.', $attributeConfig->getCode())
            );
        }

        // Update Attribute Group and Set Assignment before changing Attribute itself
        $this->_updateAttributeSetsAndGroups($attributeConfig);

        // Update Attribute Settings
        $this->_updateAttribute($attribute, $attributeConfig);
    }

    /**
     * Update the data of an existing attribute
     *
     * @param Mage_Catalog_Model_Entity_Attribute $attribute Attribute to update
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Skipped_Exception
     * @return void
     */
    protected function _updateAttribute($attribute, $attributeConfig)
    {
        if (!$this->_getHelper()->checkMigrationActivated()) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Skipped_Exception(
                sprintf('Migration/Updating deactivated: Attribute \'%s\' not modified.', $attributeConfig->getCode())
            );
        }
        sprintf('Attribute \'%s\' left unmodified - Attribute migration implementation not production safe.', $attributeConfig->getCode());
        // TODO: Check implementation for (mostly) safe execution as this can potentially destroy data

        // Compute Differences between existing settings and incoming settings
        $attributeDiff = $this->_getAttributeDiff($attribute, $attributeConfig);

        // Remove Fixed Properties = can not be changed
        $attributeDiff = array_diff($attributeDiff, $this->_fixedProps);

        // Update Changeable Settings
        $this->_changeableAttributeUpdate($attribute, $attributeConfig, $attributeDiff);

        // Update Settings that need migration methods
        $this->_migratableAttributeUpdate($attribute, $attributeConfig, $attributeDiff);
    }

    /**
     * Create a new (managed) attribute
     *
     * @param Mage_Catalog_Model_Entity_Attribute $attribute Attribute to update
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config
     * @return void
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Exception
     */
    protected function _createAttribute($attribute, $attributeConfig)
    {
        $data = $attributeConfig->getSettingsAsArray();
        $data['frontend_input'] = $this->_getFrontendForBackend(
            $data['backend_type'],
            $data['frontend_input']
        );
        $attribute->setData(
            $data
        );
        $attribute->setEntityTypeId($this->_getEntityTypeId())
            ->setAttributeCode($attributeConfig->getCode())
            ->setData($this->_getHelper()->getFlagColumnName(), 1);

        try {
            $attribute->save();
        } catch (Exception $e) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Exception(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        $this->_updateAttributeSetsAndGroups($attributeConfig);
    }

    /**
     * Update all attribute sets and groups mentioned in the $attributeConfig
     *
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config
     * @return void
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Exception
     */
    protected function _updateAttributeSetsAndGroups($attributeConfig)
    {
        foreach ($attributeConfig->getAttributeSets() as $_attributeSet) {
            /** @var Aoe_AttributeConfigurator_Model_Config_Attribute_Attributeset $_attributeSet */
            $this->_updateAttributeSetAndGroup($attributeConfig, $_attributeSet);
        }
    }

    /**
     * Update attribute set and attribute group config
     *
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute_Attributeset $attributeSet Attributeset to use
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Exception
     * @return void
     */
    protected function _updateAttributeSetAndGroup($attributeConfig, $attributeSet)
    {
        /** @var Mage_Eav_Model_Entity_Setup $setup */
        $setup = Mage::getModel('eav/entity_setup', 'core_setup');

        /*
         * Autocorrection for incoming 'default' Attribute Set Name in different capitalization,
         * we don´t want to have more than one Default Attribute Set, so everything remotely like this
         * will be redirected to the Magento Default Attribute Set
         */
        $attributeSetName = trim($attributeSet->getName());
        if (strtolower($attributeSetName) == 'default') {
            $attributeSetName = trim($attributeSetName);
        }

        $entityTypeCode = $setup->getEntityType($attributeConfig->getEntityTypeId())['entity_type_code'];
        // Attribute ID - we will need this later
        $attributeId = Mage::getModel('eav/entity_attribute')
            ->loadByCode($entityTypeCode, $attributeConfig->getCode())
            ->getData('attribute_id');

        /*
         * Much Overhead to get the Attribute Set Model, but entity attribute set returns
         * wrong id when loading via name
         */
        /** @var Mage_Eav_Model_Entity_Attribute_Set $attributeSetModel */
        $attributeSetModel = Mage::getModel('eav/entity_attribute_set')
            ->getCollection()
            ->addFieldToFilter('attribute_set_name', $attributeSetName)
            ->addFieldToFilter('entity_type_id', $attributeConfig->getEntityTypeId())
            ->getFirstItem();
        // Attribute Set ID - we will need this later
        $attributeSetId = (int)$attributeSetModel->getData('attribute_set_id');

        if (!$attributeSetId) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Exception(
                sprintf(
                    'Unknown attribute set with name \'%s\'.',
                    $attributeSet->getName()
                )
            );
        }

        // TODO: we need real update (also remove if the attribute config has changed)
        // Most likely only one, fetch all
        $attributeGroups = (array)$attributeSet->getAttributeGroups();

        // Iterate through Attribute Groups
        foreach ($attributeGroups as $group) {
            $groupId = $setup->getAttributeGroup(
                $attributeConfig->getEntityTypeId(),
                $attributeSetId,
                $group,
                'attribute_group_name'
            );
            if ($groupId) {
                try {
                    $setup->addAttributeToSet(
                        $attributeConfig->getEntityTypeId(),
                        $attributeSetId,
                        $groupId,
                        $attributeId,
                        $attributeConfig->getSortOrder()
                    );
                    $this->_getHelper()->log(sprintf('Added: Attribute \'%s\' to Attribute Set Id #%s.', $attributeConfig->getCode(), $attributeSetId));
                } catch (Exception $e) {
                    $this->_getHelper()->log(sprintf('Error: When Adding Attribute \'%s\' to Attibute Set Id #%s.', $attributeConfig->getCode(), $attributeSetId), $e);
                }
            }
        }
    }

    /**
     * @param Mage_Catalog_Model_Entity_Attribute              $attribute       Attribute to update
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config
     * @param array $attributeDiff
     */
    protected function _migratableAttributeUpdate($attribute, $attributeConfig, $attributeDiff)
    {
        foreach($this->_migratableProps as $prop) {
            // Only act if this is a changed setting#
            if(in_array($prop, $attributeDiff)) {
                switch ($prop) {
                    case 'backend_type':
                        $backendType = $attributeConfig->getSettingsAsArray()['backend_type'];
                        $frontendInput = $attributeConfig->getSettingsAsArray()['frontend_input'];
                        if ($backendType == 'static') {
                            $this->_getHelper()->log(sprintf('Skipping Migration of setting %s for attribute %s, static type not supported', $prop, $attribute->getName()));
                            break;
                        }
                        $this->_getHelper()->log(sprintf('Migrating setting %s for attribute %s', $prop, $attribute->getName()));
                        $this->_convertBackendType(
                            $attribute,
                            $backendType,
                            $frontendInput
                        );
                        break;
                    case 'attribute_model':
                        $this->_getHelper()->log(sprintf('Skipping Migration of setting %s for attribute %s, not implemented', $prop, $attribute->getName()));
                        break;
                    case 'backend_model':
                        $this->_getHelper()->log(sprintf('Skipping Migration of setting %s for attribute %s, not implemented', $prop, $attribute->getName()));
                        break;
                    case 'frontend_model':
                        $this->_getHelper()->log(sprintf('Skipping Migration of setting %s for attribute %s, not implemented', $prop, $attribute->getName()));
                        break;
                    case 'frontend_input':
                        $this->_getHelper()->log(sprintf('Skipping Migration of setting %s for attribute %s, can\'t be set independent of backend_type.', $prop, $attribute->getName()));
                        break;
                    case 'frontend_label':
                        $this->_getHelper()->log(sprintf('Skipping Migration of setting %s for attribute %s, not implemented', $prop, $attribute->getName()));
                        break;
                    case 'frontend_class':
                        $this->_getHelper()->log(sprintf('Skipping Migration of setting %s for attribute %s, not implemented', $prop, $attribute->getName()));
                        break;
                    case 'source_model':
                        $this->_getHelper()->log(sprintf('Skipping Migration of setting %s for attribute %s, not implemented', $prop, $attribute->getName()));
                        break;
                    case 'default_value':
                        $this->_getHelper()->log(sprintf('Skipping Migration of setting %s for attribute %s, not implemented', $prop, $attribute->getName()));
                        break;
                }
            }
        }
    }

    /**
     * Converts existing Attribute to different type
     *
     * @param  Mage_Catalog_Model_Entity_Attribute $attribute     Attribute
     * @param  string                              $backendType   New Backend Type
     * @param  string                              $frontendInput New Frontend Input
     * @return void
     */
    protected function _convertBackendType($attribute, $backendType, $frontendInput)
    {
        $_dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $frontendInput = $this->_getFrontendForBackend(
            $backendType,
            $frontendInput
        );

        // Actual Conversion of Attribute
        $sql = <<<EOS
UPDATE
    eav_attribute
SET
    backend_type = ?,
    frontend_input = ?
WHERE
    attribute_id = ?;
EOS;

        try {
            $_dbConnection->query(
                $sql,
                [
                    $backendType,
                    $frontendInput,
                    $attribute->getId(),
                ]
            );

            // Migrate existing Data
            if (!in_array($frontendInput, ['select', 'multiselect'])) {
                $this->_migrateData($attribute, $backendType);
            } else {
                $this->_migrateSelect($attribute, $backendType, $frontendInput);
            }

        }catch(Exception $e){
            $this->_getHelper()->log(sprintf('Exception occured while converting Backend Type'), $e);
        }
    }

    /**
     * Migrate Entries from source to target tables (if possible)
     *
     * TODO: Delete existing Select/Multiselect Values if the new Backend Type is not one of Select/Multiselect
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute Attribute Model
     * @param string $targetType Target Backend Type
     * @return void
     */
    protected function _migrateData($attribute, $targetType)
    {
        /** @var Varien_Db_Adapter_Interface $_dbConnection */
        $_dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        // e.g. Entity is 'catalog_product'
        $entityTypeCode = $attribute->getEntity()->getData('entity_type_code');

        // Set Backend Types for later reference
        $sourceType = $attribute->getBackendType();

        // Create complete Entity Table names, e.g. 'catalog_product_entity_text'
        $sourceTable = implode([$entityTypeCode, 'entity', $sourceType], '_');
        $targetTable = implode([$entityTypeCode, 'entity', $targetType], '_');

        // Select all existing entries for given Attribute
        $srcSql = 'SELECT' .
            ' * FROM ' . $sourceTable . ' WHERE attribute_id = ? AND entity_type_id = ?';
        /** @var Zend_Db_Statement_Interface $sourceQuery */
        $sourceQuery = $_dbConnection->query(
            $srcSql,
            [
                $attribute->getId(),
                $attribute->getEntity()->getData('entity_type_id')
            ]
        );

        $this->_mergeNonSelect(
            $targetType, $sourceQuery, $sourceType, $targetTable,
            $_dbConnection, $sourceTable
        );
    }

    /**
     * Migrate Different Select/Multiselect Combinations
     *
     * @param Mage_Catalog_Model_Entity_Attribute $attribute       Attribute
     * @param string                              $targetType      New Backend Type
     * @param string                              $targetInputType Frontend Input Type
     */
    protected function _migrateSelect($attribute, $targetType, $targetInputType)
    {
        $selectTypes = ['select', 'multiselect'];
        $sourceType = $attribute->getBackendType();
        $sourceInputType = $attribute->getData('frontend_input');

        if(in_array($sourceInputType, $selectTypes) && in_array($targetInputType, $selectTypes)) {
            // Everything is SelectType
            /**
             * Convert Multiselect to Select
             * Note: Select to Multiselect is not necessary, same Backend Type, just able to save more values
             */
        }

        if(in_array($sourceInputType, $selectTypes)) {
            // Source is SelectType
            /**
             * Convert From Select to 'flat' Entity
             */
        }

        if(in_array($targetInputType, $selectTypes)) {
            // Target is SelectType
            /**
             * Convert from 'flat' Entity to Select/Multiselect
             */
        }
    }

    /**
     * Force Casting of Backend Types
     *
     * TODO : Fetch Field Type and Length Definition from Database Entity Tables
     *
     * @param mixed $value Current Value
     * @param string $sourceType Current Source Type
     * @param string $targetType New Source Type
     * @return null
     */
    protected function _typeCast($value, $sourceType, $targetType)
    {
        if ($sourceType === $targetType) {
            return $value;
        }
        switch ($targetType) {
            case 'decimal':
                return min((int)$value, 2147483648);
            case 'gallery':
                return $this->truncateString((string)$value, 254);
            case 'group_price':
                return min((int)$value, 65535);
            case 'int':
                return min((int)$value, 2147483648);
            case 'media_gallery':
                return $this->truncateString((string)$value, 254);
            case 'media_gallery_value':
                return min((int)$value, 65535);
            case 'text':
                return (string)$value;
            case 'tier_price':
                return min((int)$value, 65535);
            case 'url_key':
                return $this->truncateString((string)$value, 254);
            case 'varchar':
                return $this->truncateString((string)$value, 254);
            case 'datetime':
                return null;
            case 'static':
                return null;
        }

        return null;
    }

    /**
     * Truncate string if too long
     *
     * @param  string $str Input String
     * @param  integer $maxlen Maximum String Length
     * @return string
     */
    public static function truncateString($str, $maxlen)
    {
        if (strlen($str) <= $maxlen) {
            return $str;
        }

        return substr($str, 0, $maxlen);
    }

    /**
     * Load a catalog entity attribute by its code
     *
     * @param string $attributeCode Attribute code
     * @return Mage_Catalog_Model_Entity_Attribute
     */
    protected function _loadAttributeByCode($attributeCode)
    {

        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $result */
        $result = Mage::getModel('catalog/resource_eav_attribute');
        $result->loadByCode($this->_getEntityTypeId(), $attributeCode);

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
     * @return Aoe_AttributeConfigurator_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('aoe_attributeconfigurator');
    }

    /**
     * @return Aoe_AttributeConfigurator_Helper_Config
     */
    protected function _getConfigHelper()
    {
        return Mage::helper('aoe_attributeconfigurator/config');
    }

    /**
     * Create Diff Data between incoming Attribute and existing Attribute Data
     *
     * @param Mage_Catalog_Model_Entity_Attribute $attribute Attribute to update
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config
     * @return array
     */
    protected function _getAttributeDiff($attribute, $attributeConfig)
    {
        $incomingData = $attributeConfig->getSettingsAsArray();
        $existingData = $attribute->getData();

        $diff = [];
        foreach ($incomingData as $key => $value) {
            /**
             * Only add this to the diff if key exists in Attribute Config (limiting incoming to valid settings)
             * and Incoming is different from existing setting
             * and Incoming Value is not empty (empty values are being ignored then
             */
            if (array_key_exists(trim($key), $existingData) && $existingData[trim($key)] != $value && !empty($value)) {

                $diff[] = $key;
            }
        }

        return $diff;
    }

    /**
     * Update changeable properties of a managed attribute
     *
     * @param Mage_Catalog_Model_Entity_Attribute $attribute Attribute to update
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config
     * @param array $attributeDiff Attributes to be updated
     * @return void
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Exception
     */
    protected function _changeableAttributeUpdate($attribute, $attributeConfig, $attributeDiff)
    {
        if (!empty($attributeDiff)) {
            $attributeSetting = $attributeConfig->getSettingsAsArray();

            foreach ($attributeDiff as $property) {
                if (in_array($property, $this->_changeableProps)) {
                    if ($this->_validateProperty($property, $attributeSetting[$property])) {
                        $attribute->setData($property, $attributeSetting[$property]);
                    } else {
                        $this->_getHelper()->log(sprintf('Property \'%s\' of Attribute \'%s\' skipped: \'%s\' is no valid value.',
                            $property, $attributeConfig->getCode(), $attributeSetting[$property]));
                    }
                }
            }

            try {
                $attribute->save();
            } catch (Exception $e) {
                throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Exception(
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
        }
    }

    /**
     * Validate Property of an Attribute
     *
     * @param string $property property of attribute
     * @param mixed  $value    value of the property
     * @return boolean
     */
    protected function _validateProperty($property, $value)
    {
        if(in_array($property, $this->_booleanValidation)) {
            if(in_array($value, array('0', '1'))) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Returns Mapping for matching Frontend Input to given Backend Type
     * Corrects Value if wrong Frontend Input is supplied
     *
     * @param string $backendType          Attribute Backend Type
     * @param string $currentFrontendInput Current Attribute Frontend Type (may be wrong, gets fixed here)
     * @return bool|string
     */
    protected function _getFrontendForBackend($backendType, $currentFrontendInput)
    {
        $frontendMapping = $this->_frontendMappping[$backendType];
        $frontendInput = false;
        if (!in_array($currentFrontendInput, $frontendMapping)) {
            // Override Frontend Input if invalid one was supplied in XML with the first one from the _frontendMappping
            $frontendInput = $this->_frontendMappping[$backendType][0];
            $this->_getHelper()->log(
                sprintf(
                    "Overriding faulty Frontend Input Type '%s' for Backend Type '%a' with Frontend Input of '%s'.",
                    $currentFrontendInput,
                    $backendType,
                    $frontendInput
                )
            );
        };
        return $frontendInput;
    }

    /**
     * Migrate Entries of non-select/multiselect Entities to new Entity Table
     *
     * @param string                      $targetType    Target Type as String
     * @param Zend_Db_Statement_Interface $sourceQuery   Source Entity Table Query with old Entity Data to Transfer
     * @param string                      $sourceType    Source Type as String
     * @param string                      $targetTable   Target Entity Table
     * @param Varien_Db_Adapter_Interface $_dbConnection Database Connection
     * @param string                      $sourceTable   Source Entity Table
     */
    protected function _mergeNonSelect($targetType, $sourceQuery, $sourceType,
        $targetTable, $_dbConnection, $sourceTable
    ) {
        if (in_array($sour))

        while ($row = $sourceQuery->fetch()) {
            $currentValue = $row['value'];
            if (!is_null($currentValue)) {
                // Cast Value Type to new Type (e.g. decimal to text)
                $targetValue = $this->_typeCast(
                    $currentValue, $sourceType, $targetType
                );

                // Insert Value to target Entity
                $sql = 'INSERT' .
                    ' INTO ' . $targetTable
                    . ' (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (?,?,?,?,?)';
                try {
                    $_dbConnection->query(
                        $sql,
                        [
                            $row['entity_type_id'],
                            $row['attribute_id'],
                            $row['store_id'],
                            $row['entity_id'],
                            $targetValue
                        ]
                    );
                } catch (Exception $e) {
                    $this->_getHelper()->log(
                        sprintf(
                            'Exception occured while migrating Data. See exception log.'
                        ), $e
                    );
                }
            }

            // Delete Value from source Entity
            $sql = 'DELETE' .
                ' FROM ' . $sourceTable . ' WHERE value_id = ?';
            $_dbConnection->query($sql, $row['value_id']);
        }
    }
}

