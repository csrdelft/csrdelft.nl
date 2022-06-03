<?php

namespace CsrDelft\model\entity\profiel;

/**
 * ProfielLogValueChangeCensuur.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * Gencensureerde wijziging van een waarde in het profiel.
 *
 */
class ProfielLogValueChangeCensuur extends AbstractProfielLogValueChangeEntry
{

    public $oldEmpty;
    public $newEmpty;

    public function __construct($property, $oldEmpty, $newEmpty)
    {
        parent::__construct($property);
        $this->oldEmpty = $oldEmpty;
        $this->newEmpty = $newEmpty;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $old = $this->oldEmpty ? "" : "[GECENSUREERD]";
        $new = $this->newEmpty ? "" : "[GECENSUREERD]";
        return "($this->field) $old => $new";
    }
}