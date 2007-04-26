<?php

ns::import('hygia.libs.sub.Buffer');
ns::import('hygia.libs.io.Request');
ns::import('hygia.libs.io.Response');

/**
 * Plugins that work on the Request data should implement the IInputPlugin interface.
 * @author Bas van Gaalen
 * @since 1.0
 */
interface IInputPlugin {
    public function run(Request $oRequest);
}

/**
 * Plugins that work on the Response data should implement the IOutputPlugin interface.
 * @author Bas van Gaalen
 * @since 1.0
 */
interface IOuputPlugin {
    public function run(Response $oResponse);
}

/**
 * Super class for plugins. Plugin classes should extend
 * Plugin and implement the IPlugin interface.
 * Plugins should also call the parent::__construct (the one below)
 * @author Bas van Gaalen
 * @since 1.0
 */
class Plugin {

    private $oParent; // reference instance to object being decorated with this plugin
    private $oBuffer;
    private $sBasePath;

    /**
     * Constructor: prepars local private variables for usage.
     * @since 1.0
     */
    public function __construct() {
        $this->oBuffer = Buffer::getInstance();
    }

    /**
     * Initialize the plugin, called from PluginFactory and hidden from child classes
     * (may not be overriden!)
     * @param $oParent Parent Plugin or null of none.
     * @param $sBasePath string Path to use in plugin
     * @since 1.0
     */
    public final function initialize($oParent = null, $sBasePath = '') {
        $this->oParent = $oParent;
        $this->sBasePath = $sBasePath;
    }

    /**
     * Save a name/value pair to be able to construct flows.
     * @param $vName string Variable name.
     * @param $vName array Literal list of name/value pairs.
     * @param $sValue string When $vName is string, this is the value.
     * @since 1.0
     */
    protected function setVar($vName, $sValue = null) {
        $this->oBuffer->set($vName, $sValue);
    }

    /**
     * Retrieve a value by name.
     * @param $sName string Name of the variable to retrieve.
     * @since 1.0
     */
    protected function getVar($sName) {
        return (string) $this->oBuffer->get($sName);
    }

    /**
     * This method basically runs either the Request or Response object through
     * the Plugin chain. It calls the parent Plugin with the provided Request/Response
     * object (which is either an empty Request object at the start or a filled
     * Response object that came from the server under test).
     * @param $oRequestResponse Either the Request or Response object, depending on
     *        the type of Plugin.
     * @return object Either the Request or Response object, depending on the type of Plugin.
     * @since 1.0
     */
    public final function getRequestResponse($oRequestResponse) {
        if (!is_null($this->oParent)) {
            $oRequestResponse = $this->oParent->getRequestResponse($oRequestResponse);
        }
        $sCurPath = getcwd();
        chdir($this->sBasePath);
        $oRequestResponse = $this->run($oRequestResponse);
        chdir($sCurPath);
        return $oRequestResponse;
    }

}

?>
