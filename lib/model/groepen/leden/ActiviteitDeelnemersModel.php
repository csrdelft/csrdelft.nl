<?php

namespace CsrDelft\model\groepen\leden;

use CsrDelft\model\entity\groepen\ActiviteitDeelnemer;

class ActiviteitDeelnemersModel extends KetzerDeelnemersModel
{

    const ORM = ActiviteitDeelnemer::class;

    protected static $instance;

}
