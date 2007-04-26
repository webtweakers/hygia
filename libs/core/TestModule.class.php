<?php

ns::import('hygia.libs.core.TestLogger');
ns::import('hygia.libs.core.TestItem');

/**
 * TestModule class. Defines the properties of a single module and provides
 * a run method to run the tests in this module.
 * @author Bas van Gaalen
 * @since 1.0
 */
class TestModule {

    private $aTests;
    private $sTitle;
    private $sProtocol;
    private $sServer;
    private $iPort;
    private $sEmail;
    private $bEmailOnError;
    private $sFile;
    private $bFileOnError;

    /**
     * Constructor initializes the module.
     * @param $sTitle string Module title
     * @param $sProtocol string Protocol to use when setting up socket connection (http/https).
     * @param $sServer string Server's domain name.
     * @param $iPort int Port number to connect to on server.
     * @param $sEmail string Email address to mail report to.
     * @param $bEmailOnError bool If true, only send emails in case of an error in the report.
     * @param $sFile string Filename to write report to.
     * @param $bFileOnError bool IF true, only write to module log in case of an error in the report.
     * @since 1.0
     */
    public function __construct($sTitle = '', $sProtocol = '', $sServer = '', $iPort = 0,
                                $sEmail = '', $bEmailOnError = false, $sFile = '', $bFileOnError = false) {
        $this->aTests = array();
        $this->setTitle($sTitle);
        $this->setProtocol($sProtocol);
        $this->setServer($sServer);
        $this->setPort($iPort);
        $this->setEmail($sEmail);
        $this->bEmailOnError = $bEmailOnError;
        $this->setFile($sFile);
        $this->bFileOnError = $bFileOnError;
    }

    /**
     * Add a test to this module.
     * @param $oTest TestItem A single TestItem object
     * @since 1.0
     */
    public function addTest(TestItem $oTest) {
        $this->aTests[] = $oTest;
    }

    /**
     * Set title of this module
     * @param $sTitle string Title of this module
     * @since 1.0
     */
    public function setTitle($sTitle) {
        $this->sTitle = $sTitle;
    }

    /**
     * Set protocol (http/https) to use in tests.
     * Currently this can only be set on a per-module basis.
     * @param $sProtocol string Protocol to be used for the defined server.
     * @since 1.0
     */
    public function setProtocol($sProtocol) {
        $this->sProtocol = $sProtocol;
    }

    /**
     * Set server domain name for use in tests.
     * @param $sServer string Server domain name.
     * @since 1.0
     */
    public function setServer($sServer) {
        $this->sServer = $sServer;
    }

    /**
     * Set port for this server.
     * @param $iPort int Port to be used.
     * @since 1.0
     */
    public function setPort($iPort) {
        $this->iPort = $iPort;
    }

    /**
     * Set log email address.
     * @param $sEmail string Reports for this module will be emailed to this address.
     * @since 1.0
     */
    public function setEmail($sEmail) {
        $this->sEmail = $sEmail;
    }

    /**
     * Set log file.
     * @param $sFile string Reports for this module will be written to this file.
     * @since 1.0
     */
    public function setFile($sFile) {
        $this->sFile = $sFile;
    }

    /**
     * Run the tests in this module. Calls the run method in the TestItem classes
     * that are defined in this module.
     * Also takes care of logging to screen, mail and/or file.
     * @since 1.0
     */
    public function run() {

        // prepare logger
        TestLogger::flush();
        TestLogger::log('<br />');
        TestLogger::log('<h2>Module: ' . $this->sTitle . '</h2>');
        TestLogger::log('<span class="debug">[' . date('j M Y @ H:i') . '] ' .
                        '<b>' . Utils::getNoun(count($this->aTests), 'test', 'tests') . '</b> defined' .
                        (!empty($this->sEmail) ? ', report will be mailed to <i>' . $this->sEmail . '</i>' : '') .
                        (!empty($this->sFile) ? ', report will be appended to <i>' . $this->sFile . '</i>' : '') .
                        '</span>');

        // run the tests
        $bRunOk = true;
        foreach ($this->aTests as $oTest) {
            if (!$oTest->run($this->sProtocol, $this->sServer, $this->iPort)) {
                $bRunOk = false;
            }
        }
        TestLogger::log('');

        // deliver report, if requested
        if (!empty($this->sEmail)) {
            if ((!$bRunOk && $this->bEmailOnError) || !$this->bEmailOnError) {
                TestLogger::sendToMail($this->sEmail);
            }
        }
        if (!empty($this->sFile)) {
            if ((!$bRunOk && $this->bFileOnError) || !$this->bFileOnError) {
                TestLogger::sendToFile($this->sFile);
            }
        }

    }

}

?>
