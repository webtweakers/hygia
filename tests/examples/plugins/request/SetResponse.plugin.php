<?php

ns::import('hygia.libs.plugin.Plugin');
ns::import('hygia.libs.exception.PluginException');
ns::import('hygia.libs.io.Request');
ns::import('hygia.libs.io.var.Variable');

class SetResponse extends Plugin implements IInputPlugin {

    public function run(Request $oRequest) {

        // set response and clear pass (mimic JavaScript submit_form behavior)
        $sResponse = $this->getVar('challenge') . $oRequest->getRequestVar('pass');
        $oRequest->setRequestVar('response', $sResponse, Variable::TYPE_POST);
        $oRequest->setRequestVar('pass', '');
        
        // set session cookie
        $oRequest->addHeader('Cookie: ' . $this->getVar('session_cookie'));

        return $oRequest;
    }

}

?>
