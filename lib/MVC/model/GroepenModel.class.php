<?php

require_once 'MVC/model/entity/groepen/OpvolgbareGroep.abstract.php';

/**
 * GroepenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class GroepenModel extends PersistenceModel {

	protected function __construct() {
		parent::__construct('groepen/');
	}

}

class GroepLedenModel extends GroepenModel {

	const orm = 'GroepLid';

	protected static $instance;

	public function getLedenVoorGroep(Groep $groep) {
		return $this->find('groep_type = ? AND groep_id = ?', array(get_class($groep), $groep->id), 'lid_sinds ASC');
	}

}

class GroepCategorienModel extends GroepenModel {

	const orm = 'GroepCategorie';

	protected static $instance;

}

class CommissiesModel extends GroepenModel {

	const orm = 'Commissie';

	protected static $instance;

}

class BesturenModel extends GroepenModel {

	const orm = 'Bestuur';

	protected static $instance;

}

class SjaarciesModel extends GroepenModel {

	const orm = 'Sjaarcie';

	protected static $instance;

}

class OnderverenigingenModel extends GroepenModel {

	const orm = 'Ondervereniging';

	protected static $instance;

}

class WerkgroepenModel extends GroepenModel {

	const orm = 'Werkgroep';

	protected static $instance;

}

class WoonoordenModel extends GroepenModel {

	const orm = 'Woonoord';

	protected static $instance;

}

class ActiviteitenModel extends GroepenModel {

	const orm = 'Activiteit';

	protected static $instance;

}

class ConferentiesModel extends GroepenModel {

	const orm = 'Conferentie';

	protected static $instance;

}

class KetzersModel extends GroepenModel {

	const orm = 'Ketzer';

	protected static $instance;

}

class KetzerSelectorsModel extends GroepenModel {

	const orm = 'KetzerSelect';

	protected static $instance;

	public function getSelectorsVoorKetzer(Ketzer $ketzer) {
		return $this->find('ketzer_id = ?', array($ketzer->id));
	}

}

class KetzerOptiesModel extends GroepenModel {

	const orm = 'KetzerOptie';

	protected static $instance;

	public function getOptiesVoorSelect(KetzerSelect $select) {
		return $this->find('select_id = ?', array($select->select_id));
	}

}
