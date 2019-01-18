let { Textcomplete, Textarea } = require('textcomplete');

export function activeerLidHints(textarea: HTMLElement) {
    let editor = new Textarea(textarea);
    let textcomplete = new Textcomplete(editor);

    textcomplete.register([{
        // @...
        search: search,
        template: template,
        match: /(^|\s|])@((?:[^ ]+ ?){1,5})$/,
        replace: function (data: any) {
            return '$1[lid=' + data.label + ']';
        }
    }, {
        // [citaat=... of [lid=...
        search: search,
        template: template,
        match: /(^|\s|])\[(citaat|lid)=(?:[0-9]{4}|([^\]]+))$/,
        index: 3,
        replace: function (data: any) {
            return '$1[$2=' + data.label;
        }
    }]);
    textcomplete.on('rendered', function () {
        if (textcomplete.dropdown.items.length >= 1) {
            // Activeer eerste keuze standaard
            textcomplete.dropdown.items[0].activate();
        }
    });

    textcomplete.on('selected', function () {
        console.log('heuj');
    });
}

function search(term: string, callback: Function) {
    if (!term || term.length === 1) {
        callback([]);
    } else {
        $.ajax('/tools/naamsuggesties.php?vorm=user&q=' + encodeURI(term))
            .done(function (data) {
                callback(data);
            })
            .fail(function() {
                callback([]);
            });
    }
}

function template(data: any, term: string) {
    return data.value;
}