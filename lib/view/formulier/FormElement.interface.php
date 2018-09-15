<?php

namespace CsrDelft\view\formulier;

use CsrDelft\view\View;

/**
 * FormElement.interface.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 *
 * Dit is een poging om maar op één plek dingen voor een formulier te defenieren:
 *  - validatorfuncties
 *  - Html voor de velden, inclusief bijbehorende javascript.
 *  - suggesties voor formuliervelden
 *
 * Alle elementen die in een formulier terecht kunnen komen stammen af van
 * de class FormElement.
 *
 * FormElement
 *  - InputField          Elementen die data leveren
 *    * SelectField        Lijst van invoeropties
 *  - FileField            Bestand upload ketzer
 *  - HtmlComment          Uitleg/commentaar in een formulier stoppen
 *  - FormulierKnop          Submitten, resetten en custom functies van het formulier
 *
 * Uitbreidingen van HtmlComment:
 *    - HtmlComment        invoer wordt als html weergegeven
 *    - HtmlBbComment        bbcode wordt geparsed en invoer wordt als html weergegeven
 *    - Fieldset          <fieldset> + <legend>invoer</legend>
 *    - Subkopje          invoer wordt als <h3> weergegeven
 *    - CollapsableSubkopje    <h3> + <div>
 *
 */
interface FormElement extends View {

	public function getModel();

	public function getType();

	public function getHtml();

	public function getJavascript();
}
