<?php

namespace CsrDelft\view\formulier\keuzevelden;
/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class KerkField extends SelectField
{

    public function __construct($name, $value, $description)
    {
        $kerken = array(
            'PKN', 'PKN Hervormd', 'PKN Gereformeerd', 'PKN Gereformeerde Bond', 'Hersteld Hervormd',
            'Evangelisch', 'Volle Evangelie Gemeente', 'Gereformeerd Vrijgemaakt', 'Nederlands Gereformeerd',
            'Christelijk Gereformeerd', 'Gereformeerde Gemeenten', 'Pinkstergemeente', 'Katholiek Apostolisch',
            'Vergadering van gelovigen', 'Rooms-Katholiek', 'Baptist');
        parent::__construct($name, $value, $description, $kerken);
    }

}
