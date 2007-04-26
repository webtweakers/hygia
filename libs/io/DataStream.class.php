<?php

ns::import('hygia.libs.plugin.Plugin');

/**
 * Super class for Input and Output data streams.
 * Basically keeps track of the plugin chain.
 * @author Bas van Gaalen
 * @since 1.0
 */
class DataStream {

    private $oPluginChain;

    /**
     * Constructor initializes objects.
     * @since 1.0
     */
    public function __construct() {
        $this->oPluginChain = null;
    }

    /**
     * Set a reference to the plugin chain, as set up through the config files.
     * @param $oPluginChain Plugin One or more linked plugins.
     * @since 1.0
     */
    public function setPluginChain($oPluginChain) {
        $this->oPluginChain = $oPluginChain;
    }

    /**
     * Get reference to plugin chain.
     * @return Plugin One ore more linked plugins.
     * @since 1.0
     */
    public function getPlugin() {
        return $this->oPluginChain;
    }

}

?>
