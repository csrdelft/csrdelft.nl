<?php

namespace CsrDelft\entity\documenten;

use CsrDelft\model\security\LoginModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @ORM\Table("DocumentCategorie")
 * @ORM\Entity(repositoryClass="CsrDelft\repository\documenten\DocumentCategorieRepository")
 */
class DocumentCategorie  {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	public $id;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $naam;
	/**
	 * @ORM\Column(type="boolean")
	 * @var boolean
	 */
	public $zichtbaar;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $leesrechten;

	public function magBekijken() {
		return LoginModel::mag($this->leesrechten);
	}
}
