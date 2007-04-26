<?php

ns::import('hygia.libs.validator.Validator');
ns::import('hygia.libs.exception.ValidatorNotFoundException');
ns::import('hygia.libs.exception.ValidatorException');
ns::import('hygia.libs.io.Response');

/**
 * Schema Validator class. Provides xml schema validation of server responses.
 * Extends the Validator class and implements the IValidator interface.
 * @author Bas van Gaalen
 * @since 1.0
 */
class ValidatorXsd extends Validator implements IValidator {

    private $sValidatorName;
    private $aValidationErrors;

    /**
     * Constructor initializes the validator.
     * @since 1.0
     */
    public function __construct($sValidatorName) {
        $this->sValidatorName = $sValidatorName;
        $this->aValidationErrors = array();
    }

    /**
     * Run the validator on given Response object. Should always return a boolean.
     * @param $oResponse Response The Response object as it is returned by the server.
     * @return bool True if everything validates, false if not.
     * @throws ValidatorException
     * @since 1.0
     */
    public function run(Response $oResponse) {

        if (!$oResponse instanceof Response) {
            throw new ValidatorException('Response is not of type Response');
        }

        // invalid if no validator defined
        if (empty($this->sValidatorName)) {
            return false;
        }

        $sXsdPath = $this->getPath() . '/xsd/';
        $sFilename = $sXsdPath . $this->sValidatorName;
        if (!is_readable($sFilename)) {
            throw new ValidatorNotFoundException('File does not exist or is not readable: ' . $sFilename);
        }
        $sValidator = file_get_contents($sFilename);

        // load cleaned-up body (we expect xml, but if content-length body is missing, there might be mess)
        $oXSchema = new DOMDocument;
        $oXSchema->preserveWhiteSpace = false;
        @$oXSchema->loadXML($oResponse->getBody());

        // set path to xsd location
        $sCurPath = getcwd();
        chdir($sXsdPath);

        // set up error handling
        set_error_handler(array(&$this, 'staticError'));
        $vOld = ini_set('html_errors', false);

        // validate
        $bValid = $oXSchema->schemaValidateSource($sValidator);

        // restore error handling
        ini_set('html_errors', $vOld);
        restore_error_handler();

        // save errors
        $this->aValidationErrors = $this->staticError(null, null, null, null, null, true);

        // restore path
        chdir($sCurPath);
        return $bValid;
    }

    /**
     * Method for private usage, but must be public to be accessible by the php sub system.
     * Saves validation errors in a static array.
     */
    public function staticError($iErrNo, $sErrStr, $sErrFile, $iErrLine, $sErrContext, $bRet = false) {
        static $aErrors = array();
        if ($bRet) {
            return $aErrors;
        }
        $aErrors[] = str_replace('DOMDocument::schemaValidateSource(): ', '', $sErrStr);
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
