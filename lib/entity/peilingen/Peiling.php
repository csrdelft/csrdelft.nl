<?php

namespace CsrDelft\entity\peilingen;

use CsrDelft\common\datatable\annotation as DT;
use CsrDelft\common\datatable\DataTableEntry;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\datatable\DataTableColumn;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\peilingen\PeilingenRepository")
 * @ORM\Table("peiling")
 *
 * @DT\DataTable(order={"id": "desc"})
 * @DT\DataTableKnop(multiplicity="== 1", url="/peilingen/bewerken", label="Bewerken", title="Deze peiling bewerken", icon="pencil")
 * @DT\DataTableKnop(multiplicity="== 0", url="/peilingen/nieuw", label="Nieuw", title="Nieuwe peiling maken", icon="add")
 * @DT\ConfirmDataTableKnop(multiplicity="== 1", url="/peilingen/verwijderen", label="Verwijderen", title="Peiling verwijderen", icon="delete")
 */
class Peiling implements DataTableEntry {
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn()
	 */
	public $id;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn(searchable=true)
	 */
	public $titel;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 * @Serializer\Groups("vue")
	 */
	public $beschrijving;
	/**
	 * @var string
	 * @ORM\Column(type="string", length=4, nullable=true)
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn()
	 */
	public $eigenaar;
	/**
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn()
	 */
	public $mag_bewerken;
	/**
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn(type="check")
	 */
	public $resultaat_zichtbaar;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @Serializer\Groups("vue")
	 */
	public $aantal_voorstellen;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @Serializer\Groups("vue")
	 */
	public $aantal_stemmen;
	/**
	 * @var string|null
	 * @ORM\Column(type="string", nullable=true)
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn()
	 */
	public $rechten_stemmen;
	/**
	 * @var string|null
	 * @ORM\Column(type="string", nullable=true)
	 * @Serializer\Groups("vue")
	 */
	public $rechten_mod;
	/**
	 * @var \DateTime|null
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn()
	 */
	public $sluitingsdatum;

	/**
	 * @var PeilingOptie[]
	 * @ORM\OneToMany(targetEntity="PeilingOptie", mappedBy="peiling")
	 * @Serializer\Groups("vue")
	 */
	public $opties;

	/**
	 * @var PeilingStem[]
	 * @ORM\OneToMany(targetEntity="PeilingStem", mappedBy="peiling")
	 * @ORM\JoinColumn(name="id", referencedColumnName="peiling_id")
	 */
	public $stemmen;

	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="eigenaar", referencedColumnName="uid", nullable=true)
	 */
	public $eigenaarProfiel;

	/**
	 * @return int
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn()
	 */
	public function getAantalGestemd() {
		if (!$this->opties) {
			return 0;
		}

		$stemmen = 0;
		foreach ($this->opties as $optie) {
			$stemmen += $optie->stemmen;
		}
		return $stemmen;
	}

	/**
	 * @Serializer\Groups("vue")
	 */
	public function getHeeftGestemd() {
		if (!$this->stemmen) {
			return false;
		}

		foreach ($this->stemmen as $stem) {
			if ($stem->uid == LoginModel::getUid()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn(type="check")
	 */
	public function getMagBewerken() {
		//Elk BASFCie-lid heeft voorlopig peilingbeheerrechten.
		return LoginModel::mag(P_ADMIN . ',bestuur,commissie:BASFCie');
	}

	/**
	 * @return bool
	 * @Serializer\Groups("vue")
	 */
	public function getIsMod() {
		return LoginModel::mag(P_PEILING_MOD) || LoginModel::getUid() == $this->eigenaar;
	}

	/**
	 * @return bool
	 * @Serializer\Groups("vue")
	 */
	public function getMagStemmen() {
		return LoginModel::mag(P_PEILING_VOTE) && ($this->eigenaar == LoginModel::getUid() || empty(trim($this->rechten_stemmen)) || LoginModel::mag($this->rechten_stemmen))
			&& $this->isPeilingOpen();
	}

	/**
	 * @return bool
	 */
	private function isPeilingOpen() {
		return $this->sluitingsdatum == NULL || time() < $this->sluitingsdatum->getTimestamp();
	}

	/**
	 * @return DataTableColumn|string
	 * @DT\DataTableColumn(name="eigenaar")
	 */
	public function getDataTableEigenaar() {
		return $this->eigenaarProfiel ? $this->eigenaarProfiel->getDataTableColumn() : '';
	}

	/**
	 * @return string
	 * @DT\DataTableColumn(name="detailSource")
	 */
	public function getDetailSource() {
		return '/peilingen/opties/' . $this->id;
	}

	/**
	 * @return bool
	 */
	public function magBekijken() {
		return LoginModel::mag(P_LOGGED_IN);
	}

}


