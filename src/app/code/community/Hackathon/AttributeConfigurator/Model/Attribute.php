<?php

/**
 * Class Hackathon_AttributeConfigurator_Model_Attribute
 */
class Hackathon_AttributeConfigurator_Model_Attribute extends Mage_Eav_Model_Entity_Attribute
{
    public function __construct(){
        parent::_construct();
    }

    /**
     * Converts existing Attribute to different type
     *
     * @param string $attributeCode
     * @param int $entityType
     * @param array $data
     */
    public function convertAttribute($attributeCode, $entityType, $data = null)
    {
        $_dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        /* @var $attribute Mage_Eav_Model_Entity_Attribute */
        $attribute = $this->loadByCode($entityType, $attributeCode);
        // Stop if $data not set or Attribute not available
        if ($data === null || !$attribute) {
            return;
        }
        // Migrate existing Attribute Values if new backend_type different from old one
        if ($attribute->getBackendType() !== $data['backend_type']){
            $this->migrateData($attribute, $data);
        }
        // Actual Conversion of Attribute
        $sql = 'UPDATE eav_attribute SET attribute_model=?, backend_model=?, backend_type=?, backend_table=?, frontend_model=?, frontend_input=?, frontend_label=?, frontend_class=?, source_model=?, is_required=?, is_user_defined=?, default_value=?, is_unique=?, note=? WHERE attribute_id=?';
        try{
            $_dbConnection->query(
                $sql,
                array(
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
                    $attribute->getId(),
                )
            );
        }catch(Exception $e){
            Mage::log(__CLASS__.' - '.__LINE__.':'.$e->getMessage());
        }
    }

    /**
     * Migrate Entries from source to target tables (if possible)
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param array $data
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
        $sourceTable = implode (array($entityTypeCode, 'entity', $sourceType), '_');
        $targetTable = implode (array($entityTypeCode, 'entity', $targetType), '_');
        // Select all existing entries for given Attribute
        $srcSql = 'SELECT * FROM '.$sourceTable.' WHERE attribute_id = ? AND entity_type_id = ?';
        $sourceQuery = $_dbConnection->query(
            $srcSql,
            array($attribute->getId(), $attribute->getEntity()->getData('entity_type_id'))
        );
        while($row = $sourceQuery->fetch())
        {
            $currentValue = $row['value'];
            if (!is_null($currentValue)) {
                // Cast Value Type to new Type (e.g. decimal to text)
                Mage::log('row:'.print_r($row, true));
                $targetValue = $this->typeCast($currentValue, $sourceType, $targetType);
                Mage::log('current:'.$currentValue.' - target:'.$targetValue);
                // Insert Value to target Entity
                $sql = 'INSERT INTO '.$targetTable.' (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (?,?,?,?,?)';
                try{
                    $_dbConnection->query(
                        $sql,
                        array($row['entity_type_id'], $row['attribute_id'], $row['store_id'], $row['entity_id'], $targetValue)
                    );
                }catch(Exception $e){
                    Mage::log(__CLASS__.' - '.__LINE__.':'.$e->getMessage());
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
     * @param $value
     * @param $sourceType
     * @param $targetType
     * @return null
     */
    private function typeCast($value, $sourceType, $targetType){
        if ($sourceType === $targetType) {
            return $value;
        }
        switch ($targetType) {
            case 'decimal':
                return min((int)$value, 999999999999);
            case 'gallery':
                return $this->truncateString((string)$value, 254);
            case 'group_price':
                return min((int)$value, 99999);
            case 'int':
                return min((int)$value, 99999999999);
            case 'media_gallery':
                return $this->truncateString((string)$value, 254);
            case 'media_gallery_value':
                return min((int)$value, 99999);
            case 'text':
                return (string)$value;
            case 'tier_price':
                return min((int)$value, 99999);
            case 'url_key':
                return $this->truncateString((string)$value, 254);
            case 'varchar':
                return $this->truncateString((string)$value, 254);
        }
        return null;
    }

    /**
     * Truncate string if too long
     *
     * @param string $str
     * @param integer $maxlen
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
     * @param $data array
     * @throws Mage_Core_Exception
     */
    public function insertAttribute($data)
    {
        $attribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode($data['code']);

        if ($attribute->getId()) {
            Mage::throwException('Attribute already exists.');
        } elseif (trim($data['settings']['frontend_label']) == '' || trim($data['code']) == '') {
            Mage::throwException("Can't import the attribute with an empty label or code.");
        } // code for set/group id checks here

        $newData = array();
        foreach ($data as $node => $value) {
            $newData[$node] = $value;
        }
        $attribute->addData($newData);
        $setup = Mage::getModel('eav/entity_setup');
        $attribute->save();
        foreach ($data['attribute_set'] as $key => $set) {
            $attributeSetId = Mage::getModel('eav/entity_attribute_set')
                            ->load($set, 'attribute_set_name')
                            ->getAttributeSetId();
            $setup->addAttributeGroup($data['entity_type_id'], $attributeSetId, $data['group']);
            $setup->addAttributeToSet($data['entity_type_id'], $attributeSetId, $data['group'], $data['attribute_code'], $data['sort_order']);
        }
    }
}
