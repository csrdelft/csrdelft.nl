<?php

namespace CsrDelft\entity\maalcie;

use CsrDelft\Component\DataTable\CustomDataTableEntry;
use CsrDelft\entity\corvee\CorveeFunctie;
use Symfony\Component\Serializer\Annotation as Serializer;

class MaaltijdBeoordelingDTO implements CustomDataTableEntry
{
	/**
	 * @var int
	 */
	#[Serializer\Groups('datatable')]
	public $maaltijd_id;
	/**
	 * @var float|null
	 */
	#[Serializer\Groups('datatable')]
	public $kwantiteit;
	/**
	 * @var float|null
	 */
	#[Serializer\Groups('datatable')]
	public $kwantiteitAfwijking;
	/**
	 * @var int|null
	 */
	#[Serializer\Groups('datatable')]
	public $kwantiteitAantal;
	/**
	 * @var float|int|null
	 */
	#[Serializer\Groups('datatable')]
	public $kwaliteit;
	/**
	 * @var float|null
	 */
	#[Serializer\Groups('datatable')]
	public $kwaliteitAfwijking;
	/**
	 * @var int|mixed
	 */
	#[Serializer\Groups('datatable')]
	public $kwaliteitAantal;

	/**
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	public $datum;
	/**
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	public $tijd;
	/**
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	public $titel;
	/**
	 * @var int
	 */
	#[Serializer\Groups('datatable')]
	public $aantalAanmeldingen;
	/**
	 * @var int
	 */
	#[Serializer\Groups('datatable')]
	public $aanmeldLimiet;
	/**
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	public $koks;

	/**
	 * @return string[]
	 *
	 * @psalm-return list{'maaltijd_id'}
	 */
	public static function getIdentifierFieldNames()
	{
		return ['maaltijd_id'];
	}

	public static function getFieldNames()
	{
		return [
			'maaltijd_id',
			'datum',
			'tijd',
			'titel',
			'kwantiteit',
			'kwaliteit',
			'kwantiteit_afwijking',
			'kwaliteit_afwijking',
		];
	}
}
