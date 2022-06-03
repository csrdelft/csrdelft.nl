<?php

namespace CsrDelft\entity\forum;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een ForumDraad kan worden verborgen door een lid.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\forum\ForumDradenVerbergenRepository")
 * @ORM\Table("forum_draden_verbergen")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class ForumDraadVerbergen
{
    /**
     * Shared primary key
     * Foreign key
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id()
     */
    public $draad_id;
    /**
     * @var ForumDraad
     * @ORM\ManyToOne(targetEntity="ForumDraad", inversedBy="verbergen")
     * @ORM\JoinColumn(name="draad_id", referencedColumnName="draad_id")
     */
    public $draad;
    /**
     * Lidnummer
     * Shared primary key
     * Foreign key
     * @var string
     * @ORM\Column(type="uid")
     * @ORM\Id()
     */
    public $uid;
}
