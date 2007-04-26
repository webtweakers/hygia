<?php

/**
 * Hygia: Web Application Test Suite
 * Written by Bas van Gaalen, December 2006 - April 2007
 * 
 * Requirements:
 * - PHP 5.2.0: older versions seem to have problems with handling the xml/xsd and crash Apache
 * - LibXML version 2.6.26 with xpath and schema support enabled (full dom/xml support)
 * - The test suite uses getcwd() so make sure the proper directory rights have been set
 * - Support for sockets
 * - OpenSSL, if needed by the test definition (in case of https protocol)
 */

// let script run indefinetely
set_time_limit(0);
include_once('../libs/sub/ns.class.php');

// initialize test suite and start it
try {
    ns::import('hygia.libs.core.TestSuite');
    $oTest = new TestSuite($_REQUEST);
    $oTest->run();
} catch (Exception $e) {
    print '<span class="warn">' . $e->getMessage() . '</span>';
}

?>
