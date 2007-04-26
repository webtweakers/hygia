<?php

/**
 * When trying to use an unknown validator...
 * @author Bas van Gaalen
 * @since 1.0
 */
class UnknownValidatorTypeException extends Exception {
   public function __construct($sMessage = null, $iCode = -1) {
       parent::__construct($sMessage, $iCode);
   }
}

?>
