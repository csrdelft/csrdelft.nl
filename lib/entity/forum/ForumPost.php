<?php

namespace CsrDelft\entity\forum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\forum\ForumDradenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ForumPost.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een forumpost zit in een ForumDraad.
 * @ORM\Entity(repositoryClass="CsrDelft\repository\forum\ForumPostsRepository")
 * @ORM\Table("forum_posts")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class ForumPost {

	/**
	 * Primary key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $post_id;
	/**
	 * Deze post is van dit draadje
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $draad_id;
	/**
	 * Lidnummer van auteur
	 * @var string
	 * @ORM\Column(type="string", length=4)
	 */
	public $uid;
	/**
	 * Tekst
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $tekst;
	/**
	 * Datum en tijd van aanmaken
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	public $datum_tijd;
	/**
	 * Datum en tijd van laatste bewerking
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	public $laatst_gewijzigd;
	/**
	 * Bewerking logboek
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 */
	public $bewerkt_tekst;
	/**
	 * Verwijderd
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $verwijderd;
	/**
	 * IP adres van de auteur
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $auteur_ip;
	/**
	 * Wacht op goedkeuring
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $wacht_goedkeuring;
	/**
	 * Aantal lezers dat deze post gelezen heeft
	 * @var int
	 */
	private $aantal_gelezen;
	/**
	 * @var ForumDraad
	 * @ORM\ManyToOne(targetEntity="ForumDraad")
	 * @ORM\JoinColumn(name="draad_id", referencedColumnName="draad_id")
	 */
	public $draad;

	public function magCiteren() {
		return LoginModel::mag(P_LOGGED_IN) && $this->draad->magPosten();
	}

	public function magBewerken() {
		$draad = $this->draad;
		if ($draad->magModereren()) {
			return true;
		}
		if (!$draad->magPosten()) {
			return false;
		}
		return $this->uid === LoginModel::getUid() && LoginModel::mag(P_LOGGED_IN);
	}

	public function getGelezenPercentage() {
		return $this->getAantalGelezen() * 100 / $this->draad->getAantalLezers();
	}

	public function getAantalGelezen() {
		if (!isset($this->aantal_gelezen)) {
			$this->aantal_gelezen = 0;
			foreach ($this->draad->lezers as $gelezen) {
				if ($this->laatst_gewijzigd && $this->laatst_gewijzigd <= $gelezen->datum_tijd) {
					$this->aantal_gelezen++;
				}
			}
		}
		return $this->aantal_gelezen;
	}

	public function getLink($external = false) {
		return ($external ? CSR_ROOT : '') . "/forum/reactie/" . $this->post_id . "#" . $this->post_id;
	}

}
