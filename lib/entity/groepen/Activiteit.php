<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Enum;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;


/**
 * Activiteit.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\ActiviteitenRepository")
 */
class Activiteit extends Groep implements Agendeerbaar, HeeftAanmeldLimiet, HeeftSoort
{
    use GroepMoment;
    use GroepAanmeldMoment;
    use GroepAanmeldRechten;
    use GroepAanmeldLimiet;

    /**
     * Intern / Extern / SjaarsActie / etc.
     * @var ActiviteitSoort
     * @ORM\Column(type="enumActiviteitSoort")
     * @Serializer\Groups("datatable")
     */
    public $activiteitSoort;
    /**
     * Locatie
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Groups("datatable")
     */
    public $locatie;
    /**
     * Tonen in agenda
     * @var boolean
     * @ORM\Column(type="boolean")
     * @Serializer\Groups("datatable")
     */
    public $inAgenda;

    /**
     * Rechten voor de gehele klasse of soort groep?
     *
     * @param AccessAction $action
     * @param array|null $allowedAuthenticationMethods
     * @param Enum $soort
     * @return boolean
     */
    public static function magAlgemeen($action, $allowedAuthenticationMethods = null, $soort = null)
    {
        if ($soort && $soort instanceof ActiviteitSoort) {
            switch ($soort) {

                case ActiviteitSoort::OWee():
                    if (LoginService::mag('commissie:OWeeCie', $allowedAuthenticationMethods)) {
                        return true;
                    }
                    break;

                case ActiviteitSoort::Dies():
                    if (LoginService::mag('commissie:DiesCie', $allowedAuthenticationMethods)) {
                        return true;
                    }
                    break;

                case ActiviteitSoort::Lustrum():
                    if (LoginService::mag('commissie:LustrumCie', $allowedAuthenticationMethods)) {
                        return true;
                    }
                    break;
            }
        }
        switch ($action) {

            case AccessAction::Aanmaken():
            case AccessAction::Aanmelden():
            case AccessAction::Bewerken():
            case AccessAction::Afmelden():
                return true;
        }
        return parent::magAlgemeen($action, $allowedAuthenticationMethods, $soort);
    }

    public function getUUID()
    {
        return $this->id . '@activiteit.csrdelft.nl';
    }

    public function getUrl()
    {
        return '/groepen/activiteiten/' . $this->id;
    }

    // Agendeerbaar:

    public function getTitel()
    {
        return $this->naam;
    }

    public function getBeschrijving()
    {
        return $this->samenvatting;
    }

    public function getLocatie()
    {
        return $this->locatie;
    }

    public function isTransparant()
    {
        // Toon als transparant (vrij) als lid dat wil, activiteit hele dag(en) duurt of lid niet ingeketzt is
        return lid_instelling('agenda', 'transparantICal') === 'ja'
            || $this->isHeledag()
            || !$this->getLid(LoginService::getUid());
    }

    public function isHeledag()
    {
        $begin = date('H:i', $this->getBeginMoment());
        $eind = date('H:i', $this->getEindMoment());
        return $begin == '00:00' && ($eind == '23:59' || $eind == '00:00');
    }

    public function getBeginMoment()
    {
        return $this->beginMoment->getTimestamp();
    }

    public function getEindMoment()
    {
        if ($this->eindMoment && $this->eindMoment !== $this->beginMoment) {
            return $this->eindMoment->getTimestamp();
        }
        return $this->getBeginMoment() + 1800;
    }

    public function getAanmeldLimiet()
    {
        return $this->aanmeldLimiet;
    }

    public function getSoort()
    {
        return $this->activiteitSoort;
    }

    public function setSoort($soort)
    {
        $this->activiteitSoort = $soort;
    }
}
