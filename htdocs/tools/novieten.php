<?php
/**
 * Created by PhpStorm.
 * User: gerbe
 * Date: 23-8-2016
 * Time: 13:09
 */

require_once 'configuratie.include.php';

if (!LoginModel::mag('commissie:NovCie,P_ADMIN')) exit;

$query = "SELECT * FROM profielen WHERE status = 'S_NOVIET'";

$content = '';
$content .= '<table class="table"><tr><th>UID</th><th>Voornaam</th><th>Tussenvoegsel</th><th>Achternaam</th><th>Mobiel</th><th>Studie</th></tr>';
foreach (Database::sqlSelect(array('*'), 'profielen', 'status = ?', array('S_NOVIET')) as $item) {
    $string = <<<NOV
<tr>
<td>%s</td>
<td>%s</td>
<td>%s</td>
<td>%s</td>
<td>%s</td>
<td>%s</td>
</tr>
NOV;

    $content .= sprintf($string, $item['uid'], $item['voornaam'], $item['tussenvoegsel'], $item['achternaam'], $item['mobiel'], $item['studie']);
}

class NovietenView implements View {

    private $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view() {
        echo $this->data;
    }

    public function getTitel()
    {
        return "Novieten";
    }

    public function getBreadcrumbs()
    {
        return "Novieten";
    }

    /**
     * Hiermee wordt gepoogt af te dwingen dat een view een model heeft om te tonen
     */
    public function getModel()
    {
        // TODO: Implement getModel() method.
    }
}

$content .= '</table>';

$pagina = new CsrLayoutPage(new NovietenView($content));
$pagina->addCompressedResources('ledenlijst');
$pagina->view();