<?php

/**
 * This class represents a request variable of both GET and POST type.
 * @author Bas van Gaalen
 * @since 1.0
 */
class Variable {

    const   TYPE_GET     = 'GET';
    const   TYPE_POST    = 'POST';

    const   TYPE_DEFAULT = Variable::TYPE_GET;

    // faking enum behavior
    private static $aAllowedTypes = array(
        Variable::TYPE_GET,
        Variable::TYPE_POST
    );

    private $sValue;
    private $sType;

    /**
     * Constructor sets the value and type of the variable. The Name component is
     * in a hash (associative array) in the RequestVars class.
     * @param $sValue string Value.
     * @param $sType string Either GET or POST.
     * @since 1.0
     */
    public function __construct($sValue = '', $sType = null) {
        $this->set($sValue, $sType);
    }

    /**
     * Set value and type of the variable.
     * @param $sValue string Value.
     * @param $sType string Either GET or POST.
     * @throws Exception when trying to set unknown type.
     * @since 1.0
     */
    public function set($sValue = '', $sType = null) {
        if (is_null($sType)) {
            $sType = Variable::TYPE_DEFAULT;
        }
        $sType = strtoupper($sType);
        if (!in_array($sType, Variable::$aAllowedTypes)) {
            throw new Exception('Unknown variable type: "' . $sType . '"');
        }
        $this->sValue = $sValue;
        $this->sType = $sType;
    }

    /**
     * Get value of the variable.
     * @return string
     * @since 1.0
     */
    public function getValue() {
        return $this->sValue;
    }

    /**
     * Get type of the variable.
     * @return string Either GET or POST.
     * @since 1.0
     */
    public function getType() {
        return $this->sType;
    }

}

?>
