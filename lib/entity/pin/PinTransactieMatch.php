<?php

namespace CsrDelft\entity\pin;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\entity\fiscaat\enum\CiviProductTypeEnum;
use CsrDelft\entity\fiscaat\enum\CiviSaldoCommissieEnum;
use CsrDelft\repository\fiscaat\CiviProductRepository;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\view\Icon;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\pin\PinTransactieMatchRepository::class
	)
]
#[ORM\Table('pin_transactie_match')]
class PinTransactieMatch implements DataTableEntry
{
	/**
	 * @var integer
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $status;
	/**
	 * @var integer
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'integer', nullable: true)]
	public $transactie_id;
	/**
	 * @var PinTransactie|null
	 */
	#[ORM\ManyToOne(targetEntity: \PinTransactie::class)]
	#[ORM\JoinColumn(nullable: true)]
	public $transactie;
	/**
	 * @var integer
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'integer', nullable: true)]
	public $bestelling_id;
	/**
	 * @var CiviBestelling|null
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\fiscaat\CiviBestelling::class)]
	#[ORM\JoinColumn(nullable: true)]
	public $bestelling;
	/**
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'text', length: 65535, nullable: true)]
	public $notitie;

	/**
	 * @param PinTransactie $pinTransactie
	 * @param CiviBestelling $pinBestelling
	 * @return static
	 */
	public static function verkeerdBedrag(
		PinTransactie $pinTransactie,
		CiviBestelling $pinBestelling
	) {
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status =
			PinTransactieMatchStatusEnum::STATUS_VERKEERD_BEDRAG;
		$pinTransactieMatch->bestelling = $pinBestelling;
		$pinTransactieMatch->transactie = $pinTransactie;

		return $pinTransactieMatch;
	}

	/**
	 * @param CiviBestelling $pinBestelling
	 * @return static
	 */
	public static function missendeTransactie(CiviBestelling $pinBestelling)
	{
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status =
			PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE;
		$pinTransactieMatch->transactie = null;
		$pinTransactieMatch->bestelling = $pinBestelling;

		return $pinTransactieMatch;
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @return static
	 */
	public static function missendeBestelling(PinTransactie $pinTransactie)
	{
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status =
			PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING;
		$pinTransactieMatch->transactie = $pinTransactie;
		$pinTransactieMatch->bestelling = null;

		return $pinTransactieMatch;
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @param CiviBestelling $pinBestelling
	 * @return static
	 */
	public static function match(
		PinTransactie $pinTransactie,
		CiviBestelling $pinBestelling
	) {
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MATCH;
		$pinTransactieMatch->transactie = $pinTransactie;
		$pinTransactieMatch->bestelling = $pinBestelling;

		return $pinTransactieMatch;
	}

	/**
	 * @param PinTransactie|null $pinTransactie
	 * @param CiviBestelling|null $pinBestelling
	 * @return static
	 */
	public static function negeer(
		PinTransactie $pinTransactie = null,
		CiviBestelling $pinBestelling = null
	) {
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status =
			PinTransactieMatchStatusEnum::STATUS_GENEGEERD;
		$pinTransactieMatch->transactie = $pinTransactie;
		$pinTransactieMatch->bestelling = $pinBestelling;

		return $pinTransactieMatch;
	}

	/**
	 * @return DateTimeImmutable
	 * @throws CsrException
	 */
	public function getMoment()
	{
		if ($this->transactie !== null) {
			return $this->transactie->datetime;
		} elseif ($this->bestelling !== null) {
			return $this->bestelling->moment;
		} else {
			throw new CsrException(
				'Pin Transactie Match heeft geen bestelling en transactie.'
			);
		}
	}

	/**
	 * @param DateTimeImmutable $moment
	 * @param bool $link
	 *
	 * @return false|string
	 */
	public static function renderMoment(DateTimeImmutable $moment, $link = true): string|false
	{
		$formatted = DateUtil::dateFormatIntl($moment, DateUtil::DATETIME_FORMAT);
		if (!$link) {
			return $formatted;
		}
		$dag = DateUtil::dateFormatIntl($moment, 'cccc');
		$agendaLink = "/agenda/{$moment->format('Y')}/{$moment->format('m')}";
		return "<a data-moment='{$formatted}' target='_blank' href='{$agendaLink}' title='{$dag}'>{$formatted}</a>"; // Data attribuut voor sortering
	}

	/**
	 * Bepaalt status van pintransactiematch op basis van bestelling en transactie.
	 * Houdt geen rekening met eventuele correcties.
	 * @return string
	 */
	public function logischeStatus(): string
	{
		if ($this->bestelling === null) {
			return PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING;
		} elseif ($this->transactie === null) {
			return PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE;
		} else {
			$bestellingInhoud = $this->bestelling->getProduct(
				CiviProductTypeEnum::PINTRANSACTIE
			);
			if (
				$bestellingInhoud->aantal === $this->transactie->getBedragInCenten()
			) {
				return PinTransactieMatchStatusEnum::STATUS_MATCH;
			} else {
				return PinTransactieMatchStatusEnum::STATUS_VERKEERD_BEDRAG;
			}
		}
	}

	/**
	 * @param CiviProductRepository $civiProductRepository
	 * @param string|null $comment
	 * @param string|null $uid
	 * @return CiviBestelling
	 */
	public function bouwBestelling(
		$civiProductRepository,
		$comment = null,
		$uid = null
	) {
		$bestellingInhoud = $this->bouwBestellingInhoud($civiProductRepository);
		if (!$bestellingInhoud) {
			throw new CsrException('Bestelling kan niet gebouwd worden');
		}

		$nieuweBestelling = new CiviBestelling();
		$nieuweBestelling->moment = date_create_immutable();
		$nieuweBestelling->uid = $uid ?: $this->bestelling->uid;
		$nieuweBestelling->civiSaldo = ContainerFacade::getContainer()
			->get(CiviSaldoRepository::class)
			->find($nieuweBestelling->uid);
		$nieuweBestelling->totaal = $bestellingInhoud->getPrijs();
		$nieuweBestelling->cie = CiviSaldoCommissieEnum::SOCCIE;
		$nieuweBestelling->deleted = false;
		$nieuweBestelling->comment = $comment;
		$nieuweBestelling->inhoud[] = $bestellingInhoud;

		return $nieuweBestelling;
	}

	/**
	 * @param CiviProductRepository $civiProductRepository
	 */
	public function bouwBestellingInhoud($civiProductRepository): CiviBestellingInhoud
	{
		$bestellingInhoud = new CiviBestellingInhoud();
		// Gebruik pincorrectie voor periode voor invoering tussenrekeningen, gebruik pintransactie erna
		$bestellingInhoud->product_id =
			$this->getMoment() < date_create_immutable('2020-05-16')
				? CiviProductTypeEnum::PINCORRECTIE
				: CiviProductTypeEnum::PINTRANSACTIE;
		$bestellingInhoud->product = $civiProductRepository->getProduct(
			$bestellingInhoud->product_id
		);

		$correct = $this->transactie ? $this->transactie->getBedragInCenten() : 0;
		$fout = $this->bestelling
			? $this->bestelling->getProduct(CiviProductTypeEnum::PINTRANSACTIE)
				->aantal
			: 0;
		$bestellingInhoud->aantal = $correct - $fout;

		return $bestellingInhoud;
	}
}
