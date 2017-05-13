<?php

namespace CsrDelft\view\documenten;

use CsrDelft\model\documenten\Document;

/**
 * Document bekijken.
 * Ongeldig aangevraagde documenten worden in de controller afgehandeld.
 */
class DocumentContent extends DocumentenView
{

    public function __construct(Document $document)
    {
        parent::__construct($document);
    }

    public function view()
    {
        $mime = $this->model->getMimetype();
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: ' . $mime);
        if (!strstr($mime, 'image') AND !strstr($mime, 'text')) {
            header('Content-Disposition: inline; filename="' . $this->model->getFileName() . '";');
            header('Content-Lenght: ' . $this->model->getFileSize() . ';');
        }
        readfile($this->model->getFullPath());
    }

}
