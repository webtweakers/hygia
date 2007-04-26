<?php

ns::import('hygia.libs.validator.Validator');
ns::import('hygia.libs.io.Response');
ns::import('hygia.libs.exception.ValidatorException');

/**
 * Validator: md5 - takes the md5 of the body and compares with the given value.
 * @author Bas van Gaalen
 * @since 1.0
 */
class ValidatorMd5 extends Validator implements IValidator {

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
            throw new ValidatorException('Response is not of type Response');
        }

        // calculate the md5 of the body
        $sBodyMd5 = md5($oResponse->getBody());
        $bValid = (int) $this->sValidator == $sBodyMd5;
        if (!$bValid) {
            $this->aValidationErrors[] = 'md5 validator failed. Expected: ' . $this->sValidator . '. Got: ' . $sBodyMd5;
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
