<?php

/**
 * Validator not found exception.
 * @author Bas van Gaalen
 * @since 1.0
 */
class ValidatorNotFoundException extends Exception {
   public function __construct($sMessage = null, $iCode = -1) {
       parent::__construct($sMessage, $iCode);
   }
}

?>
