<?php


namespace CsrDelft\entity\corvee;


use CsrDelft\entity\profiel\Profiel;

class CorveePuntenOverzichtDTO
{
	/**
	 * @var CorveeTaak
	 */
	public $laatste;
	/**
	 * @var integer
	 */
	public $prognose;
	/**
	 * @var integer
	 */
	public $aantal;
	/**
	 * @var integer[]
	 */
	public $aantallen = [];
	/**
	 * @var integer
	 */
	public $relatief;
	/**
	 * @var boolean
	 */
	public $voorkeur;
	/**
	 * @var boolean
	 */
	public $recent;
	/**
	 * @var bool
	 */
	public $vrijstelling;
	/**
	 * @var integer[]
	 */
	public $punten;
	/**
	 * @var integer[]
	 */
	public $bonus;
	/**
	 * @var string
	 */
	public $prognoseColor;
	/**
	 * @var Profiel
	 */
	public $lid;
	/**
	 * @var int
	 */
	public $puntenTotaal;
	/**
	 * @var int
	 */
	public $bonusTotaal;
	/**
	 * @var int
	 */
	public $tekort;
	/**
	 * @var string
	 */
	public $tekortColor;

}
