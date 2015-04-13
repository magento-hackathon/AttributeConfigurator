<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Config_Attribute_Attributeset
 *
 * Wrapper class for SimpleXML attributeset (inside attribute config objects)
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Config_Attribute_Attributeset extends Aoe_AttributeConfigurator_Model_Config_Abstract
{
    /**
     * @return string
     */
    public function getName()
    {
        return (string) $this->_xmlElement['name'];
    }

    /**
     * Get the array of attribute groups
     *
     * @return string[]
     */
    public function getAttributeGroups()
    {
        /** @var SimpleXMLElement $groups Attribute groups */
        $groups = $this->_xmlElement->{'attributegroups'};
        $result = [];
        foreach ($groups->children() as $_group) {
            /** @var SimpleXmlElement $_group */
            $result[] = (string) $_group['name'];
        }

        return $result;
    }

    /**
     * Validate the wrapped xml item.
     * Add validation messages for each error that is found
     *
     * @return void
     */
    protected function _validateXml()
    {
        if (!$this->getName()) {
            $this->_addValidationMessage('missing name on attributeset');
        }

        /** @var SimpleXMlElement $groupsNode */
        $groupsNode = $this->_xmlElement->{'attributegroups'};
        if (!$groupsNode || !$groupsNode->count()) {
            $this->_addValidationMessage('missing attributegroups config node');
        } else {
            $groups = $this->getAttributeGroups();
            if (empty($groups)) {
                $this->_addValidationMessage('no groups defined on attributegroups');
            }
        }
    }
}
