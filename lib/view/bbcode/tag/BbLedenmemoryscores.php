<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\Lichting;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\groepen\VerticalenRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\ledenmemory\LedenMemoryScoreTable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbLedenmemoryscores extends BbTag
{

    /**
     * @var Groep|Lichting|false|null
     */
    private $groep;
    private $titel;
    /**
     * @var VerticalenRepository
     */
    private $verticalenRepository;
    /**
     * @var LichtingenRepository
     */
    private $lichtingenRepository;

    public function __construct(VerticalenRepository $verticalenRepository, LichtingenRepository $lichtingenRepository)
    {
        $this->verticalenRepository = $verticalenRepository;
        $this->lichtingenRepository = $lichtingenRepository;
    }

    public static function getTagName()
    {
        return 'ledenmemoryscores';
    }

    public function isAllowed()
    {
        return LoginService::mag(P_LOGGED_IN);
    }

    public function renderLight()
    {
        return BbHelper::lightLinkBlock('ledenmemoryscores', '/forum/onderwerp/8017', 'Ledenmemory Scores', $this->titel);
    }

    /**
     * @param $arguments
     */
    public function parse($arguments = [])
    {
        $groep = null;
        $titel = null;
        if (isset($arguments['verticale'])) {
            $v = filter_var($arguments['verticale'], FILTER_SANITIZE_STRING);
            if (strlen($v) > 1) {
                $verticale = $this->verticalenRepository->searchByNaam($v);
            } else {
                $verticale = $this->verticalenRepository->get($v);
            }
            if ($verticale) {
                $titel = ' Verticale ' . $verticale->naam;
                $groep = $verticale;
            }
        } elseif (isset($arguments['lichting'])) {
            $l = (int)filter_var($arguments['lichting'], FILTER_SANITIZE_NUMBER_INT);
            if ($l < 1950) {
                $l = LichtingenRepository::getJongsteLidjaar();
            }
            $lichting = $this->lichtingenRepository->get($l);
            if ($lichting) {
                $titel = ' Lichting ' . $lichting->lidjaar;
                $groep = $lichting;
            }
        }
        $this->groep = $groep;
        $this->titel = $titel;
    }

    public function render()
    {
        $table = new LedenMemoryScoreTable($this->groep, $this->titel);
        return $table->getHtml();
    }
}
