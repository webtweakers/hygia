<?php

ns::import('hygia.libs.io.DataStream');
ns::import('hygia.libs.io.Request');

/**
 * Input class, extends the DataStream class. Keeps track of the request object
 * and can run the plugin-chain on the request.
 * @author Bas van Gaalen
 * @since 1.0
 */
class Input extends DataStream {

    private $oRequest;

    /**
     * Constructor initializes the object and calls parent constructor.
     * @since 1.0
     */
    public function __construct() {
        parent::__construct();
        $this->oRequest = null;
    }

    /**
     * Set the request object for the test input.
     * @param $oRequest Request The Request object.
     * @since 1.0
     */
    public function setRequest(Request $oRequest) {
        $this->oRequest = $oRequest;
    }

    /**
     * If defined, run the plugins on the Request object and return it.
     * @return Request
     * @since 1.0
     */
    public function getRequest() {
        return is_null($this->getPlugin()) ? $this->oRequest :
            $this->getPlugin()->getRequestResponse($this->oRequest);
    }

}

?>
