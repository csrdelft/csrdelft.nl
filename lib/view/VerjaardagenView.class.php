<?php
require_once 'model/VerjaardagenModel.class.php';

/**
 * Class AlleVerjaardagenView
 *
 * Laat alle verjaardagen zien
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 */
class AlleVerjaardagenView extends SmartyTemplateView {
    public function getTitel() {
        return "Verjaardagen";
    }

    public function getBreadcrumbs() {
        return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <span class="active">' . $this->getTitel() . '</span>';
    }

    function view() {
        $nu = time();

        $this->smarty->assign('dezemaand', date('n', $nu));
        $this->smarty->assign('dezedag', date('j', $nu));
        $this->smarty->display('verjaardagen/alleverjaardagen.tpl');
    }
}

/**
 * Class KomendeVerjaardagenView
 *
 * Laat komende verjaardagen zien, gebasseerd op LidInstellingen
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 */
class KomendeVerjaardagenView extends SmartyTemplateView {

    function view() {
        $this->smarty->assign('verjaardagen', VerjaardagenModel::getKomende((int)LidInstellingen::get('zijbalk', 'verjaardagen')));
        $this->smarty->assign('toonpasfotos', LidInstellingen::get('zijbalk', 'verjaardagen_pasfotos') == 'ja');
        $this->smarty->display('verjaardagen/komendeverjaardagen.tpl');
    }
}
