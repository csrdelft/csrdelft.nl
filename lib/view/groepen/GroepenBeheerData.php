<?php

namespace CsrDelft\view\groepen;

use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\view\datatable\DataTableResponse;
use Exception;

class GroepenBeheerData extends DataTableResponse {

	/**
	 * @param AbstractGroep $groep
	 * @return string
	 * @throws Exception
	 */
	public function renderElement($groep) {
		$array = (array)$groep;

		$array['detailSource'] = $groep->getUrl() . '/leden';

		$title = $groep->naam;
		if (!empty($groep->samenvatting)) {
			$title .= '&#13;&#13;' . mb_substr($groep->samenvatting, 0, 100);
			if (strlen($groep->samenvatting) > 100) {
				$title .= '...';
			}
		}
		$array['naam'] = '<span title="' . $title . '">' . $groep->naam . '</span>';
		$array['status'] = $groep->status->getDescription();
		$array['samenvatting'] = null;
		$array['omschrijving'] = null;
		$array['website'] = null;
		$array['maker_uid'] = null;
		$array['leden'] = null;

		if (property_exists($groep, 'in_agenda')) {
			$array['in_agenda'] = $groep->in_agenda ? 'ja' : 'nee';
		}

		return $array;
	}

}
