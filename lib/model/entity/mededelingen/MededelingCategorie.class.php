<?php

require_once 'model/entity/mededelingen/MededelingAccess.enum.php';

/**
 * MededelingCategorie.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class MededelingCategorie extends PersistentEntity {

    public $naam;
    public $prioriteit;
    public $permissie;
    public $plaatje;
    public $beschrijving;

    protected static $persistent_attributes = array(
        'naam'	        => array(T::String),
        'prioriteit'    => array(T::Integer),
        'permissie'     => array(T::Enumeration, false, 'MededelingAccess'),
        'plaatje'       => array(T::String),
        'beschrijving'  => array(T::Text, true)
    );

    protected static $table_name = 'mededelingcategorie';

    public function magUitbreiden() {
        return LoginModel::mag($this->permissie);
    }

}
