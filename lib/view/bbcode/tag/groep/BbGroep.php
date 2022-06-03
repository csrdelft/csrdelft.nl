<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\repository\groepen\RechtenGroepenRepository;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbGroep extends BbTagGroep
{
	public function __construct(RechtenGroepenRepository $model, SerializerInterface $serializer)
	{
		parent::__construct($model, $serializer);
	}

	public static function getTagName()
	{
		return 'groep';
	}

	public function getLidNaam()
	{
		return 'personen';
	}
}
