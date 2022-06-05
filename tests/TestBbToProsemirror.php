<?php
declare(strict_types=1);

use CsrDelft\tests\CsrTestCase;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\bbcode\CsrBB;

final class TestBbToProsemirror extends CsrTestCase
{
	protected $parser;
	/**
	 * @var BbToProsemirror
	 */
	private $converter;

	public function setUp(): void
	{
		parent::setUp();

		$this->parser = new CsrBB(self::$container);
		$this->converter = self::$container->get(BbToProsemirror::class);
	}

	public function testString()
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

	public function testBold()
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

	public function testMultipleMark()
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

	public function testLink()
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
