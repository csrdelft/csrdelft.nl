<?php

namespace CsrDelft\repository\forum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\forum\ForumDraadMelding;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\forum\ForumDraadMeldingNiveau;
use CsrDelft\model\entity\forum\ForumPost;
use CsrDelft\model\entity\Mail;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Model voor bijhouden, bewerken en verzenden van meldingen voor forumberichten
 *
 * @author J.P.T. Nederveen <ik@tim365.nl>
 * @method ForumDraadMelding|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumDraadMelding|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumDraadMelding[]    findAll()
 * @method ForumDraadMelding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumDradenMeldingRepository extends AbstractRepository {
	const ORM = ForumDraadMelding::class;

	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, ForumDraadMelding::class);
	}

	public function getVoorkeursNiveauVoorLid(ForumDraad $draad, $uid = null) {
		if ($uid === null) $uid = LoginModel::getUid();

		$voorkeur = $this->find(['draad_id' => $draad->draad_id, 'uid' => $uid]);
		if ($voorkeur) {
			return $voorkeur->niveau;
		} else {
			return false;
		}
	}

	public function setNiveauVoorLid(ForumDraad $draad, $niveau) {
		$uid = LoginModel::getUid();
		$voorkeur = $this->find(['draad_id' => $draad->draad_id, 'uid' => $uid]);
		if ($voorkeur) {
			$voorkeur->niveau = $niveau;
			$this->getEntityManager()->persist($voorkeur);
			$this->getEntityManager()->flush();
		} else {
			$this->maakForumDraadMelding($draad->draad_id, $uid, $niveau);
		}
	}

	protected function maakForumDraadMelding($draad_id, $uid, $niveau) {
		$melding = new ForumDraadMelding();
		$melding->draad_id = $draad_id;
		$melding->uid = $uid;
		$melding->niveau = $niveau;

		$this->getEntityManager()->persist($melding);
		$this->getEntityManager()->flush();
		return $melding;
	}

	public function stopAlleMeldingenVoorLid($uid) {
		$manager = $this->getEntityManager();
		foreach ($this->find('uid = ?', array($uid)) as $volgen) {
			$manager->remove($volgen);
		}

		$manager->flush();
	}

	public function stopMeldingenVoorIedereen($draad) {
		$manager = $this->getEntityManager();
		foreach ($this->find('draad_id = ?', array($draad->draad_id)) as $volgen) {
			$manager->remove($volgen);
		}

		$manager->flush();
	}

	public function stuurMeldingen(ForumPost $post) {
		$this->stuurMeldingenNaarVolgers($post);
		$this->stuurMeldingenNaarGenoemden($post);
	}

	/**
	 * Stuurt meldingen van nieuw bericht naar leden met meldingsniveau op altijd
	 *
	 * @param ForumPost $post
	 */
	public function stuurMeldingenNaarVolgers(ForumPost $post) {
		$auteur = ProfielRepository::get($post->uid);
		$draad = $post->getForumDraad();

		// Laad meldingsbericht in
		$bericht = file_get_contents(TEMPLATE_DIR . 'mail/forumaltijdmelding.mail');
		foreach ($this->getAltijdMeldingVoorDraad($draad) as $volger) {
			$volger = ProfielRepository::get($volger->uid);

			// Stuur geen meldingen als lid niet gevonden is of lid de auteur
			if (!$volger || $volger->uid === $post->uid) {
				continue;
			}
			$this->stuurMelding($volger, $auteur, $post, $draad, $bericht);
		}
	}

	public function getAltijdMeldingVoorDraad(ForumDraad $draad) {
		return $this->findBy(['draad_id' => $draad->draad_id, 'niveau' => ForumDraadMeldingNiveau::ALTIJD]);
	}

	/**
	 * Verzendt mail
	 *
	 * @param Profiel $ontvanger
	 * @param Profiel $auteur
	 * @param ForumPost $post
	 * @param ForumDraad $draad
	 * @param string $bericht
	 */
	private function stuurMelding($ontvanger, $auteur, $post, $draad, $bericht) {
		$values = array(
			'NAAM' => $ontvanger->getNaam('civitas'),
			'AUTEUR' => $auteur->getNaam('civitas'),
			'POSTLINK' => $post->getLink(true),
			'TITEL' => $draad->titel,
			'TEKST' => str_replace('\r\n', "\n", $post->tekst),
		);

		// Stel huidig UID in op ontvanger om te voorkomen dat ontvanger privé of andere persoonlijke info te zien krijgt
		LoginModel::instance()->overrideUid($ontvanger->uid);

		// Verzend mail
		try {
			$mail = new Mail(array($ontvanger->getPrimaryEmail() => $ontvanger->getNaam('volledig')), 'C.S.R. Forum: nieuwe reactie op ' . $draad->titel, $bericht);
			$mail->setPlaceholders($values);
			$mail->setLightBB();
			$mail->send();
		} finally {
			// Zet UID terug in sessie
			LoginModel::instance()->resetUid();
		}
	}

	/**
	 * Stuurt meldingen van nieuw bericht naar leden die genoemd / geciteerd worden in bericht
	 *
	 * @param ForumPost $post
	 */
	public function stuurMeldingenNaarGenoemden(ForumPost $post) {
		$auteur = ProfielRepository::get($post->uid);
		$draad = $post->getForumDraad();

		// Laad meldingsbericht in
		$bericht = file_get_contents(TEMPLATE_DIR . 'mail/forumvermeldingmelding.mail');
		$genoemden = $this->zoekGenoemdeLeden($post->tekst);
		foreach ($genoemden as $uid) {
			$genoemde = ProfielRepository::get($uid);

			// Stuur geen meldingen als lid niet gevonden is, lid de auteur is of als lid geen meldingen wil voor draadje
			// Met laatste voorwaarde worden ook leden afgevangen die sowieso al een melding zouden ontvangen
			if (!$genoemde || !AccountModel::existsUid($genoemde->uid) || $genoemde->uid === $post->uid || $this->getNiveauVoorLid($draad, $genoemde->uid) !== ForumDraadMeldingNiveau::VERMELDING) {
				continue;
			}

			// Controleer of lid bij draad mag, stel hiervoor tijdelijk de ingelogde gebruiker in op gegeven lid
			LoginModel::instance()->overrideUid($genoemde->uid);
			try {
				$magMeldingKrijgen = $draad->magMeldingKrijgen();
			} finally {
				LoginModel::instance()->resetUid();
			}

			if (!$magMeldingKrijgen) {
				continue;
			}

			$this->stuurMelding($genoemde, $auteur, $post, $draad, $bericht);
		}
	}

	/**
	 * Zoek genoemde leden in gegeven bericht
	 *
	 * @param string $bericht
	 * @return string[]
	 */
	public function zoekGenoemdeLeden($bericht) {
		$regex = "/\[(?:lid|citaat)=?\s*]?\s*([[:alnum:]]+)\s*(?:\]|\[)/";
		preg_match_all($regex, $bericht, $leden);

		return $leden[1];
	}

	public function getNiveauVoorLid(ForumDraad $draad, $uid = null) {
		if ($uid === null) $uid = LoginModel::getUid();

		$voorkeur = $this->find(['draad_id' => $draad->draad_id, 'uid' => $uid]);
		if ($voorkeur) {
			return $voorkeur->niveau;
		} else {
			$lidInstellingenRepository = ContainerFacade::getContainer()->get(LidInstellingenRepository::class);
			$wilMeldingBijVermelding = $lidInstellingenRepository->getInstellingVoorLid('forum', 'meldingStandaard', $uid);
			return $wilMeldingBijVermelding === 'ja' ? ForumDraadMeldingNiveau::VERMELDING : ForumDraadMeldingNiveau::NOOIT;
		}
	}
}
