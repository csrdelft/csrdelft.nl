<?php
/**
 * GroepLijstView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\groepen\leden;

use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\groepen\formulier\GroepAanmeldenForm;
use CsrDelft\view\groepen\formulier\GroepBewerkenForm;
use CsrDelft\view\Icon;

class GroepLijstView extends GroepTabView {

	public function getTabContent() {
		$html = '<table class="groep-lijst"><tbody>';
		if ($this->groep->mag(AccessAction::Aanmelden)) {
			$html .= '<tr><td colspan="2">';
			$lid = $this->groep::getLedenModel()->nieuw($this->groep, LoginModel::getUid());
			$form = new GroepAanmeldenForm($lid, $this->groep, false);
			$html .= $form->getHtml();
			$html .= '</td></tr>';
		}
		$leden = group_by_distinct('uid', $this->groep->getLeden());
		if (empty($leden)) {
			return $html . '</tbody></table>';
		}
		// sorteren op achernaam
		$uids = array_keys($leden);
		/** @var Profiel[] $profielen */
		$profielen = ProfielModel::instance()->prefetch('uid IN (' . implode(', ', array_fill(0, count($uids), '?')) . ')', $uids, null, 'achternaam ASC');
		foreach ($profielen as $profiel) {
			$html .= '<tr><td>';
			if ($profiel->uid === LoginModel::getUid() AND $this->groep->mag(AccessAction::Afmelden)) {
				$html .= '<a href="' . $this->groep->getUrl() . '/ketzer/afmelden" class="post confirm float-left" title="Afmelden">' . Icon::getTag('bullet_delete') . '</a>';
			}
			$html .= $profiel->getLink('civitas');
			$html .= '</td><td>';
			if ($profiel->uid === LoginModel::getUid() AND $this->groep->mag(AccessAction::Bewerken)) {
				$form = new GroepBewerkenForm($leden[$profiel->uid], $this->groep);
				$html .= $form->getHtml();
			} else {
				$html .= $leden[$profiel->uid]->opmerking;
			}
			$html .= '</td></tr>';
		}
		return $html . '</tbody></table>';
	}

}
