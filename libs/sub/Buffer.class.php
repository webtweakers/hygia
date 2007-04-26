<?php

/**
 * Buffer Singleton class, provides the ability to save variables.
 * Used in the Plugin system of Hygia to be able to construct flows.
 * @author Bas van Gaalen
 * @since 1.0
 */
class Buffer {

    private static $oInstance;
    private $aVars;

    /**
     * Constructor: initialize the Buffer object
     * @since 1.0
     */
    private function __construct() {
        $this->aVars = array();
    }

    /**
     * Gets a unique instance of the class
     * @return Buffer instance
     * @since 1.0
     */
    public static function getInstance() {
        if (!isset(self::$oInstance)) {
            self::$oInstance = new Buffer();
        }
        return self::$oInstance;
    }

    /**
     * Set a variable. This can be done in three ways:
     * - supply one single string value, which will be saved in an indexed array
     * - supply one single associative array of name/value pairs
     * - supply a name and a value, both strings
     * @param $vName mixed Is either a value, an associative array of variables or
     *        the name-component of a variable
     * @param $sValue string The value (optional in case of set)
     * @since 1.0
     */
    public function set($vName, $sValue = null) {

        // if only $vName is provided as a string, then save as indexed value
        if (is_string($vName) && is_null($sValue)) {
            $this->aVars[] = $vName;
        }

        // if only $vName is provided as array, then save associative array of variables
        else if (is_array($vName) && is_null($sValue)) {
            foreach ($vName as $sKey => $sValue) {
                $this->set($sKey, (string) $sValue);
            }
        }

        // a straight-forward name/value pair
        else {
            $this->aVars[$vName] = (string) $sValue;
        }
    }

    /**
     * Get the value of a variable.
     * @param $sName string Name of the variable
     * @return mixed Value of the variable
     * @since 1.0
     */
    public function get($sName) {
        return isset($this->aVars[$sName]) ? $this->aVars[$sName] : null;
    }

    /**
     * Get all variables.
     * @return array List of all variables
     * @since 1.0
     */
    public function getAll() {
        return $this->aVars;
    }

}

?>
