<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 15/03/2019
 */
class HighlightZoektermTest extends TestCase {
	public function testSplitString() {
		$this->assertEquals('Lorem ipsum dolor sit amet,…',
			split_on_keyword('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque aliquam, justo ac blandit fringilla, ', 'do',	10,	2)
		);

		$this->assertEquals('…nunc temsplitpus turpis, eget feugiat enim neque quis nulla. Vestibulum frisplitngilla nisl sit amet odio convallis',
			split_on_keyword('Lorem ipsum split dolor sit amet, consectetur adipiscing elit. Quisque aliquam, justo ac blandit fringilla, sem nunc temsplitpus turpis, eget feugiat enim neque quis nulla. Vestibulum frisplitngilla nisl sit amet odio convallis', 'odio')
		);

		$this->assertEquals('Lorem ipsum split dolor sit amet, consectetur adipiscing elit. Quisque aliquam, justo ac blandit fringilla, sem nunc temsplitpus turpis, eget feugiat enim neque quis nulla. Vestibulum frisplitngilla nisl sit amet odio convallis, id auctor mauris lacinia. Praesent est ligula, ullamcorper id metus…iaculis. Sed bibendum auctor lacus non iaculis. Integer ut tempor nisl, non rutrum massa. Vestibulum maurissplit ipsum, gravida ac dui a, tempor consequat justo. Nullam augue sem, malesuada pellentesque magna sit amet, vehicula tristique sapien. Phasellus ac auctor risus, eu ultricies nisl. In ut urna sit…',
			split_on_keyword('Lorem ipsum split dolor sit amet, consectetur adipiscing elit. Quisque aliquam, justo ac blandit fringilla, sem nunc temsplitpus turpis, eget feugiat enim neque quis nulla. Vestibulum frisplitngilla nisl sit amet odio convallis, id auctor mauris lacinia. Praesent est ligula, ullamcorper id metus quis, tincidunt mattis quam. Duis suscipit vulputate ornare. Duis dictum, libero vitae placerat consectetur, nisl nisl finibus felis, at mattis odio eros id enim. Aliquam cursus efficitur iaculis. Sed bibendum auctor lacus non iaculis. Integer ut tempor nisl, non rutrum massa. Vestibulum maurissplit ipsum, gravida ac dui a, tempor consequat justo. Nullam augue sem, malesuada pellentesque magna sit amet, vehicula tristique sapien. Phasellus ac auctor risus, eu ultricies nisl. In ut urna sit amet eros vehicula mattis. Nam tristique ligula vel volutpat porta.', 'split')
		);

		$this->assertEquals('…hoogleraar christelijke filosofie aan de Universiteit Leiden. De koffie staat klaar vanaf 19:30, de lezing vangt aan om 20:00. Wees welkom!',
			split_on_keyword('Zij is bijzonder hoogleraar christelijke filosofie aan de Universiteit Leiden. De koffie staat klaar vanaf 19:30, de lezing vangt aan om 20:00. Wees welkom!', 'lezing'));
		$this->assertEquals('',
			split_on_keyword('Vanavond 8 uur is er een [url=http://sg.tudelft.nl/event/wetenschap-vs-religie-met-filosoof-herman-philipse/]lezing[/url] over geloof en wetenschap door Herman Philipse op Virgiel. Ze kwamen ons afgelopen maaltijd nog uitnodigen.  Het lijkt me zelf wel interessant om er naar toe te gaan ik weet alleen niet of ik optijd er kan zijn.   Het is een wetenschapsfilosoof en schrijver en heeft boeken geschreven zoals het Atheïstisch manifest. Als het goed is is het een interactieve lezing, dus dan kunnen we ook het christelijke standpunt inbrengen. Nu is het makkelijker vragen stellen als je met meer ben dus wie wil ook naar de lezing gaan vanavond, misschien kunnen de genen die wille gaan verzamelen bij Confide en dan naar Virgiel gaan.', 'lezing'));
	}

}
