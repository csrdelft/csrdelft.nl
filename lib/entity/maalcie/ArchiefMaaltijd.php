<?php

namespace CsrDelft\entity\maalcie;

use CsrDelft\repository\maalcie\ArchiefMaaltijdenRepository;
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
#[ORM\Table('mlt_archief')]
#[ORM\Entity(repositoryClass: ArchiefMaaltijdenRepository::class)]
class ArchiefMaaltijd implements Agendeerbaar
{
	/**
  * @var integer
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[Serializer\Groups('datatable')]
 public $maaltijd_id;
	/**
  * @var string
  */
 #[ORM\Column(type: 'string')]
 #[Serializer\Groups('datatable')]
 public $titel;
	/**
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'date_immutable')]
 public $datum;
	/**
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'time')]
 public $tijd;
	/**
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 #[Serializer\Groups('datatable')]
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
 public function getTijdFormatted(): string|false
	{
		return DateUtil::dateFormatIntl($this->tijd, DateUtil::TIME_FORMAT);
	}

	/**
  * @return string
  */
 #[Serializer\Groups('datatable')]
 #[Serializer\SerializedName('datum')]
 public function getDatumFormatted(): string|false
	{
		return DateUtil::dateFormatIntl($this->datum, DateUtil::DATE_FORMAT);
	}

	/**
  * @return int
  */
 #[Serializer\Groups('datatable')]
 #[Serializer\SerializedName('aanmeldingen')]
 public function getAantalAanmelding(): int
	{
		return count($this->getAanmeldingenArray());
	}

	// Agendeerbaar ############################################################

	public function getPrijsFloat(): float
	{
		return (float) $this->prijs / 100.0;
	}

	public function getTitel()
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

	public function getAantalAanmeldingen(): int
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

	public function jsonSerialize(): array
	{
		$json = (array) $this;
		$json['aanmeldingen'] = count($this->getAanmeldingenArray());
		return $json;
	}

	/**
  * @return string[][]
  */
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
