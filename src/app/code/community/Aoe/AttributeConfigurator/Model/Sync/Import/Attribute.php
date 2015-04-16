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
class Aoe_AttributeConfigurator_Model_Sync_Import_Attribute extends Mage_Eav_Model_Entity_Attribute
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
            } catch (Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Skipped_Exception $akippedException) {
                $this->_getHelper()->log(
                    $akippedException->getMessage(),
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
                sprintf('Attribute \'%s\' skipped (see System Config).', $attributeConfig->getCode())
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
     * @param Mage_Catalog_Model_Entity_Attribute              $attribute       Attribute to update
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config
     * @return void
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Exception
     */
    protected function _updateOrMigrateAttribute($attribute, $attributeConfig)
    {
        if (!$this->_getHelper()->checkAttributeMaintained($attribute)) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Exception(
                sprintf('Attribute \'%s\' is not maintained.', $attributeConfig->getCode())
            );
        }

        $this->_updateAttribute($attribute, $attributeConfig);
        // TODO: migration is dangerous and may be implemented later
        // $this->migrateAttribute($attribute, $attributeConfig);
    }

    /**
     * Update the data of an existing attribute
     *
     * @param Mage_Catalog_Model_Entity_Attribute              $attribute       Attribute to update
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config
     * @return void
     */
    protected function _updateAttribute($attribute, $attributeConfig)
    {
        // TODO: implement attribute updates later
    }

    /**
     * Create a new (managed) attribute
     *
     * @param Mage_Catalog_Model_Entity_Attribute              $attribute       Attribute to update
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config
     * @return void
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Exception
     */
    protected function _createAttribute($attribute, $attributeConfig)
    {
        $attribute->setData(
            $attributeConfig->getSettingsAsArray()
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
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute              $attributeConfig Attribute config
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute_Attributeset $attributeSet    Attributeset to use
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
            $attributeSetName = ucwords(strtolower($attributeSetName));
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
        $attributeSetId = (int) $attributeSetModel->getData('attribute_set_id');

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
        $attributeGroups = (array) $attributeSet->getAttributeGroups();

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
                    $this->_getHelper()->log(sprintf('Added Attribute \'%s\' to Attribute Set Id #%s.', $attributeConfig->getCode(), $attributeSetId));
                } catch (Exception $e) {
                    $this->_getHelper()->log(sprintf('Exception while adding Attribute \'%s\' to Attibute Set Id #%s.', $attributeConfig->getCode(), $attributeSetId), $e);
                }
            }
        }
    }

    /**
     * Converts existing Attribute to different type
     *
     * @param  string $attributeCode Attribute Code
     * @param  int    $entityType    Entity Type which Attribute is attached to
     * @param  array  $data          New Attribute Data
     * @return void
     */
    public function convertAttribute($attributeCode, $entityType, $data = null)
    {
        $_dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        /* @var $attribute Mage_Eav_Model_Entity_Attribute */
        $attribute = $this->loadByCode($entityType, $attributeCode);
        // Stop if $data not set or Attribute not available or Attribute not maintained by module
        $this->_getHelper()->checkAttributeMaintained($attribute);
        if ($data === null || !$attribute || !$this->_getHelper()->checkAttributeMaintained($attribute)) {
            return;
        }
        // Migrate existing Attribute Values if new backend_type different from old one
        if ($attribute->getBackendType() !== $data['backend_type']) {
            $this->migrateData($attribute, $data);
        }
        /*
         * @TODO: jadhub, muss hier noch eventuell vorhandene Select/Multiselect-Values löschen falls der neue BackendType ein anderer ist
         */
        // Actual Conversion of Attribute
        $sql = 'UPDATE eav_attribute SET attribute_model=?, backend_model=?, backend_type=?, backend_table=?, frontend_model=?, frontend_input=?, frontend_label=?, frontend_class=?, source_model=?, is_required=?, is_user_defined=?, default_value=?, is_unique=?, note=? WHERE attribute_id=?';
        try{
            $_dbConnection->query(
                $sql,
                [
                    $data['attribute_model'],
                    $data['backend_model'],
                    $data['backend_type'],
                    $data['backend_table'],
                    $data['frontend_model'],
                    $data['frontend_input'],
                    $data['frontend_label'],
                    $data['frontend_class'],
                    $data['source_model'],
                    $data['is_required'],
                    $data['is_user_defined'],
                    $data['default_value'],
                    $data['is_unique'],
                    $data['note'],
                    $attribute->getId()
                ]
            );
        }catch(Exception $e){
            Mage::exception(__CLASS__.' - '.__LINE__.':'.$e->getMessage());
        }
        // If entity of catalog_product, also update catalog_eav_attribute
        if ($attribute->getEntity()->getData('entity_type_code') === Mage_Catalog_Model_Product::ENTITY) {
            $sql = 'UPDATE catalog_eav_attribute SET frontend_input_renderer=?, is_global, is_visible=?, is_searchable=?, is_filterable=?, is_comparable=?, is_visible_on_front=?, is_html_allowed_on_front=?, is_used_for_price_rules=?, is_filterable_in_search=?, used_in_product_listing=?, used_for_sort_by=?, is_configurable=?, apply_to=?, is_visible_in_advanced_search=?, position=?, is_wysiwyg_enabled=?, is_used_for_promo_rules=?';
            try{
                $_dbConnection->query(
                    $sql,
                    [
                        $data['frontend_input_renderer'],
                        $data['is_global'],
                        $data['is_visible'],
                        $data['is_searchable'],
                        $data['is_filterable'],
                        $data['is_comparable'],
                        $data['is_visible_on_front'],
                        $data['is_html_allowed_on_front'],
                        $data['is_used_for_price_rules'],
                        $data['is_filterable_in_search'],
                        $data['used_in_product_listing'],
                        $data['used_for_sort_by'],
                        $data['is_configurable'],
                        $data['apply_to'],
                        $data['is_visible_in_advanced_search'],
                        $data['position'],
                        $data['is_wysiwyg_enabled'],
                        $data['is_used_for_promo_rules'],
                    ]
                );
            }catch(Exception $e){
                Mage::exception(__CLASS__.' - '.__LINE__.':'.$e->getMessage());
            }
        }
    }

    /**
     * Migrate Entries from source to target tables (if possible)
     *
     * @param  Mage_Eav_Model_Entity_Attribute $attribute Attribute Model
     * @param  array                           $data      Attribute Data
     * @return void
     */
    private function migrateData($attribute, $data = null)
    {
        if ($data === null) {
            return;
        }
        $_dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        // e.g. Entity is 'catalog_product'
        $entityTypeCode = $attribute->getEntity()->getData('entity_type_code');
        // Set Backend Types for later reference
        $sourceType = $attribute->getBackendType();
        $targetType = $data['backend_type'];
        // Create complete Entity Table names, e.g. 'catalog_product_entity_text'
        $sourceTable = implode([$entityTypeCode, 'entity', $sourceType], '_');
        $targetTable = implode([$entityTypeCode, 'entity', $targetType], '_');
        // Select all existing entries for given Attribute
        $srcSql = 'SELECT'.
            ' * FROM '.$sourceTable.' WHERE attribute_id = ? AND entity_type_id = ?';
        $sourceQuery = $_dbConnection->query(
            $srcSql,
            [
                $attribute->getId(),
                $attribute->getEntity()->getData('entity_type_id')
            ]
        );
        while ($row = $sourceQuery->fetch()) {
            $currentValue = $row['value'];
            if (!is_null($currentValue)) {
                // Cast Value Type to new Type (e.g. decimal to text)
                $targetValue = $this->typeCast($currentValue, $sourceType, $targetType);
                // Insert Value to target Entity
                $sql = 'INSERT' .
                    ' INTO '.$targetTable.' (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (?,?,?,?,?)';
                try{
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
                }catch(Exception $e){
                    Mage::exception(__CLASS__.' - '.__LINE__.':'.$e->getMessage());
                }
            }
            // Delete Value from source Entity
            $sql = 'DELETE' .
                ' FROM '.$sourceTable.' WHERE value_id = ?';
            $_dbConnection->query($sql, $row['value_id']);
        }
    }

    /**
     * Force Casting of Backend Types
     *
     * @param mixed  $value      Current Value
     * @param string $sourceType Current Source Type
     * @param string $targetType New Source Type
     * @return null
     */
    private function typeCast($value, $sourceType, $targetType)
    {
        if ($sourceType === $targetType) {
            return $value;
        }
        switch ($targetType) {
            case 'decimal':
                return min((int) $value, 2147483648);
            case 'gallery':
                return $this->truncateString((string) $value, 254);
            case 'group_price':
                return min((int) $value, 65535);
            case 'int':
                return min((int) $value, 2147483648);
            case 'media_gallery':
                return $this->truncateString((string) $value, 254);
            case 'media_gallery_value':
                return min((int) $value, 65535);
            case 'text':
                return (string) $value;
            case 'tier_price':
                return min((int) $value, 65535);
            case 'url_key':
                return $this->truncateString((string) $value, 254);
            case 'varchar':
                return $this->truncateString((string) $value, 254);
        }
        return null;
    }

    /**
     * Truncate string if too long
     *
     * @param  string  $str    Input String
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

}
