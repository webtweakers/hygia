<?php

/**
 * NameSpace class that provides one single method to include modules that allows
 * project wide module imports in a consistant way. This is an abstract class.
 * @author Bas van Gaalen
 * @since 1.0
 */
abstract class ns {

    /**
     * Import a given module (class file).
     * This method includes a given class file relatively to the project's root.
     * File extensions should be omitted (.class.php) and directories and filename
     * should be seperated by a dot.
     * 
     * @param $sModulePath string Path + name of the module to include.
     * @return bool True on success, false otherwise.
     * @since 1.0
     */
    public static function import($sModulePath = '') {

        // get last section of import path: module name
        $sModuleName = substr(strrchr($sModulePath, '.'), 1);

        // check to see if module is already imported
        if (class_exists($sModuleName)) return false;

        // get first section of import path: package name
        $sPackageName = substr($sModulePath, 0, strpos($sModulePath, '.'));

        // directory of current file (this one)
        $strFileDir = dirname(__FILE__);

        // package root directory
        $strRoot = substr($strFileDir, 0, strpos($strFileDir, $sPackageName));

        // full path name to requested module
        $strFullPath = $strRoot . str_replace('.', DIRECTORY_SEPARATOR, $sModulePath) . '.class.php';

        // check if file exists
        if (!file_exists($strFullPath)) return false;

        // include it
        return include_once($strFullPath);
    }

}

?>
