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
		{separator: '---------------'},
		{name: 'Afbeelding', replaceWith: '[img][![Url]!][/img]'},
		{name: 'Link', key: 'L', openWith: '[url=[![Url]!]]', closeWith: '[/url]', placeHolder: 'Link tekst'},
		{separator: '---------------'},
		/*{name: 'Grootte', key: 'G', openWith: '[size=[![Text size]!]]', closeWith: '[/size]',
			dropMenu: [
				{name: 'Groot', openWith: '[size=200]', closeWith: '[/size]'},
				{name: 'Normaal', openWith: '[size=100]', closeWith: '[/size]'},
				{name: 'Klein', openWith: '[size=50]', closeWith: '[/size]'}
			]},
		{separator: '---------------'},
		{name: 'Genummerde lijst', openWith: '[list=[![Starting number]!]]\n', closeWith: '\n[/list]'},
		{name: 'Ongenummerde lijst', openWith: '[list]\n', closeWith: '\n[/list]'},
		{name: 'Lijstpunt', openWith: '[*] '},
		{separator: '---------------'},*/
		{name: 'Citaat', key: 'Q', openWith: '[citaat=Naam_of_lidnummer]', closeWith: '[/citaat]'},
		{name: 'Code', openWith: '[code]', closeWith: '[/code]'},
		{separator: '---------------'},
		{name: 'Opmaak wissen', className: "clean", replaceWith: function (markitup) {
				return markitup.selection.replace(/\[(.*?)\]/g, "")
			}}/*,
		{name: 'Voorbeeld', className: "preview", call: 'preview'}*/
	]
};