<?php
/**
 * BerichtenResponse.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\gesprekken;

use CsrDelft\model\entity\gesprekken\GesprekBericht;
use CsrDelft\model\gesprekken\GesprekBerichtenModel;
use CsrDelft\view\datatable\DataTableResponse;

class BerichtenResponse extends DataTableResponse {

	/**
	 * @param GesprekBericht $bericht
	 * @return string
	 */
	public function getJson($bericht) {
		$array = $bericht->jsonSerialize();

		$previous = GesprekBerichtenModel::instance()->find('gesprek_id = ? AND bericht_id < ?', array($bericht->gesprek_id, $bericht->bericht_id), null, 'bericht_id DESC', 1)->fetch();
		$array['inhoud'] = $bericht->getFormatted($previous);

		return parent::getJson($array);
	}

}
