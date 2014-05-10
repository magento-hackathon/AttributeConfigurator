<?php

/**
 * Class Hackathon_AttributeConfigurator_Model_Attribute
 */
class Hackathon_AttributeConfigurator_Model_Attribute extends Mage_Eav_Model_Entity_Attribute
{
    /**
     * Converts existing Attribute to different type
     *
     * @param string $attributeCode
     * @param int $entityType
     * @param array $data
     */
    public function convertAttribute($attributeCode, $entityType, $data)
    {
        /* @var $attribute Mage_Eav_Model_Entity_Attribute */
        $attribute = $this->loadByCode($entityType, $attributeCode);
        $this->migrateData($attribute);
        // TODO: Actual Conversion of Attribute
    }

    /**
     * Migrate Entries from source to target tables (if possible)
     *
     * @param $attribute
     */
    private function migrateData($attribute)
    {
        /* @var $attribute Mage_Eav_Model_Entity_Attribute */
        Mage::log('migrateData');
        Mage::log($attribute->getEntityType());
        Mage::log($attribute->getBackendType());
    }
}
