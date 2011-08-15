 

UPDATE  `csrdelft`.`biebboek` SET  `uitgavejaar` =  '0' WHERE  `uitgavejaar` IS NULL ;
UPDATE  `csrdelft`.`biebboek` SET  `paginas` =  '0' WHERE  `paginas` IS NULL ;



ALTER TABLE  `biebboek` 
CHANGE  `paginas`  `paginas` SMALLINT( 6 ) NOT NULL  DEFAULT  '0',
CHANGE  `uitgavejaar`  `uitgavejaar` MEDIUMINT( 4 ) NOT NULL DEFAULT  '0',
CHANGE  `uitgeverij`  `uitgeverij` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '',
CHANGE  `code`  `code` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '',
CHANGE  `taal`  `taal` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'Nederlands';

ALTER TABLE  `biebbeschrijving` 
CHANGE  `toegevoegd`  `toegevoegd` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00';

ALTER TABLE  `biebbeschrijving` 
ADD  `bewerkdatum` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00';

UPDATE biebboek 
	SET auteur_id= 0
	WHERE `auteur_id`=9;


INSERT INTO  `csrdelft`.`menu` (
`ID` ,
`pID` ,
`prioriteit` ,
`tekst` ,
`link` ,
`permission` ,
`zichtbaar` ,
`gasnelnaar`
)
VALUES (
NULL ,  '3',  '20',  'Bibliotheek',  '/communicatie/bibliotheek',  'P_LOGGED_IN',  'ja',  'nee'
);

ALTER TABLE  `biebexemplaar` 
ADD  `opmerking` VARCHAR( 255 ) NOT NULL AFTER  `eigenaar_uid`,
ADD  `status` ENUM(  'beschikbaar',  'uitgeleend',  'teruggegeven',  'vermist' ) NOT NULL DEFAULT  'beschikbaar',
ADD  `uitleendatum` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00',
ADD  `leningen` INT( 11 ) NOT NULL DEFAULT  '0';


ALTER TABLE  `biebexemplaar` 
DROP  `extern`;
ALTER TABLE  `biebexemplaar` 
CHANGE  `toegevoegd`  `toegevoegd` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00';

DROP TABLE  `biebbevestiging`;
DROP TABLE  `biebadmingewijzigd`;

--
-- Gegevens worden uitgevoerd voor tabel `pagina`
--

INSERT INTO `pagina` (`naam`, `titel`, `inhoud`, `rechten_bekijken`, `rechten_bewerken`) VALUES
('wenslijst', 'Bibliotheek | Wenslijst', '[prive=P_BIEB_READ][html]<ul class="horizontal">\r\n	<li>\r\n		<a href="/communicatie/bibliotheek/" title="Naar de catalogus">Catalogus</a>\r\n	</li>\r\n	<li>\r\n		<a href="/communicatie/bibliotheek/boekstatus" title="Uitgebreide boekstatus">Boekstatus</a>\r\n	</li>\r\n	<li class="active">\r\n		<a href="/communicatie/bibliotheek/wenslijst" title="Wenslijst van bibliothecaris">Wenslijst</a>\r\n	</li>\r\n</ul>[/html][/prive][prive=!P_BIEB_READ][html]<ul class="horizontal">\r\n	<li>\r\n		<a href="/communicatie/bibliotheek/" title="Naar de catalogus">Catalogus</a>\r\n	</li>\r\n	<li class="active">\r\n		<a href="/communicatie/bibliotheek/wenslijst" title="Wenslijst van bibliothecaris">Wenslijst</a>\r\n	</li>\r\n</ul>[/html][/prive]\r\n[h=1]Wenslijst[/h]\r\n\r\n[img float=right]http://plaetjes.csrdelft.nl/vereniging/societeit/bieb.jpg[/img]De bibliothecaris van de C.S.R.-bibliotheek heeft voor hen die een boek willen schenken aan de bibliotheek hieronder een lijst samengesteld met boeken die hem een aanwinst lijken. etc etc etc\r\n\r\nDe bibliothecaris verwelkomt ook nieuwe ideeën voor aanvullingen van de bibliotheek[prive] op [email]bibliothecaris@csrdelft.nl[/email][/prive].\r\n\r\n\r\n\r\n[i]Wouter van Wijngaarden\r\nbibliothecaris [url=/actueel/groepen/Commissies/957]2010-2012[/rul][/i]\r\n\r\n\r\n\r\n\r\n\r\n\r\nweergave met lijst:\r\n\r\n[ulist]\r\n	[li]schrijver		titel	opmerkingen	verkrijgbaar bij	ISBN	uitgever[/li]\r\n	[li]Leanne	Payne - 	(maakt niet veel uit)	http://navigators.solcon.nl/asp/listbooks.asp	Ruben Verhaaf	[/li]\r\n	[li]bijbelverklaring - 		alles behalve wat we al hebben		[/li]\r\n	[li]Geert Mak - 		De Eeuw van mijn Vader	Super vet boek. Standaardwerk natuurlijk	De Slegte?[/li]\r\n	[li]Karen & Rod Morris - 		Leading better bible studies			[/li]\r\n	[li]Larry	Crabb - 	Bemoedigen doet goed		9076596522 [/li]\r\n	[li]Peter	Kreeft - 	Pascals gedachten voor nu	http://navigators.solcon.nl/asp/listbooks.asp		9076596328 	navigators boek[/li]\r\n	[li]Alister	McGrath - 	(zie opmerkingen voor de boeken die we al hebben)	we hebben al: Uitleggen wat je gelooft, Geloof en natuurwetenschap	[/li]\r\n	[li]H.R.	Rookmaker - 	modern art and the death of a culture	[/li]\r\n	[li]Chris	Wright - 	Jezus leren kennen door het oude testament	[/li]\r\n	[li]Wim	Rietkerk - 	(maakt niet veel uit)			[/li]\r\n	[li]Philip	Yancey - 	alles behalve wat we al hebben	[/li]\r\n	[li]Francis	Schaeffer - 	Het liefst: Escape from reason en He is there and He is not silent	We hebben al: Leven door de Geest en The God who is there			[/li]\r\n	[li]George	MacDonald - 		[/li]\r\n	[li]G.K.	Chesterton - 	niet Orthodoxy, wel st. Thomas Aquinas, heretics			[/li]\r\n	[li]Richard	Foster - 		We hebben al: Het feest van de navolging		http://www.clk.nu/	[/li]\r\n	[li]John	Eldredge - 	De fantastische vrouw	[/li]\r\n	[li]Theodore 	dalrymple - 	beschaving, of wat er van over is (NL), Our culture, What is left of it. (En) LET OP wat we al hebben	Zeer verstandig man, Peter de Graaff		[/li]\r\n	[li]Edmund	Burke - 	Reflections on the revolution in France		[/li]\r\n	[li]Eusebius - 		Kerkgeschiedenis/Historiae ecclesiae/Church history	[/li]			\r\n[/list]\r\n\r\n\r\nof:\r\n\r\n\r\n[ulist]\r\n	[li]....bla...[/li]\r\n	[li]boek a[ulist]\r\n		[li]..schrijver..[/li]\r\n		[li]..info....[/li]\r\n	[/list][/li]\r\n	[li]boek b[ulist]\r\n		[li]..schrijver..[/li]\r\n		[li]..info....[/li]\r\n	[/list][/li]\r\n[/list]\r\n\r\n\r\n\r\n[commentaar]\r\n\r\n[ulist]\r\n	[li].......[/li]\r\n	[li]......[/li]\r\n\r\n[/list]\r\n\r\n[/commentaar]\r\n\r\n\r\nof een tabel:\r\n\r\n[table border=1px_solid_black][tr][th]schrijver[/th][th]titel[/th]\r\n  [th]	opmerkingen[/th][th]verkrijgbaar bij[/th]\r\n  [th]ISBN	[/th][th]uitgever[/th]\r\n[/tr][tr]						\r\n[td] Leanne Payne[/td][td](maakt niet veel uit)[/td]\r\n  [td]http://navigators.solcon.nl/asp/listbooks.asp[/td][td]Ruben Verhaaf[/td]\r\n  [td][/td][td][/td]\r\n[/tr][tr]						\r\n[td]bijbelverklaring[/td][td]alles behalve wat we al hebben[/td]\r\n  [td]	[/td][td][/td]\r\n  [td][/td][td][/td]	\r\n[/tr][tr]\r\n[td]Geert Mak[/td][td]	De Eeuw van mijn Vader[/td]\r\n  [td]Super vet boek. Standaardwerk natuurlijk[/td][td]	De Slegte?	[/td]\r\n  [td][/td][td][/td]\r\n[/tr][tr]\r\n[td]Karen & Rod Morris[/td][td]Leading better bible studies[/td]\r\n  [td][/td][td][/td]\r\n  [td][/td][td][/td][/tr][/table]\r\n\r\n\r\n\r\n[commentaar]\r\n\r\n\r\n[table border=1px_solid_black]\r\n[tr]\r\n[th]Schrijver[/th][th]Titel[/th]\r\n  [th]Opmerkingen[/th][th]Verkrijgbaar bij[/th]\r\n  [th]ISBN	[/th][th]Uitgever[/th]\r\n[/tr][tr]						\r\n[td]schrijver..[/td][td]titel...[/td]\r\n  [td]opmerking...[/td][td]verkrijgbaar bij ...[/td]\r\n  [td]isbn...[/td][td]uitgeverij...[/td]\r\n[/tr]\r\n[/table]\r\n\r\n[/commentaar]', 'P_NOBODY', 'P_ADMIN,groep:basfcie');
