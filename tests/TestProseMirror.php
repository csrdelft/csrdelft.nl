<?php
declare(strict_types=1);

use CsrDelft\common\ContainerFacade;
use CsrDelft\view\bbcode\BbToProseMirror;
use CsrDelft\view\bbcode\CsrBB;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TestProseMirror extends KernelTestCase
{
	protected $parser;
	/**
	 * @var BbToProseMirror
	 */
	private $converter;

	public function setUp(): void
	{
		self::bootKernel();
		ContainerFacade::init(self::$container);
		$this->parser = new CsrBB(self::$container);
		$this->converter = new BbToProseMirror($this->parser);
	}

	public function testString()
	{
		$this->assertEquals([
			'type' => 'doc',
			'content' => [
				[
					'type' => 'text',
					'text' => 'testString',
				],
			],
		], $this->converter->toProseMirror("testString"));
	}

	public function testBold()
	{
		$this->assertEquals([
			'type' => 'doc',
			'content' => [
				[
					'type' => 'text',
					'marks' => [
						['type' => 'bold'],
					],
					'text' => 'vetgedrukt',
				]
			],
		], $this->converter->toProseMirror("[b]vetgedrukt[/b]"));
	}

	public function testMultipleMark()
	{
		$this->assertEquals([
			'type' => 'doc',
			'content' => [
				[
					'type' => 'text',
					'marks' => [
						['type' => 'bold'],
					],
					'text' => 'vetgedrukt en ',
				],
				[
					'type' => 'text',
					'marks' => [
						['type' => 'bold'],
						['type' => 'italic'],
					],
					'text' => 'schuingedrukt',
				],
			],
		], $this->converter->toProseMirror("[b]vetgedrukt en [i]schuingedrukt[/i][/b]"));
	}

}
