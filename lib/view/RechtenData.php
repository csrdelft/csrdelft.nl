<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 13-6-18
 * Time: 23:34
 */

namespace CsrDelft\view;

use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\entity\security\AccessControl;
use CsrDelft\view\datatable\DataTableResponse;
use Exception;

class RechtenData extends DataTableResponse {

	/**
	 * @param AccessControl $ac
	 * @throws Exception
	 */
	public function renderElement($ac) {
		$array = $ac->jsonSerialize();

		$array['action'] = AccessAction::getDescription($ac->action);

		if ($ac->resource === '*') {
			$array['resource'] = 'Elke ' . lcfirst($ac->environment);
		} else {
			$array['resource'] = 'Deze ' . lcfirst($ac->environment);
		}

		return $array;
	}

}
