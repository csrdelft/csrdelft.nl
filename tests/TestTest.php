<?php
declare(strict_types=1);

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TestTest extends KernelTestCase {
	public function setUp(): void {
		self::bootKernel();
		ContainerFacade::init(self::$container);
	}

	public function testGetNaam(): void {
		$profiel = new Profiel();
		$profiel->status = LidStatus::Lid;
		$profiel->voornaam = "Jan";
		$profiel->achternaam = "Lid";
		$this->assertEquals("Am. Lid", $profiel->getNaam('civitas'));
	}
}
