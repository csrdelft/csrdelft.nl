<?php

namespace CsrDelft\common;

use Throwable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 25/08/2018
 */
class CsrToegangException extends CsrException {
	public function __construct($message, $statusCode = 403, \Exception $previous = null, array $headers = [], $code = 0)
	{
		parent::__construct($message, $statusCode, $previous, $headers, $code);
	}
}
