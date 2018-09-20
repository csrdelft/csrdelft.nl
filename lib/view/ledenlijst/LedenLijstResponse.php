<?php

namespace CsrDelft\view\ledenlijst;

use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\datatable\ServerSideDataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 19/09/2018
 */
class LedenLijstResponse extends ServerSideDataTableResponse {
	/**
	 * @param Profiel $entity
	 * @return string
	 */
	public function toArray($entity) {

		$arr = [
			'UUID' => $entity->getUUID(),
			'pasfoto' => $entity->getPasfotoTag(),
			'uid' => $entity->uid,
			'naam' => [
				'display' => $entity->getLink('volledig'),
				'sort' => $entity->achternaam,
				'export' => $entity->getNaam('volledig'),
				'filter' => $entity->getNaam('volledig'),
			],
			'voorletters' => $entity->voorletters,
			'voornaam' => $entity->voornaam,
			'tussenvoegsel' => $entity->tussenvoegsel,
			'achternaam' => $entity->achternaam,
			'nickname' => $entity->nickname,
			'duckname' => $entity->duckname,
			'geslacht' => $entity->geslacht,
			'email' => $entity->email,
			'adres' => $entity->adres,
			'telefoon' => $entity->telefoon,
			'mobiel' => $entity->mobiel,
			'linkedin' => $entity->linkedin,
			'website' => $entity->website,
			'studie' => $entity->studie,
			'status' => $entity->status,
			'gebdatum' => $entity->gebdatum,
			'beroep' => $entity->beroep,
			'verticale' => $entity->verticale,
			'moot' => $entity->moot,
			'lidjaar' => $entity->lidjaar,
			'kring' => $entity->getKring() ? [
				'display' => '<a href="' . $entity->getKring()->getUrl() . '">' . $entity->getKring()->naam . '</a>',
				'sort' => $entity->getKring()->naam,
				'export' => $entity->getKring()->naam,
				'filter' => $entity->getKring()->naam,
			] : null,
			'patroon' => $entity->patroon ? ProfielModel::get($entity->patroon)->getLink('volledig') : null,
			'woonoord' => $entity->getWoonoord() ? $entity->getWoonoord()->naam : null,
			'bankrekening' => $entity->bankrekening,
			'eetwens' => $entity->eetwens,
		];

		if (LoginModel::mag('P_LEDEN_MOD')) {
			$arr = array_merge($arr, [
				'muziek' => $entity->muziek,
				'ontvangtcontactueel' => $entity->ontvangtcontactueel,
				'kerk' => $entity->kerk,
				'lidafdatum' => $entity->lidafdatum,
				'echtgenoot' => $entity->echtgenoot ? ProfielModel::get($entity->echtgenoot)->getLink('volledig') : null,
				'adresseringechtpaar' => $entity->adresseringechtpaar,
				'land' => $entity->land,
				'bankrekening' => $entity->bankrekening,
				'machtiging' => $entity->machtiging,
			]);
		}

		return $arr;
	}
}
