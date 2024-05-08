<?php

namespace CsrDelft\entity;

use CsrDelft\repository\PushAbonnementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author S. Benjamins <sebastiaan@benjami.in>
 *
 * De informatie die nodig is voor de web-push notificaties.
 */
#[ORM\Entity(repositoryClass: PushAbonnementRepository::class)]
class PushAbonnement
{
	/**
  * Primary key
  * @var int
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 public $id;

	/**
  * @var string
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'uid')]
 public $uid;

	/**
  * @var string
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'string')]
 public $client_endpoint;

	/**
  * @var string
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'string')]
 public $client_keys;
}
