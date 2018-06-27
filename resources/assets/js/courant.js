import $ from 'jquery';

/**
 * @param {string} id
 */
export function importAgenda(id) {
    $.ajax({
        type: 'POST',
        cache: false,
        url: '/agenda/courant/',
        data: ''
    }).done((data) => document.getElementById(id).value += '\n' + data);
}
