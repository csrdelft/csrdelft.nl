<?php

namespace CsrDelft\common;

use Throwable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 25/08/2018
 */
class CsrToegangException extends CsrException {
	public function __construct(string $message = "", int $code = 403, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}

}
