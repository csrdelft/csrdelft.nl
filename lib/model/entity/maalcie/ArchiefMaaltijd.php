<?php

namespace CsrDelft\model\entity\maalcie;

use CsrDelft\model\entity\agenda\Agendeerbaar;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;

/**
 * MaaltijdArchief.class.php  |  P.W.G. Brussee (brussee@live.nl)
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
 * Zie ook Maaltijd.class.php
 *
 */
class ArchiefMaaltijd extends PersistentEntity implements Agendeerbaar {
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 */
	public $maaltijd_id;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $titel;
	/**
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="date")
	 */
	public $datum;
	/**
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="time")
	 */
	public $tijd;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $prijs;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $aanmeldingen;

	public function getPrijsFloat() {
		return (float)$this->prijs / 100.0;
	}

	public function getAanmeldingenArray() {
		$result = array();
		$aanmeldingen = explode(',', $this->aanmeldingen);
		foreach ($aanmeldingen as $id => $aanmelding) {
			if ($aanmelding !== '') {
				$result[$id] = explode('_', $aanmelding);
			}
		}
		return $result;
	}

	public function getAantalAanmeldingen() {
		return substr_count($this->aanmeldingen, ',');
	}

	// Agendeerbaar ############################################################

	public function getTitel() {
		return $this->titel;
	}

	public function getBeginMoment() {
		return $this->datum->setTime($this->tijd->format('H'), $this->tijd->format('i'), $this->tijd->format('s'));
	}

	public function getEindMoment() {
		return $this->getBeginMoment() + 7200;
	}

	public function getBeschrijving() {
		return 'Maaltijd met ' . $this->getAantalAanmeldingen() . ' eters';
	}

	public function getLocatie() {
		return 'C.S.R. Delft';
	}

	public function getUrl() {
		return '/maaltijdenbeheer/archief';
	}

	public function isHeledag() {
		return false;
	}

	public function isTransparant() {
		return true;
	}

	protected static $table_name = 'mlt_archief';
	protected static $persistent_attributes = array(
		'maaltijd_id' => array(T::Integer, false, 'auto_increment'),
		'titel' => array(T::String),
		'datum' => array(T::Date),
		'tijd' => array(T::Time),
		'prijs' => array(T::Integer),
		'aanmeldingen' => array(T::Text),
	);

	protected static $primary_key = array('maaltijd_id');

	public function jsonSerialize() {
		$json = parent::jsonSerialize();
		$json['aanmeldingen'] = count($this->getAanmeldingenArray());
		return $json;
	}
}
