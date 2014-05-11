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
