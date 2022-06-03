<?php

namespace CsrDelft\model\entity\profiel;

/**
 * AbstractProfielLogEntry.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 */
abstract class AbstractProfielLogEntry
{

    public abstract function toHtml();

    public function censureerVeld($naam)
    {
        return $this;
    }

}