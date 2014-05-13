<?php

class ConfigManagerTwoLineLeftImageConfigCascade extends ConfigManagerTwoLineCascadeConfig implements ConfigManagerUploadable {

    private $imageFolder;
    private $extension;

    public function __construct($name, $imageFolder, $extension) {
        parent::__construct($name);
         $this->setImageFolder($imageFolder);
        $this->extension = explode(',',$extension);
    }

    public function display() {
        $configs = $this->readConfig();
        $default = $configs['default'];
        $local = $configs['local'];
        $configs = array_merge($default, $local);

        uksort($configs, array($this->helper, '_sortHuman'));
        include DOKU_PLUGIN . 'confmanager/tpl/showConfigTwoLineLeftImage.php';
    }

    private function getImage($key) {
        foreach($this->extension as $ext){
        $path = $this->imageFolder . "$key." . $ext;
        if (is_file($path)) {
            return DOKU_BASE . $path;
        }
        }
        return '';
    }

    public function setImageFolder($imageFolder) {
        if (substr($imageFolder, strlen($imageFolder) -1) !== '/') {
            $imageFolder = "$imageFolder/";
        }
        $this->imageFolder = $imageFolder;
    }

    public function upload() {
        global $INPUT;
        if (!isset($_FILES['icon'])) {
            header('Content-Type: text/plain');
            echo $this->helper->getLang('upload_errNoFileSend');
            return false;
        }
        $icon = $_FILES['icon'];
        $key = $INPUT->str('key');
        if ($key === '') {
            header('Content-Type: text/plain');
            echo $this->helper->getLang('upload_errNoConfigKeySend');
            return false;
        }
        $configs = $this->readConfig();
        if (isset($configs['default'][$key])) {
            header('Content-Type: text/plain');
            echo $this->helper->getLang('upload_errCannotOverwriteDefaultKey');
            return false;
        }

        if ($icon['error'] != UPLOAD_ERR_OK) {
            header('Content-Type: text/plain');
            echo $this->helper->getLang('upload_errUploadError');
            return false;
        }

        $extension = strrpos($icon['name'], '.');
        if ($extension === false) {
            header('Content-Type: text/plain');
            echo $this->helper->getLang('upload_errNoFileExtension');
            return false;
        }
        $extension = substr($icon['name'], $extension+1);
        if (!in_array($extension, $this->extension)) {
            header('Content-Type: text/plain');
            echo $this->helper->getLang('upload_errWrongFileExtension');
            return false;
        }

        if (!@move_uploaded_file($icon['tmp_name'], DOKU_INC . $this->imageFolder . "$key." . $extension)) {
            header('Content-Type: text/plain');
            echo $this->helper->getLang('upload_errCannotMoveUploadedFileToFolder');
            return false;
        }

        return true;
    }

    function deleteIcon() {
        global $INPUT;

        $key = $INPUT->str('key');
        if ($key === '') {
            header('Content-Type: text/plain');
            echo $this->helper->getLang('upload_errNoConfigKeySend');
            return false;
        }

        $configs = $this->readConfig();
        if (isset($configs['default'][$key])) {
            header('Content-Type: text/plain');
            echo $this->helper->getLang('upload_errCannotOverwriteDefaultKey');
            return false;
        }
        if (!@unlink(DOKU_INC . $this->imageFolder . "$key." . $extension)) {
            echo $this->helper->getLang('iconDelete_error');
            return false;
        }

        return true;
    }


}
