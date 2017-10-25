<?php

namespace CsrDelft\model\entity\mededelingen;

use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;


/**
 * MededelingCategorie.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class MededelingCategorie extends PersistentEntity {

	public $id;
	public $naam;
	public $prioriteit;
	public $permissie;
	public $plaatje;
	public $beschrijving;

	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'naam' => array(T::String),
		'prioriteit' => array(T::Integer),
		'permissie' => array(T::Enumeration, false, MededelingAccess::class),
		'plaatje' => array(T::String),
		'beschrijving' => array(T::Text, true)
	);

	protected static $primary_key = array('id');

	protected static $table_name = 'mededelingcategorie';

	public function magUitbreiden() {
		return LoginModel::mag($this->permissie);
	}

}
