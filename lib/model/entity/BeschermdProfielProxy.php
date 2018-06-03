<?php

namespace CsrDelft\model\entity;

use CsrDelft\model\LidToestemmingModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 22/05/2018
 */
class BeschermdProfielProxy extends Profiel {
	/**
	 * @var Profiel
	 */
	private $profiel;

	protected $defaults = [
		'voorletters' => '',
		'nickname' => '',
		'geslacht' => '',
		'gebdatum' => '',
		'adres' => '',
		'postcode' => '',
		'woonplaats' => '',
		'land' => '',
		'mobiel' => '',
		'email' => '',
		'status' => LidStatus::Nobody,
		'bankrekening' => '',
		'verticale' => '',
		'patroon' => '',
		'eetwens' => '',
	];

	/**
	 * Zie LidToestemmingModel, configureert voor functies wat de zichtbaarheidseisen zijn.
	 *
	 * Als er geen default is, wordt er een lege string terug gegeven.
	 *
	 * @var array
	 */
	protected $mapCallToField = [
		'getPrimaryEmail' => [
			'module' => 'profiel',
			'fields' => 'email',
		],
		'getContactgegevens' => [
			'module' => 'profiel',
			'fields' => 'email'
		],
		'getAdres' => [
			'module' => 'profiel',
			'fields' => ['adres', 'postcode', 'woonplaats']
		],
		'getFormattedAddress' => [
			'module' => 'profiel',
			'fields' => ['adres', 'postcode', 'woonplaats']
		],
		'getFormattedAddressOuders' => [
			'cat' => 'profiel',
			'fields' => ['o_adres', 'o_postcode', 'o_woonplaats', 'o_land']
		],
		'isJarig' => [
			'module' => 'profiel',
			'fields' => 'gebdatum',
			'default' => false,
		],
		'getJarigOver' => [
			'module' => 'profiel',
			'fields' => 'gebdatum',
		],
		'getBeginMoment' => [
			'module' => 'profiel',
			'fields' => 'gebdatum',
		],
		'getEindMoment' => [
			'module' => 'profiel',
			'fields' => 'gebdatum',
		],
		'getTitel' => [
			'module' => 'intern',
			'fields' => 'naam',
		],
		'getBeschrijving' => [
			'module' => 'profiel',
			'fields' => 'gebdatum'
		],
		'getLocatie' => [
			'module' => 'profiel',
			'fields' => ['adres', 'postcode', 'woonplaats']
		],
		'getLink' => [
			'module' => 'intern',
			'fields' => 'naam',
		],
		'getNaam' => [
			'module' => 'intern',
			'fields' => 'naam',
		],
		'getPasfotoPath' => [
			'module' => 'intern',
			'fields' => 'pasfoto',
			'default' => '/plaetjes/pasfoto/geen-foto.jpg'
		],
		'getPasfotoTag' => [
			'module' => 'intern',
			'fields' => 'pasfoto',
			'default' => '<img class="pasfoto" src="/plaetjes/pasfoto/geen-foto.jpg" alt="Geen pasfoto" />'
		],
		'getKinderen' => [
			'module' => 'intern',
			'fields' => 'kinderen',
		],
		'hasKinderen' => [
			'module' => 'intern',
			'fields' => 'kinderen',
			'default' => false,
		],
		'getWoonoord' => [
			'module' => 'intern',
			'fields' => ['adres', 'postcode', 'woonplaats'],
			'default' => false,
		],
		'getVerticale' => [
			'module' => 'intern',
			'fields' => 'verticale',
			'default' => '-'
		],
		'getKring' => [
			'module' => 'intern',
			'fields' => 'kring',
		],
	];

	/**
	 * BeschermdProfielProxy constructor.
	 * @param Profiel $profiel
	 * @throws \ReflectionException
	 */
	public function __construct(Profiel $profiel) {
		parent::__construct();
		$this->profiel = $profiel;

		$class = new \ReflectionClass(static::class);
		$props = $class->getProperties(\ReflectionProperty::IS_PUBLIC);

		foreach ($props as $prop) {
			unset($this->{$prop->name});
		}
	}

	/**
	 * Controleer of een veld zichtbaar is.
	 *
	 * Als er geen toestemming mogelijkheid is, betekent dit dat het veld zichtbaar is.
	 *
	 * @param $name
	 * @return string
	 */
	public function __get($name) {
		if (LidToestemmingModel::has('profiel', $name) && !is_zichtbaar($this->profiel, $name)) {
			if (isset($this->defaults[$name])) {
				return $this->defaults[$name];
			} else {
				return '';
			}
		} else {
			return $this->profiel->$name;
		}
	}

	/**
	 * Controleer of een functie aanroepbaar is.
	 *
	 * Als er geen toestemming mogelijkheid is, betekent dit dat het veld zichtbaar is.
	 *
	 * Eventuele default wordt uit de instellingen gehaald.
	 *
	 * @param $name
	 * @param $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		if (isset($this->mapCallToField[$name]) && !is_zichtbaar($this->profiel, $name, $this->mapCallToField[$name]['module'])) {
			if (isset($this->mapCallToField[$name]['default'])) {
				return $this->mapCallToField[$name]['default'];
			} else {
				return '';
			}
		} else {
			return \Closure::fromCallable([$this->profiel, $name])->call($this, $arguments);
		}
	}
}