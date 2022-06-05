<?php

namespace CsrDelft\common;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 20170824
 */
class CsrException extends HttpException
{
	public function __construct(
		$message = '',
		$statusCode = 500,
		Exception $previous = null,
		array $headers = [],
		$code = 0
	) {
		parent::__construct($statusCode, $message, $previous, $headers, $code);
	}
}
