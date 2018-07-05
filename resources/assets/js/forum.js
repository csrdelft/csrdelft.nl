import $ from 'jquery';
import {CsrBBPreview} from './bbcode';
import {domUpdate} from './context';
import {bbCodeSet} from './bbcode-set';

function toggleForumConceptBtn(enable) {
    let $concept = $('#forumConcept');
    if (typeof enable === 'undefined') {
        $concept.attr('disabled', !$concept.prop('disabled'));
    } else {
        $concept.attr('disabled', !enable);
    }
}

export function saveConceptForumBericht() {
    toggleForumConceptBtn(false);
    let $concept = $('#forumConcept');
    let $textarea = $('#forumBericht');
    let $titel = $('#nieuweTitel');
    if ($textarea.val() !== $textarea.attr('origvalue')) {
        $.post($concept.attr('data-url'), {
            forumBericht: $textarea.val(),
            titel: ($titel.length === 1 ? $titel.val() : '')
        }).done(function () {
            $textarea.attr('origvalue', $textarea.val());
        }).fail(function(error) {
            alert(error);
        });
    }
    setTimeout(toggleForumConceptBtn, 3000);
}

let bewerkContainer = null;
let bewerkContainerInnerHTML = null;
/**
 * @see inline in forumBewerken
 */
function restorePost() {
    bewerkContainer.html(bewerkContainerInnerHTML);
    $('#bewerk-melding').slideUp(200, function() {
        $(this).remove();
    });
    $('#forumPosten').css('visibility', 'visible');
}

function submitPost() {
    let form = $('#forumEditForm');
    $.ajax({
        type: 'POST',
        cache: false,
        url: form.attr('action'),
        data: form.serialize()
    }).done((data) => {
        restorePost();
        domUpdate(data);
    }).fail((jqXHR) => alert(jqXHR.responseJSON));
}

/**
 * Een post bewerken in het forum.
 * Haal een post op, bouw een formuliertje met javascript.
 *
 * @see templates/forum/post_lijst.tpl
 */
export function forumBewerken(postId) {
    $.ajax({
        url: '/forum/tekst/' + postId,
        method: 'POST'
    }).done((data) => {
        if (document.getElementById('forumEditForm')) {
            restorePost();
        }
        bewerkContainer = $('#post' + postId);
        bewerkContainerInnerHTML = bewerkContainer.html();
        let bewerkForm = `<form id="forumEditForm" class="Formulier" action="/forum/bewerken/${postId}" method="post">` +
            '<div id="bewerkPreview" class="preview forumBericht"></div>' +
            '<textarea name="forumBericht" id="forumBewerkBericht" class="FormElement BBCodeField" rows="8"></textarea>' +
            'Reden van bewerking: <input type="text" name="reden" id="forumBewerkReden"/><br /><br />' +
            '<div class="float-right"><a href="/wiki/cie:diensten:forum" target="_blank">Opmaakhulp</a></div>' +
            '<input type="button" class="opslaan" value="Opslaan" /> ' +
            '<input type="button" class="voorbeeld" value="Voorbeeld" /> ' +
            '<input type="button" class="annuleren" value="Annuleren" /> ' +
            '</form>';
        bewerkContainer.html(bewerkForm);
        bewerkContainer.find('input.opslaan').on('click', submitPost);
        bewerkContainer.find('input.voorbeeld').on('click', CsrBBPreview.bind(null, 'forumBewerkBericht', 'bewerkPreview'));
        bewerkContainer.find('input.annuleren').on('click', restorePost);

        let $forumBewerkBericht = $('#forumBewerkBericht');
        $forumBewerkBericht.val(data);
        $forumBewerkBericht.autosize();
        $forumBewerkBericht.markItUp(bbCodeSet);
        $(bewerkContainer).parent().children('td.auteur:first').append('<div id="bewerk-melding">Als u dingen aanpast zet er dan even bij w&aacute;t u aanpast! Gebruik bijvoorbeeld [s]...[/s]</div>');
        $('#bewerk-melding').slideDown(200);
        $('#forumPosten').css('visibility', 'hidden');
    });
    return false;
}

function forumCiteren(postId) {
    $.ajax({
        url: '/forum/citeren/' + postId,
        method: 'POST'
    }).done((data) => {
        let bericht = $('#forumBericht');
        bericht.val(bericht.val() + data);
        $(window).scrollTo('#reageren');
    });
    // We returnen altijd false, dan wordt de href= van <a> niet meer uitgevoerd.
    // Het werkt dan dus nog wel als javascript uit staat.
    return false;
}

$(function () {

    let $textarea = $('#forumBericht');
    let $concept = $('#forumConcept');

    // The last value that we pinged
    let lastPing = null;
    if ($concept.length === 1) {

        /*var ping = */setInterval(() => {
            let pingValue = $textarea.val() !== $textarea.attr('origvalue');
            if (pingValue !== false || lastPing !== false) {
                $.post($concept.attr('data-url'), {
                    ping: pingValue
                }).done(domUpdate).fail((error) => console.log(error));
                lastPing = pingValue;
            }
        }, 60000);
        /*var autosave;
         $textarea.focusin(function () {
         autosave = setInterval(saveConceptForumBericht, 3000);
         });
         $textarea.focusout(function () {
         clearInterval(autosave);
         });*/
    }

    // naar juiste forumreactie scrollen door hash toe te voegen
    if (!window.location.hash && window.location.pathname.substr(0, 15) === '/forum/reactie/') {
        let reactieid = parseInt(window.location.pathname.substr(15), 10);
        window.location.hash = '#' + reactieid;
    }

    $textarea.on('keyup', (event) => {
        if (event.keyCode === 13) { // enter
            CsrBBPreview('forumBericht', 'berichtPreview');
        }
    });

    let $nieuweTitel = $('#nieuweTitel');

    if ($nieuweTitel.length !== 0) {
        let $draadMelding = $('#draad-melding');
        $nieuweTitel.on('focusin', () => $draadMelding.slideDown(200));
        $nieuweTitel.on('focusout', () => $draadMelding.slideUp(200));
    }

		$('.togglePasfoto').on('click', function () {
			$(this).parent().find('.forumpasfoto').toggleClass('verborgen');
		});

    $('.auteur').hoverIntent(
        function () {
            $(this).find('a.forummodknop').css('opacity', '1');
        },
        function () {
            $(this).find('a.forummodknop').css('opacity', '0');
        }
    );

    $('a.citeren').on('click', function () {
        let postid = $(this).attr('data-citeren');
        forumCiteren(postid);
    });
});
