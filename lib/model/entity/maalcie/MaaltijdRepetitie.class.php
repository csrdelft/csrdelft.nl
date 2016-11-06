<?php

/**
 * MaaltijdRepetitie.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een mlt_repetitie instantie beschrijft een maaltijd die periodiek wordt gehouden als volgt:
 *  - uniek identificatienummer
 *  - op welke dag van de week de maaltijd wordt gehouden
 *  - na hoeveel dagen deze opnieuw wordt gehouden
 *  - de standaard naam van de maaltijd (bijv. donderdag-maaltijd)
 *  - de standaard tijd van de maaltijd (bijv. 18:00)
 *  - of er een abonnement kan worden genomen op deze periodieke maaltijden
 *  - de standaard limiet van het aantal aanmeldingen
 *  - of er restricties gelden voor wie zich mag abonneren op deze maaltijd
 * 
 * 
 * De standaard titel, limiet en filter worden standaard overgenomen, maar kunnen worden overschreven per maaltijd.
 * Bij het aanmaken van een nieuwe maaltijd (op basis van deze repetitie) worden alle leden met een abonnement op deze repetitie aangemeldt voor deze nieuwe maaltijd.
 * 
 * 
 * Zie ook MaaltijdAbonnement.class.php
 * 
 */
class MaaltijdRepetitie extends PersistentEntity {
	# primary key

	public $mlt_repetitie_id; # int 11
    /**
     * 0: Sunday
     * 6: Saturday
     */
	public $dag_vd_week; # int 1
	public $periode_in_dagen; # int 11
	public $standaard_titel; # string 255
	public $standaard_tijd; # time
	public $standaard_prijs; # double
	public $abonneerbaar; # boolean
	public $standaard_limiet; # int 11
	public $abonnement_filter; # string 255

    protected static $table_name = 'mlt_repetities';
    protected static $persistent_attributes = array(
        'mlt_repetitie_id' => array(T::Integer, false, 'auto_increment'),
        'dag_vd_week' => array(T::Boolean),
        'periode_in_dagen' => array(T::Integer),
        'standaard_titel' => array(T::String),
        'standaard_tijd' => array(T::Time),
        'standaard_prijs' => array(T::Integer),
        'abonneerbaar' => array(T::Boolean),
        'standaard_limiet' => array(T::Integer),
        'abonnement_filter' => array(T::String, true)
    );
    protected static $primary_key = array('mlt_repetitie_id');

	public function getDagVanDeWeekText() {
		return strftime('%A', ($this->dag_vd_week + 3) * 24 * 3600);
	}

	public function getPeriodeInDagenText() {
		switch ($this->periode_in_dagen) {
			case 0: return '-';
			case 1: return 'elke dag';
			case 7: return 'elke week';
			default:
				if ($this->periode_in_dagen % 7 === 0) {
					return 'elke ' . ($this->periode_in_dagen / 7) . ' weken';
				} else {
					return 'elke ' . $this->periode_in_dagen . ' dagen';
				}
		}
	}

	public function getStandaardPrijsFloat() {
		return (float) $this->standaard_prijs / 100.0;
	}
}

?>