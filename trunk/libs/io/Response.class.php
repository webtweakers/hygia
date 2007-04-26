<?php

ns::import('hygia.libs.io.Headers');

/**
 * The Response class provides data access to output streams.
 * Its properties and methods define the data send through the end point
 * and basically this object is part of the core of a test. It gets instantiated
 * in the TestItem class (when the response comes back) and then gets thrown
 * around through the output-plugins and the Validators, which check the data
 * at that point.
 * The class provides some semi intelligent getters and setters to work
 * with the data as convinient as possible (making the plugins easy to write).
 * @author Bas van Gaalen
 * @since 1.0
 */
class Response extends Headers {

    private $sBody;
    private $iConnectionTime;
    private $iTransferTime;

    /**
     * Constructor: prepars local private variables for usage
     * @since 1.0
     */
    public function __construct() {
        $this->sBody = '';
        $this->iConnectionTime = 0;
        $this->iTransferTime = 0;
    }

    /**
     * Save body.
     * @param $sBody string Body
     * @throws Exception
     * @since 1.0
     */
    public function setBody($sBody) {
        $this->sBody = $sBody;
    }

    /**
     * Get body.
     * @return string the body of the response
     * @since 1.0
     */
    public function getBody() {
        return $this->sBody;
    }

    /**
     * Get size of body.
     * @return int the size of the body
     * @since 1.0
     */
    public function getBodySize() {
        return strlen($this->sBody);
    }

    /**
     * Get size of body by provided content-length header
     * @return int the size of the body according to the content-length header
     * @since 1.0
     */
    public function getBodySizeByHeader() {
        return (int) $this->getHeaderValue('content-length');
    }

    /**
     * Save the connection time - set in the TestItem class when the response is returned.
     * The connection time is actually the total time taken for the complete request, starting
     * at the connection and ending when the response has been received.
     * @param $iTime int Time as integer.
     * @since 1.0
     */
    public function setConnectionTime($iTime) {
        $this->iConnectionTime = $iTime;
    }

    /**
     * Get the connection time.
     * @return int
     * @since 1.0
     */
    public function getConnectionTime() {
        return $this->iConnectionTime;
    }

    /**
     * Save the transfer time - set in the TestItem class when the response is returned.
     * The transfer time is the duration of the data transfer (total time - connection).
     * @param $iTime int Time as integer.
     * @since 1.0
     */
    public function setTransferTime($iTime) {
        $this->iTransferTime = $iTime;
    }

    /**
     * Get the transfer time.
     * @return int
     * @since 1.0
     */
    public function getTransferTime() {
        return $this->iTransferTime;
    }

}

?>
