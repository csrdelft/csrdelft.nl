<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>

<html>
<head>
  <title>OWee-Courant</title>
  <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
  <meta name='author' content='pubCie der C.S.R. Delft' />
  <meta name='robots' content='index, follow' />
  <style type="text/css"><!--
body{
	font-face: verdana, arial;
	font-size: 12px;
	margin: 0 0 20px 0; padding: 0 ;
}
table{
	border: 0;
	margin: 0; padding: 0;
	width: 100%
}
td{
	vertical-align: top;
	font-size: 11px;font-face: verdana, arial;
}
.Zijbalk{
	width: 150px;
	margin: 0; padding: 0;
	vertical-align: top;

	background-repeat: repeat-y;
}
.hoofdKolom{
	margin: 0; 
	padding: 23px 20px 0 0;
	vertical-align: top;
}
img{
	border: 0;
}
h4{
	background-color: #6ea200;
	font-size: 15px;
	margin: 10px 0 0 0; padding: 5px 5px 5px 10px;
	color: white;
}
p{
	background-color: #b1cd76;
	margin: 0 0 0 0;
	padding: 10px 5px 5px 10px;
	color: black;
	font-size: 11px; font-face: verdana, arial;
	line-height: 1.4em;
}
.inhoud{
	border: 0;
	width: 100%;
	background-color: #b1cd76;
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
	color: #000;
	font-size: 11px;
} 
a{
	color: black;
}
-->
</style>
</head>
<body >
<table>
<tr>
<td class="Zijbalk" valign="top">
<img src="/plaetjes/csrmail/logo_owee2007.jpg" width="150" 
height="187" alt="OWee-courant" />
</td>
<td class="hoofdKolom">
<h4><font size="-3" face="verdana">Inhoud</font></h4>
<table class="inhoud">
<tr>
<td class="inhoudKolom" valign="top">
<font face="verdana" size="-1">
<div class="inhoudKop"><b>OWee</b></div>
<ul>
[inhoud-csr]
</ul>
</font>
</td>
<td class="inhoudKolom" valign="top">
<font face="verdana" size="-1">
<div class="inhoudKop"><b>Overig</b></div>
<ul>
[inhoud-overig]
</ul>
</font>
</td>
</tr>
</table>
<font face="verdana" size="-1">
	{foreach from=$courant.getBerichten() item=bericht}
		<h4><a name={$bericht.ID}</a>{$bericht.titel|bbcode:"mail"}</h4>
		<p>{$bericht.bericht|bbcode:"mail"}</p>
	{/foreach}
</font>
</td>
</tr>
</table>
</body>
</html>
