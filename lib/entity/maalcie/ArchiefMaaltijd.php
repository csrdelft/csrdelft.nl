<?php

namespace CsrDelft\entity\maalcie;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\agenda\Agendeerbaar;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * MaaltijdArchief  |  P.W.G. Brussee (brussee@live.nl)
 *
 *
 * Een mlt_archief instantie beschrijft een individuele archiefmaaltijd als volgt:
 *  - uniek identificatienummer
 *  - titel (bijv. Donderdagmaaltijd)
 *  - datum en tijd waarop de maaltijd plaatsvond
 *  - de prijs van de maaltijd
 *  - het aantal aanmeldingen op moment van archiveren
 *  - de aanmeldingen en aanmelder in tekstvorm
 *
 * Een gearchiveerde maaltijd is alleen-lezen en kan nooit meer uit het archief worden gehaald.
 *
 *
 * @see Maaltijd
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\maalcie\ArchiefMaaltijdenRepository::class
	)
]
#[ORM\Table('mlt_archief')]
class ArchiefMaaltijd implements Agendeerbaar
{
	/**
	 * @var integer
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $maaltijd_id;
	/**
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'string')]
	public $titel;
	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'date')]
	public $datum;
	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'time')]
	public $tijd;
	/**
	 * @var int
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'integer')]
	public $prijs;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'text')]
	public $aanmeldingen;

	/**
	 * @return string
	 */
	#[Serializer\Groups('datatable')]
	#[Serializer\SerializedName('tijd')]
	public function getTijdFormatted()
	{
		return DateUtil::dateFormatIntl($this->tijd, DateUtil::TIME_FORMAT);
	}

	/**
	 * @return string
	 */
	#[Serializer\Groups('datatable')]
	#[Serializer\SerializedName('datum')]
	public function getDatumFormatted()
	{
		return DateUtil::dateFormatIntl($this->datum, DateUtil::DATE_FORMAT);
	}

	/**
	 * @return int
	 */
	#[Serializer\Groups('datatable')]
	#[Serializer\SerializedName('aanmeldingen')]
	public function getAantalAanmelding()
	{
		return count($this->getAanmeldingenArray());
	}

	// Agendeerbaar ############################################################

	public function getPrijsFloat()
	{
		return (float) $this->prijs / 100.0;
	}

	public function getTitel(): string
	{
		return $this->titel;
	}

	public function getEindMoment(): DateTimeImmutable
	{
		return $this->getBeginMoment()->add(new DateInterval('PT1H30M'));
	}

	public function getBeginMoment(): DateTimeImmutable
	{
		return $this->datum->setTime(
			$this->tijd->format('H'),
			$this->tijd->format('i'),
			$this->tijd->format('s')
		);
	}

	public function getBeschrijving(): string
	{
		return 'Maaltijd met ' . $this->getAantalAanmeldingen() . ' eters';
	}
    /**
     * @return int<0, max>
     */
    public function getAantalAanmeldingen()
	{
		return substr_count($this->aanmeldingen, ',');
	}

	public function getLocatie(): string
	{
		return 'C.S.R. Delft';
	}

	public function getUrl(): string
	{
		return '/maaltijdenbeheer/archief';
	}

	public function isHeledag(): bool
	{
		return false;
	}

	public function isTransparant(): bool
	{
		return true;
	}

	public function jsonSerialize(): mixed
	{
		$json = (array) $this;
		$json['aanmeldingen'] = count($this->getAanmeldingenArray());
		return $json;
	}

	public function getAanmeldingenArray(): array
	{
		$result = [];
		$aanmeldingen = explode(',', $this->aanmeldingen);
		foreach ($aanmeldingen as $id => $aanmelding) {
			if ($aanmelding !== '') {
				$result[$id] = explode('_', $aanmelding);
			}
		}
		return $result;
	}

	public function getUUID(): string
	{
		return $this->maaltijd_id . '@archiefmaaltijd.csrdelft.nl';
	}
}
