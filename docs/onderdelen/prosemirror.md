---
layout: default
parent: Onderdelen
nav_order: 1
title: Prosemirror
---

# Prosemirror in de stek

De interne representatie van gestylede content getyped door gebruikers is bbcode. Zie hiervoor ook [csrdelft/bb](https://github.com/csrdelft/bb).

De frontend is gebasseerd op [prosemirror](https://prosemirror.net). Dit is een framework om rich-text editors mee te bouwen.

## Backend

In de database staat BB code, deze wordt geconverteerd naar HTML of Prosemirror JSON bij het uitlezen. HTML is de standaard en is dus ook wat de Parser zelf kan fixen. Met `CsrDelft\view\bbcode\BbToProsemirror` en `CsrDelft\view\bbcode\ProsemirrorToBb` wordt bbcode naar de representatie van Prosemirror geconverteerd. In het kort is deze conversie als volgt:

```
[verklapper][b]dingen[b][/verklapper]
```
```json
{
	"type": "doc",
	"content": [{
		"type": "verklapper",
		"content": [{
			"type": "text",
			"text": "dingen",
			"marks": [{"type": "bold"}]
		}]
	}]
}
```
```html
<div class="card">
	<a class="btn btn-secondary btn-sm" data-toggle="collapse" href="#verklapper_123">Verklapper</a>
	<div id="verklapper_123" class="collapse">
		<div class="card-body">
			<strong class="dikgedrukt bb-tag-b">dingen</strong>
		</div>
	</div>
</div>
```

In de klassen die `CsrDelft\view\bbcode\prosemirror\Node` en `CsrDelft\view\bbcode\prosemirror\Mark` extenden wordt gedefinieerd welke tags geconverteerd kunnen wordne en ook of er bepaalde extra velden zijn voor conversie.

Niet alle bbcode is valide Prosemirror JSON, er is op dit moment geen controle hier van. Prosemirror JSON kan bijvoorbeeld om het hoofdniveau (direct in `doc`) alleen maar elementen bevatten die als `block` zijn gedefinieerd in het schema. Dit wordt niet afgedwongen door de converteerder. Omdat alle bb code die in Prosemirror terecht komt ook ooit uit Prosemirror kwam is dit geen groot probleem.

### Marks & Nodes

In Prosemirror is er een onderscheid tussen marks en nodes.

Marks worden gebruikt om een bepaald stuk tekst te markeren met een eigenschap. Voorbeelden van marks zijn: underline, bold, url, prive. Marks kunnen makkelijk inline gebruikt worden.

Nodes zijn block types, ze zijn een los blok of kunnen nog nieuwe elementen bevatten. Voorbeelden van nodes die nog dingen kunnen bevatten zijn: verklapper, citaat, paragraph. Voorbeelden van nodes die niets bevatten zijn: video, maaltijd, peiling.

Nodes implementeren `CsrDelft\view\bbcode\prosemirror\Node` en Marks implementeren `CsrDelft\view\bbcode\prosemirror\Mark`, deze worden via een [`service_locator`](https://symfony.com/doc/current/service_container/service_subscribers_locators.html#defining-a-service-locator) beschikbaar gemaakt aan de `BbToProsemirror` en `ProsemirrorToBb` services (zie `config/services.yaml`).

## Frontend

De frontend is dus gebaseerd op prosemirror. Zie de `assets/js/editor` map voor de implementatie. Er is altijd 1 editor de `currentEditor`, deze is beschikbaar in `window.currentEditor`. De current editor wordt gebruikt voor citeren, of in de courant voor het invoegen van sponsors/agenda. Voor nu is het gebruik van currentEditor een prima oplossing.

### Schema

In `js/editor/schema/index.ts` is het schema gedefinieerd. In het schema staat welke elementen er zijn, welke eigenschappen ze hebben en hoe ze weergegeven moeten worden.

### Menu

In `js/editor/menu.ts` wordt het menu opgebouwd. Hier wordt een onderscheid gemaakt tussen ingelogd en niet-ingelogd, omdat niet-ingelogd een boel bb tags niet kan/mag gebruiken.
