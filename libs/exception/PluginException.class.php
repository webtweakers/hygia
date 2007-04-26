<?php

/**
 * Used for general exceptions in the plugins.
 * @author Bas van Gaalen
 * @since 1.0
 */
class PluginException extends Exception {
   public function __construct($sMessage = null, $iCode = -1) {
       parent::__construct($sMessage, $iCode);
   }
}

?>
