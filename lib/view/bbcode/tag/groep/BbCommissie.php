<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\repository\groepen\CommissiesRepository;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbCommissie extends BbTagGroep
{
    public function __construct(CommissiesRepository $model, SerializerInterface $serializer)
    {
        parent::__construct($model, $serializer);
    }

    public static function getTagName()
    {
        return 'commissie';
    }

    public function getLidNaam()
    {
        return 'leden';
    }
}
