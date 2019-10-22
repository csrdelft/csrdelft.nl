<?php
namespace CsrDelft\view;

use Symfony\Component\HttpFoundation\Response;

trait ToHtmlResponse
{
	public function toResponse(): Response
	{
		ob_start();
		$this->view();
		return new Response(ob_get_clean());
	}
}
