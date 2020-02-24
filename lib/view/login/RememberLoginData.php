<?php

namespace CsrDelft\view\login;

use CsrDelft\view\datatable\DataTableResponse;
use CsrDelft\view\Icon;

class RememberLoginData extends DataTableResponse {

	public function renderElement($remember) {
		$array = (array)$remember;

		$array['token'] = null; // keep it private

		$array['remember_since'] = reldate($array['remember_since']);

		if ($remember->lock_ip) {
			$array['lock_ip'] = Icon::getTag('lock', null, 'Gekoppeld aan IP-adres');
		} else {
			$array['lock_ip'] = '';
		}

		return $array;
	}

}
