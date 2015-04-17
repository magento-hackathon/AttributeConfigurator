<?php

// @codingStandardsIgnoreStart
require_once 'abstract.php';
// @codingStandardsIgnoreEnd

/**
 * Class Aoe_AttributeConfigurator_Shell_Import
 *
 * @category Shell
 * @package  Aoe_AttributeConfigurator
 * @author   FireGento Team <team@firegento.com>
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  Open Software License v. 3.0 (OSL-3.0)
 * @link     https://github.com/AOEpeople/AttributeConfigurator
 * @see      https://github.com/magento-hackathon/AttributeConfigurator
 */
class Aoe_AttributeConfigurator_Shell_Command extends Mage_Shell_Abstract
{
    /** @var Aoe_AttributeConfigurator_Model_Shell */
    protected $_shellModel;

    /**
     * Validate the input parameters
     *
     * @return void
     */
    protected function _validate()
    {
        parent::_validate();
        /** @var Aoe_AttributeConfigurator_Model_Shell _shellModel */
        $this->_shellModel = Mage::getModel('aoe_attributeconfigurator/shell');
        $this->_shellModel->setIni(
            [
                'memory_limit' => '20000M'
            ]
        );
        $this->_shellModel->validate($this);
    }

    /**
     * Stub required by Abstract
     *
     * @return void
     */
    public function run()
    {
        // empty body, execution implemented via _validate
    }
}

/** @var Aoe_AttributeConfigurator_Shell_Command $shell */
$shell = new Aoe_AttributeConfigurator_Shell_Command();
