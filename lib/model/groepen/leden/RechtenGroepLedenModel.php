<?php
/**
 * The ${NAME} file.
 */

namespace CsrDelft\model\groepen\leden;

use CsrDelft\model\AbstractGroepLedenModel;
use CsrDelft\model\entity\groepen\RechtenGroepLid;

class RechtenGroepLedenModel extends AbstractGroepLedenModel
{

    const ORM = RechtenGroepLid::class;

    protected static $instance;

}
