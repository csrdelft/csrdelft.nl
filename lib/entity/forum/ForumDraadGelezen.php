<?php

namespace CsrDelft\entity\forum;

use CsrDelft\entity\profiel\Profiel;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een ForumDraad kan worden gelezen door een lid op een bepaald moment.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\forum\ForumDradenGelezenRepository")
 * @ORM\Table("forum_draden_gelezen")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class ForumDraadGelezen
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
     * Lidnummer
     * Shared primary key
     * Foreign key
     * @var string
     * @ORM\Column(type="uid")
     * @ORM\Id()
     */
    public $uid;
    /**
     * @var Profiel
     * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
     * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
     */
    public $profiel;
    /**
     * Datum en tijd van laatst gelezen
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime")
     */
    public $datum_tijd;

    /**
     * @var ForumDraad
     * @ORM\ManyToOne(targetEntity="ForumDraad", inversedBy="lezers")
     * @ORM\JoinColumn(name="draad_id", referencedColumnName="draad_id")
     */
    public $draad;
}
