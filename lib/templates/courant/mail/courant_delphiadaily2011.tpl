{if $headers}{include file='courant/mail/c_header.tpl'}{/if}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html>
<head>
  <title>Delphia Daily</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="author" content="Broeders van Delphia" />
  <meta name="robots" content="index, follow" />
  <style type="text/css">{literal}<!--
body{
	font-face: "Adobe Garamond Pro", "Garamond", "Times", serif;
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
	font-face: "Adobe Garamond Pro", "Garamond", "Times", serif;
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
	font-size: 20px;
	margin: 10px 0 0 0; padding: 5px 5px 5px 10px;
	color: #151515;
}
p{
	color: #151515;
	margin: 0 0 0 0;
	padding: 10px 5px 5px 10px;
	font-size: 16px; 
	font-face: "Adobe Garamond Pro", "Garamond", "Times", serif;
	line-height: 1.4em;
}
.inhoud{
	border: 0;
	width: 800px;
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
	color: #5b5b5b;
	font-size: 11px;
}

a, a:link, a:active, a:hover
{
color: #5b5b5b;
}


div.citaatContainer{
	margin: 5px 5px 5px 20px;
} -->{/literal}
</style>
</head>
<body>
<table>
<tr><td>
<br />
<br />
<img src="{$smarty.const.CSR_ROOT}/bestuur/delphiadaily/delphia-daily.png" alt="delphia-daily" width="654" height="142" /></td>
<tr>
<td class="hoofdKolom">
<font size="2" face="garamond"><h4>Inhoud</h4></font>
<table class="inhoud" width="800">
<tr>
{foreach from=$indexCats item=categorie key=catKey}
	{if $categorie!='voorwoord' AND $categorie!='sponsor'}
		<td class="inhoudKolom" valign="top">
		<font face="garamond" size="2">
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
<font face="garamond" size="2">	
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
