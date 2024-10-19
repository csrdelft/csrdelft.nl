<?php
declare(strict_types=1);

use CsrDelft\tests\CsrTestCase;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\bbcode\CsrBB;

final class BbToProsemirrorTest extends CsrTestCase
{
	protected $parser;
	/**
	 * @var BbToProsemirror
	 */
	private $converter;

	public function setUp(): void
	{
		parent::setUp();

		$this->parser = new CsrBB($this->getContainer());
		$this->converter = $this->getContainer()->get(BbToProsemirror::class);
	}

	public function testString(): void
	{
		$this->assertEquals(
			[
				'type' => 'doc',
				'content' => [
					[
						'type' => 'text',
						'text' => 'testString',
					],
				],
			],
			$this->converter->toProseMirror('testString')
		);
	}

	public function testBold(): void
	{
		$this->assertEquals(
			[
				'type' => 'doc',
				'content' => [
					[
						'type' => 'text',
						'marks' => [['type' => 'strong']],
						'text' => 'vetgedrukt',
					],
				],
			],
			$this->converter->toProseMirror('[b]vetgedrukt[/b]')
		);
	}

	public function testMultipleMark(): void
	{
		$this->assertEquals(
			[
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
			],
			$this->converter->toProseMirror(
				'[b]vetgedrukt en [i]schuingedrukt[/i][/b]'
			)
		);
	}

	public function testLink(): void
	{
		$this->assertEquals(
			[
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
			],
			$this->converter->toProseMirror(
				'Een linkje naar: [url=https://google.com]Google[/url]'
			)
		);
	}
}
