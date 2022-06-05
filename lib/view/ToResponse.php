<?php

namespace CsrDelft\view;

use Symfony\Component\HttpFoundation\Response;

interface ToResponse
{
	public function toResponse(): Response;
}
