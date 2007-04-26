<?php

ns::import('hygia.libs.sub.Utils');
ns::import('hygia.libs.core.TestConfig');

/**
 * Buffered Logger class. Provides logging facilities for reporting to
 * screen, email, file. This is an implicit singleton class and is
 * specifically written for this application.
 * @author Bas van Gaalen
 * @since 1.0
 */
class TestLogger {

    private static $oInstance;
    private $bImplicitScreenWrites;
    private $aBuffer;
    private $sReport;

    /**
     * Private constructor initializes the TestLogger object.
     * @param $bImplicitScreenWrites bool True if message should be written to screen directly.
     * @since 1.0
     */
    private function __construct($bImplicitScreenWrites = true) {
        $this->bImplicitScreenWrites = $bImplicitScreenWrites;
        $this->aBuffer = array();
        $this->sReport = '';
    }

    /**
     * Get an instance of the TestLogger object. May only be used privately.
     * @param $bImplicitScreenWrites bool True if message should be written to screen directly.
     * @return TestLogger An instance of the TestLogger class
     * @since 1.0
     */
    private static function getInstance($bImplicitScreenWrites = true) {
        if (!isset(self::$oInstance)) {
            self::$oInstance = new TestLogger($bImplicitScreenWrites);
        }
        return self::$oInstance;
    }

    /**
     * Log message (with HTML markup) to buffer and optionally to screen.
     * @param $sMessage string The message to log.
     * @param $bImplicitScreenWrites bool True if message should be written to screen directly.
     * @since 1.0
     */
    public static function log($sMessage = '', $bImplicitScreenWrites = true) {
        $oLog = TestLogger::getInstance($bImplicitScreenWrites);
        $oLog->aBuffer[] = $sMessage;
        if (!empty($sMessage)) {
            TestLogger::addReport($sMessage);
            if ($bImplicitScreenWrites) {
                print $sMessage . "\r\n";
            }
        }
    }

    /**
     * Clears the internal buffer and returns the contents.
     * @return Buffer string Contents before cleaned.
     * @since 1.0
     */
    public static function flush() {
        $oLog = TestLogger::getInstance();
        $sLog = $oLog->getBuffer();
        $oLog->aBuffer = array();
        return $sLog;
    }

    /**
     * Send the current log buffer to provided email address.
     * @param $sEmailAddress string The email address to send the test report to.
     * @return bool True on success, false on failure.
     * @since 1.0
     */
    public static function sendToMail($sEmailAddress) {
        $sLog = Utils::stripHtml(TestLogger::getInstance()->getBuffer());
        return @mail($sEmailAddress, 'Test report', $sLog, 'From: bas@webtweakers.com');
    }

    /**
     * Write the current log buffer to given file name.
     * @param $sFilename string The filename of the file to write the report to.
     * @return bool True on success, false on failure.
     * @since 1.0
     */
    public static function sendToFile($sFilename) {
        $sLog = Utils::stripHtml(TestLogger::getInstance()->getBuffer());
        return @file_put_contents($sFilename, $sLog, FILE_APPEND) > 0;
    }

    /**
     * Get current log buffer as a string.
     * @return string Log buffer.
     * @since 1.0
     */
    private function getBuffer() {
        return implode("\r\n", $this->aBuffer);
    }

    /**
     * Print HTML header information - for use when logging to browser.
     * @return string
     * @since 1.0
     */
    public static function header() {
        $sApp = TestConfig::ApplicationTitle . ' ' . TestConfig::ApplicationVersion;
        $a = array();
        $a[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"';
        $a[] = '    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $a[] = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
        $a[] = '<head>';
        $a[] = '    <title>' . $sApp . '</title>';
        $a[] = '    <link type="text/css" rel="stylesheet" href="' . TestConfig::ApplicationWebRoot . 'css/main.css" />';
        $a[] = '    <script type="text/javascript" src="' . TestConfig::ApplicationWebRoot . 'js/main.js"></script>';
        $a[] = '</head>';
        $a[] = '<body>';
        $a[] = '<h1>' . $sApp . '</h1>';
        $s = implode("\r\n", $a);
        TestLogger::addReport($s);
        print $s;
    }

    /**
     * Print HTML footer - for use when logging to browser.
     * @return string
     * @since 1.0
     */
    public static function footer() {
        $a = array();
        $a[] = '</body>';
        $a[] = '</html>';
        $s = implode("\r\n", $a);
        TestLogger::addReport($s);
        print $s;
    }

    /**
     * Add text to the html report.
     * @param $sText string Text to add.
     */
    public static function addReport($sText) {
        TestLogger::getInstance()->sReport .= $sText;
    }

    /**
     * Get complete report in html.
     * @return string The report.
     * @since 1.0
     */
    public static function getReport() {
        return TestLogger::getInstance()->sReport;
    }

    /**
     * Write the complete html report to file.
     * @return bool True on success
     * @since 1.0
     */
    public static function writeReport() {
        return @file_put_contents(TestConfig::ReportFilePath . '-' . date('Ymd\THi') . '.html', TestLogger::getReport()) > 0;
    }

}

?>
