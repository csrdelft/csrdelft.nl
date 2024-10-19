<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\repository\security\AccountRepository;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class UsernameField extends TextField
{
	public function __construct($name, $value)
	{
		parent::__construct($name, $value, 'Gebruikersnaam');
		$this->title = 'Om mee in te loggen in plaats van het lidnummer.';
	}

	public function validate()
	{
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		// check met strtolower is toegevoegd omdat je anders niet van case kan veranderen
		// doordat usernameExists case-insensitive zoekt
		if (
			ContainerFacade::getContainer()
				->get(AccountRepository::class)
				->existsUsername($this->value) &&
			strtolower((string) $this->value) !==
				strtolower((string) $this->origvalue)
		) {
			$this->error = 'Deze gebruikersnaam is al in gebruik';
		}
		return $this->error === '';
	}
}
