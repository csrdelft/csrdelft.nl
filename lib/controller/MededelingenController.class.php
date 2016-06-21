<?php

require_once 'model/mededelingen/MededelingenModel.class.php';
require_once 'view/MededelingenView.class.php';

/**
 * BijbelroosterController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van het bijbelrooster.
 */
class MededelingenController extends AclController {

    /**
     * @var bool
     */
    private $prullenbak = false;

    public function __construct($query) {
        parent::__construct($query, MededelingenModel::instance());
        if (!$this->isPosted()) {
            $this->acl = array(
                'lijst' => 'P_PUBLIC',
                'bekijken' => 'P_PUBLIC',
                'bewerken' => 'P_NEWS_POST',
                'verwijderen' => 'P_NEWS_POST',
                'toevoegen' => 'P_NEWS_POST',
                'top3overzicht' => 'P_NEWS_MOD',
                'goedkeuren' => 'P_NEWS_MOD'
            );
        } else {
            $this->acl = array(
                'bewerken' => 'P_NEWS_POST'
            );
        }
    }

    public function performAction(array $args = array()) {
        $this->action = 'bekijken';
        $base = 2; // Om prullenbak ertussenuit te halen
        if ($this->hasParam(2) && $this->getParam(2) == 'prullenbak') {
            // /mededelingen/prullenbak/*
            $this->prullenbak = true;
            $base = 3;
        }


        if ($this->hasParam($base)){
            if ($this->getParam($base) == 'pagina') {
                $args = array(0) + $this->getParams($base + 1); // Id is 0
            } else if (ctype_digit($this->getParam($base))) { // /mededelingen/{prullenbak}/xxx
                $args = $this->getParams($base);
            } else { // /mededelingen/{prullenbak?}/{bekijken|bewerken|verwijderen}/xxx
                $this->action = $this->getParam($base);
                $args = $this->getParams($base + 1);
            }
        }
        $body = parent::performAction($args);
        $this->view = new CsrLayoutPage($body);
        $this->view->addCompressedResources('mededelingen');
    }
    
    public function top3overzicht() {
        return new MededelingenOverzichtView();
    }

    public function bekijken($id = 0, $pagina = 1) {
        return new MededelingenView($id, $pagina, $this->prullenbak);
    }

    public function toevoegen() {
        return new MededelingView(new Mededeling());
    }

    public function bewerken($id = 0) {
        if ($id == 0) {
            $mededeling = new Mededeling();
        } else {
            $mededeling = $this->model->getUUID($id);
        }
        if ($this->isPosted() && isset($_POST['titel'], $_POST['tekst'], $_POST['categorie'])) {
            $mededeling->datum = getDateTime();
            $mededeling->titel = $_POST['titel'];
            $mededeling->tekst = $_POST['tekst'];
            $mededeling->categorie = $_POST['categorie'];
            $mededeling->doelgroep = $_POST['doelgroep'];
            $mededeling->uid = LoginModel::getUid();
            $mededeling->verwijderd = false;

            if (isset($_POST['prioriteit'])) {
                $mededeling->prioriteit = $_POST['prioriteit'];
            }

            if (isset($_POST['vervaltijd'])) {
                $mededeling->vervaltijd = $_POST['vervaltijd'];
            }

            if (!$this->model->isModerator()) {
                $mededeling->zichtbaarheid = 'wacht_goedkeuring';
            }else {
                $mededeling->zichtbaarheid = isset($_POST['verborgen']) ? 'onzichtbaar':'zichtbaar';
            }
            if (isset($_POST['verborgen'])) { // slechts voor frontend
                $mededeling->verborgen = true;
            }

            // TODO: Plaatje

            if (($errors = $this->model->validate($mededeling)) != '') {
                setMelding('<h3>Niet opgeslagen</h3>'. $errors, -1);
            } else {
                if ($mededeling->id) {
                    $this->model->update($mededeling);
                } else {
                    $nieuweId = $this->model->create($mededeling);
                    $nieuweLocatie = MededelingenView::mededelingenRoot;
                    if ($this->prullenbak) {
                        $nieuweLocatie .= '/prullenbak';
                    }

                    $nieuweLocatie .= '/'.$nieuweId;
                    redirect($nieuweLocatie);
                }


            }
        }
        return new MededelingView($mededeling);
    }

    public function goedkeuren($id) {
        $mededeling = $this->model->getUUID($id);
        $mededeling->zichtbaarheid = 'zichtbaar';
        $this->model->update($mededeling);
        setMelding("Mededeling is goedgekeurd.", 1);
        redirect(MededelingenView::mededelingenRoot . $mededeling->id);
    }

}
