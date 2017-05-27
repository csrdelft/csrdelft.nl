<?php

namespace CsrDelft\view;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\VerjaardagenModel;
use CsrDelft\view\forum\ForumDraadZijbalkView;

/**
 * Class VoorpaginaView.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class VoorpaginaView extends SmartyTemplateView
{
    function view()
    {
        $forum_belangrijk = (LidInstellingenModel::get('zijbalk', 'forum_belangrijk') > 0 ? false : null);

        $verjaardagen = VerjaardagenModel::getKomende((int)LidInstellingenModel::get('zijbalk', 'verjaardagen'));
        $forum = ForumDradenModel::instance()->getRecenteForumDraden(
            (int) LidInstellingenModel::get('zijbalk', 'forum'),
            $forum_belangrijk
        );

        $this->smarty->assign([
            'verjaardagen' => new KomendeVerjaardagenView(
                $verjaardagen,
                LidInstellingenModel::get('zijbalk', 'verjaardagen_pasfotos') == 'ja'
            ),
            'forum' => new ForumDraadZijbalkView($forum, $forum_belangrijk)
        ]);

        $this->smarty->display('layout/voorpagina.tpl');
    }
}
