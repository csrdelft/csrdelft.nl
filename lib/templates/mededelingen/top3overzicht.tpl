<ul class="horizontal nobullets">
	<li>
		<a href="{CsrDelft\view\mededelingen\MededelingenView::MEDEDELINGEN_ROOT}" title="Mededelingenketzer">Mededelingenketzer</a>
	</li>
	<li class="active">
		<a href="{CsrDelft\view\mededelingen\MededelingenView::MEDEDELINGEN_ROOT}top3overzicht/" title="Top 3 Overzicht">Top 3 Overzicht</a>
	</li>
	<li>
		<a href="{CsrDelft\view\mededelingen\MededelingenView::MEDEDELINGEN_ROOT}prullenbak/" title="Prullenbak">Prullenbak</a>
	</li>
</ul>
<hr />
<h1>Mededelingen Top 3 Overzicht</h1>
Deze pagina laat zien hoe de Top 3 wordt weergegeven bij niet-leden, oudleden en leden.
<div id="top3overzicht">
	<div class="top3container">
		<h3>Niet-leden</h3>
		{'[mededelingen=top3nietleden]'|bbcode}
	</div>
	<div class="top3container">
		<h3>Oudleden</h3>
		{'[mededelingen=top3oudleden]'|bbcode}
	</div>
	<div class="top3container">
		<h3>Normale leden</h3>
		{'[mededelingen=top3leden]'|bbcode}
	</div>
</div>
