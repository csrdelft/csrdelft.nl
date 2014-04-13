<?php

require_once 'MVC/model/entity/GroepCategorie.class.php';

/**
 * GroepenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepenModel extends PersistenceModel {

	const orm = 'GroepCategorie';

	protected static $instance;

}

class GroepLedenModel extends PersistenceModel {

	const orm = 'GroepLid';

	protected static $instance;

}

class CommissiesModel extends PersistenceModel {

	const orm = 'Commissie';

	protected static $instance;

}

class BesturenModel extends PersistenceModel {

	const orm = 'Bestuur';

	protected static $instance;

}

class SjaarciesModel extends PersistenceModel {

	const orm = 'Sjaarcie';

	protected static $instance;

}

class OnderverenigingenModel extends PersistenceModel {

	const orm = 'Ondervereniging';

	protected static $instance;

}

class WerkgroepenModel extends PersistenceModel {

	const orm = 'Werkgroep';

	protected static $instance;

}

class WoonoordenModel extends PersistenceModel {

	const orm = 'Woonoord';

	protected static $instance;

}

class KetzersModel extends PersistenceModel {

	const orm = 'Ketzer';

	protected static $instance;

}

class ActiviteitenModel extends PersistenceModel {

	const orm = 'Activiteit';

	protected static $instance;

}

class ConferentiesModel extends PersistenceModel {

	const orm = 'Conferentie';

	protected static $instance;

}
