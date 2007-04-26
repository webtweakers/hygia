<?php

/**
 * Application configuration class (abstract).
 * @author Bas van Gaalen
 * @since 1.0
 */
abstract class TestConfig {

    // application title and version
    const ApplicationTitle          = 'Hygia Web Application Test Suite';
    const ApplicationVersion        = '0.9';

    // web root of hygia, used in html header
    const ApplicationWebRoot        = 'http://hygia/';

    // path and file name (excluding extension) of html reports
    const ReportFilePath            = '/var/log/hygia-report';

    // show debug content with a binary percentage below this treshhold
    const ShowBinaryPercTresh       = 10;

    // show only content below this treshhold (number of bytes)
    const ShowContentLengthTresh    = 1024;

}

?>
