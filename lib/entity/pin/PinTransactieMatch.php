<?php

namespace CsrDelft\entity\pin;

use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use Doctrine\ORM\Mapping as ORM;
use function common\short_class;


/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 * @ORM\Entity(repositoryClass="CsrDelft\repository\pin\PinTransactieMatchRepository")
 * @ORM\Table("pin_transactie_match")
 */
class PinTransactieMatch {
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
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
	 */
	public $transactie_id;
	/**
	 * @var integer
	 * @ORM\Column(type="integer", nullable=true)
	 */
	public $bestelling_id;

	/**
	 * @param PinTransactie $pinTransactie
	 * @param CiviBestellingInhoud $pinBestelling
	 * @return static
	 */
	public static function verkeerdBedrag($pinTransactie, $pinBestelling) {
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_VERKEERD_BEDRAG;
		$pinTransactieMatch->transactie_id = $pinTransactie->id;
		$pinTransactieMatch->bestelling_id = $pinBestelling->bestelling_id;

		return $pinTransactieMatch;
	}

	/**
	 * @param CiviBestellingInhoud $pinBestelling
	 * @return static
	 */
	public static function missendeTransactie($pinBestelling) {
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE;
		$pinTransactieMatch->transactie_id = null;
		$pinTransactieMatch->bestelling_id = $pinBestelling->bestelling_id;

		return $pinTransactieMatch;
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @return static
	 */
	public static function missendeBestelling($pinTransactie) {
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING;
		$pinTransactieMatch->transactie_id = $pinTransactie->id;
		$pinTransactieMatch->bestelling_id = null;

		return $pinTransactieMatch;
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @param CiviBestellingInhoud $pinBestelling
	 * @return static
	 */
	public static function match($pinTransactie, $pinBestelling) {
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MATCH;
		$pinTransactieMatch->transactie_id = $pinTransactie->id;
		$pinTransactieMatch->bestelling_id = $pinBestelling->bestelling_id;

		return $pinTransactieMatch;
	}

	public function getUUID() {
		return strtolower(sprintf('%s@%s.csrdelft.nl', $this->id, short_class($this)));
	}
}
