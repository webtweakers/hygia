<?php

ns::import('hygia.libs.io.Headers');
ns::import('hygia.libs.io.var.RequestVars');
ns::import('hygia.libs.io.var.Variable');

/**
 * The Request class provides data access to input streams.
 * Its properties and methods define the data send through the end point
 * and basically this object is part of the core of a test. It gets instantiated
 * in the TestSuite class and then gets thrown around through the plugin-
 * chain of the input and to the end point.
 * The class provides some semi intelligent getters and setters to work
 * with the data as convinient as possible (making the plugins easy to write).
 * @author Bas van Gaalen
 * @since 1.0
 */
class Request extends Headers {

    private $oRequestVars;
    private $sBody;
    private $sBodyName;
    private $sBodyFileName;
    private $sMimeType;

    /**
     * Constructor: prepares local private variables for usage
     * @since 1.0
     */
    public function __construct() {
        $this->oRequestVars = new RequestVars();
        $this->sBody = '';
        $this->sBodyName = '';
        $this->sBodyFileName = '';
        $this->sMimeType = '';
    }

    /**
     * Set a request variable.
     * @param $sName string Name of the variable.
     * @param $sValue string Value of the variable.
     * @param $sType string Either GET or POST
     * @return bool True on success, false on failure.
     * @since 1.0
     */
    public function setRequestVar($sName, $sValue = null, $sType = null) {
        return $this->oRequestVars->set($sName, $sValue, $sType);
    }

    /**
     * Get a single request variable by name.
     * @param $sName string name of variable to retrieve.
     * @return string Value of requested variable or null when not found.
     * @since 1.0
     */
    public function getRequestVar($sName) {
        return $this->oRequestVars->getValue($sName);
    }

    /**
     * Get access to RequestVars object.
     * @return RequestVars object.
     * @since 1.0
     */
    public function getRequestVars() {
        return $this->oRequestVars;
    }

    /**
     * Get request variables by type (either GET or POST) as string,
     * where values are url-encoded and variables are seperated by '&amp;'.
     * @param $sType string The type of variables to retrieve.
     * @return string 
     * @since 1.0
     */
    public function getRequestByType($sType = Variable::TYPE_DEFAULT) {
        $aVariables = $this->oRequestVars->getByType($sType);
        $s = '';
        foreach ($aVariables as $sName => $sValue) {
            $s .= $sName . '=' . urlencode($sValue) . '&';
        }
        return substr($s, 0, -1);
    }

    /**
     * Set request body. Can be either the body directly or the name to a file when $sPath
     * is provided (set to the test root in the TestSuite class).
     * @param $sBody string File name where request body is stored.
     * @param $sMimeType string The mime-type of the body, for use in request headers. If none is
     *        provided, the previously set mime-type will be used (if any).
     * @param $sPath string If provided, it is assumed that $sBody is the name and this the path.
     * @param $sName string If provided, name of the body (used in multipart/form-data posts)
     * @since 1.0
     */
    public function setBody($sBody, $sMimeType = '', $sPath = '', $sName = '') {
        if (!empty($sPath)) {
            $this->sBodyFileName = $sBody;
            $sBody = file_get_contents($sPath . '/' . $sBody);
        }
        $this->sMimeType = empty($sMimeType) ? $this->getMimeType() : $sMimeType;
        $this->sBody = $sBody;
        $this->sBodyName = $sName;
    }

    /**
     * Get request body.
     * @return string The request body.
     * @since 1.0
     */
    public function getBody() {
        return $this->sBody;
    }

    /**
     * Get size of the request body.
     * @return int Size.
     * @since 1.0
     */
    public function getBodySize() {
        return strlen($this->sBody);
    }

    /**
     * Get name of request body.
     * @return string Body name.
     * @since 1.0
     */
    public function getBodyName() {
        return $this->sBodyName;
    }

    /**
     * Get file name of request body.
     * @return string File name.
     * @since 1.0
     */
    public function getBodyFileName() {
        return $this->sBodyFileName;
    }

    /**
     * Get mime-type of the request body.
     * @return string Mime-type to be used in request header.
     * @since 1.0
     */
    public function getMimeType() {
        return $this->sMimeType;
    }

}

?>
