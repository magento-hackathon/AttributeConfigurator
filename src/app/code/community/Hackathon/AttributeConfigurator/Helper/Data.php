<?php

/**
 * Class Hackathon_AttributeConfigurator_Helper_Data
 */
class Hackathon_AttributeConfigurator_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Method creates md5 hash of a given file based on its content
     * Intent: We need to figure out when to re-import a file so we have to know when its content changes
     *
     * @param $file path and filename of Attribute Configuration XML
     *
     * @return bool|string
     */

    public function createFileHash($file)
    {
        if (file_exists('./'.$file)) {
            return md5_file('./'.$file);
        }

        return false;
    }
}
