<?php
/**
 * GroepPasfotosView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\groepen\leden;

use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\groepen\formulier\GroepAanmeldenForm;

class GroepPasfotosView extends GroepTabView {

	protected function getTabContent() {
		$html = '';
		if ($this->groep->mag(AccessAction::Aanmelden)) {
			$orm = get_class($this->groep);
			$leden = $orm::leden;
			$lid = $leden::instance()->nieuw($this->groep, LoginModel::getUid());
			$form = new GroepAanmeldenForm($lid, $this->groep);
			$form->css_classes[] = 'pasfotos';
			$html .= $form->getHtml();
		}
		foreach ($this->groep->getLeden() as $lid) {
			$html .= ProfielModel::getLink($lid->uid, 'pasfoto');
		}
		return $html;
	}

}
