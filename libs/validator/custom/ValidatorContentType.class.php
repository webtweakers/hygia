<?php

ns::import('hygia.libs.validator.Validator');
ns::import('hygia.libs.io.Response');
ns::import('hygia.libs.exception.ValidatorException');

/**
 * Validator: Content-Type - gets the content-type header and compares with the given value.
 * @author Bas van Gaalen
 * @since 1.0
 */
class ValidatorContentType extends Validator implements IValidator {

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

        // get the content-type header
        $sContentType = $oResponse->getHeaderValue('Content-Type');
        $bValid = (int) $this->sValidator == $sContentType;
        if (!$bValid) {
            $this->aValidationErrors[] = 'Content-Type validator failed. Expected: ' . $this->sValidator . '. Got: ' . $sContentType;
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
