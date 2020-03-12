<?php

namespace CsrDelft\entity;

use CsrDelft\model\security\LoginModel;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Content Management System Paginas zijn statische pagina's die via de front-end kunnen worden gewijzigd.
 *
 * @ORM\Table("cms_paginas")
 * @ORM\Entity(repositoryClass="CsrDelft\repository\CmsPaginaRepository")
 */
class CmsPagina {

	/**
	 * Primary key
	 * @ORM\Id()
	 * @ORM\Column(type="stringkey")
	 * @var string
	 */
	public $naam;
	/**
	 * Titel
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $titel;
	/**
	 * Inhoud
	 * @ORM\Column(type="text", length=16777216)
	 * @var string
	 */
	public $inhoud;
	/**
	 * DateTime
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	public $laatst_gewijzigd;
	/**
	 * Permissie voor tonen
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $rechten_bekijken;
	/**
	 * Link
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $rechten_bewerken;
	/**
	 * Inline HTML
	 * @ORM\Column(type="boolean")
	 * @var boolean
	 */
	public $inline_html;

	/**
	 * @return bool
	 */
	public function magBekijken() {
		return LoginModel::mag($this->rechten_bekijken);
	}

	/**
	 * @return bool
	 */
	public function magBewerken() {
		return LoginModel::mag($this->rechten_bewerken);
	}

	/**
	 * @return bool
	 */
	public function magRechtenWijzigen() {
		return LoginModel::mag(P_ADMIN);
	}

	/**
	 * @return bool
	 */
	public function magVerwijderen() {
		return LoginModel::mag(P_ADMIN);
	}

}
