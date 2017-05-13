<?php

namespace CsrDelft\view\documenten;

use CsrDelft\model\documenten\DocCategorie;

class DocumentenContent extends DocumentenView
{

    public function __construct()
    {
        $categorieen = array();
        foreach (DocCategorie::getAll() as $categorie) {
            if ($categorie->magBekijken()) {
                $categorieen[] = $categorie;
            }
        }
        parent::__construct($categorieen, 'Documentenketzer');
    }

    public function view()
    {
        $this->smarty->assign('categorieen', $this->model);
        $this->smarty->display('documenten/documenten.tpl');
    }

}
