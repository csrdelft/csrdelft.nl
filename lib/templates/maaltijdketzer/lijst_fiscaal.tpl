<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>
<head>
  <title>Maaltijdaanmeldingen van {$maaltijd.datum|date_format:$datumFormaat}</title>
</head>
<body>

<h1>C.S.R.-maaltijd {$maaltijd.datum|date_format:$datumFormaat}</h1>

<pre>
{if $maaltijd.aantal>0}
{foreach from=$maaltijd.aanmeldingen item=aanmelding}
{$aanmelding.uid},{$aanmelding.naam}
{/foreach}
{else}
	Nog geen aanmeldingen voor deze maaltijd.
{/if}
</pre>

</body>
</html>
