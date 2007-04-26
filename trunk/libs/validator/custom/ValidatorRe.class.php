<?php

ns::import('hygia.libs.validator.Validator');
ns::import('hygia.libs.io.Response');
ns::import('hygia.libs.exception.ValidatorException');

/**
 * Validator: Regular Expression - runs the given regular expression on the body and checks for a match.
 * @author Bas van Gaalen
 * @since 1.0
 */
class ValidatorRe extends Validator implements IValidator {

    private $sValidator;
    private $aValidationErrors;

    public function __construct($sValidator) {
        // TODO: known issue: chars like & and how to match (htmlentities/html_entity_decode)
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

        // invalid when no validator is defined
        if (empty($this->sValidator)) {
            return false;
        }

        // run the regular expression on the body of the response
        $bValid = preg_match($this->sValidator, $oResponse->getBody());
        if (!$bValid) {
            $this->aValidationErrors[] = 'Regular Expression validator failed: <tt>' . $this->sValidator . '</tt>';
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
