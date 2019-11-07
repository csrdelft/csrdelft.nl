<?php

namespace CsrDelft\common;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 20170824
 */
class CsrGebruikerException extends CsrException {
	public function __construct($message, $statusCode = 200, \Exception $previous = null, array $headers = [], $code = 0)
	{
		parent::__construct($message, $statusCode, $previous, $headers, $code);
	}
}
