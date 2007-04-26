<?php

ns::import('hygia.libs.io.var.Variable');

/**
 * The RequestVars class is used to store request variables of both GET and POST types.
 * Each variable is stored in a object, Variable, that holds the value and the type.
 * The name of the variable is the key in a local associative array (hash).
 * @author Bas van Gaalen
 * @since 1.0
 */
class RequestVars {

    private $aVariables;

    /**
     * Constructor, sets up variable hash.
     * @since 1.0
     */
    public function __construct() {
        $this->aVariables = array();
    }

    /**
     * Set a request name/value pair (will be used in GET or POST).
     * Can either set "a=b" or seperated name/value variables.
     * @param $sName string Name of variable or complete variable if value is null.
     * @param $sValue mixed Value of variable, when string, or null.
     * @param $sType string Type, either GET or POST, default to GET (enum type would be nice...)
     *          The type is optional. If this is a new variable, the default GET type will be used.
     *          If this is an existing variable and the type is not explicitely provided,
     *          the previous type will be preserved.
     * @return bool True if set ok, false on failure ("a=b" assignment not found).
     * @since 1.0
     */
    public function set($sName, $sValue = null, $sType = null) {
        if (is_null($sValue)) {
            if (stripos($sName, '=') === false) {
                return false;
            }
            list($sName, $sValue) = explode('=', $sName);
        }
        $sName = trim($sName);
        if (isset($this->aVariables[$sName]) && is_null($sType)) {
            // preserve type when resetting variable
            $this->aVariables[$sName] = new Variable(trim($sValue), $this->getType($sName));
        } else {
            // set new variable
            $this->aVariables[$sName] = new Variable(trim($sValue), $sType);
        }
        return true;
    }

    /**
     * Get a single request variable by name.
     * @param $sName string name of variable to retrieve.
     * @return Variable Requested Variable object or null when not found.
     * @since 1.0
     */
    public function get($sName) {
        return isset($this->aVariables[$sName]) ? $this->aVariables[$sName] : null;
    }

    /**
     * Get the value of a variable, or null when undefined.
     * @param $sName string Name of the variable.
     * @return string Value of the variable.
     * @since 1.0
     */
    public function getValue($sName) {
        return isset($this->aVariables[$sName]) ? $this->aVariables[$sName]->getValue() : null;
    }

    /**
     * Get type of a viriable or null when the variable is undefined.
     * @param $sName string Name of the variable.
     * @return string Either GET or POST.
     * @since 1.0
     */
    public function getType($sName) {
        return isset($this->aVariables[$sName]) ? $this->aVariables[$sName]->getType() : null;
    }

    /**
     * Get list of variable values by type.
     * @param $sType string Type of variables to retrieve (defaults to GET).
     * @return array Associative array of variables of requested type.
     * @since 1.0
     */
    public function getByType($sType = Variable::TYPE_DEFAULT) {
        $aVariables = array();
        foreach ($this->aVariables as $sName => $oVariable) {
            if ($oVariable->getType() == $sType) {
                $aVariables[$sName] = $oVariable->getValue();
            }
        }
        return $aVariables;
    }

}

?>
