<?php

namespace CsrDelft\view\documenten;

use CsrDelft\model\documenten\Document;

/**
 * Document downloaden, allemaal headers goedzetten.
 * Ongeldig aangevraagde documenten worden in de controller afgehandeld.
 */
class DocumentDownloadContent extends DocumentenView
{

    public function __construct(Document $document)
    {
        parent::__construct($document);
    }

    public function view()
    {
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $this->model->getMimetype());
        header('Content-Disposition: attachment; filename="' . $this->model->getFileName() . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $this->model->getFileSize());
        readfile($this->model->getFullPath());
    }

}
