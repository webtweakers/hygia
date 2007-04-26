<?php

/**
 * The Headers class provides methods to work with both request and response headers.
 * Extended by Request and Response classes, which add specific functionality.
 * @author Bas van Gaalen
 * @since 1.0
 */
class Headers {

    private $aHeaders;

    /**
     * Constructor initializes the object.
     * @since 1.0
     */
    public function __construct() {
        $this->aHeaders = array();
    }

    /**
     * Set all headers.
     * @param $aHeaders array List of headers.
     * @since 1.0
     */
    public function setHeaders($aHeaders) {
        $this->aHeaders = $aHeaders;
    }

    /**
     * Add a single header, will overwrite existing header.
     * @param $sHeader string A single header.
     * @return bool False in case header could not be added (invalid header).
     * @since 1.0
     */
    public function addHeader($sNewHeader) {
        if (stripos($sNewHeader, ':') === false) return false;
        list($sNewHeaderName, $sNewHeaderValue) = explode(':', $sNewHeader);
        $sNewHeaderName = strtolower(trim($sNewHeaderName));
        $bFound = false;
        if (!empty($this->aHeaders)) {
            foreach ($this->aHeaders as &$sHeader) {
                if (stripos($sHeader, ':') !== false) {
                    list($sHeaderName, $sHeaderValue) = explode(':', $sHeader);
                    if (strtolower(trim($sHeaderName)) == strtolower($sNewHeaderName)) {
                        $bFound = true;
                        $sHeader = $sHeaderName . ': ' . $sNewHeaderValue;
                        break;
                    }
                }
            }
        }
        if (!$bFound) {
            $this->aHeaders[] = $sNewHeader;
        }
        return true;
    }

    /**
     * Get all headers as either array or string, depending on input (defaults to array).
     * @param $bAsString bool When true, headers will be returned as string. Defaults to false and array.
     * @return mixed Array or string of headers.
     * @since 1.0
     */
    public function getHeaders($bAsString = false) {
        $aHeaders = $this->aHeaders;
        if ($bAsString) {
            return !empty($aHeaders) ? implode("\r\n", $aHeaders) . "\r\n" : "";
        } else {
            return $aHeaders;
        }
    }

    /**
     * Get a specific header by name.
     * @param $sName mixed Header name.
     * @return string Value of header (part after the ':'); returns null if header not found.
     * @since 1.0
     */
    public function getHeaderValue($sName) {
        foreach ($this->aHeaders as $sHeader) {
            if (stripos($sHeader, ':') !== false) {
                list($sHeaderName, $sHeaderValue) = explode(':', $sHeader);
                if (strtolower(trim($sHeaderName)) == strtolower($sName)) {
                    return trim($sHeaderValue);
                }
            }
        }
        return null;
    }

}

?>
