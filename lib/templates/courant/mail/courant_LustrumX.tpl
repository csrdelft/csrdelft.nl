{if $headers}{include file='courant/mail/c_header.tpl'}{/if}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html>
<head>
  <title>C.S.R.-courant</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="author" content="PubCie der C.S.R. Delft" />
  <meta name="robots" content="index, follow" />
  <link href='http://fonts.googleapis.com/css?family=Parisienne' rel='stylesheet' type='text/css'>
  <style type="text/css">{literal}<!--
body{
    font-family: 'Parisienne', cursive;
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
    font-size: 11px;
    font-family: 'Parisienne', cursive;
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
    background-color: #E0A937;
    font-size: 15px;
    margin: 10px 0 0 0; padding: 5px 5px 5px 10px;
    font-family: 'Parisienne' cursive;
    color: black;
}
div.p{
    background-color: #FAFAFF;
    margin: 0 0 0 0;
    padding: 10px 5px 5px 10px;
    color: #000000;
    font-family: 'Parisienne' cursive;
    line-height: 1.4em;
}
.inhoud{
    border: 0;
    width: 100%;
    background-color: #FAFAFF;
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
a{
	font-family: 'Parisienne' cursive;
}
a:link{
	color:  #E0A937;
}
a:visited{
	color:  #EDA137;
}
a:hover{
	color:  #000000;
}
a:active{
	color:  #000000;
}
li{
    margin: 0 0 0 00px;
    color: #000000;
    font-size: 11px;
}div.citaatContainer{
    margin: 5px 5px 5px 20px;
} -->{/literal}
</style>
</head>
<body >
<table>
<tr>
<td class="Zijbalk" valign="top">
<img src="/plaetjes/csrmail/logoLustrum.png" width="150px" alt="Logo van C.S.R." />
<img src="/plaetjes/csrmail/balkjeLustrum.png" width="150px" height="100%" />
</td>
<td class="hoofdKolom">
<h4><font size="-3" face="verdana">Inhoud</font></h4>
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
    <div class="p">{$bericht.bericht|bbcode:"mail"}</div>
{/foreach}
</font>
</td>
</tr>
</table>
</body>
</html>
