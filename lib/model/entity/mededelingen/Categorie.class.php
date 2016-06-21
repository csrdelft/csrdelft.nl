<?php

require_once 'model/entity/mededelingen/MededelingAccess.enum.php';

/**
 * MededelingCategorieModel.class.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
class Categorie extends PersistentEntity {

    public $id;
    public $naam;
    public $prioriteit;
    public $permissie;
    public $plaatje;
    public $beschrijving;
    public $mededelingen = null;

    protected static $persistent_attributes = array(
        'id'		    => array(T::Integer, false, 'auto_increment'),
        'naam'	        => array(T::String),
        'prioriteit'    => array(T::Integer),
        'permissie'     => array(T::Enumeration, false, 'MededelingAccess'),
        'plaatje'       => array(T::String),
        'beschrijving'  => array(T::Text, true)
    );

    protected static $primary_key = array('id');

    protected static $table_name = 'mededelingcategorie';

    public function magUitbreiden() {
        return LoginModel::mag($this->permissie);
    }
}
