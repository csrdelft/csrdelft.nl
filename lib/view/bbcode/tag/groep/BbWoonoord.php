<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\repository\groepen\WoonoordenRepository;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbWoonoord extends BbTagGroep {
	public function __construct(WoonoordenRepository $model, SerializerInterface $serializer) {
		parent::__construct($model, $serializer);
	}

	public static function getTagName() {
		return 'woonoord';
	}

	public function getLidNaam() {
		return 'bewoners';
	}
}
