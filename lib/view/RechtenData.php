<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 13-6-18
 * Time: 23:34
 */

namespace CsrDelft\view;

use CsrDelft\entity\security\AccessControl;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\view\datatable\DataTableResponse;
use Exception;

class RechtenData extends DataTableResponse {

	/**
	 * @param AccessControl $ac
	 * @throws Exception
	 */
	public function renderElement($ac) {
		$array = (array)$ac;

		$array['action'] = AccessAction::from($ac->action)->getDescription();

		if ($ac->resource === '*') {
			$array['resource'] = 'Elke ' . lcfirst($ac->environment);
		} else {
			$array['resource'] = 'Deze ' . lcfirst($ac->environment);
		}

		return $array;
	}

}
