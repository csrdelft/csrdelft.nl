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
	nameSpace: 'bbcode',
	previewParserPath: '/tools/ubb.php', // path to your BBCode parser
	markupSet: [
		{className: 'knop-b', name: 'Dikgedrukt', key: 'B', openWith: '[b]', closeWith: '[/b]'},
		{className: 'knop-i', name: 'Cursief', key: 'I', openWith: '[i]', closeWith: '[/i]'},
		{className: 'knop-u', name: 'Onderstreept', key: 'U', openWith: '[u]', closeWith: '[/u]'},
		{className: 'knop-s', name: 'Doorgestreept', key: 'S', openWith: '[s]', closeWith: '[/s]'},
		{separator: '|'},
		{className: 'knop-ot', name: 'Offtopic', key: 'O', openWith: '[offtopic]', closeWith: '[/offtopic]'},
		{className: 'knop-quote', name: 'Citaat', key: 'Q', openWith: '[citaat=Naam_of_lidnummer]', closeWith: '[/citaat]'},
		{separator: '|'},
		{className: 'knop-link', name: 'Link', key: 'L', openWith: '[url=[![Url]!]]', closeWith: '[/url]', placeHolder: 'Link tekst'},
		{className: 'knop-mail', name: 'Email', key: 'E', openWith: '[email=[![Email adres]!]]', closeWith: '[/email]', placeHolder: 'Link tekst'},
		{separator: '|'},
		{className: 'knop-album', name: 'Fotoalbum', replaceWith: '[fotoalbum][![Url]!][/fotoalbum]'},
		{className: 'knop-foto', name: 'Poster of foto uit album', replaceWith: function (markitup) {
				var url = window.prompt('Url', '').trim();
				var pos = url.indexOf('/fotoalbum/');
				if (pos > 0) {
					url = url.substring(pos + 10).replace('_resized/', '').replace('_thumbs/', '').replace('#', '').replace('%20', ' ');
					return '[foto]' + url + '[/foto]';
				}
				alert('Ongeldige url!');
				return markitup.selection;
			}},
		{className: 'knop-img', name: 'Afbeelding', replaceWith: '[img][![Url]!][/img]'},
		{className: 'knop-vid', name: 'Video', replaceWith: '[video][![Url]!][/video]'},
		{separator: '|'},
		{className: 'knop-map', name: 'Kaart', openWith: '[locatie]', closeWith: '[/locatie]', placeHolder: 'C.S.R. Delft'},
		{className: 'knop-spoiler', name: 'Verklapper', openWith: '[verklapper]', closeWith: '[/verklapper]'},
		{className: 'knop-prive', name: 'Privé', openWith: '[prive]', closeWith: '[/prive]', placeHolder: 'Afgeschermde gegevens'},
		//{className: 'knop-kop', name: 'Kop',
		//	dropMenu: [
		//		{className: 'knop-h1', name: 'H1', openWith: '[h=1]', closeWith: '[/h]'},
		//		{className: 'knop-h2', name: 'H2', openWith: '[h=2]', closeWith: '[/h]'},
		//		{className: 'knop-h3', name: 'H3', openWith: '[h=3]', closeWith: '[/h]'},
		//		{className: 'knop-h4', name: 'H4', openWith: '[h=4]', closeWith: '[/h]'},
		//		{className: 'knop-h5', name: 'H5', openWith: '[h=5]', closeWith: '[/h]'},
		//		{className: 'knop-h6', name: 'H6', openWith: '[h=6]', closeWith: '[/h]'}
		//	]},
		//{separator: '|'},
		//{className: 'knop-lijst-1', name: 'Genummerde lijst', openWith: '[list=[![Starting number]!]]\n', closeWith: '\n[/list]'},
		//{className: 'knop-lijst-a', name: 'Ongenummerde lijst', openWith: '[list]\n', closeWith: '\n[/list]'},
		//{className: 'knop-lijst-punt', name: 'Lijstpunt', openWith: '[*] '},
		{separator: '|'},
		{className: 'knop-code', name: 'Code', openWith: '[code]', closeWith: '[/code]'},
		{className: 'knop-off', name: 'Opmaakcode tonen', openWith: '[ubboff]', closeWith: '[/ubboff]'},
		{separator: '|'},
		{className: 'knop-clean', name: 'Opmaak wissen', replaceWith: function (markitup) {
				return markitup.selection.replace(/\[(.*?)\]/g, '');
			}}
		//{className: 'knop-preview', name: 'Voorbeeld', call: 'preview'}
	]
};