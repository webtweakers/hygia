<?php

ns::import('hygia.libs.validator.custom.ValidatorXsd');
ns::import('hygia.libs.validator.custom.ValidatorRe');
ns::import('hygia.libs.validator.custom.ValidatorSize');
ns::import('hygia.libs.validator.custom.ValidatorContentType');
ns::import('hygia.libs.validator.custom.ValidatorTime');
ns::import('hygia.libs.validator.custom.ValidatorMd5');
ns::import('hygia.libs.exception.UnknownValidatorTypeException');
ns::import('hygia.libs.io.Response');

/**
 * ValidatorCollection defines the validators as set in the Response object.
 * By adding validators to the response, this class instantiates several
 * classes, each performing a specific type of validation. Also has methods
 * to check the results of the validators.
 * @author Bas van Gaalen
 * @since 1.0
 */
class ValidatorCollection {

    const   TYPE_RE            = 're';
    const   TYPE_XSD           = 'xsd';
    const   TYPE_DTD           = 'dtd';
    const   TYPE_SIZE          = 'size';
    const   TYPE_CONTENT_TYPE  = 'content-type';
    const   TYPE_TIME          = 'time';
    const   TYPE_MD5           = 'md5';
    const   TYPE_PHP           = 'php'; // needed? i think not...

    private $aValidators;
    private $sBasePath;
    private $iValid;

    /**
     * Constructor initializes the class.
     * @param $sBasePath string optional base path of test.
     * @since 1.0
     */
    public function __construct($sBasePath = '') {
        $this->aValidators = array();
        $this->setBasePath($sBasePath);
    }

    /**
     * Set test base path.
     * @param $sPath string
     * @since 1.0
     */
    public function setBasePath($sPath) {
        $this->sBasePath = $sPath;
    }

    /**
     * Add a validator to the collection. Instantiates a specific class to handle
     * the requested validation type.
     * @param $sValidator string Either the name of a validator or a string to be validated.
     * @param $sType string The validator type, defaults to Regular Expression.
     * @since 1.0
     */
    public function addValidator($sValidator, $sType = ValidatorCollection::TYPE_RE) {
        switch ($sType) {

            case ValidatorCollection::TYPE_RE:
                $this->aValidators[] = new ValidatorRe($sValidator);
                break;

            case ValidatorCollection::TYPE_XSD:
                $oValidator = new ValidatorXsd($sValidator);
                $oValidator->setPath($this->sBasePath);
                $this->aValidators[] = $oValidator;
                break;

            case ValidatorCollection::TYPE_SIZE:
                $this->aValidators[] = new ValidatorSize($sValidator);
                break;

            case ValidatorCollection::TYPE_CONTENT_TYPE:
                $this->aValidators[] = new ValidatorContentType($sValidator);
                break;

            case ValidatorCollection::TYPE_TIME:
                $this->aValidators[] = new ValidatorTime($sValidator);
                break;

            case ValidatorCollection::TYPE_MD5:
                $this->aValidators[] = new ValidatorMd5($sValidator);
                break;

            case ValidatorCollection::TYPE_PHP:
                break;

            default:
                throw new UnknownValidatorTypeException('Validator type ' . $sType . ' is unknown.');
                break;

        }
    }

    /**
     * Get all validators.
     * @return array all validators in this collection
     * @since 1.0
     */
    public function getValidators() {
        return $this->aValidators;
    }

    /**
     * Get number of validators in this collection.
     * @return int number of validators
     * @since 1.0
     */
    public function getValidatorCount() {
        return count($this->aValidators);
    }

    /**
     * Get number of validators that run successfully (are valid).
     * Should be called after the validateBatch method.
     * @return int number of valid validators
     * @since 1.0
     */
    public function getValidCount() {
        return $this->iValid;
    }

    /**
     * Get number of validators that failed to run successfully.
     * Should be called after the validateBatch method.
     * @return int failed validators
     * @since 1.0
     */
    public function getFailCount() {
        return $this->getValidatorCount() - $this->getValidCount();
    }

    /**
     * Return true if the whole collection is valid, false otherwise
     * @return bool true on success
     * @since 1.0
     */
    public function isValid() {
        return $this->getValidCount() == $this->getValidatorCount();
    }

    /**
     * Run all validators in turn.
     * @param $oResponse Response response object to run the validators on
     * @since 1.0
     */
    public function validateBatch(Response $oResponse) {
        $this->iValid = 0;
        foreach ($this->getValidators() as $oValidator) {
            if ($oValidator->run($oResponse)) {
                $this->iValid++;
            }
        }
    }

    /**
     * Retrieve all error messages from the validators in the collection
     * and return as array.
     * @return array list of error messages.
     * @since 1.0
     */
    public function getValidationErrors() {
        $aErrors = array();
        foreach ($this->getValidators() as $oValidator) {
            $aErrors = array_merge($aErrors, $oValidator->getValidationErrors());
        }
        return $aErrors;
    }

}

?>
