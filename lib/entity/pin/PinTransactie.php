<?php

namespace CsrDelft\entity\pin;

use CsrDelft\common\CsrException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 06/09/2017
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\pin\PinTransactieRepository::class
	)
]
#[ORM\Table('pin_transacties')]
class PinTransactie
{
	/**
	 * @var integer
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * @var \DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime')]
	public $datetime;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $brand;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $merchant;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $store;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $terminal;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $TID;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $MID;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $ref;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $type;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $amount;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $AUTRSP;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $STAN;

	/**
	 * @return int
	 * @throws CsrException
	 */
	public function getBedragInCenten()
	{
		[$valuta, $bedrag] = explode(' ', $this->amount);

		if ($valuta !== 'EUR') {
			throw new CsrException(
				sprintf('Betaling niet in euro id: "%d".', $this->id)
			);
		}

		$centen = ltrim(str_replace(',', '', $bedrag), '0');

		return intval($centen);
	}

	/**
	 * @return string
	 * @throws CsrException
	 */
	public function getKorteBeschrijving()
	{
		return sprintf('€%.2f', $this->getBedragInCenten() / 100);
	}
}
