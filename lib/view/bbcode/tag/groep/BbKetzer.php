<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\repository\groepen\KetzersRepository;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbKetzer extends BbTagGroep
{
	public function __construct(KetzersRepository $model, SerializerInterface $serializer)
	{
		parent::__construct($model, $serializer);
	}

	public static function getTagName()
	{
		return 'ketzer';
	}

	public function getLidNaam()
	{
		return 'aanmeldingen';
	}
}
