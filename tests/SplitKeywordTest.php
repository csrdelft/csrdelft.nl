<?php
declare(strict_types=1);

use CsrDelft\common\Util\TextUtil;
use PHPUnit\Framework\TestCase;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 15/03/2019
 */
class SplitKeywordTest extends TestCase
{
	public function testSplitString()
	{
		$this->assertEquals(
			'Lorem ipsum dolor sit amet,…',
			TextUtil::split_on_keyword(
				'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque aliquam, justo ac blandit fringilla, ',
				'do',
				10,
				2
			)
		);

		$this->assertEquals(
			'…nunc temsplitpus turpis, eget feugiat enim neque quis nulla. Vestibulum frisplitngilla nisl sit amet odio convallis',
			TextUtil::split_on_keyword(
				'Lorem ipsum split dolor sit amet, consectetur adipiscing elit. Quisque aliquam, justo ac blandit fringilla, sem nunc temsplitpus turpis, eget feugiat enim neque quis nulla. Vestibulum frisplitngilla nisl sit amet odio convallis',
				'odio'
			)
		);

		$this->assertEquals(
			'Lorem ipsum split dolor sit amet, consectetur adipiscing elit. Quisque aliquam, justo ac blandit fringilla, sem nunc temsplitpus turpis, eget feugiat enim neque quis nulla. Vestibulum frisplitngilla nisl sit amet odio convallis, id auctor mauris lacinia. Praesent est ligula, ullamcorper id metus…iaculis. Sed bibendum auctor lacus non iaculis. Integer ut tempor nisl, non rutrum massa. Vestibulum maurissplit ipsum, gravida ac dui a, tempor consequat justo. Nullam augue sem, malesuada pellentesque magna sit amet, vehicula tristique sapien. Phasellus ac auctor risus, eu ultricies nisl. In ut urna sit amet eros vehicula mattis. Nam tristique ligula vel volutpat porta.',
			TextUtil::split_on_keyword(
				'Lorem ipsum split dolor sit amet, consectetur adipiscing elit. Quisque aliquam, justo ac blandit fringilla, sem nunc temsplitpus turpis, eget feugiat enim neque quis nulla. Vestibulum frisplitngilla nisl sit amet odio convallis, id auctor mauris lacinia. Praesent est ligula, ullamcorper id metus quis, tincidunt mattis quam. Duis suscipit vulputate ornare. Duis dictum, libero vitae placerat consectetur, nisl nisl finibus felis, at mattis odio eros id enim. Aliquam cursus efficitur iaculis. Sed bibendum auctor lacus non iaculis. Integer ut tempor nisl, non rutrum massa. Vestibulum maurissplit ipsum, gravida ac dui a, tempor consequat justo. Nullam augue sem, malesuada pellentesque magna sit amet, vehicula tristique sapien. Phasellus ac auctor risus, eu ultricies nisl. In ut urna sit amet eros vehicula mattis. Nam tristique ligula vel volutpat porta.',
				'split'
			)
		);

		$this->assertEquals(
			'…hoogleraar christelijke filosofie aan de Universiteit Leiden. De koffie staat klaar vanaf 19:30, de lezing vangt aan om 20:00. Wees welkom!',
			TextUtil::split_on_keyword(
				'Zij is bijzonder hoogleraar christelijke filosofie aan de Universiteit Leiden. De koffie staat klaar vanaf 19:30, de lezing vangt aan om 20:00. Wees welkom!',
				'lezing'
			)
		);
	}

	public function testSplitMultiByte()
	{
		for ($i = 0; $i < 5; $i++) {
			$this->assertEquals(
				'…🦄 removed word word split.',
				TextUtil::split_on_keyword(
					'Test 🦄 removed word word split.',
					'split',
					16 + $i,
					2
				)
			);
		}
	}
}
