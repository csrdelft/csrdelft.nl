<?php

class ConfigManagerTwoLine implements ConfigManagerConfigType {

    private $name;
    private $description;
    private $configFile;

    /**
     * @var helper_plugin_confmanager
     */
    private $helper;

    /**
     * @param string $name Display name
     * @param string $description Config file description
     * @param string $configFile config files
     */
    function __construct($name, $description = '', $configFile) {
        $this->configFile = $configFile;
        $this->description = $description;
        $this->name = $name;
        $this->helper = plugin_load('helper', 'confmanager');
    }

    /**
     * Get the name of the config file. Will be displayed in the admin menu.
     *
     * @return string single line
     */
    function getName() {
        return $this->name;
    }

    /**
     * Get a short description of the config file to show in the admin menu.
     * wiki text is allowed.
     *
     * @return string multi line
     */
    function getDescription() {
        return $this->description;
    }

    /**
     * get all paths to config file (local or protected).
     * this is used to generate the config id and warnings if the files are not writeable.
     *
     * @return array
     */
    function getPaths() {
        return array($this->configFile);
    }

    /**
     * Display the config file in some html view.
     * You have to provide input elements for values.
     * They will be embedded in a form to save changes.
     *
     * @return void
     */
    function display() {
        $local = confToHash($this->configFile);
        $default = array();
        $configs = $local;
        uksort($configs, array($this->helper, '_sortHuman'));

        include DOKU_PLUGIN . 'confmanager/tpl/showConfigTwoLine.php';
    }

    /**
     * this method can fetch the information from the fields generated in display().
     * it has to handle the correct writing process.
     *
     * @return void
     */
    function save() {
        global $INPUT;
        $keys = $INPUT->arr('keys');
        $values = $INPUT->arr('values');
        $newKey = $INPUT->arr('newKey');
        $newValue = $INPUT->arr('newValue');

        if (count($keys) !== count($values) || count($newKey) !== count($newValue)) {
            msg('invalid save arguments', -1);
            return;
        }

        $lines = array();
        if (!empty($keys)) {
            $lines = array_combine($keys, $values);
        }
        if (!empty($newKey)) {
            $lines = array_merge($lines, array_combine($newKey, $newValue));
        }

        uksort($lines, array($this->helper, '_sortConf'));

        $content = '';
        foreach ($lines as $key => $value) {
            $key = $this->helper->prepareEntity($key);
            $value = $this->helper->prepareEntity($value);

            $content .= "$key\t$value\n";
        }

        file_put_contents($this->configFile, $content);
        msg($this->helper->getLang('changes applied'), 1);
    }
}
