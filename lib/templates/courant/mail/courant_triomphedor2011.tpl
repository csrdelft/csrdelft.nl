{if $headers}{include file='courant/mail/c_header.tpl'}{/if}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html>
<head>
  <title>C.S.R.-courant</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta name="author" content="PubCie der C.S.R. Delft" />
  <meta name="robots" content="index, follow" />
  <style type="text/css">{literal}<!--
body{
	font-face: verdana, arial, sans-serif;
	font-size: 12px;
	margin: 0px 0px 20px 0px; padding: 0px ;
}
table{
	border: 0px;
	margin: 10px; padding: 0px;
	margin-top: 0px;
	width: 830px;
}
td{
	vertical-align: top;
	font-size: 11px; font-face: verdana, arial, sans-serif;
}
.hoofdKolom{
	margin: 10px; 
	padding: 23px 20px 0px 0px;
	vertical-align: top;
}
img{
	border: 0px;
}
h4{
	background-color: #777;
	font-size: 15px;
	margin: 10px 0px 0px 0px; padding: 5px 5px 5px 10px;
	color: white;
}
p{
	background-color: #eee;
	margin: 0px 0px 0px 0px;
	padding: 10px 5px 5px 10px;
	font-size: 11px; font-face: verdana, arial, sans-serif;
	line-height: 1.4em;
}
.inhoud{
	border: 0px;
	width: 100%;
	background-color: #eee;
	margin: 0px 0px 15px 0px;
	padding: 0px;
}
.inhoudKolom{
	margin: 0px 0px 10px 0px; padding: 5px 5px 5px 10px;
	font-size: 11px;
	vertical-align: top;
	width: 33%;
}
.inhoudKop{
	font-weight: bold;
	font-size: 11px;
}
ul{
	margin: 0px 0px 0px 10px; padding: 0px 0px 0px 5px;
	
}
.onderlijn{
	text-decoration: underline;
}
li{
	margin: 0px 0px 0px 00px;
	color: #020883;
	font-size: 11px;
}div.citaatContainer{
	margin: 5px 5px 5px 20px;
} -->{/literal}
</style>
</head>
<body>
<table>
<tr><td><a href="http://csrdelft.nl/dies/"><img src="http://plaetjes.csrdelft.nl/csrmail/triomphodor-header.jpg" /></a></td>
<tr>
<td class="hoofdKolom">
<font size="-1" face="verdana"><h4>Inhoud</h4></font>
<table class="inhoud">
<tr>
{*
	Weergave van de kopjes bovenaan. Beetje een raar verhaal geworden, maar zo is het wel lekker dynamisch
*}
{foreach from=$indexCats item=categorie key=catKey}
	{if $categorie!='voorwoord' AND $categorie!='sponsor'}
		<td class="inhoudKolom" valign="top">
		<font face="verdana" size="-1">
		<div class="inhoudKop"><b>{$catNames[$catKey]}</b></div>
		<ul>
		{foreach from=$courant->getBerichten() item=bericht}
			{if $bericht.categorie==$categorie}
				<li><a href="#{$bericht.ID}" style="text-decoration: none;">{$bericht.titel|ubb}</a></li>
			{/if}
		{/foreach}
		</ul>
		</font>
		</td>
	{/if}
{/foreach}
</tr>
</table>
<font face="verdana" size="-1">	
{foreach from=$courant->getBerichten() item=bericht}
	<h4><a name="{$bericht.ID}"></a>{$bericht.titel|ubb}</h4>
	<p>{$bericht.bericht|ubb}</p>
{/foreach}
</font>
</td>
</tr>
</table>
</body>
</html>
