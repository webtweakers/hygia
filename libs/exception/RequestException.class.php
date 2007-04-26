<?php

/**
 * Used for exceptions during the request.
 * @author Bas van Gaalen
 * @since 1.0
 */
class RequestException extends Exception {
   public function __construct($sMessage = null, $iCode = -1) {
       parent::__construct($sMessage, $iCode);
   }
}

?>
