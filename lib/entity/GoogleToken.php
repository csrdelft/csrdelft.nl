<?php

namespace CsrDelft\entity;

use CsrDelft\repository\GoogleTokenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class GoogleToken.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
#[ORM\Entity(repositoryClass: GoogleTokenRepository::class)]
class GoogleToken
{
	/**
  * @var string
  */
 #[ORM\Column(type: 'uid')]
 #[ORM\Id]
 public $uid;
	/**
  * @var string
  */
 #[ORM\Column(type: 'string')]
 public $token;
}
