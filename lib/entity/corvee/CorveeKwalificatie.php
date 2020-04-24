<?php

namespace CsrDelft\entity\corvee;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\maalcie\CorveeFunctie;
use CsrDelft\model\maalcie\CorveeFunctiesModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * CorveeKwalificatie.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een CorveeKwalificatie instantie geeft aan dat een lid gekwalificeerd is voor een functie en sinds wanneer.
 * Dit is benodigd voor sommige CorveeFuncties zoals kwalikok.
 *
 * Zie ook CorveeFunctie.class.php
 * @ORM\Entity(repositoryClass="CsrDelft\repository\corvee\CorveeKwalificatiesRepository")
 * @ORM\Table("crv_kwalificaties")
 */
class CorveeKwalificatie {
	/**
	 * Lidnummer
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 */
	public $functie_id;
	/**
	 * Datum en tijd
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $wanneer_toegewezen;

	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $profiel;

	public function setProfiel($profiel) {
		$this->profiel = $profiel;

		if ($profiel) {
			$this->uid = $profiel->uid;
		}
	}

	/**
	 * Lazy loading by foreign key.
	 *
	 * @return CorveeFunctie
	 */
	public function getCorveeFunctie() {
		return CorveeFunctiesModel::instance()->get($this->functie_id);
	}
}
