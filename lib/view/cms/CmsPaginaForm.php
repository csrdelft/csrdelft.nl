<?php

namespace CsrDelft\view\cms;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\CmsPagina;
use CsrDelft\view\formulier\elementen\HtmlComment;
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
class CmsPaginaForm implements FormulierTypeInterface {
	/**
	 * @param FormulierBuilder $builder
	 * @param CmsPagina $data
	 * @param array $options
	 */
	public function createFormulier(FormulierBuilder $builder, $data, $options = []) {
		$builder->setTitel('Pagina bewerken: ' . $data->naam);

		$fields = [];
		$fields[] = new HtmlComment('<div class="row"><label class="col-3 col-form-label">Laatst gewijzigd</label><div class="col-9"><div class="form-control-plaintext">' . reldate($data->laatst_gewijzigd) . '</div></div></div>');
		$fields[] = new TextField('titel', $data->titel, 'Titel');
		if ($data->magRechtenWijzigen()) {
			$fields[] = new RechtenField('rechten_bekijken', $data->rechten_bekijken, 'Rechten bekijken');
			$fields[] = new RechtenField('rechten_bewerken', $data->rechten_bewerken, 'Rechten bewerken');
			$fields['html'] = new RadioField('inline_html', (int)$data->inline_html, 'Inline HTML', array('[html] tussen [/html]', 'Direct <html>'));
			$fields['html']->title = 'Geen [html] nodig en zelf regeleindes plaatsen met [rn] of <br />';
		} else {
			$fields[] = new HtmlComment('<div><label>Rechten bekijken</label>' . $data->rechten_bekijken .
				'</div><div class="clear-left"><label>Rechten bewerken</label>' . $data->rechten_bewerken . '</div>');
		}
		$fields[] = new BBCodeField('inhoud', $data->inhoud, 'Inhoud');
		$fields['btn'] = new FormDefaultKnoppen('/pagina/' . $data->naam);
		$delete = new DeleteKnop('/data/verwijderen/' . $data->naam);
		$fields['btn']->addKnop($delete, true);

		$builder->addFields($fields);
	}
}
