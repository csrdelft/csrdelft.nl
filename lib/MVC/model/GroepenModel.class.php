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

class GroepCategorienModel extends GroepenModel {

	const orm = 'GroepCategorie';

	protected static $instance;

}

class GroepLedenModel extends GroepenModel {

	const orm = 'GroepLid';

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

class KetzersModel extends GroepenModel {

	const orm = 'Ketzer';

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
