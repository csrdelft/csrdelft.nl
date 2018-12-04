<?php
/**
 * GesprekkenResponse.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\gesprekken;

use CsrDelft\model\entity\gesprekken\Gesprek;
use CsrDelft\model\gesprekken\GesprekBerichtenModel;
use CsrDelft\view\datatable\DataTableResponse;

class GesprekkenResponse extends DataTableResponse {

	/**
	 * @param Gesprek $gesprek
	 * @return string
	 */
	public function getJson($gesprek) {
		$array = $gesprek->jsonSerialize();

		$array['details'] = '<a class="lichtgrijs" href="/gesprekken/web/' . $gesprek->gesprek_id . '">';
		if ($gesprek->aantal_nieuw > 0) {
			$array['details'] .= '<span class="badge">' . $gesprek->aantal_nieuw . '</span>';
		} else {
			$array['details'] .= '<span class="fa fa-envelope fa-lg"></span>';
		}
		$array['details'] .= '</a>';

		$array['deelnemers'] = $gesprek->getDeelnemersFormatted();

		$laatste_bericht = GesprekBerichtenModel::instance()->find('gesprek_id = ?', array($gesprek->gesprek_id), null, 'bericht_id DESC', 1)->fetch();
		if ($laatste_bericht) {
			$array['laatste_update'] = $laatste_bericht->getFormatted(false, 30);
		}

		return parent::getJson($array);
	}

}
