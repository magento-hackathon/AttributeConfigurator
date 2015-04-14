<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Config_Attributeset
 *
 * Wrapper class for SimpleXML attribute setconfig objects
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Model_Config_Attributeset extends Aoe_AttributeConfigurator_Model_Config_Abstract
{

    /**
     * @return string
     */
    public function getName()
    {
        return (string) $this->_xmlElement['name'];
    }

    /**
     * @return string
     */
    public function getSkeleton()
    {
        return (string) $this->_xmlElement['skeleton'];
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
            $this->_addValidationMessage('name missing');
        }

        if (!$this->getSkeleton()) {
            $this->_addValidationMessage('skeleton missing');
        }
    }
}
