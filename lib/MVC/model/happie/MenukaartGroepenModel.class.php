<?php

/**
 * MenukaartGroepenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Menukaart groepen CRUD.
 * 
 */
class HappieMenukaartGroepenModel extends PersistenceModel {

	const orm = 'HappieMenukaartGroep';

	protected static $instance;

	protected function __construct() {
		parent::__construct('happie/');
	}

	public function getGroep($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function newGroep($titel, $gang) {
		$groep = new HappieMenuKaartGroep();
		$groep->titel = $titel;
		$groep->gang = $gang;
		$groep->groep_id = $this->create($groep);
		return $groep;
	}

}
