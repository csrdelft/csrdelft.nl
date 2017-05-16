<?php
/**
 * The ${NAME} file.
 */

namespace CsrDelft\view\commissievoorkeuren;

use CsrDelft\model\CommissieVoorkeurenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\View;

class CommissieVoorkeurenProfiel implements View
{

    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getModel()
    {
        return $this->id;
    }

    public function getBreadcrumbs()
    {
        return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <a href="/commissievoorkeuren">Voorkeuren voor commissies</a> » <span class="active">' . $this->getTitel() . '</span>';
    }

    public function getTitel()
    {
        return 'Voorkeur van lid';
    }

    public function view()
    {
        echo '<h1>' . $this->getTitel() . ' </h1>';
        echo '<p>Naam: ' . ProfielModel::getLink($this->id, 'volledig') . '</p>';
        $voorkeur = new CommissieVoorkeurenModel($this->id);
        $voorkeuren = $voorkeur->getVoorkeur();
        $commissies = $voorkeur->getCommissies();
        echo '<table>';
        $opties = array(1 => 'nee', 2 => 'misschien', 3 => 'ja');
        foreach ($voorkeuren as $cid => $voork) {
            echo '<tr><td>' . $commissies[$cid] . '</td><td>' . $opties[$voork] . '</td></tr>';
        }
        echo '</table><br />';
        echo '<h3>Lid opmerkingen</h3><p>' . $voorkeur->getLidOpmerking() . '</p>';
        echo '
		<form name="opties" action="/commissievoorkeuren/lidpagina/' . $this->id . '" method="POST">
			<textarea name = "opmerkingen" cols=40 rows = 10 >' . $voorkeur->getPraesesOpmerking() . ' </textarea> <br />
			<input type="submit" value="Opslaan" />
		</form>
		';
    }

}
