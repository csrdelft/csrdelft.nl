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

//    public function __construct($init) {
//        if (is_array($init)) {
//            $this->array2properties($init);
//        } else {
//            $init = (int) $init;
//            if ($init != 0) {
//                $this->load($init);
//            } else {
//                //default waarden
//            }
//        }
//    }

    public function load($id = 0) {
        $db = MijnSqli::instance();
        $loadQuery = "
			SELECT id, naam, prioriteit, permissie, plaatje, beschrijving
			FROM mededelingcategorie
			WHERE id=" . (int) $id . ";";
        $mededeling = $db->getRow($loadQuery);
        if (!is_array($mededeling)) {
            throw new Exception('MededelingCategorie bestaat niet. (MededelingCategorie::load() met id=' . $id . ')');
        }
        $this->array2properties($mededeling);
    }

    public function loadMededelingen() {
        $query = "SELECT id FROM mededelingen WHERE categorie=" . $this->getId() . ";";

        $this->mededelingen = MijnSqli::instance()->query($query);
    }

//    public function save() {
//        throw new Exception('Nog niet geïmplementeerd');
//        $db = MijnSqli::instance();
//        if ($this->getId() == 0) {
//            $saveQuery = "
//				INSERT INTO mededelingcategorie (
//					naam, prioriteit, plaatje, beschrijving
//				)VALUES(
//					'" . $db->escape($this->getNaam()) . "',
//					'" . $db->escape($this->getPrioriteit()) . "',
//					'" . $db->escape($this->getPlaatje()) . "',
//					'" . $db->escape($this->getBeschrijving()) . "'
//				)";
//        } else {
//            $saveQuery = "
//				UPDATE mededelingcategorie
//				SET
//					naam='" . $db->escape($this->getNaam()) . "',
//					prioriteit='" . $db->escape($this->getPrioriteit()) . "',
//					plaatje='" . $db->escape($this->getPlaatje()) . "',
//					beschrijving='" . $db->escape($this->getBeschrijving()) . "'
//				WHERE id=" . $this->getId() . "
//				LIMIT 1;";
//        }
//    }

//    public function delete() {
//        throw new Exception('Nog niet geïmplementeerd MededelingCategorie::delete()');
//    }
//
//    public function array2properties($array) {
//        $this->id = $array['id'];
//        $this->naam = $array['naam'];
//        $this->prioriteit = $array['prioriteit'];
//        $this->permissie = $array['permissie'];
//        $this->plaatje = $array['plaatje'];
//        $this->beschrijving = $array['beschrijving'];
//    }
//
//    public function getMededelingen($force = false) {
//        if ($force OR $this->mededelingen === null) {
//            //load
//            $this->loadMededelingen();
//        }
//        return $this->mededelingen;
//    }
//
//    public function getId() {
//        return $this->id;
//    }
//
//    public function getNaam() {
//        return $this->naam;
//    }
//
//    public function getPrioriteit() {
//        return $this->prioriteit;
//    }
//
//    public function getPlaatje() {
//        return $this->plaatje;
//    }
//
//    public function getBeschrijving() {
//        return $this->beschrijving;
//    }
//
    public function magUitbreiden() {
        return LoginModel::mag($this->permissie);
    }
}
