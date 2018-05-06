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
export default {
    nameSpace: 'CsrBB',
    previewParserPath: '/tools/bbcode.php', // path to your BBCode parser
    markupSet: [
        {className: 'btn-b', name: 'Dikgedrukt', key: 'B', openWith: '[b]', closeWith: '[/b]'},
        {className: 'btn-i', name: 'Cursief', key: 'I', openWith: '[i]', closeWith: '[/i]'},
        {className: 'btn-u', name: 'Onderstreept', key: 'U', openWith: '[u]', closeWith: '[/u]'},
        {className: 'btn-s', name: 'Doorgestreept', key: 'S', openWith: '[s]', closeWith: '[/s]'},
        {separator: '|'},
        {className: 'btn-ot', name: 'Offtopic', key: 'O', openWith: '[offtopic]', closeWith: '[/offtopic]'},
        {className: 'btn-quote', name: 'Citaat', key: 'Q', openWith: '[citaat=Naam_of_lidnummer]', closeWith: '[/citaat]'},
        {separator: '|'},
        {className: 'btn-link', name: 'Link', key: 'L', openWith: '[url=[![Url]!]]', closeWith: '[/url]', placeHolder: 'Link tekst'},
        {className: 'btn-mail', name: 'Email', key: 'E', openWith: '[email=[![Email adres]!]]', closeWith: '[/email]', placeHolder: 'Link tekst'},
        {separator: '|'},
        {className: 'btn-album', name: 'Fotoalbum', replaceWith: (markitup) => {
                let url = decodeURIComponent(window.prompt('Url', '').trim());
                let pos = url.indexOf('/fotoalbum/');
                if (pos > 0) {
                    url = url.substring(pos + 10);
                    return '[fotoalbum]' + url + '[/fotoalbum]';
                }
                alert('Ongeldige url!');
                return markitup.selection;
            }},
        {className: 'btn-foto', name: 'Poster of foto uit album', replaceWith: (markitup) => {
                let url = decodeURIComponent(window.prompt('Url', '').trim());
                let pos = url.indexOf('/fotoalbum/');
                if (pos > 0) {
                    url = url.substring(pos + 10).replace('_resized/', '').replace('_thumbs/', '').replace('#', '');
                    return '[foto]' + url + '[/foto]';
                }
                alert('Ongeldige url!');
                return markitup.selection;
            }},
        {className: 'btn-img', name: 'Afbeelding', replaceWith: '[img][![Url]!][/img]'},
        {className: 'btn-vid', name: 'Video', replaceWith: '[video][![Url]!][/video]'},
        {separator: '|'},
        {className: 'btn-map', name: 'Kaart', openWith: '[locatie]', closeWith: '[/locatie]', placeHolder: 'C.S.R. Delft'},
        {className: 'btn-spoiler', name: 'Verklapper', openWith: '[verklapper]', closeWith: '[/verklapper]'},
        {className: 'btn-prive', name: 'Privé', openWith: '[prive]', closeWith: '[/prive]', placeHolder: 'Afgeschermde gegevens'},
        //{className: 'btn-kop', name: 'Kop',
        //	dropMenu: [
        //		{className: 'btn-h1', name: 'H1', openWith: '[h=1]', closeWith: '[/h]'},
        //		{className: 'btn-h2', name: 'H2', openWith: '[h=2]', closeWith: '[/h]'},
        //		{className: 'btn-h3', name: 'H3', openWith: '[h=3]', closeWith: '[/h]'},
        //		{className: 'btn-h4', name: 'H4', openWith: '[h=4]', closeWith: '[/h]'},
        //		{className: 'btn-h5', name: 'H5', openWith: '[h=5]', closeWith: '[/h]'},
        //		{className: 'btn-h6', name: 'H6', openWith: '[h=6]', closeWith: '[/h]'}
        //	]},
        //{separator: '|'},
        //{className: 'btn-lijst-1', name: 'Genummerde lijst', openWith: '[list=[![Starting number]!]]\n', closeWith: '\n[/list]'},
        //{className: 'btn-lijst-a', name: 'Ongenummerde lijst', openWith: '[list]\n', closeWith: '\n[/list]'},
        //{className: 'btn-lijst-punt', name: 'Lijstpunt', openWith: '[*] '},
        {separator: '|'},
        {className: 'btn-code', name: 'Code', openWith: '[code]', closeWith: '[/code]'},
        {className: 'btn-off', name: 'Opmaakcode tonen', openWith: '[tekst]', closeWith: '[/tekst]'},
        {separator: '|'},
        {className: 'btn-clean', name: 'Opmaak wissen', replaceWith: (markitup) => markitup.selection.replace(/\[(.*?)\]/g, '')}
        //{className: 'btn-preview', name: 'Voorbeeld', call: 'preview'}
    ]
};