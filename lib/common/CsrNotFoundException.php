<?php

namespace CsrDelft\common;

use Throwable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 25/08/2018
 */
class CsrNotFoundException extends CsrException {
	public function __construct($message = "")
	{
		parent::__construct($message, 404);
	}
}
