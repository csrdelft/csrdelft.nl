<?php

namespace CsrDelft\entity\agenda;

use Doctrine\ORM\Mapping as ORM;

/**
 * AgendaVerbergen.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Items in de agenda kunnen worden verborgen per gebruiker.
 * @ORM\Entity(repositoryClass="CsrDelft\repository\agenda\AgendaVerbergenRepository")
 * @ORM\Table("agenda_verbergen")
 */
class AgendaVerbergen
{

    /**
     * Lidnummer
     * Shared primary key
     * @ORM\Id()
     * @ORM\Column(type="uid")
     * @var string
     */
    public $uid;
    /**
     * UUID of Agendeerbaar entity
     * Shared primary key
     * @ORM\Column(type="stringkey")
     * @ORM\Id()
     * @var string
     */
    public $refuuid;
}
