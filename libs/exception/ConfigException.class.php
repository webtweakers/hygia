<?php

/**
 * Used for exceptions while reading the configuration XML files.
 * @author Bas van Gaalen
 * @since 1.0
 */
class ConfigException extends Exception {
   public function __construct($sMessage = null, $iCode = -1) {
       parent::__construct($sMessage, $iCode);
   }
}

?>
