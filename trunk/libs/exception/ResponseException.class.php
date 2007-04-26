<?php

/**
 * Used for exceptions during the response.
 * @author Bas van Gaalen
 * @since 1.0
 */
class ResponseException extends Exception {
   public function __construct($sMessage = null, $iCode = -1) {
       parent::__construct($sMessage, $iCode);
   }
}

?>
