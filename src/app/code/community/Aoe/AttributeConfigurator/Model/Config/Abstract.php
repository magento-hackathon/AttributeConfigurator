<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Config_Abstract
 *
 * Abstract class for wrapped SimpleXML Elements
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   Firegento <contact@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
abstract class Aoe_AttributeConfigurator_Model_Config_Abstract
{
    /**
     * The wrapped xml element
     *
     * @var SimpleXmlElement $_xmlElement
     */
    protected $_xmlElement;

    /**
     * Array of validation messages
     *
     * @var string[]
     */
    protected $_validationMessages = [];

    /**
     * Array of info messages
     * Info messages may occour beside validation due to auto-corrections
     *
     * @var string[]
     */
    protected $_infoMessages = [];

    /**
     * Validate the wrapped xml item.
     * Add validation messages for each error that is found
     *
     * @return void
     */
    abstract protected function _validateXml();

    /**
     * @param string|SimpleXMLElement $xml Wrapped xml
     */
    public function __construct($xml)
    {
        if (is_string($xml)) {
            $this->_xmlElement = new SimpleXMLElement($xml);
        } else if ($xml instanceof SimpleXMLElement) {
            $this->_xmlElement = $xml;
        } else {
            $this->_xmlElement = new SimpleXMLElement('<attribute></attribute>');
        }
    }

    /**
     * Get the info messages
     *
     * @return string[]
     */
    public function getInfoMessages()
    {
        return $this->_infoMessages;
    }

    /**
     * Add an info message
     *
     * @param string $infoMessage Info message
     * @return $this
     */
    public function _addInfoMessage($infoMessage)
    {
        $this->_infoMessages[] = $infoMessage;

        return $this;
    }

    /**
     * Get the validation messages
     *
     * @return string[]
     */
    public function getValidationMessages()
    {
        return $this->_validationMessages;
    }

    /**
     * Add a validation message
     *
     * @param string $string Validation message
     * @return $this
     */
    protected function _addValidationMessage($string)
    {
        $this->_validationMessages[] = $string;
        return $this;
    }

    /**
     * Merge in validation messages
     *
     * @param string[] $messages Validation messages
     * @return $this
     */
    protected function _mergeValidation($messages)
    {
        $this->_validationMessages = array_merge(
            $this->_validationMessages,
            $messages
        );

        return $this;
    }

    /**
     * Trigger xml element validation.
     * Returns false if the validation collected error messages.
     *
     * @return bool
     */
    public function validate()
    {
        $this->_validationMessages = [];
        $this->_validateXml();

        return empty($this->_validationMessages);
    }
}
