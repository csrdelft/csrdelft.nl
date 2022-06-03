<?php

namespace CsrDelft\view\maalcie\corvee\functies;

use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use CsrDelft\view\View;

/**
 * Requires id of deleted corveefunctie.
 */
class FunctieDeleteView implements ToResponse, View
{
    use ToHtmlResponse;

    private $functieId;

    public function __construct($functieId)
    {
        $this->functieId = $functieId;
    }

    public function __toString()
    {
        $html = '';
        $html .= '<tr id="corveefunctie-row-' . $this->functieId . '" class="remove"></tr>';
        $html .= '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';

        return $html;
    }

    public function getTitel()
    {
        return '';
    }

    public function getBreadcrumbs()
    {
        return '';
    }

    public function getModel()
    {
        return $this->functieId;
    }
}
