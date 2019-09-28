<?php
declare(strict_types=1);

use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\view\bbcode\CsrBB;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class BbTest extends TestCase
{
	use MatchesSnapshots;

	protected $parser;
	public function setUp(): void
	{
		$this->parser = new CsrBB();
	}

	public function testBbSpotify(): void
	{
		$this->assertBbCodeMatchSnapshot("[spotify]spotify:track:4uLU6hMCjMI75M1A2tKUQC[/spotify]");
	}

	private function assertBbCodeMatchSnapshot($code)
	{
		$this->assertMatchesSnapshot($this->parser->getHtml($code));
	}
}
