<?php

namespace CsrDelft\view\login;

use CsrDelft\view\formulier\datatable\DataTableResponse;
use CsrDelft\view\Icon;

class RememberLoginData extends DataTableResponse {

	public function getJson($remember) {
		$array = $remember->jsonSerialize();

		$array['token'] = null; // keep it private

		$array['remember_since'] = reldate($array['remember_since']);

		if ($remember->lock_ip) {
			$array['lock_ip'] = Icon::getTag('lock', null, 'Gekoppeld aan IP-adres');
		} else {
			$array['lock_ip'] = '';
		}

		return parent::getJson($array);
	}

}
