<?php
/**
 * AgendaZijbalkView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */

namespace CsrDelft\view\agenda;

use CsrDelft\model\LidInstellingenModel;

class AgendaZijbalkView extends AgendaItemsView {

	public function view() {
		if (count($this->items) > LidInstellingenModel::get('zijbalk', 'agenda_max')) {
			$this->items = array_slice($this->items, 0, LidInstellingenModel::get('zijbalk', 'agenda_max'));
		}
		$this->smarty->assign('items', $this->items);
		$this->smarty->display('agenda/zijbalk.tpl');
	}

}