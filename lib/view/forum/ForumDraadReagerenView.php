<?php
/**
 * The ${NAME} file.
 */

namespace CsrDelft\view\forum;

use CsrDelft\view\ForumView;

class ForumDraadReagerenView extends ForumView
{

    public function __construct($lijst)
    {
        parent::__construct($lijst);
    }

    public function view()
    {
        $this->smarty->assign('reageren', $this->model);
        $this->smarty->display('forum/draad_reageren.tpl');
    }

}
