<?php
/**
 * GroepLijstView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */

namespace CsrDelft\view\groepen\leden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\groepen\formulier\GroepAanmeldenForm;
use CsrDelft\view\groepen\formulier\GroepBewerkenForm;
use CsrDelft\view\Icon;

class GroepLijstView extends GroepTabView
{

    public function getTabContent()
    {
        $em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');

        $html = '<table class="groep-lijst"><tbody>';
        if ($this->groep->mag(AccessAction::Aanmelden())) {
            $html .= '<tr><td colspan="2">';
            $lid = $em->getRepository(GroepLid::class)->nieuw($this->groep, LoginService::getUid());
            $form = new GroepAanmeldenForm($lid, $this->groep, false);
            $html .= $form->getHtml();
            $html .= '</td></tr>';
        }
        $leden = group_by_distinct('uid', $this->groep->getLeden());
        if (empty($leden)) {
            return $html . '</tbody></table>';
        }
        foreach ($this->groep->getLedenOpAchternaamGesorteerd() as $lid) {
            $html .= '<tr><td>';
            if ($lid->uid === LoginService::getUid() and $this->groep->mag(AccessAction::Afmelden())) {
                $html .= '<a href="' . $this->groep->getUrl() . '/ketzer/afmelden" class="post confirm float-start" title="Afmelden">' . Icon::getTag('bullet_delete') . '</a>';
            }
            $html .= ProfielRepository::getLink($lid->uid, 'civitas');
            $html .= '</td><td>';
            if ($lid->uid === LoginService::getUid() and $this->groep->mag(AccessAction::Bewerken())) {
                $form = new GroepBewerkenForm($lid, $this->groep);
                $html .= $form->getHtml();
            } else {
                $html .= $lid->opmerking;
            }
            $html .= '</td></tr>';
        }
        return $html . '</tbody></table>';
    }

}
