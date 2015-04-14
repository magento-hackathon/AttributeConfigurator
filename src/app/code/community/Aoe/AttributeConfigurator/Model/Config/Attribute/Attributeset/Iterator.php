<?php

/**
 * Aoe_AttributeConfigurator_Model_Config_Attribute_Attributeset_Iterator
 *
 * Wrapper class for SimpleXML attributesets inside attributes list config objects
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Config_Attribute_Attributeset_Iterator extends Aoe_AttributeConfigurator_Model_Config_Iterator_Abstract
{
    /**
     * Get the short code of a node class
     *
     * @return string
     */
    protected function _getNodeClass()
    {
        return 'aoe_attributeconfigurator/config_attribute_attributeset';
    }
}
