<?php

namespace CsrDelft\view\groepen;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use CsrDelft\view\datatable\DataTableResponse;
use Exception;

class GroepenBeheerData extends DataTableResponse
{
	/**
	 * @param Groep $groep
	 *
	 * @return (mixed|null|string)[]
	 *
	 * @throws Exception
	 *
	 * @psalm-return array{detailSource: string, naam: string, status: mixed|null, samenvatting: null, omschrijving: null, website: null, leden: null, inAgenda?: 'ja'|'nee'|mixed,...}
	 */
	public function renderElement($groep): array
	{
		$array = (array) $groep;

		$array['detailSource'] = $groep->getUrl() . '/leden';

		$title = $groep->naam;
		if (!empty($groep->samenvatting)) {
			$title .= '&#13;&#13;' . mb_substr($groep->samenvatting, 0, 100);
			if (strlen($groep->samenvatting) > 100) {
				$title .= '...';
			}
		}
		$array['naam'] = '<span title="' . $title . '">' . $groep->naam . '</span>';
		if ($groep instanceof HeeftMoment) {
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
