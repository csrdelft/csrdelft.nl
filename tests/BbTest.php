<?php
declare(strict_types=1);

use CsrDelft\common\ContainerFacade;
use CsrDelft\view\bbcode\CsrBB;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class BbTest extends KernelTestCase
{
	use MatchesSnapshots;

	protected $parser;

	public function setUp(): void
	{
		self::bootKernel();
		ContainerFacade::init(self::$container);
		$this->parser = new CsrBB(self::$container);
	}

	public function testBbSpotify(): void
	{
		$this->assertBbCodeMatchSnapshot(
			'[spotify]spotify:track:4uLU6hMCjMI75M1A2tKUQC[/spotify]'
		);
	}

	private function assertBbCodeMatchSnapshot($code)
	{
		$this->assertMatchesSnapshot($this->parser->getHtml($code));
	}

	public function testBbImage(): void
	{
		$this->assertBbCodeMatchSnapshot(
			'[img]http://www.csrdelft.nl/plaetjes/test.jpg[/] en tekst'
		);
	}

	public function testCitaat(): void
	{
		$this->assertBbCodeMatchSnapshot(
			'[citaat=Albert_Einstein]Why is it that nobody understands me, yet everybody likes me?[/citaat]'
		);
	}
}
