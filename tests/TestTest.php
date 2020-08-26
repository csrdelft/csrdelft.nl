<?php
declare(strict_types=1);

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\entity\profiel\Profiel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

define('MODE', 'TEST');

final class TestTest extends KernelTestCase
{
	protected function setUp(): void {
		self::bootKernel();
		ContainerFacade::init(self::$container);
	}

	public function testGetNaam(): void
    {
    	$profiel = new Profiel();
    	$profiel->status = LidStatus::Lid;
    	$profiel->voornaam = "Jan";
    	$profiel->achternaam = "Lid";
    	$this->assertEquals("Am. Lid", $profiel->getNaam('civitas'));
    }
}
