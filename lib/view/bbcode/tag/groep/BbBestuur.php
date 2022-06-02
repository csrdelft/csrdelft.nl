<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\repository\groepen\BesturenRepository;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbBestuur extends BbTagGroep
{
	public function __construct(BesturenRepository $model, SerializerInterface $serializer)
	{
		parent::__construct($model, $serializer);
	}

	public static function getTagName()
	{
		return 'bestuur';
	}

	public function getLidNaam()
	{
		return 'personen';
	}
}
