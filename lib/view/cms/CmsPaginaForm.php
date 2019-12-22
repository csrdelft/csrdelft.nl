<?php

namespace CsrDelft\view\cms;

use CsrDelft\entity\CmsPagina;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\BBCodeField;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\RadioField;
use CsrDelft\view\formulier\knoppen\DeleteKnop;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

/**
 * CmsPaginaForm.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Bewerken van een CmsPagina.
 */
class CmsPaginaForm extends Formulier {

	function __construct(CmsPagina $pagina) {
		parent::__construct($pagina, '/pagina/bewerken/' . $pagina->naam);
		$this->titel = 'Pagina bewerken: ' . $pagina->naam;

		$fields = [];
		$fields[] = new HtmlComment('<div class="row"><label class="col-3 col-form-label">Laatst gewijzigd</label><div class="col-9"><div class="form-control-plaintext">' . reldate($pagina->laatst_gewijzigd) . '</div></div></div>');
		$fields[] = new TextField('titel', $pagina->titel, 'Titel');
		if ($pagina->magRechtenWijzigen()) {
			$fields[] = new RechtenField('rechten_bekijken', $pagina->rechten_bekijken, 'Rechten bekijken');
			$fields[] = new RechtenField('rechten_bewerken', $pagina->rechten_bewerken, 'Rechten bewerken');
			$fields['html'] = new RadioField('inline_html', (int)$pagina->inline_html, 'Inline HTML', array('[html] tussen [/html]', 'Direct <html>'));
			$fields['html']->title = 'Geen [html] nodig en zelf regeleindes plaatsen met [rn] of <br />';
		} else {
			$fields[] = new HtmlComment('<div><label>Rechten bekijken</label>' . $pagina->rechten_bekijken .
				'</div><div class="clear-left"><label>Rechten bewerken</label>' . $pagina->rechten_bewerken . '</div>');
		}
		$fields[] = new BBCodeField('inhoud', $pagina->inhoud, 'Inhoud');
		$fields['btn'] = new FormDefaultKnoppen('/pagina/' . $pagina->naam);
		$delete = new DeleteKnop('/pagina/verwijderen/' . $pagina->naam);
		$fields['btn']->addKnop($delete, true);

		$this->addFields($fields);
	}

}
