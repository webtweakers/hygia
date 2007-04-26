<?php

ns::import('hygia.libs.exception.RequestException');
ns::import('hygia.libs.exception.ResponseException');
ns::import('hygia.libs.sub.Utils');
ns::import('hygia.libs.core.TestConfig');
ns::import('hygia.libs.core.TestLogger');
ns::import('hygia.libs.io.Request');
ns::import('hygia.libs.io.Response');

/**
 * TestItem class. Holds all properties of one single end point and provides
 * run method to call and validate the end point.
 * @author Bas van Gaalen
 * @since 1.0
 */
class TestItem {

    // debug: 1 = show request and response
    const    DEBUG = 0;

    private $sTitle;
    private $sPath;
    private $sUser;
    private $sPass;
    private $oInput;
    private $oOutput;

    /**
     * Constructor initializes test item.
     * @param $sTitle string Title of this end point.
     * @param $sPath string URL without domain name.
     * @since 1.0
     */
    public function __construct($sTitle = '', $sPath = '') {
        $this->setTitle($sTitle);
        $this->setPath($sPath);
        $this->sUser = '';
        $this->sPass = '';
    }

    /**
     * Set title of end point. Used for presentation, something like the
     * title of the page, rpc, feed or service being tested.
     * @param $sTitle string
     * @since 1.0
     */
    public function setTitle($sTitle) {
        $this->sTitle = $sTitle;
    }

    /**
     * Set path of end point (URL without the http and domain name parts).
     * @param $sPath string
     * @since 1.0
     */
    public function setPath($sPath) {
        $this->sPath = $sPath;
    }

    /**
     * Set basic authentication credentials.
     * @param $sUser string User name.
     * @param $sPass string Password.
     * @since 1.0
     */
    public function setAuth($sUser, $sPass) {
        $this->sUser = $sUser;
        $this->sPass = $sPass;
    }

    /**
     * Get user name and password for basic authentication.
     * @return base64 encoded concatenation of user and pass.
     * @since 1.0
     */
    public function getAuth() {
        return base64_encode($this->sUser . ':' . $this->sPass);
    }

    /**
     * Set input object.
     * @param $o Input Setter to inject the object that holds the input properties.
     * @since 1.0
     */
    public function setInput(Input $o = null) {
        $this->oInput = $o;
    }

    /**
     * Set output object.
     * @param $o Output Setter to inject the object that holds the output properties.
     * @since 1.0
     */
    public function setOutput(Output $o = null) {
        $this->oOutput = $o;
    }

    /**
     * This run method is the meat of the TesSuite. It builds the request,
     * runs the input plugins, sends the request to the server being tested,
     * receives the response, runs the output plugins and validates the output.
     * @param $sProtocol string Either http or https.
     * @param $sServer string Domain name of the server to connect with.
     * @param $iPort int Port number, defaults to 80 (http).
     * @since 1.0
     */
    public function run($sProtocol = null, $sServer = null, $iPort = 80) {

        // don't even bother when server is not provided
        if (empty($sServer)) {
            return false;
        }

        // unknown protocol? currently only http and https are supported
        if ($sProtocol != 'http' && $sProtocol != 'https') {
            return false;
        }

        TestLogger::log('<br />');
        TestLogger::log('<h3>Test: ' . $this->sTitle . '</h3>');

        try {

            // get request arguments, run plugin(s)...
            $oRequest = $this->oInput->getRequest();

            // send request and receive response
            $oResponse = $this->sendRequest($sProtocol . '://' . $sServer . ':' . $iPort . $this->sPath, $oRequest);

            // set response object in output, run plugin(s) and get processed response
            $this->oOutput->setResponse($oResponse);
            $oResponse = $this->oOutput->getResponse();

            // validate
            $oValidators = $this->oOutput->getValidators();
            if ($oValidators->getValidatorCount() == 0) {
                TestLogger::log('<span class="debug">No validators defined for this end point</span>');
            } else {
                $oValidators->validateBatch($oResponse);
                if ($oValidators->isValid()) {
                    TestLogger::log('<span class="good">Ran ' . Utils::getNoun($oValidators->getValidatorCount(), 'validator', 'validators') . ' successfully!</span>');
                } else {
                    TestLogger::log('<span class="warn">Ran ' . Utils::getNoun($oValidators->getValidatorCount(), 'validator', 'validators') . ': ' . $oValidators->getValidCount() . ' ok, ' . Utils::getNoun($oValidators->getFailCount(), 'fail', 'fails') . '</span>');
                    TestLogger::log('<span class="warnsub"><ol><li>' . implode("</li>\r\n<li>", $oValidators->getValidationErrors()) . "</li>\r\n</ol></span>");
                }
            }
            return $oValidators->isValid();

        } catch (Exception $e) {

            TestLogger::log('<span class="warn">An error occured: ' . $e->getMessage() . '</span>');
            return false;

        }

    }

    /**
     * Performs the actual request to the provided end-point definition.
     * Uses sockets to connect with the server. This method mimics what a
     * www-browser usually does and should probably be rewritten to one
     * or more dedicated classes for better flexibility and extensibility.
     * For now, this method is sufficient.
     * @param $sUrl string Full URL to the end point.
     * @param $oRequest Request Request object.
     * @return Response Response object.
     * @since 1.0
     */
    private function sendRequest($sUrl, Request $oRequest) {

        $sDebugUid = 'id' . uniqid(rand(), true);

        // parse url and get parts
        $aUrlParts = parse_url($sUrl);
        $sScheme = isset($aUrlParts['scheme']) ? $aUrlParts['scheme'] : 'http';
        $sServer = isset($aUrlParts['host']) ? $aUrlParts['host'] : 'localhost';
        $sPort = isset($aUrlParts['port']) ? $aUrlParts['port'] : 80;
        $sPath = isset($aUrlParts['path']) ? $aUrlParts['path'] : '/';

        // handle get/post
        $sFullPath = $sPath;
        $sGetQuery = $oRequest->getRequestByType(Variable::TYPE_GET);
        $sPostQuery = $oRequest->getRequestByType(Variable::TYPE_POST);
        $sBody = $oRequest->getBody();
        $sMimeType = $oRequest->getMimeType();

        // get request
        if (!empty($sGetQuery)) {
            $sFullPath .= '?' . $sGetQuery;
        }

        if (!empty($sPostQuery)) {

        	if (empty($sBody)) {

                // posting a form
                $sBody = $sPostQuery;
                $sMimeType = 'application/x-www-form-urlencoded';
        		
        	} else {

                // posting vars and body: create (rfc-1867 compliant) multi-part mime body
                $aPostVars = $oRequest->getRequestVars()->getByType(Variable::TYPE_POST);
                $sMimeType = 'multipart/form-data; boundary=' . md5($sDebugUid);
                $sFile = $sBody;

                // add post vars
                $sBody = '';
                foreach ($aPostVars as $sName => $sValue) {
                    $sBody .= '--' . md5($sDebugUid) . "\r\n";
                    $sBody .= 'Content-Disposition: form-data; name="' . $sName . '"' . "\r\n\r\n" . $sValue . "\r\n";
                }

                // add file
                $sBody .= '--' . md5($sDebugUid) . "\r\n";
                $sBody .= 'Content-Disposition: form-data; name="' . $oRequest->getBodyName() . '"; filename="' . $oRequest->getBodyFileName() . '"' . "\r\n";
                $sBody .= 'Content-Type: ' . $oRequest->getMimeType() . "\r\n\r\n";
                $sBody .= $sFile . "\r\n";

                // end
                $sBody .= '--' . md5($sDebugUid) . '--' . "\r\n";

        	}

        }

        // handle protocol
        if ($sScheme == 'https') {
            $sPort = 443;
            $sTransportUrl = 'ssl://' . $sServer;
        } else {
            $sTransportUrl = 'tcp://' . $sServer;
        }

        try {

            // prepare request: define full message to send over socket
            $iSize = strlen($sBody);
            $oRequest->addHeader('Host: ' . $sServer);
            $sUserAgent = $oRequest->getHeaderValue('User-Agent');
            if (empty($sUserAgent)) {
                $oRequest->addHeader('User-Agent: ' . TestConfig::ApplicationTitle . ' ' .
                                                      TestConfig::ApplicationVersion);
            }
            $oRequest->addHeader('Keep-Alive: 300');
            $oRequest->addHeader('Connection: keep-alive');
            if ($iSize > 0) {
                $oRequest->addHeader('Content-Length: ' . $iSize);
                $oRequest->addHeader('Content-Type: ' . $sMimeType);
            }
            if (!empty($this->sUser) && !empty($this->sPass)) {
            	$oRequest->addHeader('Authorization: Basic ' . $this->getAuth());
            }
            $sHeaders = (($iSize > 0) ? 'POST' : 'GET') . ' ' . $sFullPath . ' HTTP/1.0' . "\r\n" .
                        $oRequest->getHeaders(true);

            $sContent = ((!empty($sBody)) ? $sBody . "\r\n" : "");

            // display some info...
            $sPath4log = $sScheme . '://' . $sServer . ($sPort != 80 ? ':' . $sPort : '') . $sFullPath;
            TestLogger::log('<span class="info">[<a href="javascript:Utils.dspSwitch(\'debug_request' . $sDebugUid . '\')">Show/Hide request</a>] End point: <a href="' . $sPath4log . '" target="_blank">' . $sPath4log . '</a></span>');

            TestLogger::log('<div id="debug_request' . $sDebugUid . '" style="margin-bottom:20px; display:' . (TestItem::DEBUG ? 'block' : 'none') . '">' .
                             'request:' .
                             '<div class="headers"><pre>' . $sHeaders . '</pre></div>');
            if (!empty($sContent)) {
                $bShowContent =    (Utils::binaryPercentage($sContent) < TestConfig::ShowBinaryPercTresh)
                                && (strlen($sContent) < TestConfig::ShowContentLengthTresh);
                TestLogger::log('<div class="content"><pre>' . ($bShowContent ? htmlentities($sContent) : '[binary or long content (' . strlen($sContent) . ' characters)]') . '</pre></div>');
            }
            TestLogger::log('</div>');

            TestLogger::log('<div id="' . $sDebugUid . '" class="wait">Connecting...</div>');

            // open a socket connection with host (surpressing warnings/notices)
            $iStartConnectionTime = microtime(true);
            $fp = @fsockopen($sTransportUrl, $sPort, $iErrorNr, $sErrorMsg);
            if (!$fp) {
                throw new RequestException('Unable to connect to ' . $sServer . ' at port ' . $sPort . ': (' . $iErrorNr . ') ' . $sErrorMsg);
            }

            // time out stuff (turned off at the moment, doesn't seem to work...)
            //stream_set_blocking($fp, 0);
            //stream_set_timeout($fp, 2);

            // send request message
            $sRequest = $sHeaders . "\r\n" . $sContent;
            $iStartTransferTime = microtime(true);
            $bWriteOk = fwrite($fp, $sRequest);
            if ($bWriteOk === false) {
                throw new RequestException('Unable to send data.');
            }

            // request
            // ----------------------------------------------------------
            // response

            // recieve response (surpressing warnings/notices)
            $sResponse = '';
            while (!feof($fp)) $sResponse .= @fread($fp, 128);
            $aStreamInfo = stream_get_meta_data($fp);
            fclose($fp);
            $iConnectionTime = microtime(true) - $iStartConnectionTime;
            $iTransferTime = microtime(true) - $iStartTransferTime;

        } catch (Exception $e) {

            // hide connection progress indicator and rethrow exception
            TestLogger::log('<script type="text/javascript">document.getElementById("' . $sDebugUid . '").style.display="none";</script>');
            throw $e;

        }

        // display some info...
        TestLogger::log('<script type="text/javascript">document.getElementById("' . $sDebugUid . '").style.display="none";</script>');
        TestLogger::log('<span class="debug">' . ((!$aStreamInfo['timed_out'] && !empty($sResponse)) ? '[<a href="javascript:Utils.dspSwitch(\'debug_response' . $sDebugUid . '\')">Show/Hide response</a>] ' : '') . 'Received response. Total time: ' . round($iConnectionTime, 2) . ' seconds, transfer time: ' . round($iTransferTime, 2) . ' seconds.</span>');

        //print 'stream info: <pre>' . print_r($aStreamInfo, 1) . '</pre>';

        // time out?
        if ($aStreamInfo['timed_out']) {
            throw new ResponseException('Error: Connection timed out.');
        }

        // skip on error
        if (empty($sResponse)) {
            throw new ResponseException('Error: Empty response.');
        }

        // get headers and body from response
        list($sHeaders, $sContent) = split("\r?\n\r?\n", $sResponse, 2);
        $aHeaders = split("\r?\n", $sHeaders);

        TestLogger::log('<div id="debug_response' . $sDebugUid . '" style="margin-bottom:20px; display:' . (TestItem::DEBUG ? 'block' : 'none') . '">' .
                         'response:' .
                         '<div class="headers"><pre>' . $sHeaders . '</pre></div>');
        if (!empty($sContent)) {
            $bShowContent =    (Utils::binaryPercentage($sContent) < TestConfig::ShowBinaryPercTresh)
                            && (strlen($sContent) < TestConfig::ShowContentLengthTresh);
            TestLogger::log('<div class="content"><pre>' . ($bShowContent ? htmlentities($sContent) : '[binary or long content (' . strlen($sContent) . ' characters)]') . '</pre></div>');
        }
        TestLogger::log('</div>');

        // get http status info
        $iStatusCode = (preg_match("/^http\/\S+ (\d+) /i", $sHeaders, $a)) ? $a[1] : 0;
        $sStatusMsg  = (preg_match("/^http\/\S+ \d+ (.*)/i", $sHeaders, $a)) ? $a[1] : '';

        // allowed status codes, if not error
        $aStatusCodes = array(302);

        // skip on error
        if ($iStatusCode != 200 && !in_array($iStatusCode, $aStatusCodes)) {
            throw new ResponseException('Error: Problem with response, returned http status code: ' . $iStatusCode . ', message: ' . $sStatusMsg);
        }

        // instantiate response object and initialize it
        $oResponse = new Response();
        $oResponse->setHeaders($aHeaders);
        $oResponse->setBody($sContent);
        $oResponse->setConnectionTime($iConnectionTime);
        $oResponse->setTransferTime($iTransferTime);

        // TODO: handle cookies: setting, deleting

        // handle status codes
        switch ($iStatusCode) {
        	case 302:
                // TODO: server might have send set-cookie headers: so set the cookies here
                $sUrl = (preg_match( "/location\:\s+(.*)/i", $sHeaders, $a )) ? trim($a[1]) : '';
                return $this->sendRequest($sUrl, $oRequest);
                break;
        }

        // return response object
        return $oResponse;

    }

}

/*

http://localhost/eventum/login.php

POST /eventum/login.php HTTP/1.1
Host: localhost
User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1
Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*-/-*;q=0.5
Accept-Language: en-us,nl;q=0.8,en;q=0.6,de-de;q=0.4,fr-fr;q=0.2
Accept-Encoding: gzip,deflate
Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7
Keep-Alive: 300
Connection: keep-alive
Referer: http://localhost/eventum/index.php?err=3
Cookie: PHPSESSID=c557a030302afd1911f0d975c39c34b1
Content-Type: application/x-www-form-urlencoded
Content-Length: 62
    cat=login&url=&email=admin%40example.com&passwd=a&Submit=Login

----------------------------------------------------------

https://www.google.com/adsense/

GET /adsense/ HTTP/1.1
Host: www.google.com
User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1
Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*-/-*;q=0.5
Accept-Language: en-us,nl;q=0.8,en;q=0.6,de-de;q=0.4,fr-fr;q=0.2
Accept-Encoding: gzip,deflate
Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7
Keep-Alive: 300
Connection: keep-alive
Referer: http://www.google.com/search?hl=en&q=https&btnG=Google+Search
Cache-Control: max-age=0

HTTP/1.x 200 OK
Set-Cookie: AdSenseLocaleSession=en_US; Path=/adsense/
Set-Cookie: AdSenseLocale=en_US; Expires=Wed, 20-Feb-08 10:11:14 GMT; Path=/adsense/
Set-Cookie: adsenseReferralSubId=; Expires=Mon, 21-May-07 10:11:14 GMT; Path=/
Set-Cookie: adsenseReferralSourceId=; Expires=Mon, 21-May-07 10:11:14 GMT; Path=/
Set-Cookie: adsenseReferralClickId=; Expires=Mon, 21-May-07 10:11:14 GMT; Path=/
Set-Cookie: I=; Expires=Mon, 19-Feb-07 10:11:14 GMT; Path=/adsense
Set-Cookie: S=adsense=eJGtVOQdhz0; Domain=.google.com; Path=/
Content-Type: text/html; charset=UTF-8
Content-Encoding: gzip
Cache-Control: no-cache,no-store
Content-Length: 2967
Date: Tue, 20 Feb 2007 10:11:14 GMT
Server: GFE/1.3

----------------------------------------------------------

https://www.google.com/adsense/1208426792-urchin.js

GET /adsense/1208426792-urchin.js HTTP/1.1
Host: www.google.com
User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1
Accept: *-/-*
Accept-Language: en-us,nl;q=0.8,en;q=0.6,de-de;q=0.4,fr-fr;q=0.2
Accept-Encoding: gzip,deflate
Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7
Keep-Alive: 300
Connection: keep-alive
Referer: https://www.google.com/adsense/
Cookie: AdSenseLocaleSession=en_US; AdSenseLocale=en_US; adsenseReferralSubId=; adsenseReferralSourceId=; adsenseReferralClickId=; S=adsense=eJGtVOQdhz0
Cache-Control: max-age=0

HTTP/1.x 200 OK
Last-Modified: Thu, 21 Dec 2006 20:45:02 GMT
Content-Type: application/x-javascript
Content-Encoding: gzip
Cache-Control: no-cache,no-store
Content-Length: 5427
Date: Tue, 20 Feb 2007 10:11:15 GMT
Server: GFE/1.3

*/

?>
