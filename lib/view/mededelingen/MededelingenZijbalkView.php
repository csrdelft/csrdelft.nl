<?php
/**
 * MededelingenZijbalkView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */

namespace CsrDelft\view\mededelingen;

use CsrDelft\model\mededelingen\MededelingenModel;
use CsrDelft\view\SmartyTemplateView;

class MededelingenZijbalkView extends SmartyTemplateView {

	public function view() {
		// De laatste n mededelingen ophalen en meegeven aan $this.
		$mededelingen = MededelingenModel::getLaatsteMededelingen($this->model);
		$this->smarty->assign('mededelingen', $mededelingen);

		$this->smarty->display('mededelingen/mededelingenzijbalk.tpl');
	}

}