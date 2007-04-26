<?php

/**
 * Validators should implement this interface.
 * This is an implementation of the Strategy Pattern.
 * @author Bas van Gaalen
 * @since 1.0
 */
interface IValidator {
    public function run(Response $oResponse);
    public function getValidationErrors();
}

/**
 * Each Validator extends this class, which adds some minimal functionality
 * for reaching the validator's path.
 * @author Bas van Gaalen
 * @since 1.0
 */
class Validator {

    private $sPath;

    /**
     * Set path.
     * @param $sPath string Path.
     * @since 1.0
     */
    public function setPath($sPath) {
        $this->sPath = $sPath;
    }

    /**
     * Get path.
     * @return string The path.
     * @since 1.0
     */
    public function getPath() {
        return $this->sPath;
    }

}

?>
