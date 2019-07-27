<?php

namespace CsrDelft\model\forum;

use CsrDelft\model\entity\forum\ForumDeel;
use CsrDelft\model\entity\forum\ForumDeelMelding;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\forum\ForumPost;
use CsrDelft\model\entity\Mail;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;

/**
 * Model voor bijhouden, bewerken en verzenden van meldingen voor forumberichten in forumdelen
 *
 * @author J.P.T. Nederveen <ik@tim365.nl>
 */
class ForumDelenMeldingModel extends CachedPersistenceModel {

	const ORM = ForumDeelMelding::class;

	protected function maakForumDeelMelding($forum_id, $uid) {
		$melding = new ForumDeelMelding();
		$melding->forum_id = $forum_id;
		$melding->uid = $uid;
		$this->create($melding);
		return $melding;
	}

	/**
	 * Haal iedereen op die een melding wil voor gegeven forumdeel.
	 * @param ForumDeel $deel
	 * @return Profiel[]
	 */
	public function getMeldingenVoorDeel(ForumDeel $deel) {
		return $this->prefetch('forum_id = ?', [$deel->forum_id]);
	}

	/**
	 * Checkt of gegeven lid melding wil ontvangen voor gegeven forumdeel.
	 * @param ForumDeel $deel
	 * @param string $uid uid van lid, standaard ingelogd lid
	 * @return bool `true` als lid melding wil ontvangen voor gegeven forumdeel
	 */
	public function lidWilMeldingVoorDeel(ForumDeel $deel, $uid = null) {
		if ($uid === null) $uid = LoginModel::getUid();

		return $this->existsByPrimaryKey([$deel->forum_id, $uid]);
	}

	/**
	 * Past gewenste meldingsactie toe voor gegeven lid.
	 *
	 * Als lid wil volgen, maar lid volgt op dit moment nog niet, activeer volgen.
	 * Als lid niet wil volgen, maar lid volgt op dit moment wel, deactiveer volgen.
	 * Anders, doe niets.
	 * @param ForumDeel $deel
	 * @param bool $actief of lid meldingen wil ontvangen
	 * @param string $uid uid van lid, standaard huidig ingelogd lid
	 */
	public function setMeldingVoorLid(ForumDeel $deel, $actief, $uid = null) {
		if ($uid === null) $uid = LoginModel::getUid();

		$lidWilMeldingVoorDeel = $this->lidWilMeldingVoorDeel($deel, $uid);
		if ($lidWilMeldingVoorDeel && !$actief) {
			// Wil niet, heeft nog wel
			$melding = $this->retrieveByPrimaryKey([$deel->forum_id, $uid]);
			$this->delete($melding);
		} elseif (!$lidWilMeldingVoorDeel && $actief) {
			// Wil wel, heeft nog niet
			$this->maakForumDeelMelding($deel->forum_id, $uid);
		}
	}

	/**
	 * Verwijder alle te ontvangen meldingen voor gegeven lid
	 * @param string $uid
	 */
	public function stopAlleMeldingenVoorLid($uid) {
		foreach ($this->find('uid = ?', [$uid]) as $melding) {
			$this->delete($melding);
		}
	}

	/**
	 * Verwijder alle te ontvangen meldingen voor gegeven forumdeel.
	 * @param ForumDeel|int $deel
	 */
	public function stopMeldingenVoorIedereen($deel) {
		$id = $deel instanceof ForumDeel ? $deel->forum_id : $deel;
		foreach ($this->find('forum_id = ?', [$id]) as $melding) {
			$this->delete($melding);
		}
	}

	/**
	 * Stuur alle meldingen rondom forumdelen.
	 * @param ForumPost $post
	 */
	public function stuurMeldingen(ForumPost $post) {
		$this->stuurMeldingenNaarVolgers($post);
	}

	/**
	 * Verzendt mail
	 *
	 * @param Profiel $ontvanger
	 * @param Profiel $auteur
	 * @param ForumPost $post
	 * @param ForumDraad $draad
	 * @param ForumDeel $deel
	 * @param string $bericht
	 */
	private function stuurMelding($ontvanger, $auteur, $post, $draad, $deel, $bericht) {
		$values = array(
			'NAAM' => $ontvanger->getNaam('civitas'),
			'AUTEUR' => $auteur->getNaam('civitas'),
			'POSTLINK' => $post->getLink(true),
			'TITEL' => $draad->titel,
			'FORUMDEEL' => $deel->titel,
			'TEKST' => str_replace('\r\n', "\n", $post->tekst),
		);

		// Stel huidig UID in op ontvanger om te voorkomen dat ontvanger privé of andere persoonlijke info te zien krijgt
		LoginModel::instance()->overrideUid($ontvanger->uid);

		// Verzend mail
		try {
			if ($draad->magMeldingKrijgen()) {
				$mail = new Mail(array($ontvanger->getPrimaryEmail() => $ontvanger->getNaam('volledig')), 'C.S.R. Forum: nieuw draadje in ' . $deel->titel . ': ' . $draad->titel, $bericht);
				$mail->setPlaceholders($values);
				$mail->setLightBB();
				$mail->send();
			}
		} finally {
			// Zet UID terug in sessie
			LoginModel::instance()->resetUid();
		}
	}

	/**
	 * Stuurt meldingen van nieuw bericht naar leden die forumdeel volgen.
	 *
	 * @param ForumPost $post
	 */
	public function stuurMeldingenNaarVolgers(ForumPost $post) {
		$auteur = ProfielModel::get($post->uid);
		$draad = $post->getForumDraad();
		$deel = $draad->getForumDeel();

		// Laad meldingsbericht in
		$bericht = file_get_contents(SMARTY_TEMPLATE_DIR . 'mail/forumdeelmelding.mail');
		foreach ($this->getMeldingenVoorDeel($deel) as $volger) {
			$volger = ProfielModel::get($volger->uid);

			// Stuur geen meldingen als lid niet gevonden is of lid de auteur
			if (!$volger || $volger->uid === $post->uid) {
				continue;
			}
			$this->stuurMelding($volger, $auteur, $post, $draad, $deel, $bericht);
		}
	}
}
