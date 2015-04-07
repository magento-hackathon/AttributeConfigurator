<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Sync_Import_Attribute
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Sync_Import_Attribute extends Mage_Eav_Model_Entity_Attribute
{
    /** @var Aoe_AttributeConfigurator_Helper_Data $_helper */
    protected $_helper;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('aoe_attributeconfigurator/data');

        parent::__construct();
    }

    /**
     * Run the attribute update/import
     *
     * @param string|SimpleXMLElement $xml XML Data to process
     * @return void
     */
    public function run($xml)
    {
        /** @var Aoe_AttributeConfigurator_Model_Config_Attribute_Iterator $iterator */
        $iterator = Mage::getModel(
            'aoe_attributeconfigurator/config_attribute_iterator',
            $xml
        );
        foreach ($iterator as $_attributeConfig) {
            /** @var Aoe_AttributeConfigurator_Model_Config_Attribute $_attributeConfig */
            try {
                $this->_processAttribute($_attributeConfig);

            } catch (Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Exception $attributeException) {
                $this->_helper->log(
                    $attributeException->getMessage(),
                    $attributeException,
                    Zend_Log::WARN
                );
            } catch (Exception $e) {
                $this->_helper->log(
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
     */
    protected function _processAttribute($attributeConfig)
    {
        if (!$attributeConfig->validate()) {
            throw new Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Validation_Exception(
                'Validation errors on attribute: \n'
                . implode('\n', $attributeConfig->getValidationMessages())
            );
        }

        $attribute = $this->_loadAttributeByCode($attributeConfig->getCode());
        if ($attribute->getId()) {
            $this->_updateOrMigrateAttribute($attribute, $attributeConfig);
        } else {
            $this->_createAttribute($attributeConfig);
        }
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
        if (!$this->_helper->checkAttributeMaintained($attribute)) {
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
     * @param Aoe_AttributeConfigurator_Model_Config_Attribute $attributeConfig Attribute config
     * @return void
     * @throws Aoe_AttributeConfigurator_Model_Sync_Import_Attribute_Exception
     */
    protected function _createAttribute($attributeConfig)
    {
        /** @var Mage_Catalog_Model_Entity_Attribute $result */
        $attribute = Mage::getModel('catalog/entity_attribute');
        $attribute->setData($attributeConfig->getSettingsAsArray());

        /** @var Mage_Core_Model_Mysql4_Resource $resource */
        $resource = $attribute->getResource();
        $resource->beginTransaction();
        try {
            $attribute->save();
            $resource->commit();
        } catch (Exception $e) {
            $resource->rollBack();
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
     * @return void
     */
    protected function _updateAttributeSetAndGroup($attributeConfig, $attributeSet)
    {
        $attributeSetId = Mage::getModel('eav/entity_attribute_set')
            ->load($attributeSet->getName(), 'attribute_set_name')
            ->getAttributeSetId();

        /** @var Mage_Eav_Model_Entity_Setup $setup */
        $setup = Mage::getModel('eav/entity_setup');

        // TODO: we need real update (also remove if the attribute config has changed)
        foreach ($attributeSet->getAttributeGroups() as $_group) {
            $setup->addAttributeGroup(
                $attributeConfig->getEntityTypeId(),
                $attributeSetId,
                $_group
            );

            $setup->addAttributeToSet(
                $attributeConfig->getEntityTypeId(),
                $attributeSetId,
                $_group,
                $attributeConfig->getSortOrder()
            );
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
        $this->_helper->checkAttributeMaintained($attribute);
        if ($data === null || !$attribute || !$this->_helper->checkAttributeMaintained($attribute)) {
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
        $srcSql = 'SELECT * FROM '.$sourceTable.' WHERE attribute_id = ? AND entity_type_id = ?';
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
                $sql = 'INSERT INTO '.$targetTable.' (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (?,?,?,?,?)';
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
            $sql = 'DELETE FROM '.$sourceTable.' WHERE value_id = ?';
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
     * Insert new Attribute
     *
     * @TODO: nhp_havocologe, this needs to set is_maintained_by_configurator to the attribute
     *
     * @param  array $data Attribute Configuration Data
     * @return void
     * @throws Aoe_AttributeConfigurator_Model_Exception
     */
    public function insertAttribute($data)
    {
        throw new Aoe_AttributeConfigurator_Model_Exception('method not implemented');
    }

    /**
     * Load a catalog entity attribute by its code
     *
     * @param string $attributeCode Attribute code
     * @return Mage_Catalog_Model_Entity_Attribute
     */
    protected function _loadAttributeByCode($attributeCode)
    {
        /** @var Mage_Catalog_Model_Entity_Attribute $result */
        $result = Mage::getModel('catalog/entity_attribute');

        $result->loadByCode(
            'catalog_product',
            $attributeCode
        );

        return $result;
    }
}
