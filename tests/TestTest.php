<?php
declare(strict_types=1);

use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\Profiel;
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function testGetNaam(): void
    {
    	$profiel = new Profiel();
    	$profiel->status = LidStatus::Lid;
    	$profiel->voornaam = "Jan";
		$profiel->achternaam = "Lid";
		$this->assertEquals("Am. Lid", $profiel->getNaam('civitas'));
    }
}
