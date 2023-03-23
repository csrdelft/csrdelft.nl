<?php
declare(strict_types=1);

use CsrDelft\tests\CsrTestCase;
use CsrDelft\view\bbcode\CsrBB;
use Spatie\Snapshots\MatchesSnapshots;

final class BbTest extends CsrTestCase
{
	use MatchesSnapshots;

	protected $parser;

	public function setUp(): void
	{
		parent::setUp();
		$this->parser = $this->getContainer()->get(CsrBB::class);
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
