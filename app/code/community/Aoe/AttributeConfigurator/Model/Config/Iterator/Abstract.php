<?php

/**
 * Class Aoe_AttributeConfigurator_Model_Config_Iterator_Abstract
 *
 * Abstract iterator for SimpleXMl
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
abstract class Aoe_AttributeConfigurator_Model_Config_Iterator_Abstract implements Iterator
{
    /**
     * The wrapped SimpleXMLIterator
     *
     * @var SimpleXMLIterator $_xmlIterator
     */
    protected $_xmlIterator;

    /**
     * The current element of the iterator
     *
     * @var Aoe_AttributeConfigurator_Model_Config_Attribute
     */
    protected $_current;

    /**
     * Get the short code of a node class
     *
     * @return string
     */
    abstract protected function _getNodeClass();

    /**
     * @param string|SimpleXMLElement $xmlItem SimpleXMLElement containing a list of config attributes
     */
    public function __construct($xmlItem)
    {
        if (is_string($xmlItem)) {
            $this->_xmlIterator = new SimpleXMLIterator($xmlItem);
        } else if ($xmlItem instanceof SimpleXMLElement) {
            $this->_xmlIterator = new SimpleXMLIterator($xmlItem->asXML());
        } else {
            // just create an empty iterator if we cannot handle this
            $this->_xmlIterator = new SimpleXMLIterator('<config></config>');
        }
    }

    /**
     * Lazy wrapper for the current element
     *
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return Aoe_AttributeConfigurator_Model_Config_Attribute
     */
    public function current()
    {
        if ($this->_current) {
            return $this->_current;
        }

        $current = $this->_createModelFromNode(
            $this->_xmlIterator->current()
        );
        $this->_current = $current;

        return $current;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->_current = null;
        $this->_xmlIterator->next();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return string|string string on success, or null on failure.
     */
    public function key()
    {
        if (!$this->_current) {
            return null;
        }

        return $this->_current->getName();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->_xmlIterator->valid();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->_current = null;
        $this->_xmlIterator->rewind();
    }

    /**
     * Create an attribute config from an xml node
     *
     * @param SimpleXMLElement $xmlNode The not to wrap as attribute config
     * @return Aoe_AttributeConfigurator_Model_Config_Attribute
     */
    protected function _createModelFromNode($xmlNode)
    {
        return Mage::getModel($this->_getNodeClass(), $xmlNode);
    }
}
