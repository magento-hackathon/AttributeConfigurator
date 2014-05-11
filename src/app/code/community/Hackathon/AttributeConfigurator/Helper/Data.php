<?php
/**
 * Class Hackathon_AttributeConfigurator_Helper_Data
 */
class Hackathon_AttributeConfigurator_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_FILENAME = 'catalog/attribute_configurator/product_xml_location';
    const XML_PATH_CURRENT_HASH = 'attributeconfigurator/hashes/current';

    /**
     * Build Import Filename from Store Config
     *
     * @return string
     */
    public function getImportFilename()
    {
        return Mage::getBaseDir() . DS . trim(Mage::getStoreConfig(self::XML_PATH_FILENAME), '/\ ');
    }

    /**
     * Method creates md5 hash of a given file based on its content
     * Intent: We need to figure out when to re-import a file so we have to know when its content changes
     *
     * @param string $file path and filename of Attribute Configuration XML
     *
     * @return bool|string
     */
    public function createFileHash($file)
    {
        if (file_exists($file)) {
            return md5_file($file);
        }
        return false;
    }

    /**
     * Check if the XML file is newer than the last imported one.
     *
     * return bool
     */
    public function isAttributeXmlNewer()
    {
        $filename = $this->getImportFilename();
        $currentFileHash = Mage::getStoreConfigFlag(self::XML_PATH_CURRENT_HASH);
        $latestFileHash  = $this->createFileHash($filename);
        if ($latestFileHash !== $currentFileHash) {
            return true;
        }
        return false;
    }

    /**
     * Check if Attribute is maintained by extension, return false if not (leave system and third party attributes as they are)
     *
     * @param string $attributeCode
     * @param string|integer $entityType
     * @return bool
     */
    public function checkAttributeMaintained($attributeCode, $entityType){
        if (is_numeric($entityType)) {
            $entityTypeId = $entityType;
        } elseif (is_string($entityType)) {
            $entityType = Mage::getModel('eav/entity_type')->loadByCode($entityType);
        }
        if ($entityType instanceof Mage_Eav_Model_Entity_Type) {
            $entityTypeId = $entityType->getId();
        }
        $_dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = 'SELECT is_maintained_by_configurator FROM eav_attribute WHERE attribute_code=? AND entity_type_id=?';
        try{
            $sourceQuery = $_dbConnection->query(
                $sql,
                array(
                    $attributeCode,
                    $entityTypeId
                )
            );
            $row = $sourceQuery->fetch();
            Mage::log($row['is_maintained_by_configurator']);
            if ($row['is_maintained_by_configurator'] === 1){
                return true;
            }
        }catch(Exception $e){
            Mage::exception(__CLASS__.' - '.__LINE__.':'.$e->getMessage());
        }
        return false;
    }
}
