<?php

namespace CsrDelft\model\entity\maalcie;

use CsrDelft\model\maalcie\FunctiesModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * CorveeRepetitie.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 *
 * Een crv_repetitie instantie beschrijft een corvee taak die periodiek moet worden uitgevoerd als volgt:
 *  - uniek identificatienummer
 *  - bij welke maaltijdrepetitie deze periodieke taken horen (optioneel)
 *  - op welke dag van de week dit moet gebeuren
 *  - na hoeveel dagen dit opnieuw moet gebeuren
 *  - welke functie deze periodieke taak inhoud (bijv. kwalikok)
 *  - standaard aantal punten dat een lid krijgt voor deze periodieke taak
 *  - standaard aantal mensen dat deze periodieke taak moeten uitvoeren (bijv. 1 kwalikok, 2 hulpkoks, etc.)
 *  - of deze periodieke taak als voorkeur kan worden opgegeven (bijv. kwalikok is niet voorkeurbaar)
 *
 * Bij het koppelen van corvee-repetities aan een maaltijd-repetitie maakt het mogelijk om bij het aanmaken van
 * een maaltijd automatisch ook corveetaken aan te maken.
 * Deze klasse weet dus welke en hoeveel corvee-functies er bij welke maaltijd-repetitie horen,
 * in verband met het later toewijzen van corvee-functies als taak aan een of meerdere leden.
 * Een maaltijd die los wordt aangemaakt, dus niet vanuit een maaltijd-repetitie, krijgt dus geen standaard corvee-taken.
 * Deze zullen met de hand moeten worden toegevoegd. Daarbij kan gebruik gemaakt worden van de dag van de week
 * van de maaltijd en te kijken naar de dag van de week van corvee-repetities.
 * Een lid kan een voorkeur aangeven voor een corvee-repetitie.
 *
 *
 * Zie ook CorveeTaak.class.php
 *
 */
class CorveeRepetitie extends PersistentEntity {
	# primary key

	public $crv_repetitie_id; # int 11
	public $mlt_repetitie_id; # foreign key mlt_repetitie.id
	/**
	 * 0: zondag
	 * 6: zaterdag
	 * @var int
	 */
	public $dag_vd_week; # int 1
	public $periode_in_dagen; # int 11
	public $functie_id; # foreign key crv_functie.id
	public $standaard_punten; # int 11
	public $standaard_aantal; # int 11
	public $voorkeurbaar; # boolean

	public function getDagVanDeWeekText() {
		return strftime('%A', ($this->dag_vd_week + 3) * 24 * 3600);
	}

	public function getPeriodeInDagenText() {
		switch ($this->periode_in_dagen) {
			case 0:
				return '-';
			case 1:
				return 'elke dag';
			case 7:
				return 'elke week';
			default:
				if ($this->periode_in_dagen % 7 === 0) {
					return 'elke ' . ($this->periode_in_dagen / 7) . ' weken';
				} else {
					return 'elke ' . $this->periode_in_dagen . ' dagen';
				}
		}
	}

	/**
	 * Lazy loading by foreign key.
	 *
	 * @return CorveeFunctie
	 */
	public function getCorveeFunctie() {
		return FunctiesModel::instance()->get($this->functie_id);
	}

	protected static $table_name = 'crv_repetities';
	protected static $persistent_attributes = array(
		'crv_repetitie_id' => array(T::Integer, false, 'auto_increment'),
		'mlt_repetitie_id' => array(T::Integer, true),
		'dag_vd_week' => array(T::Integer),
		'periode_in_dagen' => array(T::Integer),
		'functie_id' => array(T::Integer),
		'standaard_punten' => array(T::Integer),
		'standaard_aantal' => array(T::Integer, true),
		'voorkeurbaar' => array(T::Boolean)
	);

	protected static $primary_key = array('crv_repetitie_id');
}
