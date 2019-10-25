<?php
namespace CsrDelft\view;

use Symfony\Component\HttpFoundation\Response;

trait ToHtmlResponse
{
	public function toResponse($status = 200): Response {
		return new Response($this->toString(), $status, ['content-type' => 'text/html']);
	}

	public function toString() : string {
		ob_start();
		$this->view();
		return ob_get_clean();
	}
}
