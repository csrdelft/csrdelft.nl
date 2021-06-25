<?php

namespace CsrDelft\tests;


use CsrDelft\common\ContainerFacade;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CsrTestCase extends KernelTestCase
{
	public function setUp(): void
	{
		self::bootKernel();
		ContainerFacade::init(self::$container);
	}
}
