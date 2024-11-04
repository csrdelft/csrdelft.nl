<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\entity\bibliotheek\BiebAuteur;
use CsrDelft\entity\bibliotheek\Boek;
use CsrDelft\entity\bibliotheek\BoekExemplaar;
use CsrDelft\entity\bibliotheek\BoekRecensie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\bibliotheek\BiebAuteurRepository;
use CsrDelft\repository\bibliotheek\BiebRubriekRepository;
use CsrDelft\repository\bibliotheek\BoekExemplaarRepository;
use CsrDelft\repository\bibliotheek\BoekRecensieRepository;
use CsrDelft\repository\bibliotheek\BoekRepository;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\BoekImporter;
use CsrDelft\view\bibliotheek\BibliotheekCatalogusDatatable;
use CsrDelft\view\bibliotheek\BibliotheekCatalogusDatatableResponse;
use CsrDelft\view\bibliotheek\BoekExemplaarFormulier;
use CsrDelft\view\bibliotheek\BoekFormulier;
use CsrDelft\view\bibliotheek\BoekRecensieFormulier;
use CsrDelft\view\Icon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BibliotheekController.class.php  |  Gerrit Uitslag (klapinklapin@gmail.com)
 *
 */
class BibliotheekController extends AbstractController
{

}
