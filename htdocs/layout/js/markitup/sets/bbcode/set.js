// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// BBCode tags example
// http://en.wikipedia.org/wiki/Bbcode
// ----------------------------------------------------------------------------
// Feel free to add more tags
// ----------------------------------------------------------------------------
mySettings = {
	previewParserPath: '/tools/ubb.php', // path to your BBCode parser
	markupSet: [
		{name: 'Dikgedrukt', key: 'B', openWith: '[b]', closeWith: '[/b]'},
		{name: 'Cursief', key: 'I', openWith: '[i]', closeWith: '[/i]'},
		{name: 'Onderstreept', key: 'U', openWith: '[u]', closeWith: '[/u]'},
		{name: 'Doorgestreept', key: 'S', openWith: '[s]', closeWith: '[/s]'},
		{separator: ' '},
		{name: 'Offtopic', key: 'O', openWith: '[offtopic]', closeWith: '[/offtopic]'},
		{name: 'Citaat', key: 'Q', openWith: '[citaat=Naam_of_lidnummer]', closeWith: '[/citaat]'},
		{separator: ' '},
		{name: 'Link', key: 'L', openWith: '[url=[![Url]!]]', closeWith: '[/url]', placeHolder: 'Link tekst'},
		{name: 'Email', key: 'E', openWith: '[email=[![Email adres]!]]', closeWith: '[/email]', placeHolder: 'Link tekst'},
		{separator: ' '},
		{name: 'Fotoalbum', replaceWith: '[fotoalbum][![Url]!][/fotoalbum]'},
		{name: 'Poster of foto uit album', replaceWith: '[foto][![Url]!][/foto]'},
		{name: 'Afbeelding', replaceWith: '[img][![Url]!][/img]'},
		{name: 'Video', replaceWith: '[video][![Url]!][/video]'},
		{separator: ' '},
		{name: 'Kaart', openWith: '[locatie]', closeWith: '[/locatie]', placeHolder: 'C.S.R. Delft'},
		{name: 'Verklapper', openWith: '[verklapper]', closeWith: '[/verklapper]'},
		{name: 'Privé', openWith: '[prive]', closeWith: '[/prive]', placeHolder: 'Afgeschermde gegevens'},/*
		{name: 'Kop',
			dropMenu: [
				{name: 'H1', openWith: '[h=1]', closeWith: '[/h]'},
				{name: 'H2', openWith: '[h=2]', closeWith: '[/h]'},
				{name: 'H3', openWith: '[h=3]', closeWith: '[/h]'},
				{name: 'H4', openWith: '[h=4]', closeWith: '[/h]'},
				{name: 'H5', openWith: '[h=5]', closeWith: '[/h]'},
				{name: 'H6', openWith: '[h=6]', closeWith: '[/h]'}
			]},
		{separator: ' '},
		{name: 'Genummerde lijst', openWith: '[list=[![Starting number]!]]\n', closeWith: '\n[/list]'},
		{name: 'Ongenummerde lijst', openWith: '[list]\n', closeWith: '\n[/list]'},
		{name: 'Lijstpunt', openWith: '[*] '},*/
		{separator: ' '},
		{name: 'Code', openWith: '[code]', closeWith: '[/code]'},
		{name: 'Opmaakcode tonen', openWith: '[ubboff]', closeWith: '[/ubboff]'},
		{separator: ' '},
		{name: 'Opmaak wissen', className: "clean", replaceWith: function (markitup) {
				return markitup.selection.replace(/\[(.*?)\]/g, "")
			}}
		//{name: 'Voorbeeld', className: "preview", call: 'preview'}
	]
};