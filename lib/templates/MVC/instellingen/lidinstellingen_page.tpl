{*
	lidinstellingen_page.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<table><tr id="maalcie-melding"><td id="maalcie-melding-veld">{SimpleHtml::getMelding()}</td></tr></table>
<h1>{$titel}</h1>
<p>Op deze pagina kunt u diverse instellingen voor de stek wijzigen. De waarden tussen haakjes zijn de standaardwaarden.</p>
<script>
$(function() {
	$("#tabs").tabs().addClass("ui-tabs-vertical ui-helper-clearfix");
	$("#tabs li").removeClass("ui-corner-top").addClass("ui-corner-left");
});
</script>
<style>
	.ui-tabs-vertical { width: 55em; }
	.ui-tabs-vertical .ui-tabs-nav { padding: .2em .1em .2em .2em; float: left; width: 12em; }
	.ui-tabs-vertical .ui-tabs-nav li { clear: left; width: 100%; border-bottom-width: 1px !important; border-right-width: 0 !important; margin: 0 -1px .2em 0; }
	.ui-tabs-vertical .ui-tabs-nav li a { display:block; }
	.ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active { padding-bottom: 0; padding-right: .1em; border-right-width: 1px; border-right-width: 1px; }
	.ui-tabs-vertical .ui-tabs-panel { padding: 1em; float: right; width: 40em;}
</style>