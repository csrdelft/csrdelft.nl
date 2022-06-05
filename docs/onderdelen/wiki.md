---
layout: default
parent: Onderdelen
nav_order: 1
title: Wiki
---

# Wiki

De wiki is een installatie van MediaWiki op [wiki.csrdelft.nl](https://wiki.csrdelft.nl). De installatie is te vinden op Syrinx.

## Configuratie

De configuratie van de MediaWiki installatie is te vinden in `LocalSettings.php` (zie [Manual:Configuration settings](https://www.mediawiki.org/wiki/Manual:Configuration_settings))

De installatie van de C.S.R. wiki is volledig volgens het boekje, behalve de custom login via de stek. Zie hiervoor [Login](#login) hier onder.

### Extensions

Zie [Speciaal:Versie](https://wiki.csrdelft.nl/wiki/Speciaal:Softwareversie#mw-version-ext) voor de lijst van plugins.

Het installeren van extensions gebeurt door het plaatsen van een map in de `/extensions` map in de installatie. En het toevoegen van een regel `wfLoadExtension('<extension>')` in `LocalSettings.php`

Zie [Manual:Extensions](https://www.mediawiki.org/wiki/Manual:Extensions) op de MediaWiki wiki voor meer informatie over extensies.

## Login

Inloggen op de wiki gebeurt niet op de standaard manier, maar met [OAuth](./oauth.md) via de stek. Hier voor wordt [Extension:WSOAuth](https://www.mediawiki.org/wiki/Extension:WSOAuth) gebruikt, er is een custom `AuthProvider`, de `CsrAuth` provider die weet hoe er met de stek gecommuniceerd kan worden.

### Rechten

De `CsrAuth` provider fixt ook toegangsrechten. Een "admin" krijgt `sysop`,`bureaucrat` en `interface-admin`. Een bestuurder krijgt `bestuur`. Deze rechten worden iedere keer bij inloggen opnieuw gezet of afgepakt.

> Een bekend probleem is dat het toekennen van rechten niet werkt als er voor de gebruiker _op dat moment_ een account wordt aangemaakt. De gebruiker krijgt dan standaard gebruiker rechten. Als de gebruiker opnieuw inlogt worden de rechten alsnog goed gezet.

## Database

De database voor de wiki is te vinden op dezelfde plek als de stek database. De naam van de database is `csr_wiki`.

## Lokaal Installeren

Het is mogelijk om de wiki lokaal te installeren. Dit is wel aardig omslachtig en er is een grote kans dat de omgeving niet 100% hetzelfde is.

Je moet een lokale test-stek hebben om in te kunnen loggen in de wiki.

Hiervoor heb je lokaal een Apache2 + MySql + PHP installatie nodig, bijvoorbeeld wampserver. De installatie van je lokale teststek kan hiervoor gebruikt worden.

Download de database en de complete `wiki.csrdelft.nl` map van de server.

### Apache2 Configuratie

De volgende Apache2 configuratie kan worden gebruikt. Belangrijk is de `AllowEncodedSlashes NoDecode`.

```
# httpd-vhosts.conf
<VirtualHost *:80>
	ServerName wiki.dev-csrdelft.nl
	DocumentRoot "<map naar wiki>/htdocs"
	<Directory  "<map naar wiki>/htdocs/">
		Options +Indexes +Includes +FollowSymLinks +MultiViews
		AllowOverride All
		Require local
	</Directory>
	# Erg belangrijk! Anders werkt de visualeditor niet op sub-paginas
	AllowEncodedSlashes NoDecode
</VirtualHost>
```

### LocalSettings

Update in ieder geval de volgende waardes in `LocalSettings.php`

- `$wgServer = 'http://wiki.dev-csrdelft.nl';` of het domein waarop je de wiki hebt geinstalleerd
- `$wgDBuser = 'root';`
- `$wgDBpassword = '';` of een andere user/password als je die hebt ingesteld
- `$wgMemCachedServers = [];`
- `$wgOAuthUri = 'http://dev-csrdelft.nl/authorize';` of het domein waarop je je lokale stek hebt geinstalleerd
- `$wgOAuthClientId = "wiki-local";`
- `$wgOAuthClientSecret = "<zet mij>";` Deze krijg je bij aanmaken van een oauth client
- `$wgOAuthRedirectUri = 'http://wiki.dev-csrdelft.nl/wiki/Special:PluggableAuthLogin';` of het domein waarop je de wiki hebt geinstalleerd.
- `$wgOAuthCsrBaseUrl = 'http://dev-csrdelft.nl';` of het domein waarop je je lokale stek hebt geinstalleerd.

### OAuth Client

Ga hiervoor naar de map van je **Test Stek** in een terminal en voer het volgende commando uit:

```
php bin/console trikoder:oauth2:create-client --redirect-uri=http://wiki.dev-csrdelft.nl/wiki/Special:PluggableAuthLogin --scope=PROFIEL:EMAIL --scope=WIKI:BESTUUR wiki-local
```

Hier wordt een secret terug gegeven, vul deze in in `$wgOAuthClientSecret` in `LocalSettings.php`
