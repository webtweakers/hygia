<?php

/**
 * @TODO: implement TestClient 
 * @TODO: add DTD and RelaxNG validation
 * @TODO: implement http/1.1 -> chars after body is blocksize, use hexdec
 * @TODO: use stream functions to allow for more protocols?
 * @TODO: research support for time outs (currently not working)
 * @TODO: build schema for the xml config files, to make sure the input is correct
 * @TODO: implement support for multiple body's (multiple file upload support)
 * @TODO: for when I feel like autodetecting content types:
 *        http://www.duke.edu/websrv/file-extensions.html
 */

// include libraries
ns::import('hygia.libs.sub.Utils');
ns::import('hygia.libs.exception.ConfigException');
ns::import('hygia.libs.core.TestLogger');
ns::import('hygia.libs.core.TestModule');
ns::import('hygia.libs.core.TestItem');
ns::import('hygia.libs.io.Input');
ns::import('hygia.libs.io.Output');
ns::import('hygia.libs.io.Request');
ns::import('hygia.libs.io.Response');
ns::import('hygia.libs.validator.ValidatorCollection');
ns::import('hygia.libs.validator.Validator');
ns::import('hygia.libs.plugin.Plugin');
ns::import('hygia.libs.plugin.PluginFactory');

/**
 * Main TestSuite class.
 * This class reads the configuration file(s) of the requested test batch
 * (provided in the GET request with argument name 'mod') and builds the
 * internal test structure of objects, all set up and ready to go. The run
 * method starts the test.
 * @author Bas van Gaalen
 * @since 1.0
 */
class TestSuite {

    const   TEST_EXTENSION = 'xml';

    private $aArgs;
    private $sModulePath;
    private $aTestFilenames;
    private $aModules;

    // public methods ---------------------------------------------------------

    /**
     * Constructor initializes test suite.
     * @param $aArgs array An array of the combined input variables of GET, POST and COOKIE.
     * @since 1.0
     */
    public function __construct($aArgs) {

        TestLogger::header();
        TestLogger::log('<span class="info">Initializing...</span>');
        $this->aModules = array();

        // save url args
        $this->aArgs = $aArgs;

        // save path to module(s)
        $this->setModulePath();

        // save list of test files
        $this->setTestFiles();

        // read modules from test files
        $this->readModules();

    }

    /**
     * Run the tests. Calls the run method in the TestModule classes as defined
     * in this TestSuite.
     * @since 1.0
     */
    public function run() {
        ob_implicit_flush();
        TestLogger::log('<span class="info">Running tests...</span>');
        foreach ($this->aModules as $oModule) {
            $oModule->run();
        }
        TestLogger::footer();
        TestLogger::writeReport();
    }

    // private methods --------------------------------------------------------

    /**
     * Get domain name which might be provided on the url to overrule the config.
     * @since 1.0
     */
    private function getDomain() {
        if (isset($this->aArgs['domain'])) {
            return $this->aArgs['domain'];
        } else {
            return '';
        }
    }

    /**
     * Get path to modules or modefule file. Assumes url args to be saved.
     * @since 1.0
     */
    private function setModulePath() {
        if (!isset($this->aArgs['mod']) || empty($this->aArgs['mod'])) {
            throw new ConfigException('Module or Modules path not defined');
        }
        $this->sModulePath = realpath(dirname($_SERVER["SCRIPT_FILENAME"]) . '/../tests/') . '/' .
                             Utils::stripTrailingSlash($this->aArgs['mod']);
    }

    /**
     * Read and prepare list of test files in a given path.
     * @since 1.0
     */
    private function setTestFiles() {

        // init module path, if necessary
        if (empty($this->sModulePath)) {
            $this->setModulePath();
        }

        // prepare list of test files
        $this->aTestFilenames = array();
        if (is_dir($this->sModulePath)) {

            // provided existing path to modules, gather test files
            if ($rDir = opendir($this->sModulePath)) {
                while (false !== ($sFilename = readdir($rDir))) {
                    if (Utils::getFileExt($sFilename) == TestSuite::TEST_EXTENSION) {
                        $sFullpath = $this->sModulePath . '/' . $sFilename;
                        if (is_readable($sFullpath)) {
                            $this->aTestFilenames[] = $sFullpath;
                        }
                    }
                }
                closedir($rDir);
            }

        } else {

            // assuming single file add extension, if necessary
            if (Utils::getFileExt($this->sModulePath) != TestSuite::TEST_EXTENSION) {
                $this->sModulePath .= '.' . TestSuite::TEST_EXTENSION;
            }

            // existing file?
            if (is_readable($this->sModulePath)) {

                // requested one single module, which is the test file
                $this->aTestFilenames[] = $this->sModulePath;

            } else {

                // requested unknown module
                throw new ConfigException('Unknown or unrecognized module path');

            }

        }

    }

    /**
     * Read test file(s) with definition of end-points and create list of modules.
     * @since 1.0
     */
    private function readModules() {

        // save list of test files, if not done before
        if (empty($this->aTestFilenames)) {
            $this->setTestFiles();
        }

        // root directory of tests
        $sTestRoot = $this->getTestRoot();

        // loop through test files
        foreach ($this->aTestFilenames as $sFilepath) {

            // read the xml file
            $oXDoc = new DOMDocument;
            $oXDoc->preserveWhiteSpace = false;
            $oXDoc->Load($sFilepath);

            // get list of modules and browse...
            $oXpath = new DOMXPath($oXDoc);
            $oXModule = $oXDoc->getElementsByTagName('module')->item(0);
            if (empty($oXModule)) {
                TestLogger::log('<span class="warn">No module(s) defined for ' . substr(strrchr($sFilepath, '/'), 1) . '</span>');
            }
            while ($oXModule) {

                // get module properties
                $sTitle = $oXpath->query('title', $oXModule)->item(0)->nodeValue;
                $oServer = $oXpath->query('server', $oXModule)->item(0);
                $sProtocol = $oServer->getAttribute('protocol');
                $iPort = (int) $oServer->getAttribute('port');
                $sGetDomain = $this->getDomain();
                $sServer = !empty($sGetDomain) ? $sGetDomain : $oServer->getAttribute('domain');
                $oXeMail = $oXpath->query('log-email', $oXModule);
                $sLogEmail = ($oXeMail->length > 0) ? $oXeMail->item(0)->nodeValue : '';
                $bLogEmailOnError = ($oXeMail->length > 0) ? $oXeMail->item(0)->getAttribute('on-error') : false;
                $oXLog = $oXpath->query('log-file', $oXModule);
                $sLogFile = ($oXLog->length > 0) ? $oXLog->item(0)->nodeValue : '';
                $bLogFileOnError = ($oXeMail->length > 0) ? $oXLog->item(0)->getAttribute('on-error') : false;

                // instantiate new test module
                $oModule = new TestModule($sTitle, $sProtocol, $sServer, $iPort, $sLogEmail, $bLogEmailOnError, $sLogFile, $bLogFileOnError);

                // get tests...
                $oXTest = $oXpath->query('tests/test', $oXModule)->item(0);
                if (empty($oXTest)) {
                    TestLogger::log('<span class="warn">No test(s) defined for ' . substr(strrchr($sFilepath, '/'), 1) . '</span>');
                }
                while ($oXTest) {

                    // ...next
                    if (get_class($oXTest) != 'DOMElement') {
                        $oXTest = $oXTest->nextSibling;
                        continue;
                    }

                    // get all test properties...
                    $bEnabled = !$oXTest->getAttribute('disabled');
                    if ($bEnabled) {
                        $sTitle = $oXpath->query('title', $oXTest)->item(0)->nodeValue;
                        $oXServerPath = $oXpath->query('path', $oXTest)->item(0);
                        $sPath = $oXServerPath->nodeValue;
                        $sUser = $oXServerPath->getAttribute('user');
                        $sPass = $oXServerPath->getAttribute('user');

                        // store test props (more later...)
                        $oTest = new TestItem($sTitle, $sPath);
                        if (!empty($sUser) && !empty($sPass)) {
                            $oTest->setAuth($sUser, $sPass);
                        }

                        // input props...
                        $oInput = new Input();
                        //$oInput->setBasePath($sTestRoot);
                        $oXInput = $oXpath->query('input', $oXTest)->item(0);
                        if (!empty($oXInput)) {

                            // only when input is defined...

                            // get request object and go set its initial state
                            $oRequest = new Request();

                            // - request-vars
                            $oXReqVars = $oXpath->query('request/vars/var', $oXInput);
                            $bHasRequestVars = $oXReqVars->length;
                            foreach ($oXReqVars as $oXReqVar) {
                                $oRequest->setRequestVar($oXReqVar->nodeValue,
                                                         null,
                                                         $oXReqVar->getAttribute('type'));
                            }

                            // - headers (can also contain cookies)
                            $oXHeaders = $oXpath->query('request/headers/header', $oXInput);
                            foreach ($oXHeaders as $oXHeader) {
                                $oRequest->addHeader($oXHeader->nodeValue);
                            }

                            // - body
                            $oXBody = $oXpath->query('request/body', $oXInput);
                            $bHasBody = $oXBody->length > 0;
                            if ($oXBody->length > 0) {
                                $oRequest->setBody($oXBody->item(0)->nodeValue,
                                                   $oXBody->item(0)->getAttribute('type'),
                                                   $sTestRoot,
                                                   $oXBody->item(0)->getAttribute('name'));
                            }

                            // save request object back in input
                            $oInput->setRequest($oRequest);

                            // plugins
                            $oPlugin = null;
                            $oXPlugins = $oXpath->query('plugins/plugin', $oXInput);
                            foreach ($oXPlugins as $oXPlugin) {
                                $oPlugin = PluginFactory::getPluginInstance($oXPlugin->nodeValue, $sTestRoot, $oPlugin);
                            }

                            $oInput->setPluginChain($oPlugin);

                        } else {

                            // save request object back in input
                            $oInput->setRequest(new Request());
                            
                        } // end if input

                        // add input to test
                        $oTest->setInput($oInput);

                        // output props...
                        $oOutput = new Output();
                        $oXOutput = $oXpath->query('output', $oXTest)->item(0);
                        if (!empty($oXOutput)) {

                            // only if defined...

                            // - validators
                            $oXValidators = $oXpath->query('validators/validator', $oXOutput);
                            if (!empty($oXValidators)) {
                                $oValidators = new ValidatorCollection($sTestRoot);
                                foreach ($oXValidators as $oXValidator) {
                                    $oValidators->addValidator($oXValidator->nodeValue,
                                                               $oXValidator->getAttribute('type'));
                                }
                                $oOutput->setValidatorCollection($oValidators);
                            }

                            // - plugins
                            $oPlugin = null;
                            $oXPlugins = $oXpath->query('plugins/plugin', $oXOutput);
                            foreach ($oXPlugins as $oXPlugin) {
                                $oPlugin = PluginFactory::getPluginInstance($oXPlugin->nodeValue, $sTestRoot, $oPlugin);
                            }

                            $oOutput->setPluginChain($oPlugin);

                        } // end if output

                        // add output to test
                        $oTest->setOutput($oOutput);

                        // save and...
                        $oModule->addTest($oTest);

                    } // end if enabled

                    // ...next
                    $oXTest = $oXTest->nextSibling;

                } // end while tests

                // save and next
                $this->aModules[] = $oModule;
                $oXModule = $oXModule->nextSibling;

            } // end module

        } // end foreach tests

    } // end method readModules

    /**
     * Get the path of the root directory of the requested test batch.
     * @return string Path
     * @since 1.0
     */
    private function getTestRoot() {
        return is_dir($this->sModulePath) ? $this->sModulePath : dirname($this->sModulePath);
    }

}

?>
