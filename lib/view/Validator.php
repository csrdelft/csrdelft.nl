<?php

namespace CsrDelft\view;

/**
 * Validator.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een interface met validate() methode om de invoer te checken
 * en verzameling van tegengekomen errors.
 *
 */
interface Validator
{
	public function validate();

	public function getError();
}
