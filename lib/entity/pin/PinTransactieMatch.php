<?php

namespace CsrDelft\entity\pin;

use CsrDelft\common\CsrException;
use CsrDelft\common\datatable\DataTableEntry;
use CsrDelft\entity\fiscaat\CiviBestelling;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;


/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 * @ORM\Entity(repositoryClass="CsrDelft\repository\pin\PinTransactieMatchRepository")
 * @ORM\Table("pin_transactie_match")
 */
class PinTransactieMatch implements DataTableEntry {
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @Serializer\Groups("datatable")
	 */
	public $id;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $status;
	/**
	 * @var integer
	 * @ORM\Column(type="integer", nullable=true)
	 * @Serializer\Groups("datatable")
	 */
	public $transactie_id;
	/**
	 * @var PinTransactie|null
	 * @ORM\ManyToOne(targetEntity="PinTransactie")
	 * @ORM\JoinColumn(nullable=true)
	 */
	public $transactie;
	/**
	 * @var integer
	 * @ORM\Column(type="integer", nullable=true)
	 * @Serializer\Groups("datatable")
	 */
	public $bestelling_id;
	/**
	 * @var CiviBestelling|null
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\fiscaat\CiviBestelling")
	 * @ORM\JoinColumn(nullable=true)
	 */
	public $bestelling;

	/**
	 * @param PinTransactie $pinTransactie
	 * @param CiviBestelling $pinBestelling
	 * @return static
	 */
	public static function verkeerdBedrag(PinTransactie $pinTransactie, CiviBestelling $pinBestelling) {
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_VERKEERD_BEDRAG;
		$pinTransactieMatch->bestelling = $pinBestelling;
		$pinTransactieMatch->transactie = $pinTransactie;

		return $pinTransactieMatch;
	}

	/**
	 * @param CiviBestelling $pinBestelling
	 * @return static
	 */
	public static function missendeTransactie(CiviBestelling $pinBestelling) {
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE;
		$pinTransactieMatch->transactie = null;
		$pinTransactieMatch->bestelling = $pinBestelling;

		return $pinTransactieMatch;
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @return static
	 */
	public static function missendeBestelling(PinTransactie $pinTransactie) {
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING;
		$pinTransactieMatch->transactie = $pinTransactie;
		$pinTransactieMatch->bestelling = null;

		return $pinTransactieMatch;
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @param CiviBestelling $pinBestelling
	 * @return static
	 */
	public static function match(PinTransactie $pinTransactie, CiviBestelling $pinBestelling) {
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MATCH;
		$pinTransactieMatch->transactie = $pinTransactie;
		$pinTransactieMatch->bestelling = $pinBestelling;

		return $pinTransactieMatch;
	}

	public function getUUID() {
		return strtolower(sprintf('%s@%s.csrdelft.nl', $this->id, short_class($this)));
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("status")
	 */
	public function getDataTableStatus() {
		return PinTransactieMatchStatusEnum::from($this->status)->getDescription();
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("transactie")
	 */
	public function getDataTableTransactie() {
		if ($this->transactie) {
			return $this->transactie->getKorteBeschrijving();
		} else {
			return '-';
		}
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("bestelling")
	 */
	public function getDataTableBestelling() {
		if ($this->bestelling) {
			return $this->bestelling->getPinBeschrijving();
		} else {
			return '-';
		}
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("moment")
	 */
	public function getDataTableMoment() {
		return self::renderMoment($this->getMoment());
	}

	/**
	 * @throws CsrException
	 * @return \DateTimeImmutable
	 */
	public function getMoment() {
		if ($this->transactie !== null) {
			return $this->transactie->datetime;
		} elseif ($this->bestelling !== null) {
			return $this->bestelling->moment;
		} else {
			throw new CsrException('Pin Transactie Match heeft geen bestelling en transactie.');
		}
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("bestelling_moment")
	 */
	public function getDataTableBestellingMoment() {
		if ($this->bestelling !== null) {
			return self::renderMoment($this->bestelling->moment);
		} else {
			return "";
		}
	}

	/**
	 * @param DateTimeImmutable $moment
	 * @return string
	 */
	private static function renderMoment(DateTimeImmutable $moment) {
		$formatted = date_format_intl($moment, DATETIME_FORMAT);
		$dag = date_format_intl($moment, 'cccc');
		$agendaLink = "/agenda/{$moment->format('Y')}/{$moment->format('m')}";
		return "<a data-moment='{$formatted}' target='_blank' href='{$agendaLink}' title='{$dag}'>{$formatted}</a>"; // Data attribuut voor sortering
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("verschil")
	 */
	public function getDataTableVerschil() {
		$verschil = $this->getVerschil();
		if ($verschil !== null) {
			$min = $verschil < 0 ? '-' : '';
			$minuten = floor(abs($verschil) / 60);
			$seconden = abs($verschil) % 60;
			return $min . sprintf("%d:%02d", $minuten, $seconden);
		} else {
			return "";
		}
	}

	/**
	 * @throws CsrException
	 * @return int Seconds difference
	 */
	public function getVerschil() {
		if ($this->transactie !== null && $this->bestelling !== null) {
			return $this->transactie->datetime->getTimestamp() - $this->bestelling->moment->getTimestamp();
		} else {
			return null;
		}
	}
}
