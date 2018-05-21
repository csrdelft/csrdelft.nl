import $ from 'jquery';

/**
 * @param {string} peiling
 */
export function peilingBevestigStem(peiling) {
    let id = $('input[name=optie]:checked', peiling).val();
    let waarde = $(`#label${id}`).text();
    if (waarde.length > 0 && confirm('Bevestig uw stem:\n\n' + waarde + '\n\n')) {
        $(peiling).trigger('submit');
    }
}