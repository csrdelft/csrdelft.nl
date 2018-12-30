<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\view\SmartyTemplateView;
use CsrDelft\view\View;

class BeheerMaaltijdenBeoordelingenView extends SmartyTemplateView {
	public function __construct(View $model, $titel = false) {
		parent::__construct($model, $titel);
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		echo <<<HTML
<p>Hieronder vind je per maaltijd de gegeven beoordelingen. Onder aantal beoordelingen 
staat eerst het aantal leden dat de kwantiteit heeft beoordeeld, gevolgd door het aantal 
leden dat de kwaliteit heeft beoordeeld. De volgende getallen zijn het gemiddelde aantal
sterren wat is gegeven voor de kwantiteit en de kwaliteit (op een schaal van 1 tot 5).</p>
<p>Om rekening te houden met het verschil in manier van beoordelen van leden, worden na de
ruwe gemiddelden ook afwijkingen getoond. Dit is het gemiddelde van de afwijking van de 
beoordeling van de maaltijd t.o.v. de gemiddelde beoordeling van elk beoordelend lid. Een
positief getal betekent dat leden de maaltijd gemiddeld gezien beter vonden dan andere 
maaltijden die ze beoordeeld hebben. Een negatief getal betekent een minder goede maaltijd.</p>
HTML;
		$this->model->view();
	}
}
