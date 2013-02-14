<?php

/**
 * @author Dominik Eckelmann
 */
interface ConfigManagerConfigType {

    /**
     * Get the name of the config file. Will be displayed in the admin menu.
     *
     * @abstract
     * @return string single line
     */
    function getName();

    /**
     * Get a short description of the config file to show in the admin menu.
     * wiki text is allowed.
     *
     * @abstract
     * @return string multi line
     */
    function getDescription();

    /**
     * get all paths to config file (local or protected).
     * this is used to generate the config id and warnings if the files are not writeable.
     *
     * @abstract
     * @return array
     */
    function getPaths();

    /**
     * Display the config file in some html view.
     * You have to provide input elements for values.
     * They will be embedded in a form to save changes.
     *
     * @abstract
     * @return
     */
    function display();

    /**
     * this method can fetch the information from the fields generated in display().
     * it has to handle the correct writing process.
     *
     * @abstract
     * @return
     */
    function save();
}
