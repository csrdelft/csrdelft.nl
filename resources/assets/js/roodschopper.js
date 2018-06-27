import $ from 'jquery';

/**
 * @see templates/roodschopper/roodschopper.tpl
 * @see htdocs/tools/roodschopper.php
 * @param actie
 */
window.roodschopper = function(actie) {
    let form = document.getElementById('roodschopper');
    let params = {
        actie,
    };
    params.push('actie=' + encodeURIComponent(actie));

    for (let i = 0; i < form.elements.length; i++) {
        let element = form.elements[i];

        if (element.type === 'select-one' || element.type === 'text' || element.type === 'textarea') {
            params[element.name] = element.value;
            element.disabled = true;
        }
    }

    $.post('/tools/roodschopper.php', params)
        .done(function (data) {
            if (actie === 'verzenden') {
                window.location.href = '/htdocs/tools/roodschopper.php';
            } else {
                let div = $('#messageContainer');
                div.html(data);
                div.show();
                $('#submitContainer').hide();
            }
        });
};

/**
 * @see htdocs/tools/roodschopper.php
 */
window.restoreRoodschopper = function() {
    let form = document.getElementById('roodschopper');
    for (let i = 0; i < form.elements.length; i++) {
        let element = form.elements[i];
        if (element.type === 'select-one' || element.type === 'text' || element.type === 'textarea') {
            element.disabled = false;
        }
    }
    $('#submitContainer').show();
    $('#messageContainer').hide();
};
