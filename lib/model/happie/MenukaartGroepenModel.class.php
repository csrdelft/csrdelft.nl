<?php

/**
 * MenukaartGroepenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Menukaart groepen CRUD.
 * 
 */
class HappieMenukaartGroepenModel extends CachedPersistenceModel {

	const orm = 'HappieMenukaartGroep';

	protected static $instance;

	protected function __construct() {
		parent::__construct('happie/');
		$this->default_order = 'gang ASC, naam ASC';
	}

	public function getGroep($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function newGroep() {
		$groep = new HappieMenuKaartGroep();
		$groep->titel = '';
		$groep->gang = null;
		$groep->aantal_beschikbaar = 0;
		return $groep;
	}

	public function create(PersistentEntity $groep) {
		$groep->groep_id = parent::create($groep);
		return $groep;
	}

}
