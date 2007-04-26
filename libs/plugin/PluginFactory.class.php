<?php

ns::import('hygia.libs.exception.PluginNotFoundException');

/**
 * The PluginFactory class instantiates Plugin's that are configured in the configuration files
 * and chains them together in a way that is similar to the Decorator Pattern.
 * @author Bas van Gaalen
 * @since 1.0
 */
abstract class PluginFactory {

    /**
     * Get a plugin instance. This methos takes care of loading, instantiating, initializing
     * and chaining the plugin.
     * @param $sPluginDef string Name of the plugin.
     * @param $sBasePath string Root path of the test case.
     * @param $oParent Plugin The plugin to chain to, or null in case of none.
     * @return Plugin The instantiated Plugin object.
     * @since 1.0
     */
    public static function getPluginInstance($sPluginDef, $sBasePath = '', $oParent = null) {

        // load plugin
        list($sType, $sPlugin) = explode('/', $sPluginDef);

        if (!class_exists($sPlugin, false)) {
            $sFilename = $sBasePath . '/plugins/' . $sType . '/' . $sPlugin . '.plugin.php';
            if (!is_readable($sFilename)) {
                throw new PluginNotFoundException('File does not exist or is not readable: ' . $sFilename);
            }
            include_once($sFilename);
        }

        // instantiate and initialize plugin; the latter will chain this plugin to the previous
        $oPlugin = new $sPlugin();
        $oPlugin->initialize($oParent, $sBasePath);
        return $oPlugin;
    }

}

?>
