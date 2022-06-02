<?php


namespace CsrDelft\entity\bibliotheek;


use Doctrine\ORM\Mapping as ORM;

/**
 * @package CsrDelft\entity\bibliotheek
 * @ORM\Entity(repositoryClass="CsrDelft\repository\bibliotheek\BiebRubriekRepository")
 * @ORM\Table("biebcategorie")
 */
class BiebRubriek
{
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $id;
	/**
	 * @var int parent rubriek
	 * @ORM\Column(type="integer", nullable=true)
	 */
	public $p_id;
	/**
	 * @var string naam
	 * @ORM\Column(type="string")
	 */
	public $categorie;

	/**
	 * @var BiebRubriek
	 * @ORM\ManyToOne(targetEntity="BiebRubriek")
	 * @ORM\JoinColumn(name="p_id", referencedColumnName="id")
	 */
	protected $parent;

	public function __toString()
	{
		if ($this->p_id == $this->id) {
			return '';
		} else {
			$parent = (string)$this->parent;
			if ($parent !== '') {
				$parent .= ' - ';
			}
			return $parent . $this->categorie;
		}
	}
}
