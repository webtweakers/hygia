<?php

ns::import('hygia.libs.plugin.Plugin');
ns::import('hygia.libs.exception.PluginException');
ns::import('hygia.libs.io.Response');

class GetChallenge extends Plugin implements IOuputPlugin {

    public function run(Response $oResponse) {
        
        // get the challenge from the html form
        if (preg_match('/value\="([a-z0-9]+)"/i', $oResponse->getBody(), $aMatches)) {
        	$this->setVar('challenge', $aMatches[1]);
        }

        // get the session cookie
        $this->setVar('session_cookie', $oResponse->getHeaderValue('set-cookie'));

        return $oResponse;
    }

}

?>
