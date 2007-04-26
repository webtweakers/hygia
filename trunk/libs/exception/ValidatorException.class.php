<?php

/**
 * Used for general exceptions in the validators.
 * @author Bas van Gaalen
 * @since 1.0
 */
class ValidatorException extends Exception {
   public function __construct($sMessage = null, $iCode = -1) {
       parent::__construct($sMessage, $iCode);
   }
}

?>
