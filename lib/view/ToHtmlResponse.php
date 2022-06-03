<?php

namespace CsrDelft\view;

use Symfony\Component\HttpFoundation\Response;

trait ToHtmlResponse
{
	public function toResponse($status = 200): Response
	{
		return new Response($this->__toString(), $status, ['content-type' => 'text/html']);
	}
}
