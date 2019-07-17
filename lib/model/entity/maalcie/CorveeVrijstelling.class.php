<?php

namespace CsrDelft\model\entity\maalcie;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * CorveeVrijstelling.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 *
 * Een crv_vrijstelling instantie bevat het volgende per lid:
 *  - begindatum van de periode waarvoor de vrijstelling geldt
 *  - einddatum van de periode waarvoor de vrijstelling geldt
 *  - percentage van de corveepunten die in een jaar gehaald dienen te worden
 *
 * Wordt gebruikt bij de indeling van corveetaken om bijv. leden die
 * in het buitenland zitten niet in te delen gedurende die periode.
 *
 */
class CorveeVrijstelling extends PersistentEntity {
	public $uid;
	public $begin_datum;
	public $eind_datum;
	// TODO: Check percentage tussen 0 en 100 in controller
	public $percentage;

	public function getPunten() {
		return (int)ceil($this->percentage * intval(instelling('corvee', 'punten_per_jaar')) / 100);
	}

	protected static $table_name = 'crv_vrijstellingen';
	protected static $persistent_attributes = array(
		'uid' => array(T::UID),
		'begin_datum' => array(T::Date),
		'eind_datum' => array(T::Date),
		'percentage' => array(T::Integer)
	);

	protected static $primary_key = array('uid');
}
