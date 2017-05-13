<?php
namespace CsrDelft\view\documenten;

use CsrDelft\model\documenten\DocCategorie;

/**
 * Documenten voor een bepaalde categorie tonen.
 */
class DocumentCategorieContent extends DocumentenView
{

    public function __construct(DocCategorie $categorie)
    {
        parent::__construct($categorie, 'Documenten in categorie: ' . $categorie->getNaam());
    }

    public function getBreadcrumbs()
    {
        return parent::getBreadcrumbs() . ' » <span class="active">' . $this->model->getNaam() . '</span>';
    }

    public function view()
    {
        $this->smarty->assign('categorie', $this->model);
        $this->smarty->display('documenten/documentencategorie.tpl');
    }

}
