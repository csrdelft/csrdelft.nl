<?php

namespace App\Http\Controllers;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\AgendaController;
use CsrDelft\controller\BibliotheekController;
use CsrDelft\controller\CmsPaginaController;
use CsrDelft\controller\CommissieVoorkeurenController;
use CsrDelft\controller\CourantController;
use CsrDelft\controller\DocumentenController;
use CsrDelft\controller\EetplanController;
use CsrDelft\controller\FiscaatRouterController;
use CsrDelft\controller\ForumController;
use CsrDelft\controller\FotoAlbumController;
use CsrDelft\controller\GesprekkenController;
use CsrDelft\controller\GoogleController;
use CsrDelft\controller\GroepenRouterController;
use CsrDelft\controller\InstellingenBeheerController;
use CsrDelft\controller\LidInstellingenController;
use CsrDelft\controller\MaalcieRouterController;
use CsrDelft\controller\MededelingenController;
use CsrDelft\controller\MenuBeheerController;
use CsrDelft\controller\PeilingenController;
use CsrDelft\controller\ProfielController;
use CsrDelft\controller\RechtenController;

class LegacyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    private function handle($controllerClass)
    {
        /** @var \CsrDelft\controller\framework\Controller $controller */
        $controller = new $controllerClass(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL));

        try {
            $controller->performAction();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (CsrGebruikerException $exception) {
            http_response_code(400);
            echo $exception->getMessage();
            exit;
        }

        return $controller->getView();
    }

    public function index()
    {
        return $this->handle(CmsPaginaController::class);
    }

    public function profiel()
    {
        return $this->handle(ProfielController::class);
    }

    public function forum()
    {
        return $this->handle(ForumController::class);
    }

    public function agenda()
    {
        return $this->handle(AgendaController::class);
    }

    public function eetplan()
    {
        return $this->handle(EetplanController::class);
    }

    public function documenten()
    {
        return $this->handle(DocumentenController::class);
    }

    public function bibliotheek()
    {
        return $this->handle(BibliotheekController::class);
    }

    public function mededelingen()
    {
        return $this->handle(MededelingenController::class);
    }

    public function peilingen()
    {
        return $this->handle(PeilingenController::class);
    }

    public function google()
    {
        return $this->handle(GoogleController::class);
    }

    public function commissievoorkeuren()
    {
        return $this->handle(CommissieVoorkeurenController::class);
    }

    public function groepen()
    {
        return $this->handle(GroepenRouterController::class);
    }

    public function maalcie()
    {
        return $this->handle(MaalcieRouterController::class);
    }

    public function fiscaat()
    {
        return $this->handle(FiscaatRouterController::class);
    }

    public function courant()
    {
        return $this->handle(CourantController::class);
    }

    public function fotoalbum()
    {
        return $this->handle(FotoAlbumController::class);
    }

    public function gesprekken()
    {
        return $this->handle(GesprekkenController::class);
    }

    public function rechten()
    {
        return $this->handle(RechtenController::class);
    }

    public function instellingenbeheer()
    {
        return $this->handle(InstellingenBeheerController::class);
    }

    public function instellingen()
    {
        return $this->handle(LidInstellingenController::class);
    }

    public function menubeheer()
    {
        return $this->handle(MenuBeheerController::class);
    }
}
