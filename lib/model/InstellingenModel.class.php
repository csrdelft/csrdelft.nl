<?php

namespace CsrDelft\model;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\Instelling;
use CsrDelft\Orm\CachedPersistenceModel;

/**
 * InstellingenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class InstellingenModel extends CachedPersistenceModel {

	const ORM = Instelling::class;

	/**
	 * Store instellingen array as a whole in memcache
	 * @var boolean
	 */
	protected $memcache_prefetch = true;

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return bool
	 */
	public static function has($module, $id) {
		return isset(static::$defaults[$module][$id]);
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return string
	 */
	public static function get($module, $id) {
		return static::instance()->getValue($module, $id);
	}

	/**
	 * @var string[][]
	 */
	protected static $defaults = array(
		'privacy' => array(
			'beleid_kort' => 'Hier kunt u aangeven of u akkoord gaat met het delen van uw gegevens. Deze keuze geldt totdat u lid-af of oud-lid wordt. Het zal altijd mogelijk zijn om uw keuze aan te passen. Een knop hier voor is te vinden op uw profiel onder uw foto. Kijk op de <a href="/privacy">privacy</a> pagina voor meer informatie. Stuur een berichtje naar de Vice-Abactis als er nog verdere vragen of opmerkingen zijn. Als u op annuleren klikt zult u voor een uur lang niet lastig gevallen worden met dit bericht.',
			'beschrijving_bestuur' => 'Met de volgende optie kunt u aangeven of u akkoord gaat met het gebruik van uw gegevens door het bestuur en commissies van C.S.R. om hun taak volledig te kunnen voldoen. Hiervoor gebruiken zij slechts de gegevens die zij nodig hebben. (Bij \'nee\' zal er contact worden opgenomen om zo tot een goede oplossing te komen.)',
			'beschrijving_bijzonder' => 'Met de volgende optie kunt u aangeven of u akkoord gaat met het gebruik van uw bijzondere persoonsgegevens, in dit geval alleen allergie, voor gebruik door het bestuur en commissies.',
			'beschrijving_foto_extern' => 'Met de volgende optie kunt u aangeven of u akkoord gaat met het gebruik van foto\'s waar u op te zien bent op de externe stek of voor promotiedoeleinden.',
			'beschrijving_foto_intern' => 'Met de volgende optie kunt u aangeven of u akkoord gaat met het plaatsen van foto\'s op het interne gedeelte van de stek.',
			'beschrijving_vereniging' => 'Maak hieronder een keuze of u akkoord gaat met het delen van uw gegevens met leden, novieten en oudleden van C.S.R.'
		),
		'agenda' => array(
			'standaard_rechten' => 'P_LOGGED_IN',
			'ical_from' => '-1 month',
			'ical_to' => '+6 months'
		),
		'beveiliging' => array(
			'remember_login_seconds' => '2592000',
			'session_lifetime_seconds' => '1440',
			'recent_login_seconds' => '600',
			'one_time_token_expire_after' => '+1 hour',
			'wachtwoorden_verlopen_ouder_dan' => '-1 year',
			'wachtwoorden_verlopen_waarschuwing_vooraf' => '-2 weeks'
		),
		'corvee' => array(
			'punten_per_jaar' => '11',
			'herinnering_aantal_mails' => '2',
			'herinnering_1e_mail' => '-4 weeks',
			'herinnering_1e_mail_uiterlijk' => '-3 weeks',
			'herinnering_2e_mail' => '-2 weeks',
			'herinnering_2e_mail_uiterlijk' => '-1 weeks',
			'suggesties_recent_verbergen' => '-2 months',
			'suggesties_recent_filter' => '1',
			'suggesties_recent_kwali_filter' => '0',
			'suggesties_voorkeur_filter' => '1',
			'suggesties_voorkeur_kwali_filter' => '1',
			'standaard_repetitie_weekdag' => '4',
			'standaard_repetitie_periode' => '7',
			'standaard_voorkeurbaar' => '1',
			'standaard_kwalificatie' => '0',
			'standaard_aantal_corveers' => '1',
			'standaard_vrijstelling_percentage' => '100',
			'weergave_ledennamen_beheer' => 'volledig',
			'weergave_ledennamen_corveerooster' => 'civitas',
			'waarschuwing_taaktoewijzing_vooraf' => '+14 days',
			'waarschuwing_puntentoewijzing_achteraf' => '-1 days',
			'mail_wel_meeeten' => 'P.S.: U eet WEL mee met de maaltijd.',
			'mail_niet_meeeten' => 'P.S.: U eet NIET mee met de maaltijd.'
		),
		'forum' => array(
			'reageren_tijd' => '-2 minutes',
			'grafiek_draad_recent' => '-1 month',
			'grafiek_stats_periode' => '-6 months',
			'externen_geentoegang_gesloten' => '-1 year'
		),
		'fotoalbum' => array(
			'slideshow_interval' => '3s'
		),
		'gesprekken' => array(
			'max_aantal_deelnemers' => '8',
			'active_threshold_seconds' => '60',
			'active_interval_seconds' => '3',
			'slow_interval_seconds' => '30'
		),
		'maaltijden' => array(
			'beoordeling_periode' => '-1 week',
			'budget_maalcie' => '100',
			'toon_ketzer_vooraf' => '+1 month',
			'recent_lidprofiel' => '-2 months',
			'standaard_repetitie_weekdag' => '4',
			'standaard_repetitie_periode' => '7',
			'standaard_abonneerbaar' => '1',
			'standaard_aanvang' => '18:00',
			'standaard_prijs' => '300',
			'standaard_limiet' => '0',
			'marge_gasten_verhouding' => '10',
			'marge_gasten_min' => '3',
			'marge_gasten_max' => '6',
			'weergave_ledennamen_beheer' => 'volledig',
			'weergave_ledennamen_maaltijdlijst' => 'volledig',
			'maaltijdlijst_tekst' => '<p>Regels omtrent het betalen van de maaltijden op Confide:</p>
<ul>
<li>Maaltijdprijs: &euro; MAALTIJDPRIJS</li>
<li>Niet betaald = nb</li>
<li>Betaald met machtiging = omcirkel "m" en vul bedrag in.</li>
<li>Contant betaald = bedrag invullen.</li>
<li>Schrijf duidelijk in het hokje hoeveel je in de helm hebt gegooid.</li>
<li>Bevat derde kolom "ok"? Dan heeft u nog voldoende tegoed voor deze maaltijd.</li>
<li>Als u onvoldoende saldo hebt bij de MaalCie en u betaalt niet voor deze maaltijd dan krijgt u een boete van 20 cent, 1 euro of 2 euro, afhankelijk van hoe negatief uw saldo is!</li>
</ul>'),
		'stek' => array(
			'homepage' => 'thuis',
			'beschrijving' => 'De Civitas Studiosorum Reformatorum is een bruisende, actieve, christelijke studentenvereniging in Delft, rijk aan tradities die zijn ontstaan in haar 50-jarig bestaan. Het is een breed gezelschap van zo&lsquo;n 270 leden met een zeer gevarieerde (kerkelijke) achtergrond, maar met een duidelijke eenheid door het christelijk geloof. C.S.R. is de plek waar al tientallen jaren studenten goede vrienden van elkaar worden, op intellectueel en geestelijk gebied groeien en goede studentengrappen uithalen.'
		)
	);

	public function getAll() {
		return static::$defaults;
	}

	/**
	 * @return string[]
	 */
	public function getModules() {
		return array_keys(static::$defaults);
	}

	/**
	 * @param string $module
	 *
	 * @return string[]
	 */
	public function getModuleInstellingen($module) {
		return array_keys(static::$defaults[$module]);
	}

	/**
	 * @return string[][]
	 */
	public function getInstellingen() {
		$instellingen = array();
		foreach ($this->getModules() as $module) {
			$instellingen[$module] = $this->getModuleInstellingen($module);
		}
		return $instellingen;
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return string
	 */
	public function getValue($module, $id) {
		return $this->getInstelling($module, $id)->waarde;
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return string
	 */
	public function getDefault($module, $id) {
		return static::$defaults[$module][$id];
	}

	/**
	 * Haal een instelling op uit het cache of de database.
	 * Als een instelling niet is gezet wordt deze aangemaakt met de default waarde en opgeslagen.
	 *
	 * @param string $module
	 * @param string $id
	 * @return Instelling
	 * @throws CsrException indien de default waarde ontbreekt (de instelling bestaat niet)
	 */
	protected function getInstelling($module, $id) {
		$instelling = $this->retrieveByPrimaryKey(array($module, $id));
		if (static::has($module, $id)) {
			if (!$instelling) {
				$instelling = $this->newInstelling($module, $id);
			}
			return $instelling;
		} else {
			if ($instelling) {
				// Haal niet-bestaande instelling uit de database
				$this->delete($instelling);
			}
			throw new CsrException(sprintf('Instelling bestaat niet: "%s" module: "%s".', $id, $module));
		}
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return Instelling
	 */
	protected function newInstelling($module, $id) {
		$instelling = new Instelling();
		$instelling->module = $module;
		$instelling->instelling_id = $id;
		$instelling->waarde = $this->getDefault($module, $id);
		$this->create($instelling);
		return $instelling;
	}

	/**
	 * @param string $module
	 * @param string $id
	 * @param string $waarde
	 *
	 * @return Instelling
	 */
	public function wijzigInstelling($module, $id, $waarde) {
		$instelling = $this->getInstelling($module, $id);
		$instelling->waarde = $waarde;
		$this->update($instelling);
		return $instelling;
	}

	/**
	 */
	public function opschonen() {
		foreach ($this->find() as $instelling) {
			if (!static::has($instelling->module, $instelling->instelling_id)) {
				$this->delete($instelling);
			}
		}
	}

}
