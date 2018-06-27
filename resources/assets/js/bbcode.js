import $ from 'jquery';
import initContext from './context';

/**
 * @see templates/courant/courantbeheer.tpl
 * @see templates/forum/post_forum.tpl
 * @see templates/mededelingen/mededeling.tpl
 * @see templates/roodschopper/roodschopper.tpl
 * @see forum.js
 * @see view/formulier/invoervelden/BBCodeField.class.php
 * @param sourceId
 * @param targetId
 * @constructor
 */
export const CsrBBPreview = (sourceId, targetId) => {
    if (sourceId.charAt(0) !== '#') {
        sourceId = `#${sourceId}`;
    }
    if (targetId.charAt(0) !== '#') {
        targetId = `#${targetId}`;
    }
    let bbcode = $(sourceId).val();
    if (typeof bbcode !== 'string' || bbcode.trim() === '') {
        $(targetId).html('').hide();
        return;
    }
    $.post('/tools/bbcode.php', {
        data: encodeURIComponent(bbcode)
    }).done((data) => {
        $(targetId).html(data);
        initContext($(targetId));
        $(targetId).show();
    }).fail((error) => {
        alert(error);
    });
};

/**
 * @see view/bbcode/CsrBB.class.php
 * @param elem
 * @returns {boolean}
 */
export const bbvideoDisplay = function(elem) {
    let $previewdiv = $(elem);
    let params = $previewdiv.data('params');
    if(params.iframe) {
        //vervang de thumb door een iframe
        $previewdiv.parent().html(
            '<iframe frameborder="0" width="' + params.width + '" height="' + params.height + '" src="' + params.src + '" allowfullscreen></iframe>'
        );
    } else {
        //godtube is nog flash (of alternatief iets met javascript, maar geen iframe versie beschikbaar)
        $previewdiv.parent().html(
            '<object type="application/x-shockwave-flash" data="http://www.godtube.com/resource/mediaplayer/5.3/player.swf"  width="' + params.width + '" height="' + params.height + '">' +
            '<param name="allowfullscreen" value="true" />' +
            '<param name="allowscriptaccess" value="always" />' +
            '<param name="wmode" value="opaque" />' +
            '<param name="movie" value="http://www.godtube.com/resource/mediaplayer/5.3/player.swf" />' +
            '<param name="autostart" value="true" />' +
            '<param name="flashvars" value="file=http://www.godtube.com/resource/mediaplayer/' + params.id + '.file' +
            '&image=http://www.godtube.com/resource/mediaplayer/' + params.id + '.jpg' +
            '&screencolor=000000' +
            '&type=video' +
            '&autostart=true' +
            '&playonce=true' +
            '&skin=http://www.godtube.com//resource/mediaplayer/skin/carbon/carbon.zip' +
            '&logo.file=http://media.salemwebnetwork.com/godtube/theme/default/media/embed-logo.png' +
            '&logo.link=http://www.godtube.com/watch/?v=' + params.id +
            '&logo.position=top-left' +
            '&logo.hide=false' +
            '&controlbar.position=over">' +
            '</object>'
        );
    }

    return false;
};