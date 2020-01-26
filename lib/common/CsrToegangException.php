<?php

namespace CsrDelft\common;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 25/08/2018
 */
class CsrToegangException extends CsrException {
	public function __construct($message = "")
	{
		parent::__construct($message, 403);
	}
}
