<?php
declare(strict_types=1);

use CsrDelft\tests\CsrTestCase;
use CsrDelft\view\bbcode\ProsemirrorToBb;

class ProsemirrorToBbTest extends CsrTestCase
{
	/**
	 * @var ProsemirrorToBb
	 */
	private $converter;

	public function setUp(): void
	{
		parent::setUp();

		$this->converter = $this->getContainer()->get(ProsemirrorToBb::class);
	}

	public function testSingleTag()
	{
		$this->assertEquals(
			'[b]vetgedrukt[/b]',
			$this->converter->convertToBb([
				'type' => 'doc',
				'content' => [
					[
						'type' => 'text',
						'marks' => [['type' => 'strong']],
						'text' => 'vetgedrukt',
					],
				],
			])
		);
	}

	/**
	 * Meerdere marks en veranderingen tussen marks worden in losse blokken gesplitst. Dit is om
	 * er voor te zorgen dat de conversie makkelijk gaat en niet te ingewikkeld is.
	 */
	public function testMultipleMark()
	{
		$this->assertEquals(
			'[b]vetgedrukt en [/b][b][i]schuingedrukt[/i][/b]',
			$this->converter->convertToBb([
				'type' => 'doc',
				'content' => [
					[
						'type' => 'text',
						'marks' => [['type' => 'strong']],
						'text' => 'vetgedrukt en ',
					],
					[
						'type' => 'text',
						'marks' => [['type' => 'strong'], ['type' => 'em']],
						'text' => 'schuingedrukt',
					],
				],
			])
		);
	}

	public function testLink()
	{
		$this->assertEquals(
			'Een linkje naar: [url=https://google.com]Google[/url]',
			$this->converter->convertToBb([
				'type' => 'doc',
				'content' => [
					[
						'type' => 'text',
						'text' => 'Een linkje naar: ',
					],
					[
						'type' => 'text',
						'text' => 'Google',
						'marks' => [
							['type' => 'link', 'attrs' => ['href' => 'https://google.com']],
						],
					],
				],
			])
		);
	}
}
