import $ from 'jquery';
import {CsrBBPreview} from './bbcode';
import {domUpdate} from './context';

function toggleForumConceptBtn(enable) {
    let $concept = $('#forumConcept');
    if (typeof enable === 'undefined') {
        $concept.attr('disabled', !$concept.prop('disabled'));
    } else {
        $concept.attr('disabled', !enable);
    }
}

window.saveConceptForumBericht = () => {
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
};

let bewerkContainer = null;
let bewerkContainerInnerHTML = null;
/**
 * @see inline in forumBewerken
 */
window.restorePost = function() {
    bewerkContainer.innerHTML = bewerkContainerInnerHTML;
    $('#bewerk-melding').slideUp(200, function() {
        $(this).remove();
    });
    $('#forumPosten').css('visibility', 'visible');
};

/**
 * Een post bewerken in het forum.
 * Haal een post op, bouw een formuliertje met javascript.
 *
 * @see templates/forum/post_lijst.tpl
 */
window.forumBewerken = function(postId) {
    $.ajax({
        url: '/forum/tekst/' + postId,
        method: 'POST'
    }).done((data) => {
        if (document.getElementById('forumEditForm')) {
            window.restorePost();
        }
        bewerkContainer = document.getElementById('post' + postId);
        bewerkContainerInnerHTML = bewerkContainer.innerHTML;
        let bewerkForm = `<form id="forumEditForm" class="Formulier" action="/forum/bewerken/${postId}" method="post">`;
        bewerkForm += '<div id="bewerkPreview" class="preview forumBericht"></div>';
        bewerkForm += '<textarea name="forumBericht" id="forumBewerkBericht" class="FormElement BBCodeField" rows="8"></textarea>';
        bewerkForm += 'Reden van bewerking: <input type="text" name="reden" id="forumBewerkReden"/><br /><br />';
        bewerkForm += '<div class="float-right"><a href="/wiki/cie:diensten:forum" target="_blank">Opmaakhulp</a></div>';
        bewerkForm += '<input type="button" value="Opslaan" onclick="submitPost();" /> <input type="button" value="Voorbeeld" onclick="CsrBBPreview(\'forumBewerkBericht\', \'bewerkPreview\');" /> <input type="button" value="Annuleren" onclick="window.restorePost();" />';
        bewerkForm += '</form>';
        bewerkContainer.innerHTML = bewerkForm;
        let $forumBewerkBericht = $('#forumBewerkBericht');
        $forumBewerkBericht.val(data);
        $forumBewerkBericht.autosize();
        $forumBewerkBericht.markItUp(require('./bbcode-set')); // CsrBBcodeMarkItUpSet is located in: /layout/js/markitup/sets/bbcode/set.js
        $(bewerkContainer).parent().children('td.auteur:first').append('<div id="bewerk-melding">Als u dingen aanpast zet er dan even bij w&aacute;t u aanpast! Gebruik bijvoorbeeld [s]...[/s]</div>');
        $('#bewerk-melding').slideDown(200);
        $('#forumPosten').css('visibility', 'hidden');
    });
    return false;
};

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

/**
 * Wordt in gegenereerde code gebruikt.
 */
window.submitPost = () => {
    let form = $('#forumEditForm');
    $.ajax({
        type: 'POST',
        cache: false,
        url: form.attr('action'),
        data: form.serialize()
    }).done((data) => {
        window.restorePost();
        domUpdate(data);
    }).fail(jqXHR => alert(jqXHR.responseJSON));
};

$(function ($) {

    let $textarea = $('#forumBericht');
    let $concept = $('#forumConcept');

    if ($concept.length === 1) {

        /*var ping = */setInterval(() => {
            $.post($concept.attr('data-url'), {
                ping: ($textarea.val() !== $textarea.attr('origvalue'))
            }).done(domUpdate).fail(error => alert(error));
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

    $textarea.keyup((event) => {
        if (event.keyCode === 13) { // enter
            CsrBBPreview('forumBericht', 'berichtPreview');
        }
    });

    let $nieuweTitel = $('#nieuweTitel');

    if ($nieuweTitel.length !== 0) {
        let $draadMelding = $('#draad-melding');
        $nieuweTitel.focusin(() => $draadMelding.slideDown(200));
        $nieuweTitel.focusout(() => $draadMelding.slideUp(200));
    }

    $('.togglePasfoto').each(function () {
        $(this).click(function () {
            let parts = $(this).attr('id').substr(1).split('-');
            let pasfoto = $('#p' + parts[1]);
            if (pasfoto.html() === '') {
                pasfoto.html('<img src=' + parts[0] + '"/htdocs/tools/pasfoto/.png" class="pasfoto" />');
            }
            if (pasfoto.hasClass('verborgen')) {
                pasfoto.toggleClass('verborgen');
                $(this).html('');
            }
        });
    });

    $('td.auteur').hoverIntent(
        function () {
            $(this).find('a.forummodknop').fadeIn();
        },
        function () {
            $(this).find('a.forummodknop').fadeOut();
        }
    );

    $('a.citeren').click(function () {
        let postid = $(this).attr('data-citeren');
        forumCiteren(postid);
    });
});
