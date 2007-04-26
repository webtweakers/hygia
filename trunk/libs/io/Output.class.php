<?php

ns::import('hygia.libs.io.DataStream');
ns::import('hygia.libs.io.Response');
ns::import('hygia.libs.validator.ValidatorCollection');

/**
 * Output class, extends DataStream class. Keeps track of Response object,
 * Output Plugins and Validators.
 * @author Bas van Gaalen
 * @since 1.0
 */
class Output extends DataStream {

    private $oValidators;
    private $oResponse;

    /**
     * Constructor initializes the object and calls the parent constructor.
     * @since 1.0
     */
    public function __construct() {
        parent::__construct();
        $this->oValidators = null;
        $this->oResponse = null;
    }

    /**
     * Set Response object.
     * @param $oResponse Response The Response object.
     * @since 1.0
     */
    public function setResponse(Response $oResponse) {
        $this->oResponse = $oResponse;
    }

    /**
     * If set, run output plugins on Response object and return it.
     * @since 1.0
     */
    public function getResponse() {
        return is_null($this->getPlugin()) ? $this->oResponse :
            $this->getPlugin()->getRequestResponse($this->oResponse);
    }

    /**
     * Set ValidatorCollection object, which holds the validators to be run
     * on the Output Response.
     * @param $oValidators ValidatorCollection
     * @since 1.0
     */
    public function setValidatorCollection(ValidatorCollection $oValidators) {
        $this->oValidators = $oValidators;
    }

    /**
     * Get the ValidatorCollection.
     * @return ValidatorCollection
     * @since 1.0
     */
    public function getValidators() {
        return !is_null($this->oValidators) ? $this->oValidators : new ValidatorCollection();
    }

}

?>
