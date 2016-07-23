<?php

require_once 'model/PeilingenModel.class.php';
require_once 'view/PeilingenView.class.php';

class PeilingenController extends AclController
{
    public function __construct($query)
    {
        parent::__construct($query, PeilingenModel::instance());
        if (!$this->isPosted()) {
            $this->acl = array(
                'beheer' => 'P_PEILING_MOD',
                'verwijderen' => 'P_PEILING_MOD',
            );
        } else {
            $this->acl = array(
                'beheer' => 'P_PEILING_MOD',
                'stem' => 'P_PEILING_VOTE',
            );
        }
    }

    public function performAction(array $args = array())
    {
        $this->action = $this->getParam(2);
        if ($this->action == 'verwijderen') {
            $args = $this->getParams(3);
        }
        $body = parent::performAction($args);
        $this->view = new CsrLayoutPage($body);
    }

    public function beheer()
    {
        $peiling = new Peiling();

        if ($this->isPosted()) {
            $peiling->tekst = filter_input(INPUT_POST, 'verhaal', FILTER_SANITIZE_STRING);
            $peiling->titel = filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING);
            $opties = filter_input(INPUT_POST, 'opties', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

            if (count($opties) > 0) {
                foreach ($opties as $optie_tekst) {
                    $peiling->addOptie(PeilingOptie::init($optie_tekst));
                }
            }
            
            if (($errors = PeilingenModel::instance()->validate($peiling)) != '') {
                setMelding($errors, -1);
            } else {
                $peilingid = PeilingenModel::instance()->create($peiling);
                setMelding('Peiling is aangemaakt', 1);

                // Voorkom dubbele submit
                redirect(HTTP_REFERER . "#peiling" . $peilingid);
            }
        }

        return new PeilingenBeheerView($this->model->lijst(), $peiling);
    }

    public function verwijderen($id) {
        $peiling = $this->model->get($id);
        if ($peiling === false) {
            setMelding('Peiling al verwijderd!', 2);
        } else {
            $this->model->delete($peiling);
            setMelding('Peiling is verwijderd!', 1);

        }

        redirect('/peilingen/beheer');
    }

    public function stem()
    {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $optie = filter_input(INPUT_POST, 'optie', FILTER_VALIDATE_INT);
        // optie en id zijn null of false als filter_input faalt
        if (is_numeric($id) && is_numeric($optie)) {
            $peiling = PeilingenModel::instance()->stem($id, $optie);

            redirect(HTTP_REFERER . '#peiling' . $id);
        } else {
            setMelding("Kies een optie om op te stemmen", 0);
        }

        redirect(HTTP_REFERER);
    }
}