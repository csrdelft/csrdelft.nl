{if $headers}{include file='courant/mail/c_header.tpl'}{/if}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html>
<head>
  <title>C.S.R.-courant</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="author" content="PubCie der C.S.R. Delft" />
  <meta name="robots" content="index, follow" />
  <style type="text/css">{literal}<!--
body{
	font-face: verdana, arial, sans-serif;
	font-size: 12px;
	margin: 0 0 20px 0; padding: 0 ;
}
table{
	border: 0;
	margin: 10px; padding: 0;
	margin-top: 0;
	width: 830px;
}
td{
	vertical-align: top;
	font-size: 11px; font-face: verdana, arial, sans-serif;
}
.hoofdKolom{
	margin: 10px; 
	padding: 23px 20px 0 0;
	vertical-align: top;
}
img{
	border: 0;
}
h4{
	background-color: #6d6b81;
	font-size: 15px;
	margin: 10px 0 0 0; padding: 5px 5px 5px 10px;
	color: #efcf1c;
}
p{
	background-color: #b9b8c0;
	margin: 0 0 0 0;
	padding: 10px 5px 5px 10px;
	font-size: 11px; font-face: verdana, arial, sans-serif;
	line-height: 1.4em;
}
.inhoud{
	border: 0;
	width: 100%;
	background-color: #eee;
	margin: 0 0 15px 0;
	padding: 0;
}
.inhoudKolom{
	margin: 0 0 10px 0; padding: 5px 5px 5px 10px;
	font-size: 11px;
	vertical-align: top;
	width: 33%;
}
.inhoudKop{
	font-weight: bold;
	font-size: 11px;
}
ul{
	margin: 0 0 0 10px; padding: 0 0 0 5px;
	
}
.onderlijn{
	text-decoration: underline;
}
li{
	margin: 0 0 0 00px;
	color: #020883;
	font-size: 11px;
}div.citaatContainer{
	margin: 5px 5px 5px 20px;
} -->{/literal}
</style>
</head>
<body>
<table>
<tr><td><a href="{$smarty.const.CSR_ROOT}/dies"><img src="/plaetjes/csrmail/triomphodor-header.jpg" /></a></td>
<tr>
<td class="hoofdKolom">
<font size="-1" face="verdana"><h4>Inhoud</h4></font>
<table class="inhoud">
<tr>
{foreach from=$indexCats item=categorie key=catKey}
	{if $categorie!='voorwoord' AND $categorie!='sponsor'}
		<td class="inhoudKolom" valign="top">
		<font face="verdana" size="-1">
		<div class="inhoudKop"><b>{$catNames[$catKey]}</b></div>
		<ul>
		{foreach from=$courant->getBerichten() item=bericht}
			{if $bericht.categorie==$categorie}
				<li><a href="#{$bericht.ID}" style="text-decoration: none;">{$bericht.titel|bbcode:"mail"}</a></li>
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
	<h4><a name="{$bericht.ID}"></a>{$bericht.titel|bbcode:"mail"}</h4>
	<p>{$bericht.bericht|bbcode:"mail"}</p>
{/foreach}
</font>
</td>
</tr>
</table>
</body>
</html>
