

function bbvideoDisplay(elem) {
    var $previewdiv = jQuery(elem);
    var params = $previewdiv.data('params');
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
}