<?php

namespace CsrDelft\view\login;

use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\view\datatable\DataTableResponse;
use CsrDelft\view\Icon;

class LoginSessionsData extends DataTableResponse {

	public function getJson($session) {
		$array = $session->jsonSerialize();

		$array['details'] = '<a href="/loginendsession/' . $session->session_hash . '" class="post DataTableResponse SingleRow" title="Log uit">' . Icon::getTag('door_in') . '</a>';

		$array['login_moment'] = reldate($array['login_moment']);

		$array['authentication_method'] = AuthenticationMethod::getDescription($array['authentication_method']);

		if ($session->lock_ip) {
			$array['lock_ip'] = Icon::getTag('lock', null, 'Gekoppeld aan IP-adres');
		} else {
			$array['lock_ip'] = null;
		}

		return parent::getJson($array);
	}

}
