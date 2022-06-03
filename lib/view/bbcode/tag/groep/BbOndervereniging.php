<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\repository\groepen\OnderverenigingenRepository;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbOndervereniging extends BbTagGroep
{
    public function __construct(OnderverenigingenRepository $model, SerializerInterface $serializer)
    {
        parent::__construct($model, $serializer);
    }

    public static function getTagName()
    {
        return 'ondervereniging';
    }

    public function getLidNaam()
    {
        return 'leden';
    }
}
