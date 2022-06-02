<?php

namespace CsrDelft\view\groepen;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepMoment;
use CsrDelft\view\datatable\DataTableResponse;
use Exception;

class GroepenBeheerData extends DataTableResponse
{

	/**
	 * @param Groep $groep
	 * @return string
	 * @throws Exception
	 */
	public function renderElement($groep)
	{
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
		if (in_array(GroepMoment::class, class_uses($groep))) {
			$array['status'] = $groep->status->getDescription();
		} else {
			$array['status'] = null;
		}
		$array['samenvatting'] = null;
		$array['omschrijving'] = null;
		$array['website'] = null;
		$array['leden'] = null;

		if (property_exists($groep, 'inAgenda')) {
			$array['inAgenda'] = $groep->inAgenda ? 'ja' : 'nee';
		}

		return $array;
	}

}
