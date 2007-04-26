<?php

ns::import('hygia.libs.validator.Validator');
ns::import('hygia.libs.io.Response');
ns::import('hygia.libs.exception.ValidatorException');

/**
 * Validator: transfer time - compares the transfer time with the given value in the
 * configuration files. Validates if given value is smaller than calculated one.
 * @author Bas van Gaalen
 * @since 1.0
 */
class ValidatorTime extends Validator implements IValidator {

    private $sValidator;
    private $aValidationErrors;

    public function __construct($sValidator) {
        $this->sValidator = $sValidator;
        $this->aValidationErrors = array();
    }

    /**
     * Run the validator on given Response object. Should always return a boolean.
     * @param $oResponse Response The Response object as it is returned by the server.
     * @return bool True if everything validates, false if not
     * @throws ValidatorException
     * @since 1.0
     */
    public function run(Response $oResponse) {

        if (!$oResponse instanceof Response) {
            throw new ValidaotrException('Response is not of type Response');
        }

        // gets the transfer time and compare with given value - valid if given
        // value is smaller (shorter) 
        $bValid = (int) $this->sValidator < $oResponse->getTransferTime();
        if (!$bValid) {
            $this->aValidationErrors[] = 'Time validator failed. Expected transfer time below: ' . $this->sValidator . '. Got: ' . $oResponse->getTransferTime();
        }
        return $bValid;
    }

    /**
     * Get all errors that occured during validation.
     * @return array List of error messages.
     * @since 1.0
     */
    public function getValidationErrors() {
        return $this->aValidationErrors;
    }

}

?>
