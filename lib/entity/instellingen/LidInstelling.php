<?php

namespace CsrDelft\entity\instellingen;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een LidInstelling beschrijft een Instelling per Lid.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\instellingen\LidInstellingenRepository")
 * @ORM\Table("lidinstellingen")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class LidInstelling {

	/**
	 * Lidnummer
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="string", length=4)
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * Shared primary key
	 * @var string
	 * @ORM\Column(type="string", length=191)
	 * @ORM\Id()
	 */
	public $module;
	/**
	 * Shared primary key
	 * @var string
	 * @ORM\Column(type="string", length=191)
	 * @ORM\Id()
	 */
	public $instelling_id;
	/**
	 * Value
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $waarde;

//	public function __construct($cast = false, array $attributes_retrieved = null) {
//		parent::__construct($cast, $attributes_retrieved);
//
//		if ($cast) {
//			$this->castWaarde();
//		}
//	}
//
//	public function onAttributesRetrieved(array $attributes) {
//		parent::onAttributesRetrieved($attributes);
//
//		$this->castWaarde();
//	}
//
//	protected function castWaarde() {
//		if (LidInstellingenRepository::instance()->getType($this->module, $this->instelling_id) === T::Integer) {
//			$this->waarde = (int)$this->waarde;
//		}
//	}
}
