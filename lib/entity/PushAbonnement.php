<?php

namespace CsrDelft\entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author S. Benjamins <sebastiaan@benjami.in>
 *
 * De informatie die nodig is voor de web-push notificaties.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\PushAbonnementRepository")
 */
class PushAbonnement
{
	/**
	 * Primary key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @Serializer\Groups("datatable")
	 */
	public $id;

	/**
	 * @var string
	 * @ORM\Column(type="uid")
	 * @Serializer\Groups("datatable")
	 */
	public $uid;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 * @Serializer\Groups("datatable")
	 */
	public $client_endpoint;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 * @Serializer\Groups("datatable")
	 */
	public $client_keys;
}
