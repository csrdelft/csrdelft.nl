<?php

namespace CsrDelft\model\forum;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\forum\ForumDraadVolgen;
use CsrDelft\model\entity\forum\ForumDraadVolgenNiveau;
use CsrDelft\model\entity\forum\ForumPost;
use CsrDelft\model\entity\Mail;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;

/**
 * ForumDradenVolgenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class ForumDradenVolgenModel extends CachedPersistenceModel {

	const ORM = ForumDraadVolgen::class;

	protected function maakForumDraadVolgen($draad_id) {
		$volgen = new ForumDraadVolgen();
		$volgen->draad_id = $draad_id;
		$volgen->uid = LoginModel::getUid();
		$this->create($volgen);
		return $volgen;
	}

	public function getAantalVolgenVoorLid() {
		return $this->count('uid = ?', array(LoginModel::getUid()));
	}

	public function getVolgersVanDraad(ForumDraad $draad) {
		return $this->prefetch('draad_id = ? AND niveau = ?', array($draad->draad_id, ForumDraadVolgenNiveau::altijd));
	}

	public function getVolgenVoorLid(ForumDraad $draad) {
		return $this->existsByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
	}

	public function setVolgenVoorLid(ForumDraad $draad, $volgen = true) {
		if ($volgen) {
			if (!$this->getVolgenVoorLid($draad)) {
				$this->maakForumDraadVolgen($draad->draad_id);
			}
		} elseif ($this->getVolgenVoorLid($draad)) {
			$rowCount = $this->deleteByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
			if ($rowCount !== 1) {
				throw new CsrException('Volgen stoppen mislukt');
			}
		}
	}

	public function volgNietsVoorLid($uid) {
		foreach ($this->find('uid = ?', array($uid)) as $volgen) {
			$this->delete($volgen);
		}
	}

	public function stopVolgenVoorIedereen(ForumDraad $draad) {
		foreach ($this->find('draad_id = ?', array($draad->draad_id)) as $volgen) {
			$this->delete($volgen);
		}
	}

    /**
     * Stuurt meldingen van nieuw bericht naar volgers van draadje
     *
     * @param ForumPost $post
     */
	public function stuurMeldingenNaarVolgers(ForumPost $post) {
        $auteur = ProfielModel::get($post->uid);
        $draad = $post->getForumDraad();

        // Laad meldingsbericht in
        $bericht = file_get_contents(SMARTY_TEMPLATE_DIR . 'mail/forumvolgendmelding.mail');
	    foreach ($draad->getVolgers() as $volger) {
            $volger = ProfielModel::get($volger->uid);
            if ($volger->uid === $post->uid OR !$volger) {
                continue;
            }

            $volgerNaam = $volger->getNaam('civitas');

			$values = array(
				'NAAM' => $volgerNaam,
                'AUTEUR' => $auteur->getNaam('civitas'),
                'POSTLINK' => $post->getLink(true),
                'TITEL' => $draad->titel,
                'TEKST' => str_replace('\r\n', "\n", $post->tekst),
			);

            $mail = new Mail(array($volger->getPrimaryEmail() => $volgerNaam), 'C.S.R. Forum: nieuwe reactie op ' . $draad->titel, $bericht);
            $mail->setPlaceholders($values);
            $mail->setLightBB();
            $mail->send();
        }
    }
}
