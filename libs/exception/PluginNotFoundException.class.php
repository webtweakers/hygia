<?php

/**
 * Plugin not found exception.
 * @author Bas van Gaalen
 * @since 1.0
 */
class PluginNotFoundException extends Exception {
   public function __construct($sMessage = null, $iCode = -1) {
       parent::__construct($sMessage, $iCode);
   }
}

?>
