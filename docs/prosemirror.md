# Prosemirror in de stek

De interne representatie van gestylede content getyped door gebruikers is bbcode. Zie hiervoor ook [csrdelft/bb](https://github.com/csrdelft/bb).

De frontend is gebasseerd op [prosemirror](https://prosemirror.net).

## Backend

Met `CsrDelft\view\bbcode\BbToProsemirror` en `CsrDelft\view\bbcode\ProsemirrorToBb` wordt bbcode naar de representatie van Prosemirror geconverteerd. In het kort is deze conversie als volgt:

```
[verklapper][b]dingen[b][/verklapper]

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

In de klassen die `CsrDelft\view\bbcode\prosemirror\Node` en `CsrDelft\view\bbcode\prosemirror\Mark` extenden wordt gedefinieerd welke tags geconverteerd kunnen wordne en ook of er bepaalde extra velden zijn voor conversie.

## Marks & Nodes

In Prosemirror is er een onderscheid tussen marks en nodes.

Marks worden gebruikt om een bepaald stuk tekst te markeren met een eigenschap. Voorbeelden van marks zijn: underline, bold, url, prive. Marks kunnen makkelijk inline gebruikt worden.

Nodes zijn block types, ze zijn een los blok of kunnen nog nieuwe elementen bevatten. Voorbeelden van nodes die nog dingen kunnen bevatten zijn: verklapper, citaat, paragraph. Voorbeelden van nodes die niets bevatten zijn: video, maaltijd, peiling.

## Schema

In `js/editor/schema.ts` is het schema gedefinieerd. In het schema staat welke elementen er zijn, welke eigenschappen ze hebben en hoe ze weergegeven moeten worden.

## Menu

In `js/editor/menu.ts` wordt het menu opgebouwd.
