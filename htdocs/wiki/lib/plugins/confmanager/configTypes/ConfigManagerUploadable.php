<?php

interface ConfigManagerUploadable {

    /**
     * handle a uploaded image
     *
     * @abstract
     * @return boolean true on success false on error
     */
    function upload();

    /**
     * delete an uploaded icon
     *
     * @abstract
     * @return boolean true on success false on error
     */
    function deleteIcon();
}