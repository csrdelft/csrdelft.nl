<?php
/**
 * GroepPasfotosView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\groepen\leden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\groepen\formulier\GroepAanmeldenForm;

class GroepPasfotosView extends GroepTabView {

	protected function getTabContent() {
		$html = '';
		if ($this->groep->mag(AccessAction::Aanmelden)) {
			$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');
			$lid = $em->getRepository($this->groep->getLidType())->nieuw($this->groep, LoginModel::getUid());
			$form = new GroepAanmeldenForm($lid, $this->groep);
			$form->css_classes[] = 'pasfotos';
			$html .= $form->getHtml();
		}
		foreach ($this->groep->getLeden() as $lid) {
			$html .= ProfielRepository::getLink($lid->uid, 'pasfoto');
		}
		return $html;
	}

}
