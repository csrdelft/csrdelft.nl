<?php

namespace CsrDelft\entity;

use CsrDelft\common\Security\Voter\Entity\CmsPaginaVoter;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @see CmsPaginaVoter
 *
 * Content Management System Paginas zijn statische pagina's die via de front-end kunnen worden gewijzigd.
 */
#[ORM\Table('cms_paginas')]
#[ORM\Entity(repositoryClass: \CsrDelft\repository\CmsPaginaRepository::class)]
class CmsPagina
{
	/**
  * Primary key
  * @var string
  */
 #[ORM\Id]
 #[ORM\Column(type: 'stringkey')]
 public $naam;
	/**
  * Titel
  * @var string
  */
 #[ORM\Column(type: 'string')]
 public $titel;
	/**
  * Inhoud
  * @var string
  */
 #[ORM\Column(type: 'text', length: 16777216)]
 public $inhoud;
	/**
  * DateTime
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime', name: 'laatst_gewijzigd')]
 public $laatstGewijzigd;
	/**
  * Permissie voor tonen
  * @var string
  */
 #[ORM\Column(type: 'string', name: 'rechten_bekijken')]
 public $rechtenBekijken;
	/**
  * Link
  * @var string
  */
 #[ORM\Column(type: 'string', name: 'rechten_bewerken')]
 public $rechtenBewerken;
	/**
  * Inline HTML
  * @var boolean
  */
 #[ORM\Column(type: 'boolean', name: 'inline_html')]
 public $inlineHtml;
}
