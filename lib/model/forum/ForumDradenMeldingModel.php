<?php

namespace CsrDelft\model\forum;

use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\forum\ForumDraadMelding;
use CsrDelft\model\entity\forum\ForumDraadMeldingNiveau;
use CsrDelft\model\entity\forum\ForumPost;
use CsrDelft\model\entity\Mail;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\instellingen\LidInstellingenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;

/**
 * Model voor bijhouden, bewerken en verzenden van meldingen voor forumberichten
 *
 * @author J.P.T. Nederveen <ik@tim365.nl>
 */
class ForumDradenMeldingModel extends CachedPersistenceModel {

	const ORM = ForumDraadMelding::class;

	protected function maakForumDraadMelding($draad_id, $uid, $niveau) {
		$melding = new ForumDraadMelding();
		$melding->draad_id = $draad_id;
		$melding->uid = $uid;
		$melding->niveau = $niveau;
		$this->create($melding);
		return $melding;
	}

	public function getAltijdMeldingVoorDraad(ForumDraad $draad) {
		return $this->prefetch('draad_id = ? AND niveau = ?', array($draad->draad_id, ForumDraadMeldingNiveau::ALTIJD));
	}

	public function getVoorkeursNiveauVoorLid(ForumDraad $draad, $uid = null) {
		if ($uid === null) $uid = LoginModel::getUid();

		/** @var ForumDraadMelding $voorkeur */
		$voorkeur = $this->retrieveByPrimaryKey(array($draad->draad_id, $uid));
		if ($voorkeur) {
			return $voorkeur->niveau;
		} else {
			return false;
		}
	}

	public function getNiveauVoorLid(ForumDraad $draad, $uid = null) {
		if ($uid === null) $uid = LoginModel::getUid();

		/** @var ForumDraadMelding $voorkeur */
		$voorkeur = $this->retrieveByPrimaryKey(array($draad->draad_id, $uid));
		if ($voorkeur) {
			return $voorkeur->niveau;
		} else {
			$wilMeldingBijVermelding = LidInstellingenModel::instance()->getInstellingVoorLid('forum', 'meldingStandaard', $uid);
			return $wilMeldingBijVermelding === 'ja' ? ForumDraadMeldingNiveau::VERMELDING : ForumDraadMeldingNiveau::NOOIT;
		}
	}

	public function setNiveauVoorLid(ForumDraad $draad, $niveau) {
		$uid = LoginModel::getUid();
		/** @var ForumDraadMelding $voorkeur */
		$voorkeur = $this->retrieveByPrimaryKey(array($draad->draad_id, $uid));
		if ($voorkeur) {
			$voorkeur->niveau = $niveau;
			$this->update($voorkeur);
		} else {
			$this->maakForumDraadMelding($draad->draad_id, $uid, $niveau);
		}
	}

	public function stopAlleMeldingenVoorLid($uid) {
		foreach ($this->find('uid = ?', array($uid)) as $volgen) {
			$this->delete($volgen);
		}
	}

	public function stopMeldingenVoorIedereen($draad) {
		foreach ($this->find('draad_id = ?', array($draad->draad_id)) as $volgen) {
			$this->delete($volgen);
		}
	}

	public function stuurMeldingen(ForumPost $post) {
		$this->stuurMeldingenNaarVolgers($post);
		$this->stuurMeldingenNaarGenoemden($post);
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
	 * Stuurt meldingen van nieuw bericht naar leden met meldingsniveau op altijd
	 *
	 * @param ForumPost $post
	 */
	public function stuurMeldingenNaarVolgers(ForumPost $post) {
		$auteur = ProfielModel::get($post->uid);
		$draad = $post->getForumDraad();

		// Laad meldingsbericht in
		$bericht = file_get_contents(TEMPLATE_DIR . 'mail/forumaltijdmelding.mail');
		foreach ($this->getAltijdMeldingVoorDraad($draad) as $volger) {
			$volger = ProfielModel::get($volger->uid);

			// Stuur geen meldingen als lid niet gevonden is of lid de auteur
			if (!$volger || $volger->uid === $post->uid) {
				continue;
			}
			$this->stuurMelding($volger, $auteur, $post, $draad, $bericht);
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

	/**
	 * Stuurt meldingen van nieuw bericht naar leden die genoemd / geciteerd worden in bericht
	 *
	 * @param ForumPost $post
	 */
	public function stuurMeldingenNaarGenoemden(ForumPost $post) {
		$auteur = ProfielModel::get($post->uid);
		$draad = $post->getForumDraad();

		// Laad meldingsbericht in
		$bericht = file_get_contents(TEMPLATE_DIR . 'mail/forumvermeldingmelding.mail');
		$genoemden = $this->zoekGenoemdeLeden($post->tekst);
		foreach ($genoemden as $uid) {
			$genoemde = ProfielModel::get($uid);

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
}
